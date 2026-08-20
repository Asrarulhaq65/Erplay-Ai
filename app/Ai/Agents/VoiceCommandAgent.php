<?php

namespace App\Ai\Agents;

use App\Models\Produk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class VoiceCommandAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public int $tokoId) {}

    public function instructions(): string
    {
        $catalog = Produk::withoutGlobalScopes()->where('toko_id', $this->tokoId)->get(['id', 'nama_produk', 'barcode', 'harga_jual_umum', 'satuan', 'stok'])->map(fn ($p) => "ID:{$p->id} | {$p->nama_produk} | Rp{$p->harga_jual_umum} | stok {$p->stok}")->implode("\n");
        return "Anda adalah asisten kasir POS Indonesia. Tugas: (1) cocokkan transkrip suara dengan katalog toko berikut, (2) kembalikan intent: add_to_cart, query_stock, atau checkout, (3) jika produk tidak ditemukan, sebutkan kemungkinan nama yang benar, (4) jika stok 0 atau kurang dari qty, laporkan keterbatasan stok. Katalog:\n{$catalog}";
    }

    public function schema(JsonSchema $schema): array
    {
        return ['intent' => $schema->string()->enum(['add_to_cart', 'query_stock', 'checkout'])->required(), 'items' => $schema->array()->items($schema->object(fn ($s) => ['produk_id' => $s->integer(), 'nama_produk' => $s->string()->required(), 'qty' => $s->integer()->min(1)->required(), 'harga_satuan' => $s->number()->min(0)->required()]))->required(), 'diskon' => $schema->number()->min(0), 'nominal_bayar' => $schema->number()->min(0), 'metode_pembayaran' => $schema->string(), 'voice_response' => $schema->string()->required()];
    }
}
