# Execution Prompt — Migrasi ERPlay AI ke Laravel AI SDK

Gunakan prompt ini untuk agent coding (OpenCode/Claude Code/Cursor). Jangan skip langkah. Jangan ubah route/response JSON yang ada.

---

## Tujuan
Migrasi seluruh AI di `C:\laragon\www\Mini-ERP-By-Arul` agar lewat **Laravel AI SDK**, tanpa mengubah perilaku frontend.

---

## Langkah 1: Install & Publish Laravel AI SDK

Jalankan di terminal dari folder proyek:
```bash
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
php artisan migrate
```

Verifikasi:
- File `config/ai.php` muncul
- Tabel `agent_conversations` dan `agent_conversation_messages` terbuat

---

## Langkah 2: Konfigurasi `config/ai.php`

Edit `config/ai.php`:

```php
'providers' => [
    'gemini' => [
        'driver' => 'gemini',
        'key' => env('GEMINI_API_KEY', config('services.gemini.api_key')),
        'models' => [
            'text' => ['default' => env('GEMINI_MODEL', 'gemini-1.5-flash')],
            'vision' => ['default' => env('GEMINI_MODEL', 'gemini-1.5-flash')],
        ],
    ],
    'local-ollama' => [
        'driver' => 'openai-compatible',
        'url' => env('OLLAMA_URL', 'http://localhost:11434/v1'),
        'key' => env('OLLAMA_API_KEY', 'ollama'),
        'models' => [
            'text' => ['default' => env('OLLAMA_MODEL', 'llama3.2')],
        ],
    ],
],
```

Catatan:
- Jangan ubah key `.env`. Tambahkan hanya jika belum ada.
- Jika `config/services/gemini.php` ada, samakan value-nya.

---

## Langkah 3: Buat Agent Inti `ErpCopilotAgent`

Jalankan:
```bash
php artisan make:agent ErpCopilotAgent
```

Implementasi Wajib:
- File: `app/Ai/Agents/ErpCopilotAgent.php`
- Interface: `Agent`, `Conversational`, `HasTools`
- Method `instructions()`: salin persis system prompt dari `AiAssistantService::buildErpSystemContext()`.
- Method `messages()`: load history dari tabel `agent_conversation_messages` milik user/toko yang login.
- Method `tools()`: daftar tools:
  - `App\Ai\Tools\LookupSalesToday`
  - `App\Ai\Tools\LookupLowStock`
  - `App\Ai\Tools\LookupTopProducts`
  - `App\Ai\Tools\LookupCustomerInfo`
- Method `schema()`: return schema structured output jika dibutuhkan, minimal:
  ```php
  return [
      'reply' => $schema->string()->required(),
      'is_mock' => $schema->boolean()->default(false),
  ];
  ```

Setiap tool WAJIB:
- Terima `toko_id` dari context
- Filter query berdasarkan `toko_id`
- Return array yang bisa di-`json_encode`

---

## Langkah 4: Refactor `AiAssistantService`

Ubah `app/Services/AiAssistantService.php` menjadi **thin wrapper** yang memanggil Agent SDK.

Hapus:
- Semua `Http::post()` langsung ke Gemini
- Manual `candidateModels` fallback
- Manual `usageMetadata` handling

Ganti dengan:
```php
$agent = new \App\Ai\Agents\ErpCopilotAgent($toko, $user);
$result = $agent->prompt(
    $userMessage,
    provider: 'gemini',
    model: $toko?->gemini_model ?? null,
    history: $chatHistory
);

return [
    'success' => true,
    'reply' => $result->text,
    'is_mock' => false,
];
```

Penting:
- Jangan ubah signature method `ask(string $userMessage, array $chatHistory = []): array`
- Jangan ubah return format array yang diharapkan controller
- Tetap record usage lewat SDK jika SDK menyediakan hooks

---

## Langkah 5: Refactor `GeminiScanService` → Agent Vision

Opsi paling cepat: jangan ubah seluruh service, tapi **buat Agent vision** baru:

```bash
php artisan make:agent ScanReceiptAgent
```

- File: `app/Ai/Agents/ScanReceiptAgent.php`
- Implement `HasAttachments`
- Method `instructions()`: prompt untuk ekstrak item struk dalam format JSON array
- Method `attachments()`: return array `['mime' => $mime, 'data' => $base64]`

