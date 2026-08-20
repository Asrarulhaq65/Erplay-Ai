# PRD — ERPlay AI Assistant (Powered Business Copilot)

> **Brand:** ERPlay AI  
> **Tagline:** *Kelola toko, gak pusing, ada AI yang bantu.*

---

## 1. Feature Overview

**ERPlay AI Assistant** adalah agent cerdas yang benar-benar memahami seluruh alur bisnis toko retail — dari input barang, transaksi kasir, pembelian ke supplier, pencatatan akuntansi, sampai analisa penjualan. Bukan cuma chatbot tanya-jawab, tapi **partner operasional** yang bisa membantu owner, kasir, dan gudang menyelesaikan tugas sehari-hari.

### Core Value Proposition
- **Untuk Owner:** Cukup tanya “Bagaimana performa toko minggu ini?” atau “Apa yang harus saya restock bulan depan?” — AI jawab dengan data real + saran aksi.
- **Untuk Kasir:** “Tambah 2 Kopi Susu, 1 Teh Pucuk” → AI langsung parsing, cek stok, hitung harga sesuai tier pelanggan, masuk ke keranjang.
- **Untuk Gudang:** “Stok Pulpen Snowman habis, mau restock 100 pcs ke Supplier X” → AI bikin draft pembelian + hitung estimasi biaya.
- **Untuk Akuntan:** “Buat draft jurnal untuk penjualan hari ini” → AI generate jurnal + validate akun yang dipakai.

---

## 2. User Stories

### Owner / Admin Toko
1. Sebagai owner, saya bisa **menyetel nama asisten AI** toko saya — misal “Asisten Toko Berkah”, “Budi Assistant”, atau “Zira AI” — agar terasa personal.
2. Sebagai owner, saya bisa bertanya **“Berapa laba bersih toko bulan ini?”** dan AI langsung jawab dengan breakdown per kategori + rekomendasi.
3. Sebagai owner, saya bisa minta **“Buat laporan stok menipis + saran restock”** dan AI kasih daftar produk + estimasi budget + rekomendasi supplier.
4. Sebagai owner, saya bisa minta **“Preview penjualan minggu depan”** dan AI kasih forecast berdasarkan data historis + event seasonal (Idul Fitri, akhir bulan, dll).

### Kasir
1. Sebagai kasir, saya bisa **berbicara** “Tambah 2 Indomie Goreng + 1 Teh Botol” dan AI langsung isi keranjang POS.
2. Sebagai kasir, saya bisa tanya **“Harga member untuk Kopi Susu ini berapa?”** dan AI jawab dengan harga sesuai tier pelanggan yang sedang aktif.
3. Sebagai kasir, saya bisa minta **“Cek stok [nama produk]”** tanpa buka halaman inventory.

### Gudang
1. Sebagai gudang, saya bisa **upload foto faktur pembelian** dan AI otomatis ekstrak item + qty + harga, masuk ke form pembelian.
2. Sebagai gudang, saya bisa minta **“Rekomendasi jumlah restock untuk stok habis”** dan AI hitung berdasarkan kecepatan penjualan + lead time supplier.

### Akuntan / Admin Keuangan
1. Sebagai akuntan, saya bisa minta **“Draft jurnal penjualan hari ini”** dan AI generate jurnal + kategori akun yang benar.
2. Sebagai akuntan, saya bisa tanya **“Apa transaksi yang mencurigakan minggu ini?”** dan AI deteksi anomaly (diskon berlebihan, transaksi di luar jam, dll).

---

## 3. Feature Breakdown

### 3.1 Customizable Assistant Name & Personality
- **Setting di `pengaturan/ai`:** Owner bisa ubah nama asisten AI (default: “ERPlay AI Assistant”).
- **Personality presets:** Pilihan tone — “Profesional”, “Santai”, “Formal” — yang mengubah gaya bahasa AI.
- **Avatar/icon:** Bisa upload ikon custom untuk asisten (tersimpan di `storage/app/public/ai-avatar/`).
- **Multi-language support:** Nama asisten bisa pakai karakter Latin atau Indonesia.

### 3.2 Complete Business Workflow Understanding
Agent utama `ErpCopilotAgent` menguasai **6 domain**:

