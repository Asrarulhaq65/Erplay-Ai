<?php

namespace App\Services;

use App\Models\Produk;

/**
 * ProductFuzzyMatcher — punya nama produk (dari AI),
 * cari produk yang paling mirip di database toko ini.
 *
 * Strategi dua langkah:
 *   1. Cari via LIKE '%name%' (lebih longgar tapi cepat)
 *   2. Untuk tiap calon + overall fallback, hitung skor similar_text
 *   3. Threshold >= 60% dianggap "tersedia", bawah itu = "barang baru"
 */
class ProductFuzzyMatcher
{
    /** Skor minimum untuk dianggap match */
    public const THRESHOLD = 60;

    /**
     * Cari produk terbaik untuk sebuah nama dari AI.
     *
     * @param string $aiName
     * @return array{produk: ?Produk, score: int, status: string, candidates: array<int, array{produk:Produk, score:int}>, catatan: string}
     */
    public function findBestMatch(string $aiName): array
    {
        $aiName = trim($aiName);
        if ($aiName === '') {
            return ['produk' => null, 'score' => 0, 'status' => 'barang_baru', 'candidates' => [], 'catatan' => ''];
        }

        // 1. Cari calon via LIKE — ambil semua produk yang mengandung kata kunci
        $tokens = array_filter(explode(' ', $aiName), fn ($t) => strlen($t) >= 3);
        $query  = Produk::query();

        if (!empty($tokens)) {
            $query->where(function ($q) use ($tokens, $aiName) {
                $q->where('nama_produk', 'LIKE', "%{$aiName}%");
                foreach ($tokens as $t) {
                    $q->orWhere('nama_produk', 'LIKE', "%{$t}%");
                }
                // Cari juga via barcode kalau ada angka
                if (preg_match('/\d{4,}/', $aiName)) {
                    $q->orWhere('barcode', 'LIKE', '%' . preg_replace('/\D/', '', $aiName) . '%');
                }
            });
        } else {
            $query->where('nama_produk', 'LIKE', "%{$aiName}%");
        }

        $candidates = $query->limit(20)->get();

        // 2. Jika tidak ada calon via LIKE, ambil semua produk (fallback)
        if ($candidates->isEmpty()) {
            $candidates = Produk::limit(50)->get();
        }

        // 3. Hitung skor similar_text untuk semua calon, ambil top
        $scored = [];
        foreach ($candidates as $produk) {
            $score = $this->similarity($aiName, $produk->nama_produk);
            $scored[] = ['produk' => $produk, 'score' => (int) $score];
        }
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        // Ambil top 5 calon untuk preview dropdown
        $topCandidates = array_slice($scored, 0, 5);

        $best   = $topCandidates[0] ?? null;
        $status = ($best && $best['score'] >= self::THRESHOLD) ? 'tersedia' : 'barang_baru';
        $catatan = $status === 'tersedia'
            ? "Cocok {$best['score']}% dengan produk database"
            : 'Produk belum terdaftar di master data — perlu ditambahkan admin';

        return [
            'produk'     => $status === 'tersedia' ? $best['produk'] : null,
            'score'      => $best['score'] ?? 0,
            'status'     => $status,
            'candidates' => array_map(fn($c) => [
                'id'        => $c['produk']->id,
                'nama'      => $c['produk']->nama_produk,
                'barcode'   => $c['produk']->barcode,
                'harga'     => (float) $c['produk']->harga_jual_umum,
                'satuan'    => $c['produk']->satuan,
                'stok'      => $c['produk']->stok,
                'score'     => $c['score'],
            ], $topCandidates),
            'catatan'    => $catatan,
        ];
    }

    /**
     * Skor kemiripan 0-100.
     * Menggunakan similar_text lalu normalize ke persen.
     * Untuk substring match (kalau A ada di B), boost 10 poin.
     */
    private function similarity(string $a, string $b): float
    {
        $aLow = strtolower(trim($a));
        $bLow = strtolower(trim($b));
        if ($aLow === '' || $bLow === '') return 0;
        if ($aLow === $bLow) return 100;

        similar_text($aLow, $bLow, $percent);
        $score = (float) $percent;

        // Boost substring (mis. "Indomie Goreng" ≈ "Indomie Goreng Spesial")
        if (str_contains($aLow, $bLow) || str_contains($bLow, $aLow)) {
            $score = max($score, 85);
        }

        return min(100, $score);
    }
}