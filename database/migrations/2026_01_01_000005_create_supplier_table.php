<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_supplier_table
 *
 * Supplier master data scoped per tenant (toko_id).
 * nama_kontak stores the PIC/sales representative name for the supplier.
 * Cascade delete ensures supplier records are removed when a toko is deleted.
 *
 * Execution Order : 5 of 11
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
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->string('nama_supplier', 100);
            $table->string('nama_kontak', 100)->nullable();
            $table->string('no_telepon', 20);
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
