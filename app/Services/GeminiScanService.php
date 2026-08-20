<?php

namespace App\Services;

use App\Ai\Agents\ScanReceiptAgent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Files\Image;

class GeminiScanService
{
    public function __construct(private readonly AiSdkService $sdk, private readonly AiInteractionLogger $interactionLogger) {}

    public function extractItems(UploadedFile $image): array
    {
        $toko = auth()->user()?->toko;
        return $this->extractItemsForTenant($image, $toko, auth()->user()?->getKey());
    }

    public function extractItemsForTenant(UploadedFile $image, ?\App\Models\Toko $toko, ?int $userId = null): array
    {
        [$provider, $model] = $this->sdk->prepare($toko);
        if (!$this->sdk->hasCredentials($toko)) return ['items' => [], 'error' => 'API key provider AI belum dikonfigurasi. Silakan isi kredensial BYOK.'];
        try {
            $startedAt = microtime(true);
            $response = ScanReceiptAgent::make()->prompt('Ekstrak item dari gambar ini.', attachments: [Image::fromUpload($image)], provider: $provider, model: $model, timeout: 60);
            $this->sdk->record($toko, $response);
            if ($toko) $this->interactionLogger->log($response, $toko, $userId, 'Ekstrak item dari gambar struk.', $startedAt, ScanReceiptAgent::class);
            return ['items' => collect($response->toArray()['items'] ?? [])->map(fn ($row) => ['name' => trim((string) ($row['name'] ?? '')), 'qty' => max(1, (int) ($row['qty'] ?? 1)), 'harga' => (float) ($row['harga'] ?? 0)])->filter(fn ($row) => $row['name'] !== '')->values()->all(), 'error' => null];
        } catch (\Throwable $e) {
            Log::error('AI SDK vision failed', ['message' => $e->getMessage()]);
            return ['items' => [], 'error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }

    public function scanImage(UploadedFile $image): array { return $this->extractItems($image); }
}
