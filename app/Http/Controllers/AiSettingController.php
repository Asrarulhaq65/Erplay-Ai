<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use App\Ai\Agents\ErpCopilotAgent;
use App\Services\AiSdkService;
use App\Services\ProviderModelDiscovery;
use App\Services\VisionModelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSettingController extends Controller
{
    /**
     * Display the AI Setup configuration page.
     *
     * @return View
     */
    public function index(): View
    {
        $user = auth()->user();
        $toko = $user?->toko;
        $envKey = env('GEMINI_API_KEY', config('services.gemini.api_key'));
        $hasEnvKey = !empty($envKey);
        $hasByokKey = !empty($toko?->ai_api_key);

        return view('pages.pengaturan.ai.index', compact('toko', 'hasEnvKey', 'hasByokKey', 'envKey'));
    }

    /**
     * Update AI configuration for current store.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'ai_provider'       => 'required|string|in:gemini,openai-compatible',
            'ai_api_key'        => 'nullable|string|max:10000',
            'ai_base_url'       => 'nullable|url|max:255',
            'ai_model'          => 'required|string|max:120',
            'ai_enabled'        => 'nullable|boolean',
            'ai_vision_enabled' => 'nullable|boolean',
        ]);

        $toko = auth()->user()?->toko;
        if (!$toko) {
            return redirect()->back()->with('error', 'Toko tidak ditemukan.');
        }

        $toko->update([
            'ai_provider'      => $request->input('ai_provider', 'gemini'),
            'ai_base_url'      => $request->input('ai_base_url'),
            'ai_model'         => $request->input('ai_model'),
            'gemini_model'     => $request->input('ai_model', 'gemini-1.5-flash-latest'),
            'ai_enabled'        => $request->has('ai_enabled'),
            'ai_vision_enabled' => $request->has('ai_vision_enabled'),
        ]);
        if ($request->filled('ai_api_key')) $toko->update(['ai_api_key' => trim($request->input('ai_api_key'))]);

        return redirect()->route('pengaturan.ai.index')->with('success', 'Pengaturan AI & Integrasi Vision berhasil diperbarui!');
    }

    public function assistant(): View
    {
        $config = auth()->user()->toko?->aiAssistantConfig;

        return view('pages.pengaturan.ai.assistant', compact('config'));
    }

    public function updateAssistant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assistant_name' => ['required', 'string', 'max:80'],
            'personality' => ['required', 'in:profesional,santai,formal'],
            'greeting_message' => ['nullable', 'string', 'max:250'],
            'proactive_enabled' => ['nullable', 'boolean'],
        ]);
        $toko = auth()->user()->toko;
        $toko->aiAssistantConfig()->updateOrCreate([], [
            ...$validated,
            'proactive_enabled' => $request->boolean('proactive_enabled'),
        ]);

        return redirect()->route('pengaturan.ai.assistant')->with('success', 'Profil ERPlay AI Assistant berhasil diperbarui.');
    }

    /**
     * Test connection to Gemini API Key from .env with smart model fallback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testConnection(Request $request, AiSdkService $sdk, \App\Services\AiInteractionLogger $interactionLogger): JsonResponse
    {
        $user = auth()->user();
        $toko = $user?->toko;
        $provider = $request->input('provider', $toko?->ai_provider ?? 'gemini');
        $apiKey = trim($request->input('api_key', '')) ?: ($toko?->ai_api_key ?: ($provider === 'gemini' ? ($toko?->gemini_api_key ?: env('GEMINI_API_KEY', config('services.gemini.api_key'))) : env('OPENAI_COMPATIBLE_API_KEY')));
        $baseUrl = trim($request->input('base_url', '')) ?: $toko?->ai_base_url;
        $selectedModel = $request->input('model', $toko?->ai_model ?: $toko?->gemini_model ?: 'gemini-1.5-flash-latest');
        $visionEnabled = (bool) ($request->input('ai_vision_enabled', $toko?->ai_vision_enabled ?? false));

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key belum diisi. Masukkan API key BYOK untuk provider yang dipilih.',
            ]);
        }
        if ($provider === 'openai-compatible' && empty($baseUrl)) {
            return response()->json(['success' => false, 'message' => 'Base URL wajib diisi untuk provider OpenAI-compatible.']);
        }

        try {
            $startedAt = microtime(true);
            [$resolvedProvider, $model, $visionRequired] = $sdk->prepare($toko, [
                'provider' => $provider,
                'key' => $apiKey,
                'base_url' => $baseUrl,
                'model' => $selectedModel,
                'vision_required' => $visionEnabled,
            ]);

            if ($toko && $model !== $selectedModel) {
                $toko->update([
                    'ai_model' => $model,
                    'gemini_model' => $provider === 'gemini' ? $model : $toko->gemini_model,
                    'ai_vision_enabled' => $visionRequired,
                ]);
            }
            $response = ErpCopilotAgent::make(user: $user)->prompt('Tes koneksi API ERPlay AI. Balas 1 kata: OK.', provider: $resolvedProvider, model: $model, timeout: 10);
            $sdk->record($user?->toko, $response);
            if ($user?->toko) $interactionLogger->log($response, $user->toko, $user->getKey(), 'Tes koneksi API ERPlay AI.', $startedAt, ErpCopilotAgent::class);
            $user?->toko?->refresh();
            return response()->json([
                'success' => true,
                'message' => $model !== $selectedModel
                    ? "Vision aktif — model otomatis diganti ke \"$model\" karena \"$selectedModel\" tidak mendukung image input."
                    : "Koneksi provider berhasil! Model \"$selectedModel\" aktif & siap digunakan.",
                'working_model' => $model,
                'requested_model' => $selectedModel,
                'model_changed' => $model !== $selectedModel,
                'provider' => $resolvedProvider,
                'vision_enabled' => $visionRequired,
                'usage' => [
                    'promptTokenCount' => $response->usage->promptTokens,
                    'candidatesTokenCount' => $response->usage->completionTokens,
                    'totalTokenCount' => $response->usage->promptTokens + $response->usage->completionTokens,
                ],
                'toko_usage' => [
                    'total_requests' => number_format($user?->toko?->ai_total_requests ?? 0),
                    'prompt_tokens' => number_format($user?->toko?->ai_prompt_tokens ?? 0),
                    'completion_tokens' => number_format($user?->toko?->ai_completion_tokens ?? 0),
                    'total_tokens' => number_format($user?->toko?->ai_total_tokens ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke provider AI. Detail Error: ' . $e->getMessage(),
                'detected_model' => $model,
                'provider' => $resolvedProvider,
            ]);
        }
    }

    public function discoverModels(Request $request, ProviderModelDiscovery $discovery, AiSdkService $sdk): JsonResponse
    {
        $user = auth()->user();
        $toko = $user?->toko;

        $provider = $request->input('provider', $toko?->ai_provider ?? 'gemini');
        $apiKey = trim($request->input('api_key', '')) ?: ($toko?->ai_api_key ?: ($provider === 'gemini' ? ($toko?->gemini_api_key ?: env('GEMINI_API_KEY', config('services.gemini.api_key'))) : env('OPENAI_COMPATIBLE_API_KEY')));
        $baseUrl = trim($request->input('base_url', '')) ?: ($toko?->ai_base_url ?? 'https://api.openai.com/v1');
        $visionRequired = (bool) ($request->input('ai_vision_enabled', $toko?->ai_vision_enabled ?? false));
        $forceRefresh = (bool) $request->input('refresh', false);

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key belum diisi. Masukkan API key untuk mendeteksi model.',
            ]);
        }

        if ($provider === 'openai-compatible' && empty($baseUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Base URL wajib diisi untuk provider OpenAI-compatible.',
            ]);
        }

        [$resolvedProvider, $model, $visionRequired] = $sdk->prepare($toko, [
            'provider' => $provider,
            'key' => $apiKey,
            'base_url' => $baseUrl,
            'model' => $request->input('model'),
            'vision_required' => $visionRequired,
        ]);

        $result = $discovery->discoverModels($toko, $provider, $apiKey, $baseUrl, $forceRefresh);

        if ($result['status'] === 'error') {
            return response()->json([
                'success' => true,
                'fallback' => true,
                'models' => [],
                'selected_model' => $model,
                'default_vision_model' => app(VisionModelResolver::class)->getDefaultVisionModel($provider),
                'message' => $result['message'],
            ]);
        }

        $filteredModels = $result['models'];
        if ($visionRequired) {
            $filteredModels = array_filter($filteredModels, fn($m) => $m['vision_capable']);
        }

        return response()->json([
            'success' => true,
            'fallback' => false,
            'models' => array_values($filteredModels),
            'selected_model' => $model,
            'default_vision_model' => app(VisionModelResolver::class)->getDefaultVisionModel($provider),
            'message' => $result['message'],
        ]);
    }
}
