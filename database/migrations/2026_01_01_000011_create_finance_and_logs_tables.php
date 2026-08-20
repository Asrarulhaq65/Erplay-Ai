<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_finance_and_logs_tables
 *
 * Creates two supporting tables in a single migration:
 *
 *   1. arus_kas  — Cash flow ledger for income & expense tracking
 *   2. log_stok  — Immutable audit trail for all stock movement events
 *
 * ─── arus_kas ───────────────────────────────────────────────────────────────
 * Records every cash inflow and outflow per tenant. Designed as a simple
 * single-entry ledger (not double-entry accounting).
 *
 *   - tipe      : 'Masuk' (income) or 'Keluar' (expense)
 *   - kategori  : Free-text category (e.g., "Penjualan", "Biaya Operasional",
 *                 "Pembelian Barang", "Gaji Karyawan")
 *   - nominal   : Transaction amount — always positive; tipe determines direction
 *   - keterangan: Optional free-text memo/description
 *   - tanggal   : The actual business date of the cash movement
 *   - user_id   : Staff member who recorded the entry
 *
 * ─── log_stok ───────────────────────────────────────────────────────────────
 * An append-only audit log for all stock mutations. This table is NEVER updated,
 * only inserted to — preserving a complete, tamper-evident inventory history.
 *
 *   - tipe_perubahan : Reason for stock change:
 *       'Masuk_Barang'       → goods received (from pembelian)
 *       'Penjualan'          → stock consumed by a sale
 *       'Retur'              → returned goods (in or out)
 *       'Penyesuaian_Stok'   → manual stock adjustment by staff
 *   - jumlah   : Quantity changed (positive = added, negative = deducted)
 *   - stok_awal: Stock level BEFORE this event (snapshot for audit)
 *   - stok_akhir: Stock level AFTER this event (snapshot for audit)
 *   - keterangan: Optional reference (e.g., invoice number, adjustment reason)
 *
 * Execution Order : 11 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys (arus_kas):
 *   - toko_id  → toko(id)    ON DELETE CASCADE
 *   - user_id  → users(id)
 *
 * Foreign Keys (log_stok):
 *   - toko_id   → toko(id)    ON DELETE CASCADE
 *   - produk_id → produk(id)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ─── Cash Flow Ledger ───────────────────────────────────────────────
        Schema::create('arus_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->enum('tipe', ['Masuk', 'Keluar']);
            $table->string('kategori', 50);
            $table->decimal('nominal', 12, 2);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        // ─── Inventory Movement Audit Log ──────────────────────────────────
        Schema::create('log_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produk');
            $table->enum('tipe_perubahan', [
                'Masuk_Barang',
                'Penjualan',
                'Retur',
                'Penyesuaian_Stok',
            ]);
            $table->integer('jumlah');
            $table->integer('stok_awal');
            $table->integer('stok_akhir');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drop log_stok first; it has no dependents.
     * Then drop arus_kas.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_stok');
        Schema::dropIfExists('arus_kas');
    }
};