| Domain | Tools yang Dimiliki | Contoh Query |
|--------|---------------------|--------------|
| **Sales & POS** | `lookup_sales_today`, `create_pos_cart`, `apply_discount`, `checkout` | “Tambah 2 Kopi Susu ke keranjang” |
| **Inventory** | `lookup_low_stock`, `predict_restock`, `update_stok`, `lookup_product_info` | “Stok Indomie tinggal berapa?” |
| **Purchasing** | `create_purchase_order`, `lookup_supplier_info`, `estimate_purchase_cost` | “Buat PO ke Supplier XYZ untuk 100 pcs Indomie” |
| **Accounting** | `generate_jurnal`, `lookup_akun`, `validate_jurnal`, `generate_laba_rugi` | “Draft jurnal penjualan hari ini” |
| **Customers** | `lookup_customer_info`, `add_customer`, `update_customer_tier` | “Cek riwayat belanja Pak Budi” |
| **Analytics** | `sales_forecast`, `top_products`, `category_performance`, `anomaly_detection` | “Forecast penjualan minggu depan” |

### 3.3 Platform Knowledge & User Guidance
Selain menjalankan aksi, assistant juga menjadi **panduan internal platform** yang menguasai seluruh fitur ERPlay. Ini berguna untuk:

- **Onboarding pengguna baru:** Owner/kasir/gudang tanya “Cara tambah produk baru?” → AI jelaskan langkah demi langkah sesuai role.
- **Context-aware help:** Saat user membuka halaman tertentu, AI bisa muncul dengan tips kontekstual: “Di halaman ini kamu bisa import CSV, export laporan, atau filter by kategori.”
- **Penjelasan fitur teknis:** Owner tanya “Apa itu tier harga member?” → AI jelaskan secara bisnis, bukan cuma definisi database.
- **Troubleshooting dasar:** “Kenapa struk tidak keluar?” → AI berikan langkah diagnosis: cek printer, cekBrowser print dialog, cek template struk di pengaturan toko.

**Contoh use case:**
1. Kasir: “Gimana cara pakai mode Custom POS?”
2. Owner: “Apa saja role yang ada di sistem ini?”
3. Gudang: “Cara import stok dari CSV?”
4. Admin: “Apa itu self-service order dan cara verifikasinya?”

Tools pendukung: `explain_feature`, `list_features_by_role`, `how_to_use`, `contextual_help`.

### 3.3 Action Capabilities (bukan cuma jawaban)
AI Assistant bisa **melakukan aksi** setelah konfirmasi user:

1. **Kerjakan & Report:** AI eksekusi langsung (misal: tambah item ke keranjang) dan report hasilnya.
2. **Draft & Minta Konfirmasi:** AI buat draft (misal: draft PO pembelian), user review → approve/reject.
3. **Jadwalkan:** AI jadwalkan task (misal: “Buatkan restock reminder besok jam 10” → masuk ke task scheduler).

### 3.4 Proactive Insights
AI tidak hanya reactif (tanya → jawab), tapi juga **proactive**:
- Saat kasir buka POS, AI munculkan notifikasi kecil: “Stok X tinggal 2, siapkan restock?”
- Saat owner login, AI kasih ringkasan: “Selamat pagi! Hari ini ada 3 stok menipis dan 1 transaksi mencurigakan.”
- Saat penjualan melebihi target harian, AI kasih alert: “Target penjualan hari ini sudah tercapai! Great job.”

### 3.5 Context Awareness
AI ingat:
- **Siapa yang sedang login** (kasir, owner, gudang) dan menyesuaikan jawaban.
- **Apa yang sedang dilakukan** (di POS, di laporan, di inventory) dan berikan saran yang relevan.
- **Riwayat percakapan** di session ini, jadi bisa follow-up: “Kemudian?” atau “Lanjutkan dari yang tadi?”

---

## 4. Technical Architecture

