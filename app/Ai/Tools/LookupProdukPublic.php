<?php

namespace App\Ai\Tools;

use App\Models\Produk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupProdukPublic implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Cari produk publik berdasarkan nama atau barcode.'; }
    public function handle(Request $request): Stringable|string { $query = trim((string) ($request['query'] ?? '')); return Produk::withoutGlobalScopes()->where('toko_id', $this->tokoId)->where(fn ($q) => $q->where('nama_produk', 'like', "%{$query}%")->orWhere('barcode', $query))->with('kategori:id,nama_kategori')->limit(10)->get(['id', 'kategori_id', 'nama_produk', 'barcode', 'satuan', 'stok'])->toJson(); }
    public function schema(JsonSchema $schema): array { return ['query' => $schema->string()->required()]; }
}
