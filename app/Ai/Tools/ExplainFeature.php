<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ExplainFeature implements Tool
{
    public function description(): Stringable|string { return 'Jelaskan cara memesan atau menghubungi toko. Jangan mengakses data internal.'; }
    public function handle(Request $request): Stringable|string { return 'Customer dapat menanyakan harga atau stok di katalog ini, lalu menghubungi toko melalui nomor kontak yang tercantum untuk melakukan pemesanan.'; }
    public function schema(JsonSchema $schema): array { return []; }
}
