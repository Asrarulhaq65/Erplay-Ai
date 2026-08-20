<?php

namespace App\Ai\Tools;

use App\Models\Produk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupStokPublic implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Cari ketersediaan stok produk secara publik.'; }
    public function handle(Request $request): Stringable|string { return Produk::withoutGlobalScopes()->where('toko_id', $this->tokoId)->where('nama_produk', 'like', '%' . trim((string) ($request['query'] ?? '')) . '%')->limit(10)->get(['nama_produk', 'stok', 'satuan', 'stok_minimum'])->map(fn ($p) => ['nama_produk' => $p->nama_produk, 'status' => $p->stok <= 0 ? 'Habis' : ($p->stok <= $p->stok_minimum ? 'Menipis' : 'Tersedia'), 'satuan' => $p->satuan])->toJson(); }
    public function schema(JsonSchema $schema): array { return ['query' => $schema->string()->required()]; }
}
