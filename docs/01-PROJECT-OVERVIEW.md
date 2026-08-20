# 01 – Project Overview

## Identitas Proyek

| Atribut | Detail |
|---|---|
| **Nama Proyek** | Retail ERP By Arul |
| **Versi** | 1.x (Development) |
| **Tipe** | Web Application – Multi-Tenant SaaS ERP |
| **Target Pengguna** | Pemilik toko ritel kecil–menengah di Indonesia |
| **Bahasa Utama** | PHP (Laravel) + Blade + Vanilla JS |
| **Database** | MySQL (via Laragon) |
| **UI Framework** | Bootstrap 5 (compact `-sm`) |
| **Build Tool** | Vite |
| **Environment** | Localhost (Laragon), phpMyAdmin |

---

## Tech Stack Detail

### Backend
- **Framework**: Laravel 13.x (`laravel/framework: ^13.8`)
- **PHP Requirement**: PHP ^8.3
- **Auth**: Laravel built-in Auth Guard (custom field: `username` bukan `email`)
- **Session Driver**: `file` (dikonfigurasi di `.env`)
- **Database**: MySQL, database name: `db_retail_mini_erp`
- **ORM**: Eloquent dengan Global Scope untuk Multi-Tenancy

### Frontend
- **Template Engine**: Blade (Laravel)
- **CSS Framework**: Bootstrap 5.3.3 (via CDN)
- **Icons**: Bootstrap Icons 1.11.3 (via CDN)
- **Font**: Google Fonts – Outfit (300, 400, 500, 600, 700)
- **JavaScript**: Vanilla JS (tidak menggunakan React/Vue/Alpine.js)
- **AJAX**: Fetch API (native browser)
- **Build**: Vite (untuk asset bundling CSS/JS lokal jika ada)

### Dependensi Composer (Production)
```
laravel/framework: ^13.8
laravel/tinker: ^3.0
```

### Dependensi Composer (Dev)
```
fakerphp/faker
laravel/pail
laravel/pao
laravel/pint (code formatter)
phpunit/phpunit: ^12.5
```

---

## Filosofi Desain

### 1. Compact & Dense UI
Seluruh UI menggunakan kelas Bootstrap `-sm` (`form-control-sm`, `btn-sm`, `table-sm`) untuk menghemat ruang layar dan memaksimalkan informasi yang terlihat tanpa scroll.

### 2. Pastel Blue Color Palette
Warna dominan: putih/abu-abu terang dengan aksen **Soft Pastel Blue** (`#EBF3F5` hingga `#A0C4DF`) untuk header tabel, komponen primer, dan status aktif.
```
--primary:       #1E4D6B  (Biru Tua Utama)
--primary-light: #3D85B0  (Biru Terang)
--accent:        #7DBA84  (Hijau Aksen)
```

### 3. Zero Trust on Client
Harga produk **tidak pernah dipercaya dari client (browser)**. Backend selalu menghitung ulang harga dari database berdasarkan `status_pelanggan` untuk mencegah manipulasi.

### 4. Keyboard-Driven POS
Mode POS Custom dirancang untuk kecepatan data entry tanpa mouse. Semua navigasi menggunakan keyboard (Enter, Arrow, F8, F9).

### 5. Absolute Multi-Tenant Isolation
Data antar toko **terisolasi secara mutlak**. Setiap model utama menerapkan `Global Scope` yang secara otomatis memfilter semua query berdasarkan `toko_id` dari user yang sedang login.

---

## Struktur Direktori Proyek

```
Mini-ERP-By-Arul/
├── app/
│   ├── Http/
│   │   ├── Controllers/          ← Semua controller
│   │   │   ├── AuthController.php
│   │   │   ├── CmsController.php
│   │   │   ├── PenjualanController.php  ← Inti transaksi POS
│   │   │   ├── PembelianController.php
│   │   │   ├── ProdukController.php
│   │   │   ├── PelangganController.php
│   │   │   ├── KelompokProdukController.php
│   │   │   ├── KategoriProdukController.php
│   │   │   ├── UserController.php
│   │   │   ├── TokoController.php
│   │   │   ├── StockOpnameController.php
│   │   │   ├── RekapPenjualanController.php
│   │   │   ├── AnalyticsDashboardController.php
│   │   │   ├── ProductSearchController.php   ← API AJAX produk
│   │   │   └── PelangganSearchController.php ← API AJAX pelanggan
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php           ← RBAC gate
│   │       └── CheckSubscription.php        ← Subscription gate
│   ├── Models/
│   │   ├── Toko.php              ← Root tenant entity
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Produk.php
│   │   ├── KelompokProduk.php
│   │   ├── KategoriProduk.php
│   │   ├── Pelanggan.php
│   │   ├── Supplier.php
│   │   ├── Pembelian.php
│   │   ├── PembelianDetail.php
│   │   ├── Penjualan.php
│   │   ├── PenjualanDetail.php
│   │   ├── ArusKas.php
│   │   └── LogStok.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/               ← 13 file migrasi (urutan penting!)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── SuperAdminSeeder.php  ← Akun superadmin
│       └── DummyRetailSeeder.php ← Data demo
├── resources/
│   ├── views/
│   │   ├── auth/                 ← login.blade.php, register.blade.php
│   │   ├── layouts/              ← app.blade.php (layout utama)
│   │   └── pages/
│   │       ├── dashboard.blade.php
│   │       ├── cms/              ← Manajemen tenant (Super Admin only)
│   │       ├── pos/              ← custom.blade.php, standard.blade.php
│   │       ├── master/           ← produk, kategori, kelompok, pelanggan
│   │       ├── pembelian/        ← index, form, show
│   │       ├── laporan/          ← rekap-penjualan, analytics
│   │       ├── inventory/        ← opname
│   │       ├── pengaturan/       ← users, toko
│   │       └── langganan/        ← expired, status
│   ├── css/
│   └── js/
├── routes/
│   └── web.php                   ← Semua routing
├── config/
│   └── session.php               ← Konfigurasi session
├── docs/                         ← ← ← ANDA SEDANG DI SINI
├── .env                          ← Environment config
└── composer.json
```

---

## Akun Default (dari Seeder)

| Role | Username | Password | Toko |
|---|---|---|---|
| Super Admin | `superadmin` | `superadmin123` | Toko Kelontong Jaya |
| Kasir | `kasir` | `password` | Toko Kelontong Jaya |

> **Catatan**: Akun Owner, Gudang harus dibuat manual via menu Pengaturan > Users setelah login sebagai Super Admin atau Owner.
