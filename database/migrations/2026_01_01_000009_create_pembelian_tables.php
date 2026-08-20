<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_pembelian_tables
 *
 * Creates two related tables in a single migration to keep the purchase
 * transaction header and its line items together:
 *
 *   1. pembelian        — Purchase order header (one record per invoice)
 *   2. pembelian_detail — Purchase order line items (one record per product/row)
 *
 * Key design decisions:
 *   - pembelian is scoped by toko_id for multi-tenant isolation.
 *   - nomor_faktur is the supplier's invoice number (not necessarily unique
 *     globally; uniqueness is enforced at application level per supplier).
 *   - metode_pembayaran distinguishes cash vs. credit purchases.
 *   - status_pembayaran + jatuh_tempo support accounts payable (hutang) tracking.
 *   - jatuh_tempo is nullable — only populated when status_pembayaran = 'Hutang'.
 *   - user_id records which staff member entered the purchase.
 *   - pembelian_detail.produk_id intentionally does NOT cascade delete; product
 *     records should not be deletable while purchase history references them.
 *
 * Execution Order : 9 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys (pembelian):
 *   - toko_id     → toko(id)      ON DELETE CASCADE
 *   - supplier_id → supplier(id)
 *   - user_id     → users(id)
 *
 * Foreign Keys (pembelian_detail):
 *   - pembelian_id → pembelian(id)  ON DELETE CASCADE
 *   - produk_id    → produk(id)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ─── Purchase Header ───────────────────────────────────────────────
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('supplier');
            $table->string('nomor_faktur', 50);
            $table->decimal('total_pembelian', 12, 2);
            $table->enum('metode_pembayaran', ['Tunai', 'Kredit']);
            $table->enum('status_pembayaran', ['Lunas', 'Hutang']);
            $table->date('jatuh_tempo')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->date('tanggal_beli');
            $table->timestamps();
        });

        // ─── Purchase Line Items ────────────────────────────────────────────
        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produk');
            $table->decimal('harga_beli_satuan', 12, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Must drop detail table first due to FK constraint on pembelian_id.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_detail');
        Schema::dropIfExists('pembelian');
    }
};
