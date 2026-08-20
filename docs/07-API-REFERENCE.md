# 07 – API & Route Reference

Semua route yang tersedia di aplikasi, beserta middleware, controller, dan hak aksesnya.

---

## Konvensi

- **`[G]`** = GET
- **`[P]`** = POST
- **`[PU]`** = PUT
- **`[D]`** = DELETE
- **`auth`** = Semua route di bawah ini memerlukan login
- **`role:X`** = Memerlukan role X (dipisah koma = OR)
- **`subscription`** = Memerlukan langganan toko aktif

---

## 1. Route Publik (Tidak Perlu Login)

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/` | (closure) | Halaman Welcome |
| `[G]` | `/login` | `AuthController@showLogin` | Halaman login |
| `[P]` | `/login` | `AuthController@login` | Proses login |
| `[G]` | `/register` | `AuthController@showRegister` | Form daftar toko |
| `[P]` | `/register` | `AuthController@register` | Proses daftar |

---

## 2. Route Auth (Perlu Login)

### 2.1 Umum (semua role)

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[P]` | `/logout` | `AuthController@logout` | Logout |
| `[G]` | `/dashboard` | (closure) | Dashboard utama |
| `[G]` | `/langganan` | (closure) | Info langganan habis |
| `[G]` | `/langganan/status` | (closure) | Status langganan |

---

### 2.2 CMS Tenant Management
**Middleware**: `role:Super Admin`

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/cms/toko` | `CmsController@index` | Daftar semua toko |
| `[PU]` | `/cms/toko/{id}/subscription` | `CmsController@updateSubscription` | Update status langganan |

**Request body** untuk `updateSubscription`:
```json
{
  "status_langganan": "Aktif",
  "berakhir_pada": "2027-01-01"
}
```

---

### 2.3 POS (Point of Sale)
**Middleware**: `role:Super Admin,Owner,Kasir,Admin Toko` + `subscription`

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/pos/custom` | (closure) | POS Mode Custom |
| `[G]` | `/pos/standard` | (closure) | POS Mode Standard |
| `[P]` | `/penjualan/store` | `PenjualanController@store` | Submit transaksi (JSON) |
| `[G]` | `/api/penjualan/detail/{id}` | `PenjualanController@detail` | Detail transaksi (JSON) |
| `[G]` | `/pos/print-struk/{id}` | `PenjualanController@printStruk` | Cetak struk |

**Request Body** untuk `POST /penjualan/store`:
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

**Response Sukses:**
```json
{
  "success": true,
  "message": "Transaksi berhasil disimpan.",
  "invoice": "INV/20260702/0001",
  "kembalian": 5000
}
```

**Response Gagal:**
```json
{
  "success": false,
  "message": "Stok tidak mencukupi untuk produk XYZ."
}
```

---

### 2.4 Pembelian (Inventory Restock)
**Middleware**: `role:Super Admin,Owner,Gudang,Admin Toko`

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/pembelian` | `PembelianController@index` | Daftar faktur pembelian |
| `[G]` | `/pembelian/create` | `PembelianController@create` | Form faktur baru |
| `[P]` | `/pembelian/store` | `PembelianController@store` | Simpan faktur (JSON) |
| `[G]` | `/pembelian/{id}` | `PembelianController@show` | Detail faktur |

**Query Parameters** untuk `index`:
- `search` — cari nomor faktur atau nama supplier
- `start_date` + `end_date` — filter rentang tanggal

**Request Body** untuk `POST /pembelian/store`:
```json
{
  "supplier_id": 1,
  "nomor_faktur": "FK-2026-001",
  "tanggal_beli": "2026-07-02",
  "items": [
    { "product_id": 3, "qty": 10, "harga_modal": 2500 },
    { "product_id": 7, "qty": 24, "harga_modal": 1500 }
  ]
}
```

---

### 2.5 Inventory Management
**Middleware**: `role:Super Admin,Owner,Gudang,Admin Toko`

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/inventory/opname` | `StockOpnameController@index` | Form stock opname |
| `[P]` | `/inventory/opname` | `StockOpnameController@store` | Simpan penyesuaian |

---

### 2.6 Laporan
| Method | URL | Controller@method | Role | Keterangan |
|---|---|---|---|---|
| `[G]` | `/laporan/rekap-penjualan` | `RekapPenjualanController@index` | SA, Owner, AdminToko, Kasir | Rekap transaksi |
| `[G]` | `/laporan/analytics` | `AnalyticsDashboardController@index` | SA, Owner, AdminToko | Dashboard analitik |

**Query Parameters** untuk `rekap-penjualan`:
- `start_date` — tanggal awal
- `end_date` — tanggal akhir
- `pelanggan_id` — filter pelanggan
- `metode_pembayaran` — Tunai / Kredit / Digital Payment

---

### 2.7 Master Data

