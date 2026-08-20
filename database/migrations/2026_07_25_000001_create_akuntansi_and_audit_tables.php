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
        // ── 1. Chart of Accounts (COA) ──────────────────────────────────────
        Schema::create('akun_akuntansi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->string('kode_akun', 20);
            $table->string('nama_akun', 100);
            $table->enum('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban']);
            $table->enum('saldo_normal', ['Debit', 'Kredit'])->default('Debit');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->boolean('is_header')->default(false);
            $table->timestamps();

            $table->unique(['toko_id', 'kode_akun']);
        });

        // ── 2. Jurnal Umum Master ───────────────────────────────────────────
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->string('nomor_jurnal', 50);
            $table->date('tanggal');
            $table->string('keterangan', 255);
            $table->string('ref_type', 50)->nullable(); // e.g. Penjualan, Pembelian, Manual
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['toko_id', 'tanggal']);
        });

        // ── 3. Jurnal Detail Lines ──────────────────────────────────────────
        Schema::create('jurnal_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnal_umum')->onDelete('cascade');
            $table->foreignId('akun_id')->constrained('akun_akuntansi')->onDelete('cascade');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->string('memo', 255)->nullable();
            $table->timestamps();
        });

        // ── 4. System Audit Trail Log ───────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->nullable()->constrained('toko')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('aktivitas', 255);
            $table->string('modul', 50);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();
            $table->timestamps();

            $table->index(['toko_id', 'modul', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('jurnal_detail');
        Schema::dropIfExists('jurnal_umum');
        Schema::dropIfExists('akun_akuntansi');
    }
};
