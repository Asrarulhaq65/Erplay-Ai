# Prompt Migrasi ke Laravel AI SDK — ERPlay AI

> **Brand:** ERPlay AI — ERP + Play = santai, cepat, dan ada AI.  
> **Tagline:** *Kelola toko, gak pusing, ada AI yang bantu.*

---

## Konteks Proyek

Kamu akan bekerja di proyek Laravel 13 `C:\laragon\www\Mini-ERP-By-Arul` yang sekarang dikenal sebagai **ERPlay AI**.  
Ini adalah **Retail ERP multi-tenant** untuk pemilik usaha pertokoan (toko kelontong, minimarket), dengan modul inti: inventory/master barang, purchasing/pembelian, sales/POS 2-mode, akuntansi, analytics, self-service, dan audit log.

## Tujuan Utama

Migrasi seluruh implementasi AI yang **saat ini custom** (langsung panggil Gemini REST API via `Http::post()`) agar menggunakan **Laravel AI SDK (`laravel/ai`)** sebagai lapisan resmi.  
Jangan ubah behavior user-facing; yang berubah hanya cara memanggil AI di backend.

## Implementasi AI Saat Ini (yang akan diganti)

1. `app/Services/AiAssistantService.php` — wrapper Gemini untuk AI Copilot chat
2. `app/Services/GeminiScanService.php` — Gemini Vision untuk scan struk/foto
3. `app/Services/VoiceTransactionService.php` — parsing transkrip suara kasir
4. `app/Services/ProductFuzzyMatcher.php` — fuzzy match produk dari hasil AI
5. `app/Http/Controllers/AiAssistantController.php` — endpoint `/ai-assistant/chat`
6. `app/Http/Controllers/PosScanController.php` — endpoint `/pos/scan/process`
7. `app/Http/Controllers/VoiceTransactionController.php` — endpoint `/pos/voice/process`
8. `app/Http/Controllers/SelfServiceController.php` — endpoint self-service voice/scan
9. `database/migrations/2026_07_25_000002_add_ai_settings_to_toko_table.php` & `...000003` — kolom AI di tabel `toko`
10. `routes/web.php` — route `pengaturan.ai.*` dan `ai-assistant/chat`

## Tujuan Akhir

- Setiap panggilan AI harus melewati **Laravel AI SDK** (`laravel/ai`)
- Manfaatkan fitur SDK: Agent, Tools, Streaming, Failover, Usage tracking
- Tetap dukungi **Gemini** sebagai provider utama, dengan **fallback OpenAI-compatible** kalau perlu
- **Jangan ubah URL route, nama controller method, atau response JSON yang dikirim ke frontend** — backward compatibility penuh
- Multi-tenant isolation tetap: tiap request SDK harus tahu `toko_id` user saat itu

---

## Langkah Kerja (urut eksekusi)

### 1. Install & Publish Laravel AI SDK
```bash
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
php artisan migrate
```

### 2. Konfigurasi `config/ai.php`
Buka `config/ai.php` dan setup provider:
- **Gemini** sebagai driver utama (`driver => 'gemini'`)
- **OpenAI-compatible** sebagai fallback (untuk local model / proxy)
- Panggil API key dari `toko.gemini_api_key` (sudah ada di DB) atau fallback ke `env('GEMINI_API_KEY')`
- Set default model: `gemini-1.5-flash` untuk text/vision, bisa diubah per-toko via `toko.gemini_model`
- Tambahkan custom headers kalau dibutuhkan (misal `X-Tenant-Id` untuk proxy)