#### Master Produk
**Middleware**: `role:Super Admin,Owner,Kasir,Gudang,Admin Toko`

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/master/produk` | `ProdukController@index` | Daftar produk |
| `[G]` | `/master/produk/create` | `ProdukController@create` | Form tambah |
| `[P]` | `/master/produk` | `ProdukController@store` | Simpan produk baru |
| `[G]` | `/master/produk/{id}/edit` | `ProdukController@edit` | Form edit |
| `[PU]` | `/master/produk/{id}` | `ProdukController@update` | Update produk |
| `[D]` | `/master/produk/{id}` | `ProdukController@destroy` | Hapus produk |
| `[G]` | `/master/produk-filter` | `ProdukController@filter` | Filter AJAX (untuk POS) |
| `[G]` | `/master/produk/export-csv` | `ProdukController@exportCsv` | **Export data produk ke CSV** |
| `[G]` | `/master/produk/download-template` | `ProdukController@downloadTemplate` | **Download template CSV kosong** |
| `[G]` | `/master/produk/panduan-export` | `ProdukController@panduanExport` | **Halaman panduan export** |
| `[G]` | `/master/produk/import` | `ProdukController@showImport` | **Halaman form & panduan import** |
| `[P]` | `/master/produk/import` | `ProdukController@importCsv` | **Proses file CSV import** |

**Query Parameters** untuk `export-csv`:
- `search` — filter nama produk atau barcode (opsional)
- `nama_kategori` — filter berdasarkan nama kategori (opsional)

> **Catatan**: Route `export-csv`, `download-template`, `panduan-export`, `import` didefinisikan **sebelum** `Route::resource()` di `web.php` untuk menghindari konflik route model binding.

#### Master Kelompok Produk
| Method | URL | Controller@method |
|---|---|---|
| `[G]` | `/master/kelompok-produk` | `KelompokProdukController@index` |
| `[P]` | `/master/kelompok-produk` | `KelompokProdukController@store` |
| `[PU]` | `/master/kelompok-produk/{id}` | `KelompokProdukController@update` |
| `[D]` | `/master/kelompok-produk/{id}` | `KelompokProdukController@destroy` |

#### Master Kategori Produk
| Method | URL | Controller@method |
|---|---|---|
| `[G]` | `/master/kategori-produk` | `KategoriProdukController@index` |
| `[P]` | `/master/kategori-produk` | `KategoriProdukController@store` |
| `[PU]` | `/master/kategori-produk/{id}` | `KategoriProdukController@update` |
| `[D]` | `/master/kategori-produk/{id}` | `KategoriProdukController@destroy` |

#### Master Pelanggan
**Middleware**: `role:Super Admin,Owner,Admin Toko`

| Method | URL | Controller@method |
|---|---|---|
| `[G]` | `/master/pelanggan` | `PelangganController@index` |
| `[P]` | `/master/pelanggan` | `PelangganController@store` |
| `[PU]` | `/master/pelanggan/{id}` | `PelangganController@update` |
| `[D]` | `/master/pelanggan/{id}` | `PelangganController@destroy` |

---

### 2.8 Pengaturan
**Middleware**: `role:Super Admin,Owner,Admin Toko`

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/pengaturan/users` | `UserController@index` | Daftar user toko |
| `[G]` | `/pengaturan/users/create` | `UserController@create` | Form tambah user |
| `[P]` | `/pengaturan/users` | `UserController@store` | Simpan user baru |
| `[G]` | `/pengaturan/users/{id}/edit` | `UserController@edit` | Form edit user |
| `[PU]` | `/pengaturan/users/{id}` | `UserController@update` | Update user |
| `[D]` | `/pengaturan/users/{id}` | `UserController@destroy` | Hapus user |
| `[G]` | `/pengaturan/toko` | `TokoController@edit` | Form edit profil toko |
| `[PU]` | `/pengaturan/toko` | `TokoController@update` | Simpan profil toko |

---

## 3. AJAX Internal Endpoints

Endpoint ini dipanggil oleh JavaScript di halaman POS:

| Method | URL | Controller@method | Keterangan |
|---|---|---|---|
| `[G]` | `/api/produk/search?q=...` | `ProductSearchController@search` | Live-search produk |
| `[G]` | `/api/pelanggan/search?q=...` | `PelangganSearchController@search` | Live-search pelanggan |
| `[G]` | `/api/penjualan/detail/{id}` | `PenjualanController@detail` | Detail transaksi JSON |
| `[G]` | `/master/produk-filter?kelompok_id=...` | `ProdukController@filter` | Filter produk per kelompok |

**Response format untuk `/api/produk/search`:**
```json
[
  {
    "id": 5,
    "barcode": "89910011",
    "nama_produk": "Buku Tulis Sidu 38 Lembar",
    "satuan": "Pcs",
    "stok": 50,
    "harga": 4000
  }
]
```
(Kolom `harga` sudah disesuaikan berdasarkan `status_pelanggan` yang di-query)
