<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_produk_table
 *
 * Core product/inventory table scoped per tenant (toko_id).
 *
 * Key design decisions:
 *   - barcode is indexed individually for fast POS barcode scanner lookup.
 *   - A composite UNIQUE constraint on (toko_id, barcode) ensures a barcode
 *     is unique within each tenant but allows the same barcode across tenants.
 *   - Five tiered pricing columns map to the pelanggan.status_pelanggan enum:
 *     Umum, Member, Rekan, Motoris — plus a base modal price for margin calc.
 *   - All price columns use decimal(12, 2) to handle up to 999,999,999,999.99
 *     without floating-point errors.
 *   - stok_minimum triggers low-stock warnings in the dashboard.
 *   - kategori_id uses RESTRICT on delete to prevent accidental product orphaning.
 *
 * Execution Order : 8 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys:
 *   - toko_id     → toko(id)            ON DELETE CASCADE
 *   - kategori_id → kategori_produk(id)  ON DELETE RESTRICT
 *
 * Indexes:
 *   - barcode              (single-column for scanner lookup speed)
 *   - UNIQUE(toko_id, barcode)  (composite uniqueness per tenant)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->foreignId('kategori_id')->constrained('kategori_produk')->onDelete('restrict');
            $table->string('barcode', 50)->index();
            $table->string('nama_produk', 150);
            $table->string('satuan', 20)->default('Pcs');
            $table->decimal('harga_modal', 12, 2)->default(0);
            $table->decimal('harga_jual_umum', 12, 2)->default(0);
            $table->decimal('harga_jual_member', 12, 2)->default(0);
            $table->decimal('harga_jual_rekan', 12, 2)->default(0);
            $table->decimal('harga_jual_motoris', 12, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->timestamps();

            // Barcode must be unique per tenant, but the same barcode
            // may exist across different toko (multi-tenant isolation).
            $table->unique(['toko_id', 'barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
