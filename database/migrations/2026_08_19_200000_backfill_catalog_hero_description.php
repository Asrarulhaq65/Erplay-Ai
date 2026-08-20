<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('toko')->whereNull('catalog_hero_description')->update(['catalog_hero_description' => 'Lihat produk, cek harga, dan tanyakan ketersediaan langsung ke asisten toko kami.']);
    }

    public function down(): void {}
};
