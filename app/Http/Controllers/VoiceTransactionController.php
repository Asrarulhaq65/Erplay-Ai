<?php

namespace App\Http\Controllers;

use App\Services\VoiceTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoiceTransactionController extends Controller
{
    public function __construct(
        private readonly VoiceTransactionService $voiceService
    ) {}

    /**
     * Process voice command transcript and parse into POS cart items.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processVoice(Request $request): JsonResponse
    {
        $request->validate([
            'transcript' => 'required|string|max:1000',
        ], [
            'transcript.required' => 'Transkrip suara tidak boleh kosong.',
        ]);

        $transcript = trim($request->input('transcript'));
        $result = $this->voiceService->parseVoiceCommand($transcript);

        if (!$result['success']) {
            return response()->json([
                'ok' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'result' => $result['data'],
        ]);
    }
}
