<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_kategori_produk_table
 *
 * Second-level product categorization. Each kategori belongs to one kelompok.
 * Scoped per tenant via toko_id for multi-tenant isolation.
 * Cascade delete on kelompok_id propagates when a kelompok is removed.
 *
 * Execution Order : 7 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys:
 *   - toko_id     → toko(id)            ON DELETE CASCADE
 *   - kelompok_id → kelompok_produk(id)  ON DELETE CASCADE
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->foreignId('kelompok_id')->constrained('kelompok_produk')->onDelete('cascade');
            $table->string('nama_kategori', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_produk');
    }
};
