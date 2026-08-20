# 08 – Konvensi Koding & Aturan Wajib

> **WAJIB DIBACA** sebelum menulis atau memodifikasi kode apapun di proyek ini.
> Dokumen ini adalah "hukum" proyek yang harus diikuti oleh developer maupun AI agent.

---

## ⚠️ ATURAN KRITIS (Jangan Dilanggar)

### 1. WAJIB: Global Scope di Setiap Model Baru

Setiap model yang memiliki kolom `toko_id` **WAJIB** menerapkan Global Scope ini di method `booted()`:

```php
protected static function booted(): void
{
    // ── Global Scope: auto-filter by tenant ──────────────────────────────
    static::addGlobalScope('tenant', function (Builder $builder) {
        if (auth()->check()) {
            $builder->where('nama_tabel.toko_id', auth()->user()->toko_id);
        }
    });

    // ── Creating Event: auto-assign toko_id ──────────────────────────────
    static::creating(function (self $model) {
        if (auth()->check() && empty($model->toko_id)) {
            $model->toko_id = auth()->user()->toko_id;
        }
    });
}
```

> **Tanpa ini, data antar toko bisa bocor satu sama lain.**

### 2. WAJIB: Semua Transaksi (POS & Pembelian) Dalam DB Transaction

```php
DB::beginTransaction();
try {
    // ... semua operasi DB ...
    DB::commit();
    return response()->json(['success' => true]);
} catch (\Throwable $e) {
    DB::rollBack();
    Log::error('...', ['message' => $e->getMessage()]);
    return response()->json(['success' => false], 500);
}
```

### 3. WAJIB: lockForUpdate() Sebelum Baca & Update Stok

```php
$produk = Produk::lockForUpdate()->find($item['product_id']);
// Lakukan update stok setelah ini
```

### 4. WAJIB: Backend Harus Validasi Ulang Harga (Jangan Percaya Client)

Harga **tidak boleh diambil dari request client**. Selalu hitung dari database:

```php
// ✅ BENAR
$harga = $produk->getHargaByStatus($statusPelanggan);

// ❌ SALAH - jangan lakukan ini
$harga = $request->input('harga_satuan');
```

### 5. DILARANG: Menggunakan withoutGlobalScopes() di Controller

```php
// ❌ DILARANG kecuali ada justifikasi kuat
User::withoutGlobalScopes()->get();

// ✅ Boleh di Seeder saja
$existing = User::withoutGlobalScopes()->where('username', 'superadmin')->first();
```

---

## Konvensi Penamaan (Naming Conventions)

### Database & Migration

| Aspek | Konvensi | Contoh |
|---|---|---|
| Nama Tabel | `snake_case`, Bahasa Indonesia | `penjualan_detail`, `arus_kas` |
| Nama Kolom | `snake_case`, deskriptif | `harga_jual_member`, `stok_minimum` |
| Foreign Key | `{model}_id` | `toko_id`, `pelanggan_id` |
| Timestamps | Gunakan `$table->timestamps()` | `created_at`, `updated_at` |
| Enum Values | CamelCase atau deskriptif | `Masuk_Barang`, `Penjualan` |

### Model

| Aspek | Konvensi |
|---|---|
| Class name | `PascalCase`, singular | `KelompokProduk`, `PenjualanDetail` |
| Tabel (override) | Explicit `protected $table` | `protected $table = 'penjualan';` |
| Relasi | camelCase | `kategoriProduk()`, `penjualanDetails()` |
| Helper method | camelCase | `getHargaByStatus()`, `isStokRendah()` |

### Controller

| Aspek | Konvensi |
|---|---|
| Class name | `{Model}Controller.php` | `ProdukController`, `PembelianController` |
| Method | Standard Laravel resourceful | `index`, `create`, `store`, `edit`, `update`, `destroy` |
| AJAX/API | Deskriptif | `search`, `detail`, `filter` |
| Return type | Blade view atau `JsonResponse` | Jangan mix return type |

### Route

| Aspek | Konvensi |
|---|---|
| Prefix | kebab-case, Bahasa Indonesia | `/master/kelompok-produk` |
| Route name | dot notation | `master.produk.index`, `pos.custom` |
| AJAX endpoints | Prefix `/api/` | `/api/produk/search` |

### View (Blade)

| Aspek | Konvensi |
|---|---|
| Direktori | lowercase, sesuai modul | `pages/master/produk/` |
| File | snake_case | `index.blade.php`, `form.blade.php` |
| Variable dari controller | camelCase atau snake_case | `$produkList`, `$totalRevenue` |

