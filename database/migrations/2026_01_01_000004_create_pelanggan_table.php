<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_pelanggan_table
 *
 * Customer master data scoped per tenant (toko_id).
 * kode_pelanggan is indexed for fast lookup on the POS screen.
 * status_pelanggan drives the tiered pricing logic in penjualan.
 *
 * Execution Order : 4 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys:
 *   - toko_id → toko(id)  ON DELETE CASCADE
 *
 * Indexes:
 *   - kode_pelanggan  (search-heavy, used in POS lookup)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->string('kode_pelanggan', 50)->index();
            $table->string('nama_pelanggan', 100);
            $table->string('no_telepon', 20);
            $table->enum('status_pelanggan', ['Umum', 'Member', 'Rekan', 'Motoris'])->default('Umum');
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
