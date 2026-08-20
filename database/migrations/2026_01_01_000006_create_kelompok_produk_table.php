<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_kelompok_produk_table
 *
 * Top-level product grouping (e.g., "Sembako", "Elektronik").
 * Scoped per tenant via toko_id.
 * Acts as the parent to kategori_produk in a two-level category hierarchy.
 *
 * Execution Order : 6 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys:
 *   - toko_id → toko(id)  ON DELETE CASCADE
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelompok_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->string('nama_kelompok', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_produk');
    }
};
