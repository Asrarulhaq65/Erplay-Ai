# 04 – Arsitektur Aplikasi

---

## 1. Arsitektur Multi-Tenancy

Sistem ini menggunakan pola **Shared Database, Shared Schema** — semua toko (tenant) menyimpan data di tabel yang sama, namun **diisolasi secara mutlak** melalui kolom `toko_id`.

### Mekanisme Isolasi: Eloquent Global Scope

**Setiap model utama** menerapkan Global Scope yang otomatis menyuntikkan filter `toko_id` ke SEMUA query Eloquent:

```php
// Diterapkan di semua model: User, Produk, Pelanggan, Supplier,
// KelompokProduk, KategoriProduk, Pembelian, Penjualan, ArusKas, LogStok

protected static function booted(): void
{
    static::addGlobalScope('tenant', function (Builder $builder) {
        if (auth()->hasUser()) {
            $builder->where('tabel.toko_id', auth()->user()->toko_id);
        }
    });

    static::creating(function (self $model) {
        if (auth()->check() && empty($model->toko_id)) {
            $model->toko_id = auth()->user()->toko_id;
        }
    });
}
```

### Cara Bypass Global Scope (Khusus Super Admin / Seeder)

```php
// Bypass untuk query cross-tenant
User::withoutGlobalScopes()->where('username', 'superadmin')->first();

// Di Seeder (login dulu sebagai user yang sesuai)
Auth::login($kasirUser);
// Setelah selesai seeding:
Auth::logout();
```

> ⚠️ **PERINGATAN**: Jangan pernah menggunakan `withoutGlobalScopes()` di controller tanpa justifikasi yang kuat. Ini bisa menyebabkan kebocoran data antar tenant.

---

## 2. RBAC (Role-Based Access Control)

### Middleware: `RoleMiddleware`

File: `app/Http/Middleware/RoleMiddleware.php`

Cara penggunaan di route:
```php
Route::middleware(['role:Super Admin,Owner'])->group(function () {
    // Route yang hanya bisa diakses Super Admin atau Owner
});
```

Logika: Cek `auth()->user()->role->nama_role` — jika tidak ada dalam daftar yang diizinkan → redirect ke `/` dengan flash error.

### Middleware: `CheckSubscription`

File: `app/Http/Middleware/CheckSubscription.php`

- **Super Admin** dikecualikan dari pengecekan ini
- Cek `toko->berakhir_pada` dan `toko->status_langganan`
- Jika expired atau blocked → redirect ke `/langganan/expired`
- Jika tanggal sudah lewat tapi status masih `Aktif` → otomatis update ke `Kedaluwarsa`

### Tabel Matrix Akses

| Route Group | Super Admin | Owner | Admin Toko | Gudang | Kasir |
|---|:---:|:---:|:---:|:---:|:---:|
| CMS (`/cms/*`) | ✅ | ❌ | ❌ | ❌ | ❌ |
| POS (`/pos/*`) | ✅ | ✅ | ✅ | ❌ | ✅ |
| Pembelian | ✅ | ✅ | ✅ | ✅ | ❌ |
| Stock Opname | ✅ | ✅ | ✅ | ✅ | ❌ |
| Master Produk | ✅ | ✅ | ✅ | ✅ | ✅ |
| Master Pelanggan | ✅ | ✅ | ✅ | ❌ | ❌ |
| Rekap Penjualan | ✅ | ✅ | ✅ | ❌ | ✅ |
| Analytics | ✅ | ✅ | ✅ | ❌ | ❌ |
| Pengaturan User | ✅ | ✅ | ✅ | ❌ | ❌ |
| Pengaturan Toko | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## 3. Alur Autentikasi

### Login Flow

```
[POST /login]
    │
    ▼
AuthController@login
    │
    ├── Auth::attempt($credentials, $remember)
    │       │
    │       ├── GAGAL → redirect back() + withErrors('Username/password salah')
    │       │
    │       └── BERHASIL
    │               │
    │               ├── Check: is_active == false?
    │               │       └── YA → logout, redirect + error 'Akun dinonaktifkan'
    │               │
    │               └── TIDAK → session()->regenerate() → redirect /dashboard
```

### Register Flow

