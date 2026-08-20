<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class ScanReceiptAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'Baca foto struk/belanja. Ekstrak semua produk yang terlihat. Harga adalah harga satuan Rupiah, isi 0 jika tidak terbaca. Jangan menebak produk yang tidak terlihat.';
    }

    public function schema(JsonSchema $schema): array
    {
        return ['items' => $schema->array()->items($schema->object(fn ($s) => ['name' => $s->string()->required(), 'qty' => $s->integer()->min(1)->required(), 'harga' => $s->number()->min(0)->required()]))->required()];
    }
}
