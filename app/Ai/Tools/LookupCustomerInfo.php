<?php

namespace App\Ai\Tools;

use App\Models\Pelanggan;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupCustomerInfo implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Cari informasi pelanggan berdasarkan nama atau nomor telepon.'; }
    public function handle(Request $request): Stringable|string { $term = trim((string) ($request['query'] ?? '')); return Pelanggan::withoutGlobalScopes()->where('toko_id', $this->tokoId)->where(fn ($q) => $q->where('nama_pelanggan', 'like', "%{$term}%")->orWhere('no_telepon', 'like', "%{$term}%"))->limit(10)->get()->toJson(); }
    public function schema(JsonSchema $schema): array { return ['query' => $schema->string()->required()]; }
}
