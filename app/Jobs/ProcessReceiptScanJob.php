<?php

namespace App\Jobs;

use App\Models\Toko;
use App\Services\GeminiScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Throwable;

class ProcessReceiptScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 90;

    public function __construct(
        public readonly int $tokoId,
        public readonly string $imagePath,
        public readonly string $resultKey,
    ) {}

    public function backoff(): array { return [5, 15]; }

    public function handle(GeminiScanService $scanner): void
    {
        $toko = Toko::query()->findOrFail($this->tokoId);
        $disk = Storage::disk('local');
        $absolutePath = $disk->path($this->imagePath);
        $image = new UploadedFile($absolutePath, basename($absolutePath), mime_content_type($absolutePath) ?: 'image/jpeg', null, true);
        $result = $scanner->extractItemsForTenant($image, $toko);

        Cache::put($this->cacheKey(), ['status' => 'completed', 'result' => $result], now()->addMinutes(10));
        $disk->delete($this->imagePath);
    }

    public function failed(Throwable $exception): void
    {
        Cache::put($this->cacheKey(), ['status' => 'failed', 'result' => ['items' => [], 'error' => 'Pemrosesan scan gagal. Silakan coba lagi.']], now()->addMinutes(10));
        Log::error('Receipt scan job failed permanently', ['toko_id' => $this->tokoId, 'result_key' => $this->resultKey, 'message' => $exception->getMessage()]);
        Storage::disk('local')->delete($this->imagePath);
    }

    private function cacheKey(): string { return 'receipt-scan:' . $this->tokoId . ':' . $this->resultKey; }
}