### 4.1 Agent Structure
```
app/
├── Ai/
│   ├── Agents/
│   │   ├── ErpCopilotAgent.php          # Agent utama
│   │   ├── ScanReceiptAgent.php         # Vision agent untuk scan struk
│   │   └── VoiceCommandAgent.php        # Voice agent untuk POS
│   ├── Tools/
│   │   ├── LookupSalesToday.php
│   │   ├── LookupLowStock.php
│   │   ├── LookupTopProducts.php
│   │   ├── CreatePosCart.php
│   │   ├── GenerateJurnal.php
│   │   ├── SalesForecast.php
│   │   └── ... (total ~15 tools)
│   └── Middleware/
│       └── AiContextMiddleware.php      # Inject toko_id + user context
```

### 4.2 Database Schema (baru)
```php
// Tabel: ai_assistant_configs (per toko)
Schema::create('ai_assistant_configs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('toko_id')->constrained()->cascadeOnDelete();
    $table->string('assistant_name')->default('ERPlay AI Assistant');
    $table->string('personality')->default('profesional'); // profesional, santai, formal
    $table->string('avatar_path')->nullable(); // path ke ikon custom
    $table->string('greeting_message')->nullable(); // "Selamat datang, {nama_user}!"
    $table->json('enabled_tools')->nullable(); // JSON array tool yang diizinkan
    $table->json('disabled_tools')->nullable(); // JSON array tool yang diblokir
    $table->boolean('proactive_enabled')->default(true);
    $table->timestamps();
});

// Tabel: ai_conversations (sudah ada di SDK migration)
// Tambah kolom: session_id, context_type (pos, inventory, analytics, general)

// Tabel: ai_actions_log (audit trail aksi AI)
Schema::create('ai_actions_log', function (Blueprint $table) {
    $table->id();
    $table->foreignId('toko_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action_type'); // lookup, create, update, delete, forecast
    $table->string('tool_name');
    $table->json('parameters');
    $table->json('result');
    $table->integer('tokens_used')->nullable();
    $table->timestamp('executed_at');
});
```

### 4.3 API Contracts
- `POST /ai-assistant/chat` — chat utama (sudah ada)
- `POST /ai-assistant/action` — eksekusi aksi dengan konfirmasi
- `GET /ai-assistant/suggestions` — ambil proactive suggestions untuk halaman saat ini
- `PUT /pengaturan/ai/assistant` — update nama, personality, avatar
- `GET /ai-assistant/history` — riwayat percakapan per user

### 4.4 Multi-Tenancy & Security
- Semua tool **wajib** filter berdasarkan `toko_id` dari user login.
- AI **tidak bisa** mengakses data toko lain.
- Tool destruktif (update/delete) **wajib** ada konfirmasi user atau role check.
- Semua aksi tercatat di `ai_actions_log` untuk audit trail.

---

## 5. Implementation Plan

### Phase 1: Foundation (Week 1)
1. Install Laravel AI SDK + publish config
2. Buat `ErpCopilotAgent` + 6 tools inti (sales, inventory, purchasing, accounting, customers, analytics)
3. Migrasi `AiAssistantService` → Agent
4. Tambah tabel `ai_assistant_configs` + migration
5. Buat halaman pengaturan nama asisten di `pengaturan/ai`

### Phase 2: Action Capabilities (Week 2)
1. Tambah tools untuk eksekusi aksi: `create_pos_cart`, `create_purchase_order`, `generate_jurnal`
2. Implementasi `POST /ai-assistant/action` dengan guardrails
3. Tambah `ai_actions_log` untuk audit trail
4. Tambah konfirmasi dialog di frontend sebelum aksi dieksekusi

### Phase 3: Proactive Intelligence (Week 3)
1. Implementasi `AiContextMiddleware` untuk inject context halaman
2. Buat sistem proactive suggestions (stok menipis, target tercapai, anomaly alert)
3. Integrasi dengan POS + Inventory + Analytics dashboard
4. Tambah badge notifikasi di sidebar

### Phase 4: Polish & Testing (Week 4)
1. Fine-tuning prompt untuk masing-masing domain
2. Testing multi-tenant isolation
3. Testing edge case: AI gagal, API timeout, data kosong
4. Smoke test semua endpoint + frontend integration

---

## 6. User Interface Mockup (Concept)

