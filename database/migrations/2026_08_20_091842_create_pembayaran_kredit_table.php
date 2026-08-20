<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_pembayaran_kredit_table
 *
 * Tracks every partial payment (cicilan/pelunasan) made against a
 * credit sale (penjualan with metode_pembayaran = 'Kredit').
 * Each row represents one payment event.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_kredit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->onDelete('cascade');
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('jumlah', 12, 2);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal_bayar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_kredit');
    }
};
