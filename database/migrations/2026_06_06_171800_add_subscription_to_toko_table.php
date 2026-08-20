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
        Schema::table('toko', function (Blueprint $table) {
            $table->string('status_langganan', 50)->default('Aktif')->after('slogan_struk');
            $table->date('berakhir_pada')->nullable()->after('status_langganan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn(['status_langganan', 'berakhir_pada']);
        });
    }
};
