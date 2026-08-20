<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Menjalankan SuperAdminSeeder...');

        $roleSuperAdmin = Role::firstOrCreate(['nama_role' => 'Super Admin']);
        Role::firstOrCreate(['nama_role' => 'Owner']);
        Role::firstOrCreate(['nama_role' => 'Gudang']);
        Role::firstOrCreate(['nama_role' => 'Kasir']);

        // Kita gunakan Toko Kelontong Jaya agar datanya terhubung dengan Kasir (sesuai request)
        $toko = Toko::firstOrCreate(
            ['nama_toko' => 'Toko Kelontong Jaya'],
            [
                'alamat'    => 'Jl. Pahlawan No. 45, Kota Bandung',
                'no_telepon'=> '081234567890',
            ]
        );

        $existing = User::withoutGlobalScopes()->where('username', 'superadmin')->first();

        if ($existing) {
            $this->command->warn('  ⚠ User "superadmin" sudah ada, dilewati.');
        } else {
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'toko_id'      => $toko->id,
                'role_id'      => $roleSuperAdmin->id,
                'nama_lengkap' => 'Super Administrator',
                'username'     => 'superadmin',
                'password'     => Hash::make('superadmin123'),
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $this->command->info('  ✔ User Super Admin berhasil dibuat di ' . $toko->nama_toko);
        }
    }
}
