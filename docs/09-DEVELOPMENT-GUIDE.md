# 09 – Panduan Pengembangan (Development Guide)

---

## 1. Setup Lokal (Laragon)

### Prasyarat
- **Laragon** (versi terbaru, sudah include PHP 8.3, MySQL, Nginx/Apache)
- **Node.js** v18+ (untuk Vite)
- **Composer** (PHP dependency manager)

### Langkah Setup

```bash
# 1. Clone/copy proyek ke direktori Laragon www
# Pastikan folder ada di: C:\laragon\www\Mini-ERP-By-Arul\

# 2. Copy file environment
cp .env.example .env

# 3. Edit .env — sesuaikan konfigurasi:
APP_NAME="Retail ERP By Arul"
APP_URL=http://mini-erp-by-arul.test   # ← sesuaikan domain Laragon
DB_DATABASE=db_retail_mini_erp
DB_USERNAME=root
DB_PASSWORD=                            # ← kosong jika Laragon default
SESSION_DRIVER=file

# 4. Install dependensi PHP
composer install

# 5. Generate app key
php artisan key:generate

# 6. Buat database
# Buka phpMyAdmin → buat database: db_retail_mini_erp

# 7. Jalankan migrasi
php artisan migrate

# 8. Jalankan seeder
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=DummyRetailSeeder

# 9. Install dependensi Node
npm install

# 10. Build assets (Vite)
npm run build
# Atau untuk development dengan hot-reload:
npm run dev
```

### Troubleshooting Setup

#### Error: "419 Page Expired" saat Login
1. Hapus cookies browser (atau buka Incognito)
2. Pastikan `APP_URL` di `.env` sama dengan URL yang diketik di browser
3. Hapus semua file di `storage/framework/sessions/` (kecuali `.gitignore`)
4. Jalankan: `php artisan config:clear && php artisan cache:clear`

#### Error: "Class not found" setelah composer install
```bash
composer dump-autoload
```

#### Error: "Connection refused" ke database
1. Pastikan Laragon sudah berjalan (MySQL service aktif)
2. Cek setting di `.env`: `DB_HOST=127.0.0.1`, `DB_PORT=3306`

#### Error: Session driver "database" tidak ada tabel `sessions`
Di file `.env`, pastikan menggunakan:
```
SESSION_DRIVER=file
```
Atau jika ingin database, jalankan:
```bash
php artisan session:table
php artisan migrate
```

---

## 2. Artisan Commands yang Sering Digunakan

```bash
# Migrasi & Database
php artisan migrate                    # Jalankan semua migrasi baru
php artisan migrate:fresh              # Drop semua tabel + migrate ulang
php artisan migrate:fresh --seed       # Drop + migrate + seed

# Seeder
php artisan db:seed                    # Jalankan DatabaseSeeder
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=DummyRetailSeeder

# Cache & Optimisasi
php artisan optimize:clear             # Clear semua cache
php artisan config:clear               # Clear config cache
php artisan route:clear                # Clear route cache
php artisan view:clear                 # Clear view cache

# Debugging
php artisan route:list                 # Lihat semua route
php artisan tinker                     # REPL interaktif Laravel

# Asset Build (via npm)
npm run dev                            # Vite dev server (hot reload)
npm run build                          # Build production assets
```

---

## 3. Alur Pengembangan Fitur Baru

### Step 1: Rencanakan Migrasi (jika ada tabel baru)

```bash
php artisan make:migration create_nama_tabel_table
```

Pastikan:
- Sertakan kolom `toko_id` (FK ke `toko.id` CASCADE)
- Sertakan `timestamps()`
- Perhatikan urutan migrasi (jika ada FK ke tabel lain)

### Step 2: Buat Model

```bash
php artisan make:model NamaModel
```

