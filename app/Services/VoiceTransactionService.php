<?php

namespace App\Services;

use App\Ai\Agents\VoiceCommandAgent;
use App\Services\ProductFuzzyMatcher;
use Illuminate\Support\Facades\Log;

class VoiceTransactionService
{
    public function __construct(private readonly ProductFuzzyMatcher $matcher, private readonly AiSdkService $sdk, private readonly AiInteractionLogger $interactionLogger) {}

    public function parseVoiceCommand(string $transcript): array
    {
        $user = auth()->user();
        $toko = $user?->toko;
        [$provider, $model] = $this->sdk->prepare($toko);
        if (!$this->sdk->hasCredentials($toko)) return ['success' => false, 'message' => 'API key provider AI belum dikonfigurasi. Silakan isi kredensial BYOK.'];
        try {
            $startedAt = microtime(true);
            $response = VoiceCommandAgent::make(tokoId: (int) $user?->toko_id)->prompt("Transkrip Suara Kasir: \"{$transcript}\"", provider: $provider, model: $model, timeout: 20);
            $this->sdk->record($toko, $response);
            $this->interactionLogger->log($response, $toko, $user?->getKey(), $transcript, $startedAt, VoiceCommandAgent::class);
            $parsed = $response->toArray();
            foreach ($parsed['items'] ?? [] as &$item) {
                if (empty($item['produk_id']) && !empty($item['nama_produk'])) { $match = $this->matcher->findBestMatch($item['nama_produk']); if ($match['produk']) { $item['produk_id'] = $match['produk']->id; $item['nama_produk'] = $match['produk']->nama_produk; $item['harga_satuan'] = (float) $match['produk']->harga_jual_umum; } }
            }
            return ['success' => true, 'data' => $parsed];
        } catch (\Throwable $e) { Log::error('AI SDK voice failed', ['message' => $e->getMessage()]); return ['success' => false, 'message' => 'Gagal memproses perintah suara via AI: ' . $e->getMessage()]; }
    }

    public function processVoiceCommand(string $transcript): array { return $this->parseVoiceCommand($transcript); }
}
