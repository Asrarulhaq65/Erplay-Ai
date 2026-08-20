<?php

namespace App\Ai\Tools;

use App\Models\Toko;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListFeaturesByRole implements Tool
{
    public function __construct(private readonly int $tokoId) {}

    public function description(): Stringable|string
    {
        return 'Daftar fitur yang bisa diakses oleh role tertentu di platform ini.';
    }

    public function handle(Request $request): Stringable|string
    {
        $role = strtolower(trim((string) ($request['role'] ?? 'kasir')));

        $map = [
            'super_admin' => ['Kelola semua toko dan subscription', 'Kelola user dan role', 'Lihat semua laporan lintas toko'],
            'owner' => ['Lihat semua laporan dan finansial', 'Kelola produk, pelanggan, supplier', 'Kelola user toko', 'Pengaturan toko dan AI'],
            'admin_toko' => ['Kelola produk, pelanggan, supplier', 'Kelola user toko', 'Lihat laporan dan analytics', 'Pengaturan toko'],
            'gudang' => ['Kelola produk dan stok', 'Import/export produk', 'Buat faktur pembelian', 'Stock opname'],
            'kasir' => ['POS Custom dan Standard', 'Lihat stok', 'Rekap penjualan', 'Verifikasi self-service order'],
        ];

        $features = $map[$role] ?? $map['kasir'];

        return json_encode([
            'role' => $role,
            'features' => $features,
            'tip' => 'Gunakan navigasi utama untuk membuka modul yang diinginkan.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'role' => $schema->string()->required(),
        ];
    }
}
