<?php

namespace App\Services;

use App\Models\Toko;
use Laravel\Ai\Responses\AgentResponse;

class AiSdkService
{
    public function __construct(private VisionModelResolver $resolver = new VisionModelResolver()) {}

    public function prepare(?Toko $toko, array $overrides = []): array
    {
        $configuredProvider = $overrides['provider'] ?? $toko?->ai_provider ?? 'gemini';
        $provider = $configuredProvider === 'openai-compatible' ? 'byok-openai' : $configuredProvider;
        $key = $overrides['key'] ?? ($toko?->ai_api_key ?: ($configuredProvider === 'gemini' ? ($toko?->gemini_api_key ?: env('GEMINI_API_KEY', config('services.gemini.api_key'))) : env('OPENAI_COMPATIBLE_API_KEY')));
        $baseUrl = $overrides['base_url'] ?? $toko?->ai_base_url;
        $visionRequired = (bool) ($overrides['vision_required'] ?? $toko?->ai_vision_enabled ?? false);
        $model = $this->resolver->resolve($configuredProvider, $overrides['model'] ?? $toko?->ai_model ?: $toko?->gemini_model ?: ($configuredProvider === 'gemini' ? config('services.gemini.model') : null), $visionRequired);

        config(["ai.providers.{$provider}.key" => $key]);
        if ($baseUrl && $provider === 'byok-openai') config(["ai.providers.{$provider}.url" => rtrim($baseUrl, '/')]);

        return [$provider, $model ?: config("ai.providers.{$provider}.models.text.default"), $visionRequired];
    }

    public function hasCredentials(?Toko $toko): bool
    {
        $provider = $toko?->ai_provider ?? 'gemini';
        return $provider === 'gemini'
            ? (bool) ($toko?->ai_api_key ?: $toko?->gemini_api_key ?: env('GEMINI_API_KEY', config('services.gemini.api_key')))
            : (bool) ($toko?->ai_api_key ?: env('OPENAI_COMPATIBLE_API_KEY'));
    }

    public function record(?Toko $toko, AgentResponse $response): void
    {
        $usage = $response->usage;
        $toko?->recordAiUsage([
            'promptTokenCount' => $usage->promptTokens,
            'candidatesTokenCount' => $usage->completionTokens,
            'totalTokenCount' => $usage->promptTokens + $usage->completionTokens,
        ]);
    }
}
