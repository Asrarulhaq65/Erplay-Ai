<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->string('agent_name', 120);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('input_text');
            $table->json('tools_called')->nullable();
            $table->text('output_text')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
            $table->index(['toko_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('ai_interaction_logs'); }
};
