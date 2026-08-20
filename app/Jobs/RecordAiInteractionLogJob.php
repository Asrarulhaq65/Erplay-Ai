<?php

namespace App\Jobs;

use App\Models\AiInteractionLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordAiInteractionLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public function __construct(public readonly array $payload) {}
    public function backoff(): array { return [2, 5]; }
    public function handle(): void { AiInteractionLog::create($this->payload); }
}
