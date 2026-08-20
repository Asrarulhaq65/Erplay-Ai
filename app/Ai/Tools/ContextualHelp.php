<?php

namespace App\Ai\Tools;

use App\Models\Toko;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ContextualHelp implements Tool
{
    public function __construct(private readonly int $tokoId) {}

    public function description(): Stringable|string
    {
        return 'Berikan tips kontekstual sesuai halaman yang sedang diakses pengguna.';
    }

    public function handle(Request $request): Stringable|string
    {
        $page = strtolower(trim((string) ($request['page'] ?? '')));

        $tips = [
            'dashboard' => 'Di dashboard Anda bisa melihat ringkasan penjualan hari ini, stok menipis, dan chart penjualan bulanan.',
            'pos' => 'Di POS Anda bisa pakai mode Custom untuk entry cepat, scan struk, atau suara untuk input transaksi.',
            'inventory' => 'Di inventory Anda bisa filter produk, import CSV, export template, dan melakukan stock opname.',
            'pembelian' => 'Di pembelian Anda bisa buat faktur pembelian, pilih supplier, dan lacak status pembayaran Tunai/Kredit.',
            'laporan' => 'Di laporan Anda bisa filter penjualan by tanggal/pelanggan/metode bayar, dan export CSV.',
            'akuntansi' => 'Di akuntansi Anda bisa kelola akun, jurnal umum, buku besar, dan laporan laba rugi.',
            'pengaturan' => 'Di pengaturan Anda bisa ubah profil toko, kelola user, dan atur konfigurasi AI.',
            'self-service' => 'Self-service memungkinkan customer memesan sendiri; kasir memverifikasi dan menerbitkan invoice.',
        ];

        $tip = $tips[$page] ?? 'Gunakan menu navigasi untuk berpindah modul. Jika ragu, tanyakan fitur spesifik ke assistant.';

        return json_encode([
            'page' => $page ?: 'umum',
            'tip' => $tip,
            'related_actions' => $this->relatedActions($page),
        ]);
    }

    private function relatedActions(string $page): array
    {
        return match ($page) {
            'dashboard' => ['Lihat stok menipis', 'Lihat penjualan hari ini', 'Tanya performa toko'],
            'pos' => ['Scan struk', 'Cek stok', 'Tanya harga produk'],
            'inventory' => ['Import CSV', 'Export produk', 'Cek stok menipis'],
            'pembelian' => ['Buat pembelian baru', 'Lihat supplier', 'Estimasi biaya restock'],
            'laporan' => ['Export CSV', 'Lihat produk terlaris', 'Lihat laba rugi'],
            'akuntansi' => ['Buat jurnal', 'Lihat buku besar', 'Lihat laba rugi'],
            default => ['Tanya cara pakai fitur', 'Lihat daftar fitur by role', 'Cek stok'],
        };
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page' => $schema->string()->nullable(),
        ];
    }
}
