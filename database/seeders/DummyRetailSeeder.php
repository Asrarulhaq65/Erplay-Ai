<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\KelompokProduk;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyRetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Semua operasi menggunakan firstOrCreate agar aman dijalankan
     * berkali-kali tanpa menyebabkan UniqueConstraintViolation.
     */
    public function run(): void
    {
        // 1. Create Toko (Tenant)
        $toko = Toko::firstOrCreate(
            ['nama_toko' => 'Toko Kelontong Jaya'],
            [
                'alamat'    => 'Jl. Pahlawan No. 45, Kota Bandung',
                'no_telepon'=> '081234567890',
            ]
        );

        // 2. Create Roles – firstOrCreate agar tidak bentrok dengan SuperAdminSeeder
        $roles = ['Super Admin', 'Owner', 'Gudang', 'Kasir'];
        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['nama_role' => $roleName]);
        }

        // 3. Create Dummy User (Kasir) – skip jika username sudah ada
        $kasirUser = User::withoutGlobalScopes()->where('username', 'kasir')->first();
        if (!$kasirUser) {
            DB::table('users')->insert([
                'toko_id'      => $toko->id,
                'role_id'      => $roleModels['Kasir']->id,
                'nama_lengkap' => 'Kasir Utama',
                'username'     => 'kasir',
                'password'     => Hash::make('password'),
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $kasirUser = User::withoutGlobalScopes()->where('username', 'kasir')->first();
        }

        // IMPORTANT: Login as the Kasir user.
        // Semua Model (Pelanggan, Produk, dll) punya 'creating' event yang
        // otomatis inject auth()->user()->toko_id. Login di sini agar
        // Global Scope & auto-assignment bekerja dengan benar.
        Auth::login($kasirUser);

        // 4. Create Dummy Customers (Pelanggan)
        $pelanggans = [
            ['kode_pelanggan' => 'PLG-0001', 'nama_pelanggan' => 'Pelanggan Umum', 'no_telepon' => '080000000000', 'status_pelanggan' => 'Umum'],
            ['kode_pelanggan' => 'PLG-0002', 'nama_pelanggan' => 'Rian Member',    'no_telepon' => '081111111111', 'status_pelanggan' => 'Member'],
            ['kode_pelanggan' => 'PLG-0003', 'nama_pelanggan' => 'Toko Barokah',   'no_telepon' => '082222222222', 'status_pelanggan' => 'Rekan'],
            ['kode_pelanggan' => 'PLG-0004', 'nama_pelanggan' => 'Asep Motoris',   'no_telepon' => '083333333333', 'status_pelanggan' => 'Motoris'],
        ];
        foreach ($pelanggans as $plg) {
            Pelanggan::firstOrCreate(['kode_pelanggan' => $plg['kode_pelanggan']], $plg);
        }

        // 5. Create Dummy Supplier
        Supplier::firstOrCreate(
            ['nama_supplier' => 'PT Alat Tulis Nusantara'],
            [
                'no_telepon' => '0219876543',
                'alamat'     => 'Kawasan Industri Pulogadung Blok A',
                'nama_kontak'=> 'Bpk. Budi',
            ]
        );

        // 6. Create Product Groups (Kelompok)
        $kelompokATK     = KelompokProduk::firstOrCreate(['nama_kelompok' => 'Alat Tulis']);
        $kelompokSembako = KelompokProduk::firstOrCreate(['nama_kelompok' => 'Sembako']);
        $kelompokLain    = KelompokProduk::firstOrCreate(['nama_kelompok' => 'Lain-lain']);

        // 7. Create Categories (Kategori)
        $kategoriBuku   = KategoriProduk::firstOrCreate(['kelompok_id' => $kelompokATK->id,     'nama_kategori' => 'Buku']);
        $kategoriPulpen = KategoriProduk::firstOrCreate(['kelompok_id' => $kelompokATK->id,     'nama_kategori' => 'Pulpen']);
        $kategoriMinyak = KategoriProduk::firstOrCreate(['kelompok_id' => $kelompokSembako->id, 'nama_kategori' => 'Minyak Goreng']);
        $kategoriMie    = KategoriProduk::firstOrCreate(['kelompok_id' => $kelompokSembako->id, 'nama_kategori' => 'Mie Instan']);
        $kategoriRumah  = KategoriProduk::firstOrCreate(['kelompok_id' => $kelompokLain->id,    'nama_kategori' => 'Perlengkapan Rumah']);

        // 8. Create Realistic Products with Multi-Tier Pricing
        $products = [
            [
                'kategori_id'        => $kategoriBuku->id,
                'barcode'            => '89910011',
                'nama_produk'        => 'Buku Tulis Sidu 38 Lembar',
                'satuan'             => 'Pcs',
                'harga_modal'        => 3000,
                'harga_jual_umum'    => 4000,
                'harga_jual_member'  => 3800,
                'harga_jual_rekan'   => 3600,
                'harga_jual_motoris' => 3400,
                'stok'               => 50,
                'stok_minimum'       => 10,
            ],
            [
                'kategori_id'        => $kategoriRumah->id,
                'barcode'            => '89920022',
                'nama_produk'        => 'Stella Pengharum Ruangan Jeruk',
                'satuan'             => 'Pcs',
                'harga_modal'        => 12000,
                'harga_jual_umum'    => 15000,
                'harga_jual_member'  => 14500,
                'harga_jual_rekan'   => 14000,
                'harga_jual_motoris' => 13500,
                'stok'               => 3,    // Deliberately low stock to trigger visual warning
                'stok_minimum'       => 5,
            ],
            [
                'kategori_id'        => $kategoriMie->id,
                'barcode'            => '89930033',
                'nama_produk'        => 'Indomie Goreng Spesial',
                'satuan'             => 'Bks',
                'harga_modal'        => 2500,
                'harga_jual_umum'    => 3500,
                'harga_jual_member'  => 3300,
                'harga_jual_rekan'   => 3100,
                'harga_jual_motoris' => 2900,
                'stok'               => 120,
                'stok_minimum'       => 20,
            ],
            [
                'kategori_id'        => $kategoriMinyak->id,
                'barcode'            => '89940044',
                'nama_produk'        => 'Minyak Goreng Bimoli 1 Liter',
                'satuan'             => 'Pch',
                'harga_modal'        => 17000,
                'harga_jual_umum'    => 20000,
                'harga_jual_member'  => 19500,
                'harga_jual_rekan'   => 19000,
                'harga_jual_motoris' => 18500,
                'stok'               => 25,
                'stok_minimum'       => 10,
            ],
            [
                'kategori_id'        => $kategoriPulpen->id,
                'barcode'            => '89950055',
                'nama_produk'        => 'Pulpen Faster Hitam C600',
                'satuan'             => 'Pcs',
                'harga_modal'        => 1500,
                'harga_jual_umum'    => 2500,
                'harga_jual_member'  => 2200,
                'harga_jual_rekan'   => 2000,
                'harga_jual_motoris' => 1800,
                'stok'               => 144,
                'stok_minimum'       => 24,
            ],
        ];

        foreach ($products as $prod) {
            Produk::firstOrCreate(['barcode' => $prod['barcode']], $prod);
        }

        // Logout after seeding to keep state clean
        Auth::logout();
    }
}