### Chat Widget
```
┌─────────────────────────────────────┐
│  🤖 {Nama Asisten}                  │
│  "Selamat pagi, Arul! Ada yang bisa │
│   saya bantu?"                       │
├─────────────────────────────────────┤
│  > Cek penjualan hari ini            │
│                                       │
│  📊 Penjualan Hari Ini:              │
│  - Total: Rp 2.450.000              │
│  - Transaksi: 18 lunas, 3 kredit    │
│  - Top produk: Indomie (45 pcs)     │
│                                       │
│  [Tambah ke laporan] [Lihat detail]  │
└─────────────────────────────────────┘
```

### Pengaturan Nama Asisten
```
Halaman: pengaturan/ai/assistant

[Nama Asisten]    [___________________]  (default: ERPlay AI Assistant)
[Personality]     [Profesional ▾]       (opsi: Profesional, Santai, Formal)
[Avatar]          [Upload Icon]         (preview 64x64)
[Pesan Sambutan]  [___________________]  (default: "Selamat datang, {nama_user}!")
[Proactive AI]    [✓] Aktifkan notifikasi cerdas

[Simpan Pengaturan]
```

---

## 7. Security & Privacy Considerations

1. **Data Scope:** AI hanya bisa akses data toko user yang login — tidak ada cross-tenant access.
2. **Audit Trail:** Semua aksi AI dicatat di `ai_actions_log` dengan user_id, timestamp, dan parameter.
3. **Rate Limiting:** Batasi request AI per user per menit untuk prevent abuse.
4. **Graceful Fallback:** Jika AI gagal, sistem tetap jalan dengan mode manual.
5. **Data Retention:** Conversation history bisa di-set auto-expire (misal: 90 hari) untuk compliance.

---

## 8. Success Metrics

| Metric | Target |
|--------|--------|
| **Adoption Rate** | 80% kasir/owner menggunakan AI assistant minimal 1x per hari |
| **Task Completion** | 90% query AI direspon dengan jawaban yang berguna (tidak “maaf saya tidak mengerti”) |
| **Latency** | Chat response <3 detik, action execution <5 detik |
| **Error Rate** | <5% AI gagal memproses query valid |
| **User Satisfaction** | Rating 4.5/5 dari feedback users |

---

## 9. Dependencies & Risks

| Dependency | Risk | Mitigation |
|------------|------|------------|
| Laravel AI SDK stability | SDK masih baru di Laravel 13 | Ikuti changelog, sediakan fallback ke custom wrapper |
| Gemini API rate limit | Kuota habis saat peak hour | Implementasi caching + fallback model + Ollama lokal |
| Multi-tenant performance | Query AI tools bisa lambat | Gunakan eager loading, index di `toko_id`, cache ringkasan data |
| Frontend integration | Chat widget butuh real-time UX | Gunakan vanilla JS + fetch, hindari dependency berat |

---

## 10. Out of Scope (V2)

- Multi-channel AI (WhatsApp, Telegram, LINE) — fokus dulu ke web app
- Voice output (AI bicara) — fokus dulu ke text + voice input
- AI-generated insights untuk supplier performance — fokus dulu ke sales + inventory
- Machine learning custom model untuk toko tertentu — gunakan model umum dulu

---

## Deliverables untuk Agent Coding

1. File migration baru: `ai_assistant_configs` + `ai_actions_log`
2. Agent class: `app/Ai/Agents/ErpCopilotAgent.php`
3. Tools: `app/Ai/Tools/*` (minimal 6 tools inti)
4. Controller update: `AiAssistantController.php` + `AiSettingController.php` (untuk nama asisten)
5. View baru: `resources/views/pages/pengaturan/ai/assistant.blade.php`
6. API route baru: `/ai-assistant/action`, `/ai-assistant/suggestions`, `/ai-assistant/history`
7. Smoke test script untuk 3 endpoint utama
8. Update `docs/AI_SDK_MIGRATION_PROMPT.md` agar selaras dengan fitur baru ini

---

**Catatan untuk Agent Coding:**  
Jangan ubah route lama `/ai-assistant/chat` — backward compatibility harus tetap 100%. Semua response JSON ke frontend harus identik dengan versi sebelumnya.
