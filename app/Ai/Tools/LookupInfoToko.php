<?php

namespace App\Ai\Tools;

use App\Models\Toko;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupInfoToko implements Tool
{
    public function __construct(private readonly Toko $toko) {}
    public function description(): Stringable|string { return 'Ambil nama toko, slogan, alamat, dan nomor kontak publik.'; }
    public function handle(Request $request): Stringable|string { return json_encode(['nama_toko' => $this->toko->nama_toko, 'slogan' => $this->toko->slogan_struk, 'alamat' => $this->toko->alamat, 'no_telepon' => $this->toko->no_telepon]); }
    public function schema(JsonSchema $schema): array { return []; }
}
