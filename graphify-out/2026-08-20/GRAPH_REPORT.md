# Graph Report - .  (2026-07-24)

## Corpus Check
- 142 files · ~87,151 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 736 nodes · 985 edges · 101 communities (89 shown, 12 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- App Http Controllers Akuntansicontroller Group
- App Http Controllers Kategoriprodukcontroller Group
- Docs 05 Modules Group
- App Http Controllers Authcontroller Group
- Composer Group
- Docs 02 Prd Group
- Docs 09 Development Guide Group
- Docs 08 Conventions Group
- App Http Controllers Usercontroller Group
- Composer Scripts Group
- Docs 03 Database Schema Group
- App Http Controllers Pembeliancontroller Group
- Concurrently Group
- Docs 04 Architecture Group
- Docs 07 Api Reference Group
- Docs 01 Project Overview Group
- App Models Akunakuntansi Group
- App Http Controllers Posscancontroller Group
- App Models Penjualan Penjualan Group
- Docs 06 Flow Diagram Group
- Product Group
- App Models Pembelian Pembelian Group
- Readme Group
- App Http Controllers Stockopnamecontroller Group
- App Models Aruskas Aruskas Group
- App Providers Appserviceprovider Group
- Illuminate Foundation Testing Testcase Group
- App Models Auditlog Auditlog Group
- App Models Kelompokproduk Kelompokproduk Group
- App Models Pelanggan Pelanggan Group
- App Models Supplier Supplier Group
- Docs Readme Group
- Partials Confirm Modal Group
- Phpunit Framework Testcase Group
- App Http Controllers Controller Group

## God Nodes (most connected - your core abstractions)
1. `User` - 21 edges
2. `ProdukController` - 17 edges
3. `05 – Panduan Modul` - 16 edges
4. `Tabel-Tabel Database` - 15 edges
5. `09 – Panduan Pengembangan (Development Guide)` - 14 edges
6. `Toko` - 12 edges
7. `Penjualan` - 10 edges
8. `Produk` - 10 edges
9. `2. Scope & Modul Fitur` - 10 edges
10. `AkuntansiController` - 9 edges

## Surprising Connections (you probably didn't know these)
- `KategoriProdukController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/KategoriProdukController.php →   _Bridges community 0 → community 1_
- `PosScanController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/PosScanController.php →   _Bridges community 0 → community 17_
- `UserController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/UserController.php →   _Bridges community 0 → community 8_
- `AuditLog` --mixes_in--> `Illuminate\Database\Eloquent\Factories\HasFactory`  [EXTRACTED]
  app/Models/AuditLog.php →   _Bridges community 16 → community 27_
- `ArusKas` --inherits--> `Illuminate\Database\Eloquent\Model`  [EXTRACTED]
  app/Models/ArusKas.php →   _Bridges community 16 → community 24_

## Import Cycles
- None detected.

## Communities (101 total, 12 thin omitted)

### Community 0 - "App Http Controllers Akuntansicontroller Group"
Cohesion: 0.05
Nodes (19): AkuntansiController, AnalyticsDashboardController, AuditLogController, AuthController, CmsController, PelangganSearchController, PembelianController, PenjualanController (+11 more)

### Community 1 - "App Http Controllers Kategoriprodukcontroller Group"
Cohesion: 0.07
Nodes (18): KategoriProdukController, KelompokProdukController, PelangganController, ProdukController, SupplierController, App\Models\KategoriProduk, App\Models\KelompokProduk, App\Models\Pelanggan (+10 more)

### Community 2 - "Docs 05 Modules Group"
Cohesion: 0.04
Nodes (44): 05 – Panduan Modul, Alur `store()`:, Catatan Penting, Catatan Route Order (Penting!), Controller, Controller, Controller, Controller (+36 more)

### Community 3 - "App Http Controllers Authcontroller Group"
Cohesion: 0.07
Nodes (12): Produk, Role, Toko, UserFactory, DatabaseSeeder, DummyRetailSeeder, SuperAdminSeeder, Illuminate\Database\Eloquent\Factories\Factory (+4 more)

### Community 4 - "Composer Group"
Cohesion: 0.05
Nodes (40): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+32 more)

### Community 5 - "Docs 02 Prd Group"
Cohesion: 0.05
Nodes (36): 02 – Product Requirements Document (PRD), 1.1 Manajemen Toko (Tenant), 1.2 Manajemen User & Role, 1.3 Roles & Hak Akses, 1. Latar Belakang & Tujuan Proyek, 2.1 Hierarki Produk (2-Level), 2.2 Data Produk, 2.3 Fitur UI Produk (+28 more)

### Community 6 - "Docs 09 Development Guide Group"
Cohesion: 0.06
Nodes (30): 09 – Panduan Pengembangan (Development Guide), 10. Lokasi File Penting, 11. Known Issues & Catatan, 12. Changelog Fitur, 13. Aturan Wajib: Update Docs Setiap Ada Perubahan, 1. Setup Lokal (Laragon), 2. Artisan Commands yang Sering Digunakan, 3. Alur Pengembangan Fitur Baru (+22 more)

### Community 7 - "Docs 08 Conventions Group"
Cohesion: 0.07
Nodes (29): 08 – Konvensi Koding & Aturan Wajib, 1. WAJIB: Global Scope di Setiap Model Baru, 1. Wajib Gunakan Bootstrap `-sm` Variant, 2. WAJIB: Semua Transaksi (POS & Pembelian) Dalam DB Transaction, 2. Warna Palette Wajib, 3. Low-Stock Warning (Tabel Produk), 3. WAJIB: lockForUpdate() Sebelum Baca & Update Stok, 4. Error/Validasi di POS — Visual Only, NO Popup Alert (+21 more)

### Community 8 - "App Http Controllers Usercontroller Group"
Cohesion: 0.10
Nodes (10): UserController, App\Models\User, User, Illuminate\Auth\Authenticatable, Illuminate\Auth\MustVerifyEmail, Illuminate\Auth\Passwords\CanResetPassword, Illuminate\Contracts\Auth\Access\Authorizable, Illuminate\Contracts\Auth\Authenticatable (+2 more)

### Community 9 - "Composer Scripts Group"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 10 - "Docs 03 Database Schema Group"
Cohesion: 0.10
Nodes (19): 03 – Database Schema, 10. `pembelian_detail` (Purchase Order Line Items), 11. `penjualan` (Sales Transaction Header), 12. `penjualan_detail` (Sales Line Items), 13. `arus_kas` (Cash Flow Ledger), 14. `log_stok` (Stock Audit Trail – Append Only), 1. `toko` (Tenant Root), 2. `roles` (+11 more)

### Community 11 - "App Http Controllers Pembeliancontroller Group"
Cohesion: 0.16
Nodes (4): KategoriProduk, PembelianDetail, PenjualanDetail, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 12 - "Concurrently Group"
Cohesion: 0.11
Nodes (17): concurrently, laravel-vite-plugin, devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite (+9 more)

### Community 13 - "Docs 04 Architecture Group"
Cohesion: 0.11
Nodes (17): 04 – Arsitektur Aplikasi, 1. Arsitektur Multi-Tenancy, 2. RBAC (Role-Based Access Control), 3. Alur Autentikasi, 4. Arsitektur Request Lifecycle (POS Transaction), 5. Arsitektur Front-End POS Custom, 6. Dependency Injection & Service Architecture, 7. Konfigurasi Middleware Stack (+9 more)

### Community 14 - "Docs 07 Api Reference Group"
Cohesion: 0.11
Nodes (17): 07 – API & Route Reference, 1. Route Publik (Tidak Perlu Login), 2.1 Umum (semua role), 2.2 CMS Tenant Management, 2.3 POS (Point of Sale), 2.4 Pembelian (Inventory Restock), 2.5 Inventory Management, 2.6 Laporan (+9 more)

### Community 15 - "Docs 01 Project Overview Group"
Cohesion: 0.12
Nodes (15): 01 – Project Overview, 1. Compact & Dense UI, 2. Pastel Blue Color Palette, 3. Zero Trust on Client, 4. Keyboard-Driven POS, 5. Absolute Multi-Tenant Isolation, Akun Default (dari Seeder), Backend (+7 more)

### Community 16 - "App Models Akunakuntansi Group"
Cohesion: 0.22
Nodes (5): AkunAkuntansi, JurnalDetail, JurnalUmum, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model

### Community 17 - "App Http Controllers Posscancontroller Group"
Cohesion: 0.26
Nodes (6): PosScanController, App\Services\GeminiScanService, GeminiScanService, App\Services\ProductFuzzyMatcher, ProductFuzzyMatcher, Illuminate\Http\UploadedFile

### Community 19 - "Docs 06 Flow Diagram Group"
Cohesion: 0.22
Nodes (8): 06 – Flow Diagram, 1. Alur Login & Autentikasi, 2. Alur Pendaftaran Toko Baru, 3. Alur Transaksi POS Custom (Quick-Entry), 4. Alur Pembelian Barang (Restock), 5. Alur Subscription Check, 6. Alur Stock Opname, 7. Alur CMS Kelola Langganan Toko (Super Admin)

### Community 20 - "Product Group"
Cohesion: 0.22
Nodes (8): Accessibility & Inclusion, Anti-references, Brand Personality, Design Principles, Product, Product Purpose, Register, Users

### Community 22 - "Readme Group"
Cohesion: 0.25
Nodes (7): About Laravel, Agentic Development, Code of Conduct, Contributing, Learning Laravel, License, Security Vulnerabilities

### Community 26 - "Illuminate Foundation Testing Testcase Group"
Cohesion: 0.40
Nodes (4): Illuminate\Foundation\Testing\TestCase, ExampleTest, Tests\TestCase, TestCase

### Community 31 - "Docs Readme Group"
Cohesion: 0.50
Nodes (3): 🚀 Cara Membaca Dokumen Ini (Untuk AI Agent), 📑 Daftar Dokumen, 📂 Dokumentasi Proyek – Retail ERP By Arul

## Knowledge Gaps
- **233 isolated node(s):** `Controller`, `$schema`, `name`, `type`, `description` (+228 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **12 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `App Http Controllers Usercontroller Group` to `App Models Akunakuntansi Group`?**
  _High betweenness centrality (0.012) - this node is a cross-community bridge._
- **Why does `Penjualan` connect `App Models Penjualan Penjualan Group` to `App Http Controllers Akuntansicontroller Group`, `App Models Akunakuntansi Group`?**
  _High betweenness centrality (0.005) - this node is a cross-community bridge._
- **Why does `scripts` connect `Composer Scripts Group` to `Composer Group`?**
  _High betweenness centrality (0.005) - this node is a cross-community bridge._
- **What connects `Controller`, `$schema`, `name` to the rest of the system?**
  _233 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `App Http Controllers Akuntansicontroller Group` be split into smaller, more focused modules?**
  _Cohesion score 0.052917232021709636 - nodes in this community are weakly interconnected._
- **Should `App Http Controllers Kategoriprodukcontroller Group` be split into smaller, more focused modules?**
  _Cohesion score 0.07188778492109878 - nodes in this community are weakly interconnected._
- **Should `Docs 05 Modules Group` be split into smaller, more focused modules?**
  _Cohesion score 0.044444444444444446 - nodes in this community are weakly interconnected._