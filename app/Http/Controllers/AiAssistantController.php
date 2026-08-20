<?php

namespace App\Http\Controllers;

use App\Ai\Tools\LookupCustomerInfo;
use App\Ai\Tools\LookupLowStock;
use App\Ai\Tools\LookupProductInfo;
use App\Ai\Tools\LookupSalesToday;
use App\Ai\Tools\LookupSupplierInfo;
use App\Ai\Tools\LookupTopProducts;
use App\Models\AiActionLog;
use App\Models\Produk;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Tools\Request as ToolRequest;

class AiAssistantController extends Controller
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle incoming chat prompt for AI Assistant Copilot.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|string|in:user,assistant,model',
            'history.*.content' => 'required_with:history|string',
        ]);

        $message = trim($request->input('message'));
        $history = $request->input('history', []);

        $result = $this->aiService->ask($message, $history);

        return response()->json($result);
    }

    public function history(): JsonResponse
    {
        $conversations = Conversation::query()->where('user_id', auth()->id())->with(['messages' => fn ($query) => $query->latest()->limit(50)])->latest('updated_at')->limit(20)->get();

        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    public function suggestions(): JsonResponse
    {
        $user = auth()->user();
        $config = $user->toko?->aiAssistantConfig;
        if ($config && !$config->proactive_enabled) return response()->json(['success' => true, 'suggestions' => []]);

        $lowStock = Produk::withoutGlobalScopes()->where('toko_id', $user->toko_id)->whereColumn('stok', '<=', 'stok_minimum')->count();
        $suggestions = [['type' => 'sales', 'message' => 'Lihat ringkasan performa toko hari ini.', 'prompt' => 'Bagaimana performa penjualan toko hari ini?']];
        if ($lowStock > 0) $suggestions[] = ['type' => 'inventory', 'message' => "Ada {$lowStock} produk yang perlu diperiksa stoknya.", 'prompt' => 'Buat laporan stok menipis dan saran restock.'];

        return response()->json(['success' => true, 'suggestions' => $suggestions]);
    }

    public function action(Request $request): JsonResponse
    {
        $validated = $request->validate(['action' => 'required|string|in:lookup_sales_today,lookup_low_stock,lookup_top_products,lookup_customer_info,lookup_product_info,lookup_supplier_info', 'parameters' => 'nullable|array', 'confirmed' => 'required|accepted']);
        $user = auth()->user();
        $tool = match ($validated['action']) {
            'lookup_sales_today' => new LookupSalesToday($user->toko_id),
            'lookup_low_stock' => new LookupLowStock($user->toko_id),
            'lookup_top_products' => new LookupTopProducts($user->toko_id),
            'lookup_customer_info' => new LookupCustomerInfo($user->toko_id),
            'lookup_product_info' => new LookupProductInfo($user->toko_id),
            'lookup_supplier_info' => new LookupSupplierInfo($user->toko_id),
        };
        $rawResult = (string) $tool->handle(new ToolRequest($validated['parameters'] ?? []));
        $result = json_decode($rawResult, true) ?? ['value' => $rawResult];
        AiActionLog::create(['toko_id' => $user->toko_id, 'user_id' => $user->id, 'action_type' => 'lookup', 'tool_name' => $validated['action'], 'parameters' => $validated['parameters'] ?? [], 'result' => $result, 'executed_at' => now()]);

        return response()->json(['success' => true, 'action' => $validated['action'], 'result' => $result]);
    }
}
