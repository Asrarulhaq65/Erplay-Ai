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
            $table->string('gemini_api_key')->nullable()->after('slogan_struk');
            $table->string('gemini_model')->default('gemini-1.5-flash')->after('gemini_api_key');
            $table->boolean('ai_enabled')->default(true)->after('gemini_model');
            $table->boolean('ai_vision_enabled')->default(true)->after('ai_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn(['gemini_api_key', 'gemini_model', 'ai_enabled', 'ai_vision_enabled']);
        });
    }
};
