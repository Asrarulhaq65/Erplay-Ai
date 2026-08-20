# 05 – Panduan Modul

Detail per modul: lokasi file, fungsi, cara kerjanya.

---

## Modul 1 – Autentikasi

### Controller
**`app/Http/Controllers/AuthController.php`**

| Method | Route | Deskripsi |
|---|---|---|
| `showLogin()` | GET `/login` | Tampilkan halaman login |
| `login()` | POST `/login` | Proses login, cek `is_active` |
| `showRegister()` | GET `/register` | Tampilkan form daftar toko |
| `register()` | POST `/register` | Buat Toko baru + User Owner |
| `logout()` | POST `/logout` | Logout + invalidate session |

### View
- `resources/views/auth/login.blade.php` — Form login dengan animasi glassmorphism
- `resources/views/auth/register.blade.php` — Form pendaftaran toko baru

### Catatan Penting
- Login menggunakan field `username` (bukan email)
- Session di-regenerate setelah login berhasil (`$request->session()->regenerate()`)
- Jika `is_active = false`, login dibatalkan setelah `Auth::attempt()` berhasil

---

## Modul 2 – Dashboard

### Route
```
GET /dashboard → resources/views/pages/dashboard.blade.php
```
Dashboard menampilkan ringkasan umum. Tidak ada controller khusus (langsung return view).

---

## Modul 3 – Master Produk

### Controller
**`app/Http/Controllers/ProdukController.php`** (14.6 KB → ~21 KB)

| Method | Route | Deskripsi |
|---|---|---|
| `index()` | GET `/master/produk` | Daftar produk dengan filter |
| `create()` | GET `/master/produk/create` | Form tambah produk |
| `store()` | POST `/master/produk` | Simpan produk baru |
| `edit($id)` | GET `/master/produk/{id}/edit` | Form edit produk |
| `update($id)` | PUT `/master/produk/{id}` | Update produk |
| `destroy($id)` | DELETE `/master/produk/{id}` | Hapus produk |
| `filter()` | GET `/master/produk-filter` | Filter AJAX untuk POS |
| `exportCsv()` | GET `/master/produk/export-csv` | **[BARU]** Download data produk sebagai CSV |
| `downloadTemplate()` | GET `/master/produk/download-template` | **[BARU]** Download template CSV kosong |
| `panduanExport()` | GET `/master/produk/panduan-export` | **[BARU]** Halaman panduan export & template |
| `showImport()` | GET `/master/produk/import` | **[BARU]** Halaman panduan & form import produk |
| `importCsv()` | POST `/master/produk/import` | **[BARU]** Proses file CSV (Upsert produk) |

### Model: `Produk`
**`app/Models/Produk.php`**

**Method penting:**
```php
// Mendapatkan harga berdasarkan status pelanggan
$produk->getHargaByStatus('Member');  // returns harga_jual_member

// Cek apakah stok rendah (untuk UI warning)
$produk->isStokRendah();  // returns bool
```

**Relasi:**
- `kategori()` → BelongsTo `KategoriProduk`
- `pembelianDetails()` → HasMany `PembelianDetail`
- `penjualanDetails()` → HasMany `PenjualanDetail`
- `logStoks()` → HasMany `LogStok`

### View
- `resources/views/pages/master/produk/index.blade.php` — Daftar + tombol Export CSV, Import CSV & Panduan
- `resources/views/pages/master/produk/form.blade.php` — Form tambah/edit
- `resources/views/pages/master/produk/export-guide.blade.php` — **[BARU]** Halaman panduan export & template
- `resources/views/pages/master/produk/import-guide.blade.php` — **[BARU]** Halaman form upload & panduan import

### Fitur Khusus
- **Chained Dropdown**: Saat pilih Kelompok → AJAX ke endpoint filter → update dropdown Kategori
- **Low-stock visual**: Baris tabel mendapat class `table-danger` jika `stok <= stok_minimum`
- **Upload Gambar**: Produk bisa memiliki gambar (disimpan di `storage/app/public`)
- **Export CSV**: Download semua data produk (atau subset via filter) ke file CSV yang kompatibel dengan Excel
  - File diberi nama otomatis: `produk_{nama_toko}_{timestamp}.csv`
  - Menyertakan BOM UTF-8 agar karakter Indonesia tampil benar di Excel
  - Menggunakan `StreamedResponse` — tidak memuat semua data ke memori sekaligus
  - Separator kolom: titik koma (`;`) — standar Excel regional Indonesia
