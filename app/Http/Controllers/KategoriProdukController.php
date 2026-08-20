<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\KelompokProduk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * KategoriProdukController
 *
 * CRUD for the second-level product categorization.
 * Each kategori belongs to one kelompok (Level 1 grouping).
 *
 * Also exposes a JSON endpoint (getByKelompok) for the chained
 * dropdown AJAX call on the Produk form.
 *
 * All queries are automatically tenant-filtered via the Global Scope
 * registered in the KategoriProduk model's booted() method.
 */
class KategoriProdukController extends Controller
{
    /**
     * Display a paginated list of kategori with an optional search filter.
     * Eager-loads the parent kelompok to display in the table.
     */
    public function index(Request $request): View
    {
        $q = $request->get('q', '');

        $kategoris = KategoriProduk::with('kelompok')
            ->withCount('produks')
            ->when($q, fn ($query) => $query->where('nama_kategori', 'like', "%{$q}%"))
            ->orderBy('nama_kategori')
            ->paginate(15)
            ->withQueryString();

        // Pass all kelompok for the create form dropdown
        $kelompoks = KelompokProduk::orderBy('nama_kelompok')->get();

        return view('pages.master.kategori.index', compact('kategoris', 'kelompoks', 'q'));
    }

    /**
     * Store a newly created kategori.
     * toko_id is auto-assigned by the model's "creating" event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelompok_id'    => ['required', 'integer', 'exists:kelompok_produk,id'],
            'nama_kategori'  => ['required', 'string', 'max:50'],
        ], [
            'kelompok_id.required'   => 'Kelompok wajib dipilih.',
            'kelompok_id.exists'     => 'Kelompok yang dipilih tidak valid.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori maksimal 50 karakter.',
        ]);

        KategoriProduk::create($validated);

        return redirect()
            ->route('master.kategori-produk.index')
            ->with('success', "Kategori \"{$validated['nama_kategori']}\" berhasil ditambahkan.");
    }

    /**
     * Update an existing kategori.
     * Model binding automatically applies the tenant Global Scope.
     */
    public function update(Request $request, KategoriProduk $kategoriProduk): RedirectResponse
    {
        $validated = $request->validate([
            'kelompok_id'    => ['required', 'integer', 'exists:kelompok_produk,id'],
            'nama_kategori'  => ['required', 'string', 'max:50'],
        ], [
            'kelompok_id.required'   => 'Kelompok wajib dipilih.',
            'kelompok_id.exists'     => 'Kelompok yang dipilih tidak valid.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori maksimal 50 karakter.',
        ]);

        $kategoriProduk->update($validated);

        return redirect()
            ->route('master.kategori-produk.index')
            ->with('success', "Kategori \"{$validated['nama_kategori']}\" berhasil diperbarui.");
    }

    /**
     * Delete a kategori.
     *
     * Guards:
     *   - Cannot delete if there are products using this kategori.
     *     (The DB also has RESTRICT FK, so this gives a clean UI error first.)
     */
    public function destroy(KategoriProduk $kategoriProduk): RedirectResponse
    {
        if ($kategoriProduk->produks()->exists()) {
            return redirect()
                ->route('master.kategori-produk.index')
                ->with('error', "Kategori \"{$kategoriProduk->nama_kategori}\" tidak dapat dihapus karena masih memiliki produk. Pindahkan produk ke kategori lain terlebih dahulu.");
        }

        $nama = $kategoriProduk->nama_kategori;
        $kategoriProduk->delete();

        return redirect()
            ->route('master.kategori-produk.index')
            ->with('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }

    /**
     * AJAX: Return categories that belong to a specific kelompok.
     *
     * Used by the chained dropdown on the Produk create/edit form.
     * The Global Scope on KategoriProduk automatically filters by
     * the authenticated user's toko_id, so no cross-tenant leakage.
     *
     * GET /api/master/kategori-by-kelompok?kelompok_id={id}
     *
     * @return JsonResponse
     */
    public function getByKelompok(Request $request): JsonResponse
    {
        $request->validate([
            'kelompok_id' => ['required', 'integer'],
        ]);

        $kategoris = KategoriProduk::where('kelompok_id', $request->get('kelompok_id'))
            ->select(['id', 'nama_kategori'])
            ->orderBy('nama_kategori')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $kategoris,
        ]);
    }
}
