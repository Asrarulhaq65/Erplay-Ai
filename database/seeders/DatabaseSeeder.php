<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,   // Akun toko + Super Admin (jalankan pertama)
            DummyRetailSeeder::class,  // Data dummy produk, pelanggan, dll
        ]);
    }
}
