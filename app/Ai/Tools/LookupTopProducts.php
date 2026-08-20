<?php

namespace App\Ai\Tools;

use App\Models\PenjualanDetail;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupTopProducts implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Daftar produk terlaris pada bulan berjalan.'; }
    public function handle(Request $request): Stringable|string { return PenjualanDetail::query()->whereHas('penjualan', fn ($q) => $q->withoutGlobalScopes()->where('toko_id', $this->tokoId)->where('status_pembayaran', 'Lunas')->where('created_at', '>=', now()->startOfMonth()))->selectRaw('produk_id, SUM(qty) as total_qty')->groupBy('produk_id')->orderByDesc('total_qty')->limit(10)->with('produk:id,nama_produk')->get()->toJson(); }
    public function schema(JsonSchema $schema): array { return []; }
}
