<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->string('catalog_hero_badge', 100)->default('Katalog publik ERPlay AI')->after('catalog_theme');
            $table->string('catalog_hero_title', 180)->default('Belanja lebih mudah, informasi lebih jelas.')->after('catalog_hero_badge');
            $table->text('catalog_hero_description')->nullable()->after('catalog_hero_title');
        });
        DB::table('toko')->whereNull('catalog_hero_description')->update(['catalog_hero_description' => 'Lihat produk, cek harga, dan tanyakan ketersediaan langsung ke asisten toko kami.']);
    }

    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn(['catalog_hero_badge', 'catalog_hero_title', 'catalog_hero_description']);
        });
    }
};
