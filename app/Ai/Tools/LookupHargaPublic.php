<?php

namespace App\Ai\Tools;

use App\Models\Produk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupHargaPublic implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Cari harga jual umum produk. Jangan tampilkan harga tier atau harga modal.'; }
    public function handle(Request $request): Stringable|string { return Produk::withoutGlobalScopes()->where('toko_id', $this->tokoId)->where('nama_produk', 'like', '%' . trim((string) ($request['query'] ?? '')) . '%')->limit(10)->get(['nama_produk', 'harga_jual_umum', 'satuan'])->toJson(); }
    public function schema(JsonSchema $schema): array { return ['query' => $schema->string()->required()]; }
}
