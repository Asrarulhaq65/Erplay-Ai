<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\LogStok;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PembelianController extends Controller
{
    /**
     * Display a listing of the purchase history.
     */
    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'user'])
                    ->withCount('details')
                    ->orderBy('tanggal_beli', 'desc')
                    ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nomor_faktur', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('nama_supplier', 'like', "%{$search}%");
                  });
        }

        if ($request->has('start_date') && $request->has('end_date') && $request->start_date != '' && $request->end_date != '') {
            $query->whereBetween('tanggal_beli', [$request->start_date, $request->end_date]);
        }

        $pembelians = $query->paginate(15)->withQueryString();

        return view('pages.pembelian.index', compact('pembelians'));
    }

    /**
     * Display the specified purchase details.
     */
    public function show($id)
    {
        $pembelian = Pembelian::with(['supplier', 'user', 'details.produk'])->findOrFail($id);
        return view('pages.pembelian.show', compact('pembelian'));
    }

    /**
     * Display the purchase order input interface.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        // Load all products, only need specific fields for frontend JS array
        $products = Produk::select(['id', 'barcode', 'nama_produk', 'harga_modal', 'stok'])->orderBy('nama_produk')->get();
        
        return view('pages.pembelian.form', compact('suppliers', 'products'));
    }

    /**
     * Process and store the purchase order payload.
     * Handles stock increments, cost price updates, logging, and cash flow.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id'        => ['required', 'integer', 'exists:supplier,id'],
            'nomor_faktur'       => ['required', 'string', 'max:100'],
            'tanggal_beli'       => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:produk,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.harga_modal'=> ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required'        => 'Supplier wajib dipilih.',
            'supplier_id.exists'          => 'Supplier tidak valid.',
            'nomor_faktur.required'       => 'No. Faktur wajib diisi.',
            'tanggal_beli.required'       => 'Tanggal pembelian wajib diisi.',
            'items.required'              => 'Minimal 1 item barang harus dimasukkan.',
            'items.min'                   => 'Minimal 1 item barang harus dimasukkan.',
            'items.*.product_id.required' => 'ID produk tidak valid.',
            'items.*.product_id.exists'   => 'Produk tidak ditemukan di database.',
            'items.*.qty.required'        => 'Qty wajib diisi.',
            'items.*.qty.min'             => 'Qty minimal 1.',
            'items.*.harga_modal.required'=> 'Harga modal wajib diisi.',
            'items.*.harga_modal.min'     => 'Harga modal tidak boleh negatif.',
        ]);

        try {
            DB::beginTransaction();

            $totalPembelian = 0;
            $resolvedItems = [];

            // Pre-process items to calculate totals
            foreach ($validated['items'] as $item) {
                $qty = (int) $item['qty'];
                $hargaBeliSatuan = (float) $item['harga_modal'];
                $subtotal = $qty * $hargaBeliSatuan;
                
                $resolvedItems[] = [
                    'product_id'        => $item['product_id'],
                    'qty'               => $qty,
                    'harga_beli_satuan' => $hargaBeliSatuan,
                    'subtotal'          => $subtotal,
                ];

                $totalPembelian += $subtotal;
            }

            // Insert Pembelian Header
            $pembelian = Pembelian::create([
                'supplier_id'       => $validated['supplier_id'],
                'nomor_faktur'      => $validated['nomor_faktur'],
                'total_pembelian'   => $totalPembelian,
                'metode_pembayaran' => 'Tunai', // Hardcoded as tunai per specs, or add to payload if needed. Assuming Tunai for cashflow.
                'status_pembayaran' => 'Lunas',
                'tanggal_beli'      => $validated['tanggal_beli'],
                'user_id'           => auth()->user()?->id,
            ]);

            // Process each item
            foreach ($resolvedItems as $item) {
                // Lock the product row for update to prevent concurrent stock modifications
                $produk = Produk::lockForUpdate()->find($item['product_id']);

                if (!$produk) {
                    throw new \RuntimeException("Produk ID {$item['product_id']} tidak ditemukan.");
                }

                $stokAwal = $produk->stok;
                $stokAkhir = $stokAwal + $item['qty'];

                // Insert detail row
                PembelianDetail::create([
                    'pembelian_id'      => $pembelian->id,
                    'produk_id'         => $produk->id,
                    'harga_beli_satuan' => $item['harga_beli_satuan'],
                    'qty'               => $item['qty'],
                    'subtotal'          => $item['subtotal'],
                ]);

                // Update Product Stock and Cost Price (Last Cost strategy)
                $produk->update([
                    'stok'        => $stokAkhir,
                    'harga_modal' => $item['harga_beli_satuan']
                ]);

                // Insert Stock Log
                LogStok::create([
                    'produk_id'      => $produk->id,
                    'tipe_perubahan' => 'Masuk_Barang',
                    'jumlah'         => $item['qty'],
                    'stok_awal'      => $stokAwal,
                    'stok_akhir'     => $stokAkhir,
                    'keterangan'     => "Pembelian Faktur: {$validated['nomor_faktur']}",
                ]);
            }

            // Record Cash Outflow (Arus Kas)
            ArusKas::create([
                'tipe'       => 'Keluar',
                'kategori'   => 'Pembelian Stok',
                'nominal'    => $totalPembelian,
                'keterangan' => "Pembelian Barang Supplier - Faktur: {$validated['nomor_faktur']}",
                'tanggal'    => $validated['tanggal_beli'],
                'user_id'    => auth()->user()?->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan, stok dan harga modal telah diperbarui.',
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PembelianController@store Throwable', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan pembelian.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
