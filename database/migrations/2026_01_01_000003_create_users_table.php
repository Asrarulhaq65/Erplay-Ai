<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_users_table
 *
 * Application users scoped per tenant via toko_id.
 * Each user is assigned exactly one role. Cascade delete ensures that
 * removing a toko removes all its associated user accounts.
 *
 * Execution Order : 3 of 11
 * Database        : db_retail_mini_erp
 *
 * Foreign Keys:
 *   - toko_id  → toko(id)   ON DELETE CASCADE
 *   - role_id  → roles(id)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles');
            $table->string('username', 50)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('password', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
