<?php

namespace App\Http\Controllers;

use App\Models\KelompokProduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * KelompokProdukController
 *
 * CRUD for the top-level product grouping (department level).
 * All queries are automatically tenant-filtered via the Global Scope
 * registered in the KelompokProduk model's booted() method.
 * toko_id is auto-assigned by the model's "creating" event.
 */
class KelompokProdukController extends Controller
{
    /**
     * Display a paginated list of kelompok with an optional search filter.
     */
    public function index(Request $request): View
    {
        $q = $request->get('q', '');

        $kelompoks = KelompokProduk::withCount('kategoriProduks')
            ->when($q, fn ($query) => $query->where('nama_kelompok', 'like', "%{$q}%"))
            ->orderBy('nama_kelompok')
            ->paginate(15)
            ->withQueryString();

        return view('pages.master.kelompok.index', compact('kelompoks', 'q'));
    }

    /**
     * Store a newly created kelompok.
     * toko_id is auto-assigned by the model's "creating" event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelompok' => ['required', 'string', 'max:50'],
        ], [
            'nama_kelompok.required' => 'Nama kelompok wajib diisi.',
            'nama_kelompok.max'      => 'Nama kelompok maksimal 50 karakter.',
        ]);

        KelompokProduk::create($validated);

        return redirect()
            ->route('master.kelompok-produk.index')
            ->with('success', "Kelompok \"{$validated['nama_kelompok']}\" berhasil ditambahkan.");
    }

    /**
     * Update an existing kelompok.
     * Model binding automatically applies the tenant Global Scope,
     * so only kelompok belonging to the current toko can be accessed.
     */
    public function update(Request $request, KelompokProduk $kelompokProduk): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelompok' => ['required', 'string', 'max:50'],
        ], [
            'nama_kelompok.required' => 'Nama kelompok wajib diisi.',
            'nama_kelompok.max'      => 'Nama kelompok maksimal 50 karakter.',
        ]);

        $kelompokProduk->update($validated);

        return redirect()
            ->route('master.kelompok-produk.index')
            ->with('success', "Kelompok \"{$validated['nama_kelompok']}\" berhasil diperbarui.");
    }

    /**
     * Delete a kelompok.
     *
     * Guards:
     *   - Cannot delete if there are kategori beneath it.
     *     (Prevents orphaned kategori; user must reassign first.)
     */
    public function destroy(KelompokProduk $kelompokProduk): RedirectResponse
    {
        if ($kelompokProduk->kategoriProduks()->exists()) {
            return redirect()
                ->route('master.kelompok-produk.index')
                ->with('error', "Kelompok \"{$kelompokProduk->nama_kelompok}\" tidak dapat dihapus karena masih memiliki kategori. Hapus atau pindahkan kategorinya terlebih dahulu.");
        }

        $nama = $kelompokProduk->nama_kelompok;
        $kelompokProduk->delete();

        return redirect()
            ->route('master.kelompok-produk.index')
            ->with('success', "Kelompok \"{$nama}\" berhasil dihapus.");
    }
}
