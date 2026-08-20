# PRD + Design System — ERPlay Public Catalog & AI Chat

> **Brand:** ERPlay AI  
> **Module:** Public Catalog + AI Chat  
> **Tagline:** *Lihat katalog toko, tanya harga/stok langsung ke AI.*

---

## 1. Product Overview

**ERPlay Public Catalog** adalah halaman publik yang menampilkan katalog produk milik toko retail. Customer bisa browse produk, lihat harga, dan chat dengan **AI Assistant khusus toko tersebut** untuk menanyakan harga, stok, atau info toko. Tidak perlu login.

### Core Value
- **Untuk Customer:** Lihat produk, cek harga, tanya stok tanpa harus ke toko atau telepon.
- **Untuk Owner:** Punya katalog online yang bisa dishare ke WhatsApp/Instagram, dengan AI yang otomatis jawab pertanyaan customer 24/7.

### Scope
- Halaman publik: `/katalog/{slug_toko}`
- Live chat AI: hanya menjawab seputar toko tersebut
- Tidak ada aksi transaksi/checkout dari catalog (order masih melalui kasir)
- Tidak ada login untuk customer

---

## 2. User Flow

```
Customer opens share link → Landing catalog page
    ↓
Browse produk / Search / Filter kategori
    ↓
Tanya AI chat: "Harga Indomie berapa?" / "Stok Kopi Susu masih ada?"
    ↓
AI jawab dari data toko saat ini
    ↓
Jika mau pesan, AI beri nomor WA toko / arahkan ke toko
```

---

## 3. Feature Set

### 3.1 Public Catalog Page
- Header: logo toko, nama toko, slogan
- Search bar: cari produk by nama/barcode
- Filter kategori: kelompok/kategori dropdown
- Grid produk: nama, harga, stok, satuan, gambar (jika ada)
- Empty state: "Produk tidak ditemukan"
- Share button: share ke WhatsApp/IG/FB
- Footer: info toko, kontak, alamat

### 3.2 AI Live Chat (Customer-Facing)
- Chat widget di pojok kanan bawah catalog
- Auto-greet: "Halo! Saya asisten [nama toko]. Ada yang bisa saya bantu?"
- Hanya bisa menjawab:
  - Harga produk
  - Stok produk
  - Kategori produk
  - Info toko (alamat, kontak)
  - Cara pesan / lokasi toko
- Tidak bisa: akses data sensitif, ubah data, transaksi
- Fallback: "Maaf, saya belum mengerti. Silakan hubungi owner di [nomor WA]."

### 3.3 AI Assistant Name
- Mengikuti nama asisten yang di-set owner di `pengaturan/ai`
- Default: "[Nama Toko] Assistant"

---

## 4. Design System

