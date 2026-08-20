<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\SelfServiceOrder;
use App\Models\Toko;
use App\Services\GeminiScanService;
use App\Services\VoiceTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SelfServiceController extends Controller
{
    /**
     * Customer Mobile-First Self Service POS Interface (/self-service)
     */
    public function index(Request $request): View
    {
        $tokoId = $request->input('toko_id', auth()->user()?->toko_id ?? 1);
        $toko = Toko::find($tokoId) ?? Toko::first();

        $produks = Produk::withoutGlobalScopes()
            ->where('toko_id', $toko?->id ?? 1)
            ->where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->get();

        $kategoris = KategoriProduk::withoutGlobalScopes()
            ->where('toko_id', $toko?->id ?? 1)
            ->orderBy('nama_kategori')
            ->get();

        return view('pages.self-service.index', compact('toko', 'produks', 'kategoris'));
    }

    /**
     * AI Voice Processing for Customer Self-Service
     */
    public function processVoice(Request $request, VoiceTransactionService $voiceService): JsonResponse
    {
        $transcript = $request->input('transcript');
        if (empty($transcript)) {
            return response()->json(['ok' => false, 'message' => 'Teks suara kosong.']);
        }

        $res = $voiceService->processVoiceCommand($transcript);

        if (!$res['success']) {
            return response()->json(['ok' => false, 'message' => $res['message']]);
        }

        return response()->json(['ok' => true, 'result' => $res['data']]);
    }

    /**
     * AI Camera / Image Scan Processing for Customer Self-Service
     */
    public function processScan(Request $request, GeminiScanService $scanService): JsonResponse
    {
        $imageFile = $request->file('image');
        if (!$imageFile || !$imageFile->isValid()) {
            return response()->json(['ok' => false, 'message' => 'Berkas gambar tidak ditemukan.']);
        }

        $res = $scanService->scanImage($imageFile);

        if (!empty($res['error'])) {
            return response()->json(['ok' => false, 'message' => $res['error']]);
        }

        return response()->json(['ok' => true, 'items' => $res['items'] ?? []]);
    }

    /**
     * Store Customer Self Service Order (Status: Pending)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'toko_id'            => ['nullable', 'integer'],
            'nama_pelanggan'     => ['nullable', 'string', 'max:100'],
            'metode_pembayaran'  => ['required', 'string', 'in:Tunai,Digital Payment,Kredit'],
            'diskon'             => ['nullable', 'numeric', 'min:0'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:produk,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $tokoId = $validated['toko_id'] ?? auth()->user()?->toko_id ?? 1;
        $resolvedItems = [];
        $totalHarga = 0;

        foreach ($validated['items'] as $item) {
            $produk = Produk::withoutGlobalScopes()->where('toko_id', $tokoId)->find($item['product_id']);
            if (!$produk) continue;

            $qty = (int) $item['qty'];
            $hargaSatuan = (float) $produk->harga_jual_umum;
            $subtotal = $hargaSatuan * $qty;

            $resolvedItems[] = [
                'produk_id'    => $produk->id,
                'nama_produk'  => $produk->nama_produk,
                'qty'          => $qty,
                'harga_satuan' => $hargaSatuan,
                'subtotal'     => $subtotal,
            ];

            $totalHarga += $subtotal;
        }

        if (empty($resolvedItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau keranjang kosong.',
            ], 422);
        }

        $diskon = (float) ($validated['diskon'] ?? 0);
        $totalBayar = max(0, $totalHarga - $diskon);
        $nomorPesanan = SelfServiceOrder::generateNomorPesanan();

        $order = SelfServiceOrder::create([
            'toko_id'           => $tokoId,
            'nomor_pesanan'     => $nomorPesanan,
            'nama_pelanggan'    => $validated['nama_pelanggan'] ?: 'Pelanggan Self-Service',
            'items'             => $resolvedItems,
            'total_harga'       => $totalHarga,
            'diskon'            => $diskon,
            'total_bayar'       => $totalBayar,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'status'            => 'Pending',
            'notes'             => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Pesanan Self-Service berhasil dikirim! Silakan tunjukkan nomor pesanan ke kasir.',
            'nomor_pesanan' => $nomorPesanan,
            'data'          => $order,
        ], 201);
    }

    /**
     * Admin/Cashier Self-Service Verification Panel (/admin/self-service)
     */
    public function adminIndex(Request $request): View
    {
        $status = $request->input('status', 'Pending');
        $query = SelfServiceOrder::query()->orderByDesc('id');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);
        $pendingCount = SelfServiceOrder::where('status', 'Pending')->count();

        return view('pages.admin.self-service.index', compact('orders', 'pendingCount', 'status'));
    }

    /**
     * API for Admin Real-Time Sound Alert & Badge Counter
     */
    public function pendingCount(): JsonResponse
    {
        $pendingCount = SelfServiceOrder::where('status', 'Pending')->count();
        $latestOrder = SelfServiceOrder::where('status', 'Pending')->orderByDesc('id')->first();

        return response()->json([
            'success'       => true,
            'pending_count' => $pendingCount,
            'latest_id'     => $latestOrder?->id ?? 0,
            'nomor_pesanan' => $latestOrder?->nomor_pesanan ?? '',
        ]);
    }

    /**
     * Verify & Convert Self-Service Order into a Penjualan Transaction
     */
    public function verifyOrder(Request $request, $id): JsonResponse
    {
        $order = SelfServiceOrder::findOrFail($id);

        if ($order->status === 'Verified') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah pernah diverifikasi sebelumnya.',
            ], 422);
        }

        try {
            $penjualanId = DB::transaction(function () use ($order, $request) {
                // Construct payload for PenjualanController@store logic
                $items = array_map(function ($i) {
                    return [
                        'product_id' => $i['produk_id'],
                        'qty'        => $i['qty'],
                    ];
                }, $order->items ?? []);

                // Call internal store via Request simulation or direct model logic
                $penjualanReq = new Request([
                    'pelanggan_id'      => null,
                    'metode_pembayaran' => $order->metode_pembayaran,
                    'diskon'            => $order->diskon,
                    'nominal_uang'      => $order->total_bayar,
                    'items'             => $items,
                ]);

                $penjualanCtrl = new PenjualanController();
                $resp = $penjualanCtrl->store($penjualanReq);
                $respData = $resp->getData(true);

                if (!$respData['success']) {
                    throw new \Exception($respData['message'] ?? 'Gagal membuat invoice penjualan.');
                }

                $penjualanId = $respData['data']['penjualan_id'];

                // Update SelfServiceOrder status
                $order->update([
                    'status'       => 'Verified',
                    'user_id'      => auth()->user()?->id,
                    'penjualan_id' => $penjualanId,
                ]);

                return $penjualanId;
            });

            return response()->json([
                'success'      => true,
                'message'      => 'Pesanan Self-Service berhasil diverifikasi & diterbitkan invoice!',
                'penjualan_id' => $penjualanId,
            ]);

        } catch (\Throwable $e) {
            Log::error('SelfServiceController@verifyOrder Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a Self-Service Order
     */
    public function rejectOrder(Request $request, $id): JsonResponse
    {
        $order = SelfServiceOrder::findOrFail($id);
        $order->update(['status' => 'Rejected', 'user_id' => auth()->user()?->id]);


        return response()->json([
            'success' => true,
            'message' => 'Pesanan Self-Service berhasil ditolak.',
        ]);
    }
}
