<?php

namespace App\Ai\Tools;

use App\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupSupplierInfo implements Tool
{
    public function __construct(private readonly int $tokoId) {}
    public function description(): Stringable|string { return 'Cari data supplier yang terdaftar pada toko.'; }
    public function handle(Request $request): Stringable|string
    {
        $query = trim((string) ($request['query'] ?? ''));
        return Supplier::withoutGlobalScopes()->where('toko_id', $this->tokoId)->where('nama_supplier', 'like', "%{$query}%")->limit(10)->get()->toJson();
    }
    public function schema(JsonSchema $schema): array { return ['query' => $schema->string()->required()]; }
}