### 4.1 Brand Tokens
| Token | Value | Usage |
|-------|-------|-------|
| `--color-primary` | `#0F172A` | Header, text utama |
| `--color-accent` | `#3B82F6` | Button, link, chat bubble AI |
| `--color-bg` | `#F8FAFC` | Background page |
| `--color-surface` | `#FFFFFF` | Card produk, chat panel |
| `--color-text` | `#1E293B` | Body text |
| `--color-text-muted` | `#64748B` | Secondary text, hint |
| `--color-border` | `#E2E8F0` | Border card/input |
| `--color-success` | `#10B981` | Stok tersedia |
| `--color-warning` | `#F59E0B` | Stok menipis |
| `--color-danger` | `#EF4444` | Stok habis |
| `--radius-sm` | `8px` | Input, button |
| `--radius-md` | `12px` | Card, chat bubble |
| `--radius-lg` | `16px` | Modal, panel |
| `--shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | Card hover |
| `--shadow-md` | `0 4px 6px -1px rgba(0,0,0,0.1)` | Chat panel |
| `--font-sans` | `Inter, ui-sans-serif, system-ui` | All text |
| `--font-mono` | `JetBrains Mono, ui-monospace` | Price, barcode |

### 4.2 Typography Scale
| Token | Value | Usage |
|-------|-------|-------|
| `--text-xs` | `12px` | Hint, badge |
| `--text-sm` | `14px` | Body small, label |
| `--text-base` | `16px` | Body, input |
| `--text-lg` | `18px` | Product title |
| `--text-xl` | `20px` | Section heading |
| `--text-2xl` | `24px` | Store name |
| `--text-3xl` | `30px` | Hero heading |

### 4.3 Spacing
| Token | Value |
|-------|-------|
| `--space-1` | `4px` |
| `--space-2` | `8px` |
| `--space-3` | `12px` |
| `--space-4` | `16px` |
| `--space-6` | `24px` |
| `--space-8` | `32px` |

### 4.4 Component Specs

#### Header
- Background: `--color-primary`
- Text: white
- Height: `64px`
- Content: logo left, store name center, share button right
- Sticky top

#### Search Bar
- Height: `48px`
- Border radius: `--radius-sm`
- Border: `--color-border`
- Padding left: `16px` + icon
- Placeholder: "Cari produk..."
- Focus ring: `--color-accent`

#### Filter Chip (Kategori)
- Height: `36px`
- Border radius: `18px`
- Border: `--color-border`
- Active: `--color-accent` bg, white text
- Gap: `--space-2`

#### Product Card
- Background: `--color-surface`
- Border radius: `--radius-md`
- Border: `--color-border`
- Padding: `--space-4`
- Shadow: `--shadow-sm` on hover
- Layout: image top, content bottom
- Image aspect ratio: 1:1, object-fit cover
- Product name: `--text-lg`, semibold, line-clamp 2
- Price: `--text-xl`, bold, `--color-accent`
- Stock badge: `--text-xs`, pill, color based on stock level

#### Stock Badge
| Status | Color | Text |
|--------|-------|------|
| Tersedia | `--color-success` | "Tersedia" |
| Menipis | `--color-warning` | "Stok menipis" |
| Habis | `--color-danger` | "Stok habis" |

#### Chat Widget
- Position: fixed bottom-right, `24px` from edges
- Width: `380px` desktop, `100%` mobile
- Height: `520px` max, `400px` min
- Border radius: `--radius-lg`
- Shadow: `--shadow-md`
- Header: `--color-accent` bg, white text, store name + close button
- Body: `--color-bg`, scrollable
- Input area: `--color-surface` border top, input + send button
- AI bubble: `--color-accent` bg, white text, left align
- User bubble: `--color-primary` bg, white text, right align
- Avatar: store logo or default bot icon, `32px` circle

#### Share Button
- Style: secondary button, outline
- Icon: share icon
- On click: Web Share API or copy link

#### Empty State
- Icon: package/search
- Text: "Produk tidak ditemukan"
- Subtext: "Coba kata kunci lain"

### 4.5 Responsive Breakpoints
| Breakpoint | Width | Layout |
|------------|-------|--------|
| Mobile | `<640px` | 1 column grid, full-width chat |
| Tablet | `640px - 1024px` | 2 column grid |
| Desktop | `>1024px` | 3-4 column grid, centered container max-w-6xl |

### 4.6 Accessibility
- WCAG AA contrast for all text
- Focus ring visible on all interactive elements
- Keyboard navigable: Tab through products, Enter to open detail
- Alt text for product images
- ARIA labels for chat widget

---

## 5. Technical Architecture

### 5.1 Routes
```php
// routes/web.php
Route::prefix('katalog')->name('katalog.')->group(function () {
    Route::get('/{slug}', [App\Http\Controllers\PublicCatalogController::class, 'index'])->name('index');
    Route::get('/{slug}/api/products', [App\Http\Controllers\PublicCatalogController::class, 'products'])->name('products');
});
```

### 5.2 Controller
```php
// app/Http/Controllers/PublicCatalogController.php
class PublicCatalogController extends Controller
{
    public function index(string $slug)
    {
        $toko = Toko::where('catalog_slug', $slug)->firstOrFail();
        $config = $toko->aiAssistantConfig;
        $assistantName = $config?->assistant_name ?: "{$toko->nama_toko} Assistant";

        return view('pages.public-catalog.index', compact('toko', 'assistantName'));
    }

    public function products(string $slug, Request $request)
    {
        $toko = Toko::where('catalog_slug', $slug)->firstOrFail();
        $query = $request->input('query');
        $category = $request->input('category');

        // Return JSON products for HTMX/fetch
    }
}
```

### 5.3 Agent & Tools
- **Agent:** `CustomerServiceAgent` (sudah ada)
  - Tools: `LookupProdukPublic`, `LookupHargaPublic`, `LookupStokPublic`, `LookupInfoToko`, `ExplainFeature`
  - Constructor: `private readonly int $tokoId`
  - Instructions: hanya boleh menjawab seputar toko, tidak boleh akses data sensitif

### 5.4 Chat API Route
```php
// routes/api.php
Route::prefix('katalog/{slug}')->name('katalog.api.')->group(function () {
    Route::post('/chat', [PublicCatalogController::class, 'chat'])->name('chat');
});
```

Chat controller logic:
1. Resolve `toko_id` from `slug`
2. Instantiate `CustomerServiceAgent($toko->id)`
3. Call `$agent->prompt($message)`
4. Return JSON: `{ reply, is_mock }`
5. Track usage: `$toko->recordAiUsage($response->usageMetadata)`

### 5.5 Database Additions
```php
// Migration: add catalog fields to toko table
Schema::table('toko', function (Blueprint $table) {
    $table->string('catalog_slug')->unique()->nullable()->after('slogan_struk');
    $table->boolean('catalog_enabled')->default(true)->after('catalog_slug');
    $table->string('catalog_theme')->default('default')->after('catalog_enabled');
    $table->string('whatsapp_number')->nullable()->after('catalog_theme');
    $table->boolean('whatsapp_enabled')->default(false)->after('whatsapp_number');
});
```

### 5.6 Security & Privacy
- AI hanya bisa akses public tools (harga jual umum, stok, info toko)
- Tidak ada aksi destruktif (tidak bisa create/update/delete)
- Rate limiting: 30 messages per session per 5 menit
- Input sanitization: strip HTML, limit length 500 chars
- No PII exposure: jangan return data pribadi customer/pelanggan

---

## 6. Frontend Implementation

### 6.1 File Structure
```
resources/views/pages/public-catalog/
├── index.blade.php
└── components/
    ├── product-card.blade.php
    ├── chat-widget.blade.php
    └── filter-bar.blade.php