Kemudian update `GeminiScanService::extractItems()`:
- Hapus direct `Http::post()` ke Gemini
- Panggil `ScanReceiptAgent` via SDK:
  ```php
  $agent = new \App\Ai\Agents\ScanReceiptAgent();
  $result = $agent->prompt(
      'Ekstrak semua produk dari gambar ini',
      attachments: [$image]
  );
  ```
- Parse response JSON items seperti sebelumnya
- Tetap panggil `ProductFuzzyMatcher` untuk match ke database

---

## Langkah 6: Refactor `VoiceTransactionService` → Agent Voice/Text

Buat agent baru:
```bash
php artisan make:agent VoiceCommandAgent
```

- File: `app/Ai/Agents/VoiceCommandAgent.php`
- Implement `HasStructuredOutput`
- Schema output:
  ```php
  return [
      'intent' => $schema->string()->required(),
      'items' => $schema->array()->items([
          'produk_id' => $schema->integer()->nullable(),
          'nama_produk' => $schema->string()->required(),
          'qty' => $schema->integer()->default(1),
          'harga_satuan' => $schema->integer()->default(0),
      ]),
      'diskon' => $schema->integer()->default(0),
      'nominal_bayar' => $schema->integer()->nullable(),
      'metode_pembayaran' => $schema->string()->nullable(),
      'voice_response' => $schema->string()->required(),
  ];
  ```

Update `VoiceTransactionService::parseVoiceCommand()`:
- Hapus direct HTTP call
- Panggil `VoiceCommandAgent` via SDK
- Tetap fallback ke `ProductFuzzyMatcher` untuk `produk_id` yang null

---

## Langkah 7: Update `AiSettingController::testConnection()`

Hapus manual `Http::post()` ke Gemini.

Ganti dengan:
```php
$agent = new \App\Ai\Agents\ErpCopilotAgent($toko, $user);
$result = $agent->prompt('Tes koneksi API Mini ERP. Balas 1 kata: OK.', provider: 'gemini');

return response()->json([
    'success' => true,
    'message' => 'Koneksi berhasil via Laravel AI SDK.',
    'working_model' => $result->model,
    'usage' => $result->usage,
]);
```

Catatan:
- Cek apakah SDK expose `usage` atau `usageMetadata` di result object. Jika beda nama, samakan.
- Tetap record usage ke `$toko->recordAiUsage()` jika tersedia dari SDK.

---

## Langkah 8: Verifikasi

Setelah migrasi selesai, jalankan:

1. `php artisan migrate` — pastikan tabel `agent_conversations` terisi
2. Smoke test endpoint yang sudah ada:
   - `POST /ai-assistant/chat` — harus return JSON sama seperti sebelum
   - `POST /pos/scan/process` — harus return JSON `{ok, items, ...}`
   - `POST /pos/voice/process` — harus return JSON `{ok, result, ...}`
3. Cek tabel `agent_conversations` dan `agent_conversation_messages` setelah chat
4. Cek tabel `toko` kolom `ai_total_requests` bertambah setelah test

---

## Yang DILARANG diubah

- Jangan ubah route di `routes/web.php`
- Jangan ubah controller method signature yang dipakai frontend
- Jangan ubah format JSON response yang di-fetch oleh JS
- Jangan ubah Blade views
- Jangan hapus `ProductFuzzyMatcher` — masih dipakai sebagai fallback

---

## Deliverables

Kirimkan setelah selesai:
1. Ringkasan file yang diubah/ditambah
2. Struktur folder `app/Ai/Agents/` dan `app/Ai/Tools/`
3. Isi `config/ai.php` final
4. Output `php artisan migrate` berhasil
5. Bukti smoke test (response JSON sample) dari 3 endpoint

---

## Troubleshooting

- Jika `php artisan make:agent` tidak ditemukan, berarti `laravel/ai` belum install dengan benar. Cek `composer show laravel/ai`.
- Jika error "Class not found" untuk Agent contract, jalankan `composer dump-autoload`.
- Jika response dari SDK beda format dengan yang diharapkan controller, buat **adapter** di service layer, jangan ubah controller.
