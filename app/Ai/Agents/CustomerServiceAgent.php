<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ExplainFeature;
use App\Ai\Tools\LookupHargaPublic;
use App\Ai\Tools\LookupInfoToko;
use App\Ai\Tools\LookupProdukPublic;
use App\Ai\Tools\LookupStokPublic;
use App\Models\Toko;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(4)]
class CustomerServiceAgent implements Agent, HasTools
{
    use Promptable;

    public function __construct(public Toko $toko) {}

    public function instructions(): Stringable|string
    {
        $name = $this->toko->aiAssistantConfig?->assistant_name ?: $this->toko->nama_toko . ' Assistant';
        return "Anda adalah {$name}, asisten publik untuk toko {$this->toko->nama_toko}. Jawab Bahasa Indonesia dengan ramah, singkat, dan jelas. Anda hanya boleh membantu tentang produk publik, harga jual umum, ketersediaan stok, kategori, alamat, kontak, dan cara menghubungi toko. Jangan pernah membocorkan harga modal, harga member/rekan/motoris, penjualan, laba, supplier, pelanggan, konfigurasi, atau data internal. Jangan melakukan transaksi atau checkout. Jika di luar cakupan, arahkan customer menghubungi toko di {$this->toko->no_telepon}.";
    }

    public function tools(): iterable
    {
        return [new LookupProdukPublic($this->toko->id), new LookupHargaPublic($this->toko->id), new LookupStokPublic($this->toko->id), new LookupInfoToko($this->toko), new ExplainFeature];
    }
}
