<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->unsignedBigInteger('ai_total_requests')->default(0)->after('ai_vision_enabled');
            $table->unsignedBigInteger('ai_prompt_tokens')->default(0)->after('ai_total_requests');
            $table->unsignedBigInteger('ai_completion_tokens')->default(0)->after('ai_prompt_tokens');
            $table->unsignedBigInteger('ai_total_tokens')->default(0)->after('ai_completion_tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn([
                'ai_total_requests',
                'ai_prompt_tokens',
                'ai_completion_tokens',
                'ai_total_tokens',
            ]);
        });
    }
};
