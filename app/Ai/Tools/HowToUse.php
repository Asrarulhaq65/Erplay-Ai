<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class HowToUse implements Tool
{
    public function __construct(private readonly int $tokoId) {}

    public function description(): Stringable|string
    {
        return 'Berikan langkah penggunaan fitur tertentu di platform.';
    }

    public function handle(Request $request): Stringable|string
    {
        $feature = strtolower(trim((string) ($request['feature'] ?? '')));
        $context = trim((string) ($request['context'] ?? ''));

        $guides = [
            'tambah produk' => [
                'title' => 'Tambah Produk',
                'steps' => ['Buka Master > Produk > Tambah', 'Isi nama, barcode, satuan, harga modal, harga jual', 'Pilih kelompok dan kategori', 'Set stok awal dan stok minimum', 'Simpan'],
                'tip' => 'Gunakan chained dropdown Kelompok -> Kategori agar data terstruktur.',
            ],
            'import produk' => [
                'title' => 'Import Produk CSV',
                'steps' => ['Download template CSV di halaman Produk', 'Isi sesuai kolom: nama, barcode, kelompok, kategori, harga, stok', 'Upload file di menu Import', 'Review hasil import dan perbaiki error jika ada'],
                'tip' => 'Untuk大批量导入, pecah menjadi 500 baris per file untuk menghindari timeout.',
            ],
            'pos custom' => [
                'title' => 'Pakai POS Custom Quick-Entry',
                'steps' => ['Buka POS > Custom', 'Pilih pelanggan atau tekan Enter untuk Umum', 'Ketik nama/barcode produk, Enter', 'Input qty, Enter untuk tambah ke keranjang', 'Tekan checkout untuk selesaikan transaksi'],
                'tip' => 'Mode ini dirancang untuk zero mouse: seluruh alur lewat keyboard.',
            ],
            'scan struk' => [
                'title' => 'Scan Struk / Faktur',
                'steps' => ['Buka POS > Scan', 'Upload foto struk atau bon pembelian', 'AI akan mengekstrak item, qty, dan harga', 'Review hasil dan cocokkan dengan master produk', 'Tambahkan ke keranjang atau pembelian'],
                'tip' => 'Gunakan gambar jelas dan pencahayaan cukup untuk hasil OCR lebih akurat.',
            ],
            'pembelian' => [
                'title' => 'Buat Faktur Pembelian',
                'steps' => ['Buka Pembelian > Create', 'Pilih supplier', 'Pilih metode pembayaran: Tunai atau Kredit', 'Input item, qty, harga beli', 'Simpan — stok bertambah dan harga modal terbaru tercatat'],
                'tip' => 'Jika Kredit, jangan lupa isi jatuh tempo dan status hutang akan tercatat.',
            ],
            'self-service' => [
                'title' => 'Verifikasi Pesanan Self-Service',
                'steps' => ['Buka Admin > Self-Service', 'Lihat daftar pesanan Pending', 'Klik Verifikasi untuk konversi menjadi Penjualan', 'Cek stok dan harga sebelum verifikasi'],
                'tip' => 'Jika ada keraguan, klik Reject dan hubungi customer.',
            ],
        ];

        $key = $feature ?: $context;
        $guide = $guides[$key] ?? null;

        if (!$guide) {
            return json_encode([
                'title' => 'Panduan Umum',
                'steps' => ['Gunakan menu navigasi utama', 'Pilih modul sesuai kebutuhan', 'Jika butuh bantuan detail, tanyakan fitur spesifik seperti "cara import produk" atau "cara pakai POS custom"'],
                'tip' => 'Assistant bisa menjelaskan fitur tertentu, coba tanyakan dengan lebih spesifik.',
            ]);
        }

        return json_encode($guide);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feature' => $schema->string()->nullable(),
            'context' => $schema->string()->nullable(),
        ];
    }
}
