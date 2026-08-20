<?php

namespace App\Services;

use App\Jobs\RecordAiInteractionLogJob;
use App\Models\Toko;
use Laravel\Ai\Responses\AgentResponse;

class AiInteractionLogger
{
    public function log(AgentResponse $response, Toko $toko, ?int $userId, string $input, float $startedAt, string $agentName): void
    {
        $tools = $response->toolCalls->map(fn ($call) => ['name' => $call->name, 'arguments' => $this->sanitize($call->arguments)])->values()->all();
        RecordAiInteractionLogJob::dispatch([
            'toko_id' => $toko->id,
            'agent_name' => $agentName,
            'user_id' => $userId,
            'input_text' => $this->sanitizeText($input),
            'tools_called' => $tools,
            'output_text' => $this->sanitizeText($response->text),
            'duration_ms' => max(0, (int) round((microtime(true) - $startedAt) * 1000)),
        ]);
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_array($value)) return array_map(fn ($item) => $this->sanitize($item), $value);
        return is_string($value) ? $this->sanitizeText($value) : $value;
    }

    private function sanitizeText(string $value): string
    {
        $value = preg_replace('/\b(?:\+?62|0)8\d{8,13}\b/', '[masked-phone]', $value) ?? $value;
        $value = preg_replace('/(?:AIza|sk-|Bearer\s+)[A-Za-z0-9_.-]+/i', '[masked-secret]', $value) ?? $value;
        return mb_substr($value, 0, 10000);
    }
}
