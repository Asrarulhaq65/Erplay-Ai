<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssistantConfig extends Model
{
    protected $fillable = ['toko_id', 'assistant_name', 'personality', 'avatar_path', 'greeting_message', 'enabled_tools', 'disabled_tools', 'proactive_enabled'];

    protected $casts = [
        'enabled_tools' => 'array',
        'disabled_tools' => 'array',
        'proactive_enabled' => 'boolean',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
