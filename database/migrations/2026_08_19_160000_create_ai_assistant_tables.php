<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistant_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->string('assistant_name')->default('ERPlay AI Assistant');
            $table->string('personality')->default('profesional');
            $table->string('avatar_path')->nullable();
            $table->string('greeting_message')->nullable();
            $table->json('enabled_tools')->nullable();
            $table->json('disabled_tools')->nullable();
            $table->boolean('proactive_enabled')->default(true);
            $table->timestamps();
            $table->unique('toko_id');
        });

        Schema::create('ai_actions_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type');
            $table->string('tool_name');
            $table->json('parameters')->nullable();
            $table->json('result')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
            $table->index(['toko_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_actions_log');
        Schema::dropIfExists('ai_assistant_configs');
    }
};