Template model dengan Global Scope:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NamaModel extends Model
{
    protected $table = 'nama_tabel';

    protected $fillable = ['toko_id', 'kolom_lain', ...];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('nama_tabel.toko_id', auth()->user()->toko_id);
            }
        });

        static::creating(function (self $model) {
            if (auth()->check() && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    // Relasi dan helper methods di sini...
}
```

### Step 3: Buat Controller

```bash
php artisan make:controller NamaController --resource
```

### Step 4: Tambahkan Route

Buka `routes/web.php` dan tambahkan ke group yang sesuai dengan hak akses:

```php
Route::middleware(['role:Super Admin,Owner'])->group(function () {
    Route::resource('nama-fitur', NamaController::class)->except(['show', 'create', 'edit']);
});
```

### Step 5: Buat View

Buat file di `resources/views/pages/{modul}/`:
- `index.blade.php` — tabel daftar + filter
- `form.blade.php` — form tambah/edit (jika dibutuhkan)

Extend layout utama:
```blade
@extends('layouts.app')

@section('title', 'Judul Halaman')

@section('content')
    <!-- konten halaman -->
@endsection
```

---

## 4. Struktur Layout Utama

**File**: `resources/views/layouts/app.blade.php`

Semua halaman yang memerlukan navigasi menggunakan layout ini via `@extends('layouts.app')`.

**Sections tersedia:**
- `@section('title')` — Title tab browser
- `@section('content')` — Konten utama halaman
- `@section('scripts')` — JavaScript tambahan (opsional)

---

## 5. Flash Messages

Semua pesan flash ditampilkan via layout. Gunakan konvensi berikut:

```php
// Sukses
return redirect()->route('...')->with('success', 'Data berhasil disimpan.');

// Error
return redirect()->back()->with('error', 'Terjadi kesalahan.');
```

Di view (jika perlu tampilkan manual):
```blade
@if(session('success'))
    <div class="alert alert-success alert-sm">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-sm">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-sm">
        @foreach($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif
```

---

## 6. Cara Menambah Role Baru

1. Tambahkan role baru ke seeder (opsional) atau langsung insert via DB
2. Update `RoleMiddleware` jika ada route khusus role baru
3. Update tabel matrix akses di `04-ARCHITECTURE.md`
4. Update dokumentasi `05-MODULES.md`

> **Catatan**: Role baru harus didaftarkan di tabel `roles` sebelum bisa digunakan.

---

## 7. Cara Menambah Metode Pembayaran Baru

Saat ini metode pembayaran di-enum:
```sql
ENUM('Tunai', 'Kredit', 'Digital Payment')
```

Untuk menambah metode baru:
1. Buat migration ALTER TABLE:
   ```php
   DB::statement("ALTER TABLE penjualan MODIFY metode_pembayaran ENUM('Tunai', 'Kredit', 'Digital Payment', 'MetodeBaru')");
   ```
2. Update validasi di `PenjualanController@store`:
   ```php
   'metode_pembayaran' => ['required', Rule::in(['Tunai', 'Kredit', 'Digital Payment', 'MetodeBaru'])],
   ```
3. Update logic di `PenjualanController@store` untuk handling `arus_kas`
4. Update UI di `pos/custom.blade.php` dan `pos/standard.blade.php`

---

## 8. Cara Test Manual Fitur POS

1. Login sebagai `kasir` / `superadmin`
2. Buka `/pos/custom`
3. Test alur:
   - Ketik nama pelanggan (coba partial match) → pilih dari dropdown
   - Tekan Enter tanpa pilih (harus default ke "Umum")
   - Ketik 2+ karakter nama produk → pilih dari dropdown
   - Ketik qty yang melebihi stok → pastikan muncul warning merah
   - Ketik qty valid → Enter → item masuk keranjang
   - Tekan F9 → modal bayar muncul
   - Test Tunai: coba nominal lebih kecil dari total (tombol harus disabled)
   - Submit → cek invoice terbuat

---

## 9. Akun Default untuk Testing

| Role | Username | Password |
|---|---|---|
| Super Admin | `superadmin` | `superadmin123` |
| Kasir | `kasir` | `password` |

Buat akun Owner/Gudang via Super Admin → Pengaturan → Users.

---

## 10. Lokasi File Penting

| File | Lokasi | Tujuan |
|---|---|---|
| Route | `routes/web.php` | Semua routing aplikasi |
| Session Config | `config/session.php` | Konfigurasi session |
| App Config | `config/app.php` | Konfigurasi utama |
| Middleware Register | `bootstrap/app.php` | Alias middleware |
| Layout Utama | `resources/views/layouts/app.blade.php` | Template HTML utama |
| Login View | `resources/views/auth/login.blade.php` | Halaman login |
| POS Custom | `resources/views/pages/pos/custom.blade.php` | POS keyboard-driven |
| POS Standard | `resources/views/pages/pos/standard.blade.php` | POS visual grid |
| Environment | `.env` | Konfigurasi environment |

---

## 11. Known Issues & Catatan

1. **Session "database" driver**: Config default di `config/session.php` adalah `database`, namun `.env` di-set ke `file`. Jika ada error session, pastikan `.env` punya `SESSION_DRIVER=file`.

2. **`APP_URL` harus konsisten**: URL di browser harus sama persis dengan `APP_URL` di `.env` untuk mencegah error 419 CSRF.

3. **Gambar produk**: File gambar disimpan di `storage/app/public`. Jalankan `php artisan storage:link` untuk membuat symlink ke `public/storage/`.

4. **File temp di root**: Ada beberapa file `temp.php`, `temp2.php`, `temp3.php` di root proyek — ini adalah file scratch yang bisa dihapus dengan aman.

5. **Subscription check bypass**: Super Admin tidak pernah terkena pengecekan subscription — ini by design untuk memastikan Super Admin bisa selalu mengakses CMS untuk memperbaiki masalah tenant.

6. **Route order untuk export produk**: Route `export-csv`, `download-template`, dan `panduan-export` HARUS didefinisikan sebelum `Route::resource('produk', ...)` di `web.php`. Jika tidak, Laravel akan mengira string tersebut adalah `{produk}` ID (route model binding).

7. **CSV Export encoding**: File CSV menggunakan BOM UTF-8 (`\xEF\xBB\xBF`) agar Excel membaca karakter Indonesia (é, ñ, dll) dengan benar. Separator kolom menggunakan titik koma (`;`), bukan koma, karena standar Excel untuk locale Indonesia.

---

## 12. Changelog Fitur

### v1.1 – 2026-07-02

#### ✅ Export & Import Produk CSV (Modul: Master Produk)
- **File yang ditambah/diubah:**
  - `app/Http/Controllers/ProdukController.php` — Tambah 5 method baru: `exportCsv()`, `downloadTemplate()`, `panduanExport()`, `showImport()`, `importCsv()`
  - `routes/web.php` — Tambah 5 route baru (sebelum `resource()`)
  - `resources/views/pages/master/produk/index.blade.php` — Tambah tombol "Import CSV", "Export CSV", dan "Panduan & Template" di header
  - `resources/views/pages/master/produk/export-guide.blade.php` — **[BARU]** Halaman panduan export dengan filter, daftar kolom, template download, dan accordion panduan
  - `resources/views/pages/master/produk/import-guide.blade.php` — **[BARU]** Halaman panduan & form import dengan live preview dan laporan error per-baris
- **Docs yang diupdate:** `docs/05-MODULES.md`, `docs/07-API-REFERENCE.md`, `docs/09-DEVELOPMENT-GUIDE.md`
- **Fitur Export:**
  - Export semua produk (atau subset via filter kategori/search) ke file `.csv`
  - Nama file otomatis: `produk_{nama_toko}_{timestamp}.csv`
  - Template CSV kosong dengan header + baris keterangan + 2 baris contoh data
- **Fitur Import (Upsert):**
  - Menerima file `.csv` yang mengikuti format template
  - Logic **Upsert**: Jika barcode belum ada → `INSERT`, jika barcode sudah ada → `UPDATE` data
  - Validasi per baris. Commit DB Transaction hanya jika ada data yang berhasil di-upsert. Rollback total jika 100% baris error.
  - Live preview 5 baris pertama di UI form upload
  - Accordion panduan penggunaan komprehensif di halaman form


---

## 13. Aturan Wajib: Update Docs Setiap Ada Perubahan

> **PENTING untuk Developer & AI Agent**: Setiap kali ada penambahan atau perubahan fitur, file docs yang relevan **WAJIB** diupdate dalam sesi yang sama.

### File Docs yang Harus Diupdate (sesuai jenis perubahan)

| Jenis Perubahan | File Docs yang Harus Diupdate |
|---|---|
| Tambah/ubah fitur di modul tertentu | `docs/05-MODULES.md` (bagian modul terkait) |
| Tambah/ubah/hapus route | `docs/07-API-REFERENCE.md` |
| Tambah tabel atau kolom baru | `docs/03-DATABASE-SCHEMA.md` |
| Ubah arsitektur / pola koding | `docs/04-ARCHITECTURE.md` |
| Tambah aturan konvensi baru | `docs/08-CONVENTIONS.md` |
| Tambah setup baru / known issue | `docs/09-DEVELOPMENT-GUIDE.md` (bagian Known Issues) |
| Semua perubahan | `docs/09-DEVELOPMENT-GUIDE.md` (bagian **Changelog Fitur**) |

### Format Entry Changelog

Setiap entri changelog di bagian **12. Changelog Fitur** HARUS mengikuti format ini:

```markdown
### v{versi} – {tanggal YYYY-MM-DD}

#### ✅ {Nama Fitur} (Modul: {Nama Modul})
- **File yang ditambah/diubah:**
  - `path/ke/file.php` — Deskripsi singkat perubahan
  - `path/ke/view.blade.php` — **[BARU]** jika file baru
- **Docs yang diupdate:** `docs/...`
- **Fitur:**
  - Poin-poin fitur yang ditambahkan/diubah
```

