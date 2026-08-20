<?php

namespace App\Services;

class VisionModelResolver
{
    public const VISION_MODELS = [
        'gemini' => [
            'gemini-2.5-pro',
            'gemini-2.0-flash',
            'gemini-1.5-pro-latest',
            'gemini-1.5-flash-latest',
            'gemini-1.5-pro-002',
        ],
        'openai-compatible' => [
            'gpt-4o',
            'gpt-4o-mini',
            'gpt-4-vision-preview',
            'gpt-4-turbo',
        ],
    ];

    public const DEFAULT_VISION_MODELS = [
        'gemini' => 'gemini-2.0-flash',
        'openai-compatible' => 'gpt-4o',
    ];

    public function normalizeProvider(string $provider): string
    {
        return $provider === 'byok-openai' ? 'openai-compatible' : $provider;
    }

    public function isVisionCapable(string $provider, string $model): bool
    {
        $normalized = $this->normalizeProvider($provider);
        $models = self::VISION_MODELS[$normalized] ?? [];

        foreach ($models as $visionModel) {
            if ($model === $visionModel) {
                return true;
            }

            if (str_starts_with($model, $visionModel) && preg_match('/^' . preg_quote($visionModel, '/') . '[-.\w]+$/', $model)) {
                return true;
            }
        }
        return false;
    }

    public function getDefaultVisionModel(string $provider): string
    {
        $normalized = $this->normalizeProvider($provider);
        return self::DEFAULT_VISION_MODELS[$normalized] ?? 'gemini-1.5-pro-latest';
    }

    public function resolve(string $provider, ?string $model, bool $visionRequired = false): string
    {
        if (!$visionRequired || empty($model)) {
            return $model ?: $this->getDefaultVisionModel($provider);
        }

        if ($this->isVisionCapable($provider, $model)) {
            return $model;
        }

        return $this->getDefaultVisionModel($provider);
    }
}