```php
// config/ai.php — cuplikan kunci
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

### 3. Buat Agent untuk AI Copilot Chat
Buat agent class baru:
```bash
php artisan make:agent ErpCopilotAgent
```

**Kontrak:**
- Implement `Agent`, `Conversational`, `HasTools`
- Method `instructions()` return system prompt yang **persis seperti** `buildErpSystemContext()` di `AiAssistantService`
- Method `messages()` load history dari tabel `agent_conversations` / `agent_conversation_messages` (yang di-migrate SDK)
- Method `tools()` daftar tools yang bisa dipanggil AI:
  - `Tool: lookup_sales_today` — ambil penjualan hari ini
  - `Tool: lookup_low_stock` — ambil produk stok menipis
  - `Tool: lookup_top_products` — produk terlaris bulan ini
  - `Tool: lookup_customer_info` — info pelanggan
- Method `schema()` untuk structured output kalau butuh

**Penting:** Agent harus membaca `toko_id` dari user yang login, lalu filter semua query tool berdasarkan `toko_id`.

### 4. Migrasi AiAssistantController
Ganti logic di `chat()`:
```php
public function chat(Request $request): JsonResponse 
{
    $request->validate([
        'message' => 'required|string|max:1000',
        'history' => 'nullable|array',
    ]);

    $user = auth()->user();
    $toko = $user?->toko;
    
    // Resolve agent dengan konteks toko
    $agent = new ErpCopilotAgent($toko, $user);
    
    // Panggil via Laravel AI SDK
    $result = $agent->prompt(
        $request->input('message'),
        provider: $toko?->ai_provider ?? 'gemini',
        model: $toko?->gemini_model ?? null,
        history: $request->input('history', [])
    );

    return response()->json([
        'success' => true,
        'reply' => $result->text,
        'is_mock' => false,
    ]);
}
```

### 5. Migrasi GeminiScanService → Agent/Tool Vision
Kita bisa pakai **Agent vision capability** dari SDK:

**Opsi A (paling native SDK):**
- Buat `ScanReceiptAgent` yang implements `HasAttachments`
- Method `instructions()` beri prompt untuk ekstrak item dari struk dalam format JSON
- Controller `PosScanController::processScan()` kirim gambar sebagai attachment ke agent
- SDK otomatis handle base64 encoding, multipart, dan response parsing

**Opsi B (tool-based):**
- Buat tool `extract_receipt_items` yang menerima image input
- Agent dengan vision capability proses tool ini

Rekomendasi: **Opsi A** karena lebih simpel dan sesuai kebutuhan “kirim gambar → dapat JSON items”.

### 6. Migrasi VoiceTransactionService → Agent Voice/Text
- Buat `VoiceCommandAgent`
- `instructions()` berisi prompt parser JSON intent + katalog produk toko
- Controller `VoiceTransactionController::processVoice()` dan `SelfServiceController::processVoice()` panggil agent ini
- Agent bisa langsung return structured output via `HasStructuredOutput` + schema `intent`, `items[]`, `diskon`, dsb

**Catatan penting:** Jangan lupa tetap panggil `ProductFuzzyMatcher` untuk fallback fuzzy match, karena itu logic bisnis yang sudah matang. Agent SDK cukup untuk parsing intent + mapping nama produk ke ID.

### 7. Update AiSettingController
- Saat `testConnection()` dipanggil, gunakan **SDK resolver** untuk test koneksi, bukan direct `Http::post()` lagi
- Contoh: resolve agent via SDK dan cek `agent()->prompt('test')`
- Simpan usage tracking lewat SDK built-in `usageMetadata`

---

## yang TIDAK BOLEH diubah

- Jangan ubah blade view atau JavaScript frontend
- Jangan ubah route definitions di `web.php`
- Jangan hapus atau rename controller methods yang sudah dipakai JS/fetch di frontend
- Jangan ubah format JSON response yang di-`fetch()` oleh halaman POS/self-service

## Testing
- Jalankan `php artisan migrate` setelah publish SDK
- Smoke test: panggil `/ai-assistant/chat` via Postman/curl
- Smoke test: upload gambar ke `/pos/scan/process`
- Smoke test: kirim transkrip ke `/pos/voice/process`
- Pastikan response JSON **identik** dengan sebelum migrate
- Cek tabel `agent_conversations` terisi

## Deliverable
Setelah selesai, kirimkan:
1. Ringkasan perubahan file yang diubah/ditambah
2. Struktur folder agent baru: `app/Ai/Agents/`, `app/Ai/Tools/`
3. Isi `config/ai.php` final
4. Output `php artisan migrate` berhasil
5. Bukti smoke test (response JSON sample) dari 3 endpoint

---

## Catatan Keamanan & Edge Case
- API key **tidak boleh** di-log atau di-return ke frontend
- Jika `GEMINI_API_KEY` kosong / AI disabled, tetap return response mock-friendly seperti sekarang
- Timeout SDK: 20-25 detik untuk chat, 60 detik untuk vision
- Fallback model jika model pilihan toko tidak ditemukan
- Record usage ke `toko.ai_total_tokens` setelah tiap request sukses

## Status Implementasi ERPlay AI Assistant

Fondasi PRD `AI_ASSISTANT_POWERED_FEATURE.md` yang sudah aktif:

- Profil asisten per toko melalui `/pengaturan/ai/assistant` (nama, personality, greeting, proactive insight).
- `ErpCopilotAgent` menggunakan enam read-only tools dengan filter `toko_id`.
- Endpoint history SDK: `GET /ai-assistant/history`.
- Endpoint proactive suggestions: `GET /ai-assistant/suggestions`.
- Endpoint action read-only dengan konfirmasi: `POST /ai-assistant/action`.
- Audit trail action pada tabel `ai_actions_log`.

Aksi mutasi seperti membuat PO, mengubah stok, checkout, atau membuat jurnal belum diaktifkan sebelum UI approval dan role guard tersedia. Ini mencegah agent menjalankan perubahan data tanpa persetujuan eksplisit pengguna.

## Opsi Provider (Pilih 1 sebagai default, sisakan fallback)
| Provider | Driver SDK | Pro | Kontra |
|---|---|---|---|
| Gemini | `gemini` | Hemat, support vision, streaming | Rate limit kadang ketat |
| OpenAI-compatible (Ollama/LM Studio) | `openai-compatible` | Bisa self-host, privat | Butuh GPU, setup manual |
| OpenRouter | `openai-compatible` | Bisa akses banyak model via 1 API | Bergantung third-party |

**Rekomendasi:** Default **Gemini** (karena kamu sudah pakai dan hemat), fallback ke **Ollama lokal** untuk kasus offline/testing.
