<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_toko_table
 *
 * Table 'toko' is the root tenant entity.
 * All subsequent transactional tables will reference this via toko_id
 * to enforce multi-tenant data isolation.
 *
 * Execution Order : 1 of 11
 * Database        : db_retail_mini_erp
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('toko', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko', 100);
            $table->text('alamat');
            $table->string('no_telepon', 20);
            $table->string('logo', 255)->nullable();
            $table->string('slogan_struk', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toko');
    }
};
