<?php

namespace App\Services;

use App\Ai\Agents\ErpCopilotAgent;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    public function __construct(private readonly AiSdkService $sdk, private readonly AiInteractionLogger $interactionLogger) {}

    public function ask(string $userMessage, array $chatHistory = []): array
    {
        $user = auth()->user();
        $toko = $user?->toko;
        if ($toko && !$toko->ai_enabled) return ['success' => true, 'reply' => "🔒 **Fitur AI Copilot dinonaktifkan.**\n\nFitur AI saat ini di-nonaktifkan oleh administrator toko. Anda dapat mengaktifkannya kembali di menu **Pengaturan AI**.", 'is_mock' => true];

        [$provider, $model] = $this->sdk->prepare($toko);
        if (!$this->sdk->hasCredentials($toko)) return ['success' => true, 'reply' => "⚠️ **API key provider AI belum dikonfigurasi.** Silakan isi kredensial BYOK pada Pengaturan AI.", 'is_mock' => true];

        $prompt = $userMessage;
        if ($chatHistory) $prompt = "Riwayat percakapan sebelumnya:\n" . json_encode($chatHistory, JSON_UNESCAPED_UNICODE) . "\n\nPesan terbaru:\n{$userMessage}";
        try {
            $startedAt = microtime(true);
            $response = ErpCopilotAgent::make(user: $user)->forUser($user)->prompt($prompt, provider: [$provider => $model, 'local-ollama' => null], timeout: 25);
            $this->sdk->record($toko, $response);
            $this->interactionLogger->log($response, $toko, $user?->getKey(), $userMessage, $startedAt, ErpCopilotAgent::class);
            return ['success' => true, 'reply' => $response->text, 'is_mock' => false];
        } catch (\Throwable $e) {
            Log::error('AI SDK copilot failed', ['message' => $e->getMessage()]);
            return ['success' => false, 'reply' => 'Gagal terhubung ke layanan Gemini AI: ' . $e->getMessage() . ' (Model diuji: ' . $model . ').', 'is_mock' => false];
        }
    }
}
