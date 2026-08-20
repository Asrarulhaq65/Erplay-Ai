<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SupplierController
 *
 * Full CRUD for the Supplier/Vendor master data.
 * Features:
 *   - Search by nama_supplier, nama_kontak, or no_telepon.
 *   - Auto tenant isolation via Supplier model Global Scope.
 *   - Destroy guard: blocks deletion if supplier is linked to purchase records.
 */
class SupplierController extends Controller
{
    /**
     * Display a paginated, searchable supplier list.
     */
    public function index(Request $request): View
    {
        $q = $request->get('q', '');

        $suppliers = Supplier::withCount('pembelians')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_supplier', 'like', "%{$q}%")
                        ->orWhere('nama_kontak', 'like', "%{$q}%")
                        ->orWhere('no_telepon', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_supplier')
            ->paginate(20)
            ->withQueryString();

        return view('pages.master.supplier.index', compact('suppliers', 'q'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:100'],
            'nama_kontak'   => ['nullable', 'string', 'max:100'],
            'no_telepon'    => ['required', 'string', 'max:20'],
            'alamat'        => ['nullable', 'string', 'max:500'],
        ], [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
            'no_telepon.required'    => 'No. telepon wajib diisi.',
        ]);

        Supplier::create($validated);

        return redirect()
            ->route('master.supplier.index')
            ->with('success', "Supplier \"{$validated['nama_supplier']}\" berhasil ditambahkan.");
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:100'],
            'nama_kontak'   => ['nullable', 'string', 'max:100'],
            'no_telepon'    => ['required', 'string', 'max:20'],
            'alamat'        => ['nullable', 'string', 'max:500'],
        ], [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
            'no_telepon.required'    => 'No. telepon wajib diisi.',
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('master.supplier.index')
            ->with('success', "Data supplier \"{$validated['nama_supplier']}\" berhasil diperbarui.");
    }

    /**
     * Delete a supplier.
     * Guard: Cannot delete if linked to purchase records.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->pembelians()->exists()) {
            return redirect()
                ->route('master.supplier.index')
                ->with('error', "Supplier \"{$supplier->nama_supplier}\" tidak dapat dihapus karena sudah memiliki riwayat transaksi pembelian.");
        }

        $nama = $supplier->nama_supplier;
        $supplier->delete();

        return redirect()
            ->route('master.supplier.index')
            ->with('success', "Supplier \"{$nama}\" berhasil dihapus.");
    }
}
