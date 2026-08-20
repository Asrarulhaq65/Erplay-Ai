<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PelangganSearchController
 *
 * Provides a real-time AJAX search endpoint for the POS Quick-Entry mode.
 * Called when the kasir types in the Pelanggan input field.
 *
 * The Global Scope on the Pelanggan model automatically applies the
 * `toko_id` filter, so results are always tenant-isolated without any
 * additional manual filtering in this controller.
 *
 * Endpoint : GET /api/pos/search-pelanggan?q={query}
 * Auth     : Required (session / sanctum)
 * Returns  : JSON array of matching customers (max 10 rows)
 */
class PelangganSearchController extends Controller
{
    /**
     * Handle the AJAX search request.
     *
     * Query Logic:
     *   - Matches against `nama_pelanggan` (for name-based typing)
     *   - OR `kode_pelanggan` (for code-based lookup or barcode scanner input)
     *   - Both use LIKE with leading and trailing wildcards for partial matching
     *   - Wrapped in a closure so the tenant Global Scope `toko_id` WHERE clause
     *     is AND-ed with the OR group, not short-circuited by it.
     *
     * Empty / Short Query Behavior:
     *   - If 'q' is empty or not provided, returns the 10 most recently
     *     added customers so the dropdown still shows useful options.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim($request->get('q', ''));

        $pelanggan = Pelanggan::select([
                'id',
                'kode_pelanggan',
                'nama_pelanggan',
                'status_pelanggan',
            ])
            ->when(
                $query !== '',
                // If search query is provided: filter by name or code
                fn ($builder) => $builder->where(function ($sub) use ($query) {
                    $sub->where('nama_pelanggan', 'like', "%{$query}%")
                        ->orWhere('kode_pelanggan', 'like', "%{$query}%");
                }),
                // If query is empty: return latest 10 as a default shortlist
                fn ($builder) => $builder->latest()
            )
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $pelanggan,
        ]);
    }
}
