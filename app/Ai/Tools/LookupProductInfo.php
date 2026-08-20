<?php

namespace App\Ai\Tools;

use App\Models\Produk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupProductInfo implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Cari detail produk, harga bertingkat, barcode, dan stok toko.'; }
    public function handle(Request $request): Stringable|string
    {
        $query = trim((string) ($request['query'] ?? ''));
        return Produk::withoutGlobalScopes()->where('toko_id', $this->tokoId)->where(fn ($q) => $q->where('nama_produk', 'like', "%{$query}%")->orWhere('barcode', $query))->limit(10)->get(['nama_produk', 'barcode', 'stok', 'harga_jual_umum', 'harga_jual_member', 'harga_jual_rekan', 'harga_jual_motoris'])->toJson();
    }
    public function schema(JsonSchema $schema): array { return ['query' => $schema->string()->required()]; }
}
