<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * PelangganController
 *
 * Full CRUD for the Customer master.
 * Key features:
 *   - Auto-generates kode_pelanggan (PLG-0001, PLG-0002, …)
 *     using withoutGlobalScope to get the correct sequence number
 *     scoped to the current toko only.
 *   - Status pelanggan (Umum / Member / Rekan / Motoris) drives
 *     which price tier column is used in POS and PenjualanController.
 *   - Destroy guard: blocks deletion if the customer has any sales records.
 *   - All queries are tenant-filtered by the model's Global Scope.
 */
class PelangganController extends Controller
{
    /**
     * Display a paginated, searchable customer list.
     */
    public function index(Request $request): View
    {
        $q = $request->get('q', '');

        $pelanggans = Pelanggan::when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_pelanggan', 'like', "%{$q}%")
                        ->orWhere('kode_pelanggan', 'like', "%{$q}%")
                        ->orWhere('no_telepon', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_pelanggan')
            ->paginate(20)
            ->withQueryString();

        return view('pages.master.pelanggan.index', compact('pelanggans', 'q'));
    }

    /**
     * Store a newly created customer.
     *
     * kode_pelanggan is auto-generated here; it is NOT accepted from the form.
     * The format PLG-XXXX is scoped per toko (different shops have independent sequences).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelanggan'   => ['required', 'string', 'max:100'],
            'no_telepon'       => ['required', 'string', 'max:20'],
            'alamat'           => ['nullable', 'string', 'max:500'],
            'status_pelanggan' => ['required', Rule::in(['Umum', 'Member', 'Rekan', 'Motoris'])],
        ], [
            'nama_pelanggan.required'   => 'Nama pelanggan wajib diisi.',
            'no_telepon.required'       => 'No. telepon wajib diisi.',
            'status_pelanggan.required' => 'Status pelanggan wajib dipilih.',
            'status_pelanggan.in'       => 'Status pelanggan tidak valid.',
        ]);

        $validated['kode_pelanggan'] = $this->generateKodePelanggan();

        Pelanggan::create($validated);

        return redirect()
            ->route('master.pelanggan.index')
            ->with('success', "Pelanggan \"{$validated['nama_pelanggan']}\" ({$validated['kode_pelanggan']}) berhasil ditambahkan.");
    }

    /**
     * Update an existing customer.
     * kode_pelanggan is immutable after creation — not included in update.
     * status_pelanggan CAN be changed (e.g., upgrading Umum → Member).
     */
    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelanggan'   => ['required', 'string', 'max:100'],
            'no_telepon'       => ['required', 'string', 'max:20'],
            'alamat'           => ['nullable', 'string', 'max:500'],
            'status_pelanggan' => ['required', Rule::in(['Umum', 'Member', 'Rekan', 'Motoris'])],
        ], [
            'nama_pelanggan.required'   => 'Nama pelanggan wajib diisi.',
            'no_telepon.required'       => 'No. telepon wajib diisi.',
            'status_pelanggan.required' => 'Status pelanggan wajib dipilih.',
            'status_pelanggan.in'       => 'Status pelanggan tidak valid.',
        ]);

        $pelanggan->update($validated);

        return redirect()
            ->route('master.pelanggan.index')
            ->with('success', "Data pelanggan \"{$validated['nama_pelanggan']}\" berhasil diperbarui.");
    }

    /**
     * Delete a customer.
     *
     * Guard: Cannot delete if the customer has any sales transactions.
     * This preserves the referential integrity of penjualan records
     * and historical sales reports.
     */
    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        if ($pelanggan->penjualans()->exists()) {
            return redirect()
                ->route('master.pelanggan.index')
                ->with('error', "Pelanggan \"{$pelanggan->nama_pelanggan}\" tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.");
        }

        $nama = $pelanggan->nama_pelanggan;
        $pelanggan->delete();

        return redirect()
            ->route('master.pelanggan.index')
            ->with('success', "Pelanggan \"{$nama}\" berhasil dihapus.");
    }

    // ─── Private Helpers ────────────────────────────────────────────────

    /**
     * Generate the next available kode_pelanggan for the current toko.
     *
     * Format: PLG-XXXX (e.g., PLG-0001, PLG-0042)
     *
     * Uses withoutGlobalScope('tenant') so we can inspect ALL pelanggan
     * rows for this specific toko regardless of any additional scope filters,
     * then manually scope to the current toko_id. This ensures the sequence
     * counter is per-toko and does not increment across tenants.
     *
     * @return string
     */
    private function generateKodePelanggan(): string
    {
        $tokoId = auth()->user()->toko_id;

        // Get the highest existing code for this toko
        $lastKode = Pelanggan::withoutGlobalScope('tenant')
            ->where('toko_id', $tokoId)
            ->where('kode_pelanggan', 'like', 'PLG-%')
            ->orderByDesc('id')
            ->value('kode_pelanggan');

        // Extract the numeric part and increment
        $nextNumber = $lastKode
            ? ((int) substr($lastKode, 4)) + 1
            : 1;

        return 'PLG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