```

### 6.2 Tech Stack
- Blade + Tailwind CSS (tanpa JS framework berat)
- Alpine.js untuk chat interactivity (lightweight)
- Fetch API untuk chat + product search
- Optional: HTMX untuk filter produk tanpa reload

### 6.3 Chat Widget (Alpine.js)
```html
<div x-data="catalogChat('{{ $assistantName }}', '{{ $slug }}')" class="fixed bottom-6 right-6 ...">
    <!-- Toggle button -->
    <!-- Chat panel -->
    <!-- Messages -->
    <!-- Input + send -->
</div>

<script>
function catalogChat(assistantName, slug) {
    return {
        open: false,
        messages: [],
        input: '',
        async send() {
            // push user message
            // fetch POST /katalog/{slug}/chat
            // push AI reply
        }
    }
}
</script>
```

### 6.4 Product Grid
- Use CSS Grid: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4`
- Lazy load images: `loading="lazy"`
- Empty state illustration: SVG inline

---

## 7. AI Behavior Rules

### Allowed
- Jawab harga jual umum
- Jawab stok tersedia/menipis/habis
- Jawab kategori produk
- Jawab info toko (alamat, kontak, jam buka)
- Jelaskan cara pesan / lokasi toko

### Not Allowed
- Harga modal, harga member/rekan/motoris
- Data akuntansi, laba rugi
- Data supplier, pembelian
- Data customer/pelanggan
- Aksi transaksi / checkout

### Fallback
- Jika pertanyaan di luar scope: "Maaf, saya hanya bisa membantu seputar produk, harga, dan info toko. Silakan hubungi owner di [WA]."

---

## 8. Testing Checklist

- [ ] Public catalog page accessible without login
- [ ] Search produk by nama/barcode works
- [ ] Filter by kategori works
- [ ] Chat widget opens/closes
- [ ] AI responds to: "Harga X", "Stok Y", "Info toko"
- [ ] AI refuses: "Harga modal", "Laba rugi", "Data supplier"
- [ ] Rate limiting works
- [ ] Mobile responsive: grid 1 col, chat full-width
- [ ] Accessibility: focus ring, ARIA labels

---

## 9. Future Enhancements (V2)

- Product detail modal with image gallery
- Inquiry form: "Tanya harga" button → send WA message
- WhatsApp deep link integration
- Analytics: catalog views, top searched products
- Multi-language support
- Theme customization per toko (color, font)
- AI voice response untuk customer

---

## 10. Implementation Notes for Agent Coding

1. **Jangan ubah route/response JSON yang sudah ada** untuk internal endpoints.
2. **Buat migration baru** untuk kolom `catalog_*` di tabel `toko`.
3. **Gunakan `CustomerServiceAgent`** yang sudah ada — jangan buat agent baru.
4. **Frontend pakai Blade + Tailwind + Alpine.js** minimal, tanpa bundler tambahan.
5. **Design system konsisten dengan ERPlay:** gunakan token yang sudah ada di `tailwind.config.js`.
6. **Chat widget reusable** — bisa dipindah ke halaman lain jika nanti dibutuhkan.

---

## Deliverables

1. Migration: `toko` tambah kolom `catalog_slug`, `catalog_enabled`, `catalog_theme`, `whatsapp_number`, `whatsapp_enabled`
2. Controller: `PublicCatalogController`
3. Route: `katalog/{slug}`, `katalog/{slug}/api/products`, `katalog/{slug}/chat`
4. View: `resources/views/pages/public-catalog/index.blade.php`
5. Agent: gunakan `CustomerServiceAgent` yang sudah ada
6. Chat API controller method
7. Smoke test via browser: browse catalog, chat AI, responsive check
