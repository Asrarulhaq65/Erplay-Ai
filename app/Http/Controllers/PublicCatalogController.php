<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CustomerServiceAgent;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\AiSdkService;
use App\Services\AiInteractionLogger;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    public function index(string $slug): View
    {
        $toko = $this->store($slug);
        $categories = KategoriProduk::withoutGlobalScopes()->where('toko_id', $toko->id)->orderBy('nama_kategori')->get(['id', 'nama_kategori']);

        return view('pages.public-catalog.index', [
            'toko' => $toko,
            'categories' => $categories,
            'assistantName' => $toko->aiAssistantConfig?->assistant_name ?: $toko->nama_toko . ' Assistant',
            'productsUrl' => route('katalog.products', $slug),
            'chatUrl' => route('katalog.chat', $slug),
        ]);
    }

    public function products(string $slug, Request $request): JsonResponse
    {
        $toko = $this->store($slug);
        $query = Produk::withoutGlobalScopes()->where('toko_id', $toko->id)->with('kategori:id,nama_kategori');
        if ($term = trim((string) $request->input('query'))) $query->where(fn ($q) => $q->where('nama_produk', 'like', "%{$term}%")->orWhere('barcode', $term));
        if ($category = $request->integer('category')) $query->where('kategori_id', $category);

        $products = $query->orderBy('nama_produk')->paginate(24)->through(fn ($product) => $this->publicProduct($product));

        return response()->json($products);
    }

    public function chat(string $slug, Request $request, AiSdkService $sdk, AiInteractionLogger $interactionLogger): JsonResponse
    {
        $toko = $this->store($slug);
        $message = trim(strip_tags((string) $request->input('message')));
        $request->validate(['message' => ['required', 'string', 'max:500'], 'history' => ['nullable', 'array', 'max:12']]);
        if ($message === '') return response()->json(['reply' => 'Silakan tuliskan pertanyaan Anda.', 'is_mock' => true]);

        $limiter = app(RateLimiter::class);
        $rateKey = 'public-catalog-chat:' . $slug . ':' . $request->ip();
        if ($limiter->tooManyAttempts($rateKey, 30)) return response()->json(['reply' => 'Batas pertanyaan sementara tercapai. Silakan coba lagi beberapa menit lagi.', 'is_mock' => true], 429);
        $limiter->hit($rateKey, 300);

        if (!$sdk->hasCredentials($toko)) return response()->json(['reply' => 'Maaf, layanan chat toko sedang belum dikonfigurasi. Silakan hubungi toko di ' . $toko->no_telepon . '.', 'is_mock' => true]);
        [$provider, $model] = $sdk->prepare($toko);
        $history = $request->input('history', []);
        $prompt = $history ? "Riwayat singkat:\n" . json_encode($history, JSON_UNESCAPED_UNICODE) . "\n\nPertanyaan customer:\n{$message}" : $message;

        try {
            $startedAt = microtime(true);
            $response = CustomerServiceAgent::make(toko: $toko)->prompt($prompt, provider: $provider, model: $model, timeout: 20);
            $sdk->record($toko, $response);
            $interactionLogger->log($response, $toko, null, $message, $startedAt, CustomerServiceAgent::class);
            return response()->json(['reply' => $response->text, 'is_mock' => false]);
        } catch (\Throwable) {
            return response()->json(['reply' => 'Maaf, saya belum bisa menjawab saat ini. Silakan hubungi toko di ' . $toko->no_telepon . '.', 'is_mock' => true]);
        }
    }

    private function store(string $slug): Toko
    {
        return Toko::query()->where('catalog_slug', $slug)->where('catalog_enabled', true)->firstOrFail();
    }

    private function publicProduct(Produk $product): array
    {
        return ['id' => $product->id, 'name' => $product->nama_produk, 'price' => (float) $product->harga_jual_umum, 'stock' => $product->stok <= 0 ? 'Habis' : ($product->stok <= $product->stok_minimum ? 'Menipis' : 'Tersedia'), 'unit' => $product->satuan, 'barcode' => $product->barcode, 'image' => $product->gambar ? asset('storage/' . $product->gambar) : null, 'category' => $product->kategori?->nama_kategori];
    }
}
