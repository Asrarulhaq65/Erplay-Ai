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
        Schema::create('self_service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->string('nomor_pesanan')->unique(); // Format: SS-YYYYMMDD-XXXX
            $table->string('nama_pelanggan')->default('Pelanggan Self-Service');
            $table->json('items'); // Array of {produk_id, nama_produk, qty, harga_satuan, subtotal}
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->string('metode_pembayaran')->default('Tunai'); // Tunai, Digital Payment, Kredit
            $table->enum('status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Verified by cashier
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualan')->onDelete('set null'); // Created sale invoice
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_service_orders');
    }
};
