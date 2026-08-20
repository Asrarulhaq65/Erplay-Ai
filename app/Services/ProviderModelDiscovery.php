<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ProviderModelDiscovery
{
    public function __construct(private VisionModelResolver $resolver = new VisionModelResolver()) {}

    public function discoverModels(?\App\Models\Toko $toko, string $provider, string $apiKey, ?string $baseUrl, bool $forceRefresh = false): array
    {
        $cacheKey = "ai_models_{$provider}_" . md5($apiKey . $baseUrl);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 3600, function () use ($toko, $provider, $apiKey, $baseUrl) {
            $models = $this->fetchFromProvider($provider, $apiKey, $baseUrl);

            if ($models === null) {
                return [
                    'status' => 'error',
                    'models' => [],
                    'fallback' => true,
                    'message' => 'Gagal mengambil model dari provider. Menggunakan model default.',
                ];
            }

            $availableModels = [];
            foreach ($models as $model) {
                $modelName = $model['name'] ?? $model['id'] ?? null;
                if ($modelName && !$this->isDeprecatedModel($modelName)) {
                    $availableModels[] = [
                        'name' => $modelName,
                        'display_name' => $model['display_name'] ?? $this->formatModelName($modelName),
                        'vision_capable' => $this->resolver->isVisionCapable($provider, $modelName),
                        'context_window' => $model['context_window'] ?? 0,
                    ];
                }
            }

            return [
                'status' => 'success',
                'models' => $availableModels,
                'fallback' => false,
                'message' => 'Berhasil mengambil model dari provider.',
            ];
        });
    }

    private function fetchFromProvider(string $provider, string $apiKey, ?string $baseUrl): ?array
    {
        try {
            if ($provider === 'gemini') {
                return $this->fetchGeminiModels($apiKey);
            }

            return $this->fetchOpenAiModels($apiKey, $baseUrl);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Model Discovery failed: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchGeminiModels(string $apiKey): ?array
    {
        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(10)->get('https://generativelanguage.googleapis.com/v1beta/models');

        if ($response->successful()) {
            $data = $response->json();
            $allModels = $data['models'] ?? [];

            $models = [];
            foreach ($allModels as $m) {
                $name = basename($m['name']);
                if (str_starts_with($name, 'gemini')) {
                    $models[] = [
                        'name' => $name,
                        'display_name' => $name,
                        'context_window' => $m['inputTokenLimit'] ?? 0,
                    ];
                }
            }

            return $models;
        }

        return null;
    }

    private function fetchOpenAiModels(string $apiKey, ?string $baseUrl): ?array
    {
        $url = rtrim((string) $baseUrl, '/') . '/models';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(10)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $models = array_map(function ($m) {
                $name = $m['id'] ?? basename($m['name'] ?? '');
                return [
                    'name' => $name,
                    'display_name' => $m['display_name'] ?? $m['id'] ?? $name,
                    'context_window' => $m['context_window'] ?? 0,
                ];
            }, $data['data'] ?? []);

            return $models;
        }

        return null;
    }

    private function isDeprecatedModel(string $modelName): bool
    {
        $deprecated = [
            'text-bison',
            'text-bison-001',
            'chat-bison',
            'chat-bison-001',
            'gpt-3.5-turbo-0301',
            'gemini-1.0-pro-vision',
            'gemini-2.0-flash-exp',
        ];

        return collect($deprecated)->contains(fn($d) => str_starts_with($modelName, $d));
    }

    private function formatModelName(string $modelName): string
    {
        if (str_starts_with($modelName, 'gemini')) {
            return match (true) {
                str_contains($modelName, '2.5-pro')          => 'Gemini 2.5 Pro',
                str_contains($modelName, '2.5-flash-lite')   => 'Gemini 2.5 Flash Lite',
                str_contains($modelName, '2.5-flash')        => 'Gemini 2.5 Flash',
                str_contains($modelName, '2.0-flash-lite')   => 'Gemini 2.0 Flash Lite',
                str_contains($modelName, '2.0-flash')        => 'Gemini 2.0 Flash',
                str_contains($modelName, '1.5-pro')          => 'Gemini 1.5 Pro',
                str_contains($modelName, '1.5-flash')        => 'Gemini 1.5 Flash',
                default                                      => str_replace('-', ' ', $modelName),
            };
        }

        if (str_starts_with($modelName, 'gpt')) {
            return match (true) {
                str_contains($modelName, 'gpt-4o') && str_contains($modelName, 'mini') => 'GPT-4o Mini',
                str_contains($modelName, 'gpt-4o') => 'GPT-4o',
                str_contains($modelName, 'gpt-4-vision') => 'GPT-4 Vision',
                default => str_replace('-', ' ', $modelName),
            };
        }

        return str_replace('-', ' ', $modelName);
    }
}
