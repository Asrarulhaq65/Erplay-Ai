<?php

namespace App\Ai\Tools;

use App\Models\Produk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupLowStock implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Daftar produk yang stoknya menipis atau habis.'; }
    public function handle(Request $request): Stringable|string { return Produk::withoutGlobalScopes()->where('toko_id', $this->tokoId)->whereColumn('stok', '<=', 'stok_minimum')->orderBy('stok')->limit(20)->get(['nama_produk', 'stok', 'stok_minimum'])->toJson(); }
    public function schema(JsonSchema $schema): array { return []; }
}
