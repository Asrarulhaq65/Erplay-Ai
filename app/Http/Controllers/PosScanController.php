<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReceiptScanJob;
use App\Services\GeminiScanService;
use App\Services\AiSdkService;
use App\Services\ProductFuzzyMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * PosScanController — halaman scan gambar berbasis AI untuk POS.
 *
 * Alur:
 *   1. User upload gambar struk/belanja ke /pos/scan
 *   2. POST /pos/scan/process → image dikirim ke Gemini Vision API
 *   3. Hasil AI difuzzy-match terhadap master Produk toko
 *   4. Frontend menampilkan review table dengan status (Tersedia/Barang Baru)
 *   5. User pilih produk → POST ke cart POS Standar
 */
class PosScanController extends Controller
{
    public function __construct(
        private readonly GeminiScanService $gemini,
        private readonly ProductFuzzyMatcher $matcher,
        private readonly AiSdkService $sdk,
    ) {}

    /**
     * Tampilkan halaman scan gambar.
     *
     * Route: GET /pos/scan
     */
    public function showScan(): View
    {
        $user = auth()->user();
        $toko = $user?->toko;
        $geminiReady = (bool) ($toko?->ai_vision_enabled ?? true) && $this->sdk->hasCredentials($toko);

        return view('pages.pos.scan', compact('geminiReady'));
    }

    /**
     * Proses gambar upload → kirim ke Gemini → fuzzy match Produk → return JSON.
     *
     * Route: POST /pos/scan/process
     */
    public function processScan(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'image.required' => 'File gambar wajib dipilih.',
            'image.image'    => 'File harus berupa gambar.',
            'image.mimes'    => 'Format harus JPG/PNG/WEBP.',
            'image.max'      => 'Ukuran maksimal 5 MB.',
        ]);

        $image = $request->file('image');

        if ($request->boolean('async')) {
            $tokoId = (int) auth()->user()->toko_id;
            $path = $image->store('ai/receipt-scans', 'local');
            $resultKey = (string) Str::uuid();
            Cache::put('receipt-scan:' . $tokoId . ':' . $resultKey, ['status' => 'queued'], now()->addMinutes(10));
            ProcessReceiptScanJob::dispatch($tokoId, $path, $resultKey);
            return response()->json(['ok' => true, 'queued' => true, 'result_key' => $resultKey, 'status_url' => route('pos.scan.status', $resultKey)]);
        }

        // 1. Kirim ke Gemini
        $aiResult = $this->gemini->extractItems($image);
        if (!empty($aiResult['error'])) {
            return response()->json([
                'ok'    => false,
                'error' => $aiResult['error'],
                'items' => [],
            ], 422);
        }

        // 2. Fuzzy match tiap item ke Produk toko
        $items = [];
        foreach ($aiResult['items'] ?? [] as $aiItem) {
            $match = $this->matcher->findBestMatch($aiItem['name']);

            $items[] = [
                'ai_name'     => $aiItem['name'],
                'ai_qty'      => $aiItem['qty'],
                'ai_harga'    => $aiItem['harga'],
                'status'      => $match['status'],
                'score'       => $match['score'],
                'catatan'     => $match['catatan'],
                'produk_id'   => $match['produk']?->id,
                'produk_nama' => $match['produk']?->nama_produk,
                'produk_barcode' => $match['produk']?->barcode,
                'produk_harga' => $match['produk'] ? (float) $match['produk']->harga_jual_umum : 0,
                'produk_satuan' => $match['produk']?->satuan,
                'produk_stok' => $match['produk']?->stok,
                'candidates'  => $match['candidates'],
            ];
        }

        return response()->json([
            'ok'        => true,
            'items'     => $items,
            'total_ai'  => count($items),
            'matched'   => count(array_filter($items, fn($i) => $i['status'] === 'tersedia')),
        ]);
    }

    public function scanStatus(string $resultKey): JsonResponse
    {
        $result = Cache::get('receipt-scan:' . auth()->user()->toko_id . ':' . $resultKey, ['status' => 'not_found']);
        return response()->json(['ok' => $result['status'] !== 'not_found', ...$result]);
    }
}
