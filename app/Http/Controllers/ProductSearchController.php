<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProductSearchController
 *
 * Provides a real-time AJAX search endpoint for the POS Quick-Entry mode.
 * Called when the kasir types ≥ 2 characters in the Produk input field,
 * or when a barcode scanner submits a code instantly.
 *
 * The Global Scope on the Produk model automatically applies the
 * `toko_id` filter, keeping all results tenant-isolated.
 *
 * The response includes all four price tiers so the client-side JavaScript
 * can immediately select and display the correct price badge based on the
 * locked pelanggan.status_pelanggan without a second round-trip.
 *
 * Endpoint : GET /api/pos/search-produk?q={query}
 * Auth     : Required (session / sanctum)
 * Returns  : JSON array of matching products (max 10 rows)
 */
class ProductSearchController extends Controller
{
    /**
     * Handle the AJAX product search request.
     *
     * Query Logic:
     *   - Requires minimum 2 characters to trigger (validated below).
     *   - Matches against `nama_produk` for keyboard-typed name searches.
     *   - OR `barcode` for barcode scanner input (exact prefix match works
     *     here since scanners submit full codes, but LIKE handles partial).
     *   - Wrapped in a closure to keep the OR group AND-ed with the tenant
     *     Global Scope, preventing any cross-tenant data leakage.
     *
     * Out-of-Stock Behavior:
     *   - Products with stok = 0 are included in results but the client
     *     JavaScript will prevent them from being added (stock validation).
     *   - stok is returned so the client can display a disabled/warning state.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $query = trim($request->get('q'));

        $produk = Produk::select([
                'id',
                'barcode',
                'nama_produk',
                'satuan',
                'stok',
                'stok_minimum',
                'harga_jual_umum',
                'harga_jual_member',
                'harga_jual_rekan',
                'harga_jual_motoris',
            ])
            ->where(function ($builder) use ($query) {
                $builder->where('nama_produk', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->orderByRaw("
                CASE
                    WHEN barcode = ? THEN 0
                    WHEN barcode LIKE ? THEN 1
                    WHEN nama_produk LIKE ? THEN 2
                    ELSE 3
                END
            ", [
                $query,           // Exact barcode match → highest priority (scanner)
                $query . '%',     // Barcode prefix match
                $query . '%',     // Name prefix match
            ])
            ->limit(10)
            ->get()
            ->map(function (Produk $item) {
                return [
                    'id'                  => $item->id,
                    'barcode'             => $item->barcode,
                    'nama_produk'         => $item->nama_produk,
                    'satuan'              => $item->satuan,
                    'stok'                => $item->stok,
                    'stok_minimum'        => $item->stok_minimum,
                    'is_stok_rendah'      => $item->isStokRendah(),
                    'harga_jual_umum'     => (float) $item->harga_jual_umum,
                    'harga_jual_member'   => (float) $item->harga_jual_member,
                    'harga_jual_rekan'    => (float) $item->harga_jual_rekan,
                    'harga_jual_motoris'  => (float) $item->harga_jual_motoris,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $produk,
        ]);
    }
}
