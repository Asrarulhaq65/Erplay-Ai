<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiInteractionLog extends Model
{
    protected $fillable = ['toko_id', 'agent_name', 'user_id', 'input_text', 'tools_called', 'output_text', 'duration_ms'];
    protected $casts = ['tools_called' => 'array'];
}
