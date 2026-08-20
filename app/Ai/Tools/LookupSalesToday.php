<?php

namespace App\Ai\Tools;

use App\Models\Penjualan;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupSalesToday implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Ambil ringkasan penjualan lunas hari ini untuk toko pengguna.'; }
    public function handle(Request $request): Stringable|string { $q = Penjualan::withoutGlobalScopes()->where('toko_id', $this->tokoId)->whereDate('created_at', today())->where('status_pembayaran', 'Lunas'); return json_encode(['transactions' => $q->count(), 'total' => $q->sum('total_bayar')]); }
    public function schema(JsonSchema $schema): array { return []; }
}
