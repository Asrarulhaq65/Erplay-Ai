<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add_kredit_fields_to_penjualan_table
 *
 * Extends penjualan with partial-payment credit support:
 *   - uang_muka           : Amount paid at time of transaction (DP)
 *   - sisa_piutang        : Remaining unpaid balance
 *   - tanggal_jatuh_tempo : Optional due date for full settlement
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->decimal('uang_muka', 12, 2)->default(0)->after('nominal_uang');
            $table->decimal('sisa_piutang', 12, 2)->default(0)->after('uang_muka');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('sisa_piutang');
        });

        // Backfill existing rows
        DB::statement("
            UPDATE penjualan
            SET uang_muka    = CASE WHEN status_pembayaran = 'Lunas' THEN total_bayar ELSE 0 END,
                sisa_piutang = CASE WHEN status_pembayaran = 'Belum Lunas' THEN total_bayar ELSE 0 END
        ");
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['uang_muka', 'sisa_piutang', 'tanggal_jatuh_tempo']);
        });
    }
};
