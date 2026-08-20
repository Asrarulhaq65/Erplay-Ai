<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_roles_table
 *
 * Defines application-level roles: Super Admin, Owner, Gudang, Kasir.
 * This table is intentionally global (no toko_id) because role definitions
 * are shared across all tenants.
 *
 * Execution Order : 2 of 11
 * Database        : db_retail_mini_erp
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role', 50); // Super Admin, Owner, Gudang, Kasir
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