- **Template CSV**: File template kosong berisi header kolom + baris contoh + baris keterangan
- **Halaman Panduan Export**: UI informatif dengan filter export, daftar kolom, dan accordion panduan penggunaan
- **Import CSV (Upsert)**: Update massal atau tambah produk via file CSV
  - Sistem mengecek berdasarkan **barcode** per toko.
  - Jika barcode sudah ada → <span class="badge bg-warning">UPDATE</span>
  - Jika barcode baru → <span class="badge bg-success">INSERT</span>
  - Divalidasi baris per baris. Transaksi di-commit jika ada baris yang valid (melewati baris yang error), kecuali jika error 100% maka akan rollback.
- **Halaman Import Guide**: Halaman upload CSV yang dilengkapi dengan preview live 5 baris pertama, info aturan format, penjelasan upsert, dan daftar kategori lookup.

#### Catatan Route Order (Penting!)
Route `export-csv`, `download-template`, `panduan-export`, `import` **HARUS** didefinisikan SEBELUM `Route::resource('produk', ...)` di `web.php`.
Tanpa urutan ini, Laravel menganggap string tersebut sebagai parameter `{produk}` dari route model binding.

---

## Modul 4 – Master Kelompok & Kategori Produk

### Controller
- **`KelompokProdukController.php`** — CRUD kelompok
- **`KategoriProdukController.php`** — CRUD kategori (terkait kelompok_id)

Keduanya adalah Resource Controllers yang menggunakan `except(['show', 'create', 'edit'])` — semua operasi via modal/AJAX inline.

---

## Modul 5 – Master Pelanggan

### Controller
**`app/Http/Controllers/PelangganController.php`** (6 KB)

Resource Controller dengan fitur:
- CRUD pelanggan
- Auto-generate `kode_pelanggan` (format: `PLG-XXXX`)
- Validasi unique `kode_pelanggan` per toko

### Controller AJAX (Pencarian POS)
**`app/Http/Controllers/PelangganSearchController.php`** (2.5 KB)

Endpoint untuk live-search di POS:
```
GET /api/pelanggan/search?q={query}
```
Returns JSON: `[{id, kode_pelanggan, nama_pelanggan, status_pelanggan}]`

---

## Modul 6 – Supplier

Dikelola melalui `SupplierController` (jika ada) atau langsung di form Pembelian.
Data Supplier digunakan di form Pembelian sebagai dropdown.

---

## Modul 7 – Pembelian (Restock)

### Controller
**`app/Http/Controllers/PembelianController.php`** (8 KB)

| Method | Route | Deskripsi |
|---|---|---|
| `index()` | GET `/pembelian` | Daftar faktur pembelian + filter |
| `create()` | GET `/pembelian/create` | Form input faktur baru |
| `store()` | POST `/pembelian/store` | Proses simpan (JSON response) |
| `show($id)` | GET `/pembelian/{id}` | Detail faktur pembelian |

### Alur `store()`:
1. Validasi payload (supplier_id, nomor_faktur, items[])
2. `DB::beginTransaction()`
3. Hitung total
4. INSERT `pembelian` header
5. Per item:
   - `Produk::lockForUpdate()->find(id)`
   - INSERT `pembelian_detail`
   - UPDATE `produk.stok += qty`
   - UPDATE `produk.harga_modal = harga_beli_satuan` (Last Cost)
   - INSERT `log_stok` (tipe: Masuk_Barang)
6. INSERT `arus_kas` (Keluar, Pembelian Stok)
7. `DB::commit()`

### View
- `resources/views/pages/pembelian/` (index.blade.php, form.blade.php, show.blade.php)

---

## Modul 8 – POS (Point of Sale)

### Controller
**`app/Http/Controllers/PenjualanController.php`** (22 KB — terbesar)

| Method | Route | Deskripsi |
|---|---|---|
| `store()` | POST `/penjualan/store` | Core POS engine (JSON) |
| `detail($id)` | GET `/api/penjualan/detail/{id}` | Detail transaksi (JSON) |
| `printStruk($id)` | GET `/pos/print-struk/{id}` | View untuk cetak struk |