```
[POST /register]
    │
    ▼
AuthController@register
    │
    ├── Validasi: nama_toko, nama_lengkap, username (unique), password (confirmed)
    │
    ├── Toko::create([...]) ← Buat tenant baru
    │
    ├── Role::where('nama_role', 'Owner')->first() ← Cari role Owner
    │
    ├── User::create([toko_id, role_id='Owner', ...]) ← Buat akun Owner
    │
    └── Auth::login($user) → redirect /dashboard
```

---

## 4. Arsitektur Request Lifecycle (POS Transaction)

Ini adalah flow paling kompleks di sistem:

```
[Browser/JS – POS Custom Page]
    │
    │ Klik "Proses Transaksi" (POST /penjualan/store)
    │ Payload JSON: { pelanggan_id, metode_pembayaran, diskon, nominal_uang, items[] }
    ▼
[Middleware Stack]
    │── auth (cek login)
    │── role:Super Admin,Owner,Kasir,Admin Toko
    └── subscription (cek langganan)
    │
    ▼
[PenjualanController@store]
    │
    ├── PHASE 1: Structural Validation (shape & types)
    │     - Validate semua field & items[]
    │     - Cek pelanggan_id exists di DB
    │
    ├── PHASE 2: Business Logic (DB::beginTransaction)
    │     │
    │     ├── Resolve Customer Status
    │     │     ├── pelanggan_id null → statusPelanggan = 'Umum'
    │     │     └── else → query pelanggan → ambil status_pelanggan
    │     │
    │     ├── Per Item Loop (lockForUpdate):
    │     │     ├── Ambil Produk (lockForUpdate) ← prevent race condition
    │     │     ├── Cek stok >= qty ← Backend validation (tidak percaya client)
    │     │     ├── Hitung harga via Produk::getHargaByStatus(status) ← TIDAK dari client
    │     │     └── Hitung subtotal
    │     │
    │     ├── INSERT penjualan (header)
    │     │
    │     ├── Per Item: INSERT penjualan_detail
    │     │
    │     ├── Per Item: UPDATE produk.stok (-= qty)
    │     │
    │     ├── Per Item: INSERT log_stok (tipe = 'Penjualan')
    │     │
    │     └── IF Tunai/Digital: INSERT arus_kas (tipe = 'Masuk')
    │
    ├── DB::commit() → return JSON {success: true, invoice, kembalian}
    │
    └── ON ERROR: DB::rollBack() → return JSON {success: false, message}
```

---

## 5. Arsitektur Front-End POS Custom

POS Custom menggunakan pattern state JavaScript yang murni tanpa framework:

```javascript
// State Utama
let pelangganState = {
    id: null,
    nama: 'Pelanggan Umum',
    status: 'Umum'  // Tier harga yang terkunci
};

let keranjang = [];  // Array of {produk_id, nama, qty, harga, subtotal}

// Flow:
// 1. Pilih pelanggan → lock pelangganState
// 2. AJAX search produk → pilih → ambil harga berdasarkan pelangganState.status
// 3. Input qty → validate stok real-time
// 4. Append ke keranjang → render tabel
// 5. F9 / klik bayar → buka modal → submit
```

### AJAX Endpoints yang Digunakan di POS

| Tujuan | Method | URL |
|---|---|---|
| Search Produk | GET | `/api/produk/search?q={query}` |
| Search Pelanggan | GET | `/api/pelanggan/search?q={query}` |
| Submit Transaksi | POST | `/penjualan/store` |
| Lihat Detail Transaksi | GET | `/api/penjualan/detail/{id}` |

---

## 6. Dependency Injection & Service Architecture

Proyek ini **tidak menggunakan** Service classes terpisah. Logic bisnis utama ditulis langsung di Controller (terutama `PenjualanController` dan `PembelianController`).

Bila mengembangkan fitur baru yang kompleks, disarankan untuk mempertimbangkan memindahkan logic ke Service Class di `app/Services/`.

---

## 7. Konfigurasi Middleware Stack

Di `bootstrap/app.php` (Laravel 13 style), middleware di-alias sebagai:
```php
'role'         => RoleMiddleware::class,
'subscription' => CheckSubscription::class,
```

---

## 8. Format Invoice

```
Format: INV/YYYYMMDD/XXXX
Contoh: INV/20260702/0001

Auto-generated oleh: Penjualan::generateNomorInvoice()
Scope: Global (UNIQUE across ALL tenants)
Alasan: Memudahkan pencarian invoice lintas toko oleh Super Admin
```