---

## Konvensi UI/UX

### 1. Wajib Gunakan Bootstrap `-sm` Variant
```html
<!-- ✅ BENAR -->
<table class="table table-sm table-bordered">
<input class="form-control form-control-sm">
<button class="btn btn-sm btn-primary">

<!-- ❌ SALAH — terlalu besar -->
<table class="table table-bordered">
<input class="form-control">
```

### 2. Warna Palette Wajib
```css
/* Jika perlu override warna, gunakan variabel ini: */
--primary:       #1E4D6B   /* Biru tua utama */
--primary-light: #3D85B0   /* Biru aksen */
--accent:        #7DBA84   /* Hijau aksen */

/* Pastel Blue untuk background elemen aktif/header: */
background: #EBF3F5;   /* Sangat terang */
background: #A0C4DF;   /* Lebih dalam */
```

### 3. Low-Stock Warning (Tabel Produk)
```blade
<tr class="{{ $produk->isStokRendah() ? 'table-danger' : '' }}">
    <!-- Jangan gunakan warna merah terang keras -->
```

### 4. Error/Validasi di POS — Visual Only, NO Popup Alert
```javascript
// ✅ BENAR: Visual warning
qtyInput.style.backgroundColor = '#fee2e2';
warningText.style.display = 'block';
submitBtn.disabled = true;

// ❌ SALAH: Jangan pernah gunakan ini di POS
alert('Stok tidak mencukupi!');
```

### 5. Format Angka (Currency)
Selalu format sebagai Rupiah Indonesia:
```javascript
// JavaScript
new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR'}).format(nilai);
// atau
'Rp ' + number.toLocaleString('id-ID');
```

```php
// PHP/Blade
'Rp ' . number_format($nilai, 0, ',', '.')
```

---

## Konvensi Data

### Format Nomor Invoice
```
INV/YYYYMMDD/XXXX
Contoh: INV/20260702/0001
```
Selalu generate via: `Penjualan::generateNomorInvoice()`

### Format Kode Pelanggan
```
PLG-XXXX
Contoh: PLG-0001
```

### Harga Modal: Last Cost Strategy
Setiap kali ada pembelian barang masuk, `produk.harga_modal` di-update ke harga beli terbaru (bukan rata-rata). Ini adalah keputusan desain yang disengaja.

### Snapshot Harga di `penjualan_detail`
Kolom `harga_satuan` di `penjualan_detail` adalah snapshot immutable. Jangan pernah JOIN ke `produk.harga_jual_*` untuk kalkulasi laporan historis — gunakan nilai dari `penjualan_detail.harga_satuan`.

---

## Konvensi Error Handling

### Controller yang Return JSON (POS, Pembelian)
```php
// Sukses
return response()->json(['success' => true, 'message' => '...'], 201);

// Gagal validasi (dihandle Laravel otomatis)
// → 422 dengan {errors: {...}}

// Gagal sistem
return response()->json(['success' => false, 'message' => '...', 'error' => $e->getMessage()], 500);
```

### Controller yang Return Blade View
```php
// Sukses (redirect)
return redirect()->route('master.produk.index')->with('success', 'Produk berhasil disimpan.');

// Gagal (kembali ke form dengan error)
return redirect()->back()->withErrors($validator)->withInput();
```

---

## Konvensi Seeder

Selalu gunakan `firstOrCreate()` di seeder agar aman dijalankan berulang kali:

```php
// ✅ BENAR - idempotent
Produk::firstOrCreate(['barcode' => '89910011'], [
    'nama_produk' => '...',
    // ...
]);

// ❌ SALAH - error jika dijalankan dua kali
Produk::create([...]);
```

Jika perlu login di seeder (untuk trigger Global Scope):
```php
Auth::login($user);
// ... operasi seeder ...
Auth::logout(); // selalu logout setelah selesai
```

---

## Checklist Sebelum Menambah Fitur Baru

- [ ] Model baru: apakah memiliki `toko_id`? → Tambahkan Global Scope
- [ ] Migration baru: apakah ada FK? → Pastikan urutan migration benar
- [ ] Route baru: apakah sudah ada middleware `role:` yang tepat?
- [ ] Route baru yang perlu subscription check: tambahkan middleware `subscription`
- [ ] Controller baru yang write ke DB: gunakan `DB::transaction()`
- [ ] Kalkulasi harga: **jangan ambil dari client**, selalu dari DB
- [ ] UI feedback: gunakan Bootstrap alert/badge, bukan `alert()` JavaScript
- [ ] Data format angka: format sebagai Rupiah