### View POS
- `resources/views/pages/pos/custom.blade.php` — Mode cepat keyboard-driven
- `resources/views/pages/pos/standard.blade.php` — Mode visual grid

### Controller AJAX (Pencarian Produk)
**`app/Http/Controllers/ProductSearchController.php`** (4 KB)

```
GET /api/produk/search?q={query}&status={status_pelanggan}
```
Returns JSON: produk dengan harga yang sesuai status pelanggan.

### Konstanta Penting di PenjualanController

```php
private const PRICE_TIER_MAP = [
    'Umum'    => 'harga_jual_umum',
    'Member'  => 'harga_jual_member',
    'Rekan'   => 'harga_jual_rekan',
    'Motoris' => 'harga_jual_motoris',
];
```

### Payload JSON yang Diterima `store()`

```json
{
  "pelanggan_id": null,
  "metode_pembayaran": "Tunai",
  "diskon": 0,
  "nominal_uang": 50000,
  "referensi_digital": null,
  "items": [
    { "product_id": 5, "qty": 2 },
    { "product_id": 12, "qty": 1 }
  ]
}
```

> Harga **TIDAK** dikirim dari client. Backend menghitung sendiri dari DB.

---

## Modul 9 – Rekap Penjualan

### Controller
**`app/Http/Controllers/RekapPenjualanController.php`** (1.5 KB)

Route: `GET /laporan/rekap-penjualan`

Filter query parameters:
- `start_date` — tanggal mulai
- `end_date` — tanggal akhir
- `pelanggan_id` — filter pelanggan
- `metode_pembayaran` — filter metode bayar

View: `resources/views/pages/laporan/rekap-penjualan.blade.php`

---

## Modul 10 – Analytics Dashboard

### Controller
**`app/Http/Controllers/AnalyticsDashboardController.php`** (3.6 KB)

Route: `GET /laporan/analytics`

**Data yang dikirim ke view:**
- `$totalRevenue` — total pendapatan bulan ini
- `$totalPiutang` — total piutang belum lunas
- `$netProfit` — laba bersih (kalkulasi SQL JOIN 3 tabel)
- `$performance` — performa per kelompok produk (SQL JOIN 4 tabel)

View: `resources/views/pages/laporan/dashboard-analytics.blade.php`

---

## Modul 11 – Stock Opname

### Controller
**`app/Http/Controllers/StockOpnameController.php`** (2.8 KB)

| Method | Route | Deskripsi |
|---|---|---|
| `index()` | GET `/inventory/opname` | Halaman stock opname |
| `store()` | POST `/inventory/opname` | Simpan penyesuaian stok |

Trigger: INSERT `log_stok` (tipe: `Penyesuaian_Stok`)

---

## Modul 12 – User Management

### Controller
**`app/Http/Controllers/UserController.php`** (3.6 KB)

Resource Controller (kecuali `show`):
- `index()` — daftar semua user dalam toko
- `create()` — form tambah user
- `store()` — simpan user baru
- `edit($id)` — form edit user
- `update($id)` — update user (bisa toggle is_active)
- `destroy($id)` — hapus user

View: `resources/views/pages/pengaturan/users/`

---

## Modul 13 – Pengaturan Toko

### Controller
**`app/Http/Controllers/TokoController.php`** (1.6 KB)

| Method | Route | Deskripsi |
|---|---|---|
| `edit()` | GET `/pengaturan/toko` | Form edit profil toko |
| `update()` | PUT `/pengaturan/toko` | Simpan perubahan |

View: `resources/views/pages/pengaturan/toko/edit.blade.php`

---

## Modul 14 – CMS Tenant Management (Super Admin Only)

### Controller
**`app/Http/Controllers/CmsController.php`** (1.1 KB)

| Method | Route | Deskripsi |
|---|---|---|
| `index()` | GET `/cms/toko` | Daftar semua toko + user count |
| `updateSubscription()` | PUT `/cms/toko/{id}/subscription` | Update status & tanggal langganan |

View: `resources/views/pages/cms/index.blade.php`

---

## Modul 15 – Subscription / Langganan

### Views
- `resources/views/pages/langganan/expired.blade.php` — Halaman jika langganan habis
- `resources/views/pages/langganan/status.blade.php` — Info status langganan

### Middleware: `CheckSubscription`
Otomatis mengecek dan memblokir akses jika toko tidak aktif.
