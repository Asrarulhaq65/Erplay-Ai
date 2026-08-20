<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiActionLog extends Model
{
    protected $table = 'ai_actions_log';
    protected $fillable = ['toko_id', 'user_id', 'action_type', 'tool_name', 'parameters', 'result', 'tokens_used', 'executed_at'];
    protected $casts = ['parameters' => 'array', 'result' => 'array', 'executed_at' => 'datetime'];

    public function toko(): BelongsTo { return $this->belongsTo(Toko::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
