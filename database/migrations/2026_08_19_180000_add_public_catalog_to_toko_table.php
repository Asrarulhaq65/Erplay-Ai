<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->string('catalog_slug')->nullable()->unique()->after('slogan_struk');
            $table->boolean('catalog_enabled')->default(true)->after('catalog_slug');
            $table->string('catalog_theme')->default('default')->after('catalog_enabled');
            $table->string('whatsapp_number')->nullable()->after('catalog_theme');
            $table->boolean('whatsapp_enabled')->default(false)->after('whatsapp_number');
        });

        DB::table('toko')->orderBy('id')->each(function ($toko) {
            DB::table('toko')->where('id', $toko->id)->update(['catalog_slug' => Str::slug($toko->nama_toko) . '-' . $toko->id]);
        });
    }

    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn(['catalog_slug', 'catalog_enabled', 'catalog_theme', 'whatsapp_number', 'whatsapp_enabled']);
        });
    }
};
