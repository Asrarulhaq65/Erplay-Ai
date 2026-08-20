# Graph Report - Mini-ERP-By-Arul  (2026-08-20)

## Corpus Check
- 192 files · ~107,615 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1073 nodes · 1730 edges · 139 communities (118 shown, 21 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 100 edges (avg confidence: 0.8)
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
- Resources Views Pages Pos Custom Blade Group
- Resources Views Pages Pos Standard Blade Group
- Laravel\Ai\Contracts\Tool
- Laravel\Ai\Tools\Request
- Execution Prompt — Migrasi ERPlay AI ke Laravel AI SDK
- Illuminate\Contracts\JsonSchema\JsonSchema
- AiAssistantController.php
- UserController
- KategoriProduk
- CheckSubscription.php
- PublicCatalogController.php
- LookupLowStock.php
- LookupProductInfo.php
- LookupSupplierInfo.php
- LookupTopProducts.php
- AuthController
- Illuminate\Database\Seeder
- enterprise.blade.php
- CmsController
- AiSdkService.php
- cms.blade.php
- pages/self-service/index.blade.php

## God Nodes (most connected - your core abstractions)
1. `Produk` - 39 edges
2. `Toko` - 33 edges
3. `User` - 33 edges
4. `Controller` - 26 edges
5. `KategoriProduk` - 23 edges
6. `Pelanggan` - 21 edges
7. `KelompokProduk` - 18 edges
8. `Penjualan` - 18 edges
9. `ProdukController` - 17 edges
10. `AiSdkService` - 17 edges

## Surprising Connections (you probably didn't know these)
- `CustomerServiceAgent` --references--> `Toko`  [EXTRACTED]
  app/Ai/Agents/CustomerServiceAgent.php → app/Models/Toko.php
- `ErpCopilotAgent` --references--> `User`  [EXTRACTED]
  app/Ai/Agents/ErpCopilotAgent.php → app/Models/User.php
- `LookupInfoToko` --references--> `Toko`  [EXTRACTED]
  app/Ai/Tools/LookupInfoToko.php → app/Models/Toko.php
- `AiAssistantController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AiAssistantController.php → app/Http/Controllers/Controller.php
- `AiSettingController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AiSettingController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (139 total, 21 thin omitted)

### Community 0 - "App Http Controllers Akuntansicontroller Group"
Cohesion: 0.14
Nodes (6): Controller, PelangganController, PelangganSearchController, ProductSearchController, RekapPenjualanController, Pelanggan

### Community 1 - "App Http Controllers Kategoriprodukcontroller Group"
Cohesion: 0.13
Nodes (3): ProdukController, Produk, Symfony\Component\HttpFoundation\StreamedResponse

### Community 2 - "Docs 05 Modules Group"
Cohesion: 0.05
Nodes (44): 05 – Panduan Modul, Alur `store()`:, Catatan Penting, Catatan Route Order (Penting!), Controller, Controller, Controller, Controller (+36 more)

### Community 4 - "Composer Group"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 5 - "Docs 02 Prd Group"
Cohesion: 0.05
Nodes (36): 02 – Product Requirements Document (PRD), 1.1 Manajemen Toko (Tenant), 1.2 Manajemen User & Role, 1.3 Roles & Hak Akses, 1. Latar Belakang & Tujuan Proyek, 2.1 Hierarki Produk (2-Level), 2.2 Data Produk, 2.3 Fitur UI Produk (+28 more)

### Community 6 - "Docs 09 Development Guide Group"
Cohesion: 0.07
Nodes (30): 09 – Panduan Pengembangan (Development Guide), 10. Lokasi File Penting, 11. Known Issues & Catatan, 12. Changelog Fitur, 13. Aturan Wajib: Update Docs Setiap Ada Perubahan, 1. Setup Lokal (Laragon), 2. Artisan Commands yang Sering Digunakan, 3. Alur Pengembangan Fitur Baru (+22 more)

### Community 7 - "Docs 08 Conventions Group"
Cohesion: 0.07
Nodes (29): 08 – Konvensi Koding & Aturan Wajib, 1. WAJIB: Global Scope di Setiap Model Baru, 1. Wajib Gunakan Bootstrap `-sm` Variant, 2. WAJIB: Semua Transaksi (POS & Pembelian) Dalam DB Transaction, 2. Warna Palette Wajib, 3. Low-Stock Warning (Tabel Produk), 3. WAJIB: lockForUpdate() Sebelum Baca & Update Stok, 4. Error/Validasi di POS — Visual Only, NO Popup Alert (+21 more)

### Community 8 - "App Http Controllers Usercontroller Group"
Cohesion: 0.18
Nodes (8): User, Illuminate\Auth\Authenticatable, Illuminate\Auth\MustVerifyEmail, Illuminate\Auth\Passwords\CanResetPassword, Illuminate\Contracts\Auth\Access\Authorizable, Illuminate\Contracts\Auth\Authenticatable, Illuminate\Contracts\Auth\CanResetPassword, Illuminate\Foundation\Auth\Access\Authorizable

### Community 9 - "Composer Scripts Group"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 10 - "Docs 03 Database Schema Group"
Cohesion: 0.10
Nodes (19): 03 – Database Schema, 10. `pembelian_detail` (Purchase Order Line Items), 11. `penjualan` (Sales Transaction Header), 12. `penjualan_detail` (Sales Line Items), 13. `arus_kas` (Cash Flow Ledger), 14. `log_stok` (Stock Audit Trail – Append Only), 1. `toko` (Tenant Root), 2. `roles` (+11 more)

### Community 11 - "App Http Controllers Pembeliancontroller Group"
Cohesion: 0.14
Nodes (6): AiActionLog, AiAssistantConfig, PembelianDetail, PenjualanDetail, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 12 - "Concurrently Group"
Cohesion: 0.11
Nodes (17): concurrently, laravel-vite-plugin, devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite (+9 more)

### Community 13 - "Docs 04 Architecture Group"
Cohesion: 0.11
Nodes (17): 04 – Arsitektur Aplikasi, 1. Arsitektur Multi-Tenancy, 2. RBAC (Role-Based Access Control), 3. Alur Autentikasi, 4. Arsitektur Request Lifecycle (POS Transaction), 5. Arsitektur Front-End POS Custom, 6. Dependency Injection & Service Architecture, 7. Konfigurasi Middleware Stack (+9 more)

### Community 14 - "Docs 07 Api Reference Group"
Cohesion: 0.04
Nodes (43): 01 – Project Overview, 1. Compact & Dense UI, 2. Pastel Blue Color Palette, 3. Zero Trust on Client, 4. Keyboard-Driven POS, 5. Absolute Multi-Tenant Isolation, Akun Default (dari Seeder), Backend (+35 more)

### Community 15 - "Docs 01 Project Overview Group"
Cohesion: 0.04
Nodes (44): 10. Implementation Notes for Agent Coding, 1. Product Overview, 2. User Flow, 3.1 Public Catalog Page, 3.2 AI Live Chat (Customer-Facing), 3.3 AI Assistant Name, 3. Feature Set, 4.1 Brand Tokens (+36 more)

### Community 16 - "App Models Akunakuntansi Group"
Cohesion: 0.08
Nodes (10): AkuntansiController, AuditLogController, AkunAkuntansi, AuditLog, JurnalDetail, JurnalUmum, UserFactory, Illuminate\Database\Eloquent\Factories\Factory (+2 more)

### Community 17 - "App Http Controllers Posscancontroller Group"
Cohesion: 0.07
Nodes (12): AiAssistantController, PosScanController, SelfServiceController, VoiceTransactionController, SelfServiceOrder, AiAssistantService, AiSdkService, GeminiScanService (+4 more)

### Community 18 - "App Models Penjualan Penjualan Group"
Cohesion: 0.16
Nodes (3): AnalyticsDashboardController, PenjualanController, Penjualan

### Community 19 - "Docs 06 Flow Diagram Group"
Cohesion: 0.06
Nodes (33): 10. Out of Scope (V2), 1. Feature Overview, 2. User Stories, 3.1 Customizable Assistant Name & Personality, 3.2 Complete Business Workflow Understanding, 3.3 Action Capabilities (bukan cuma jawaban), 3.3 Platform Knowledge & User Guidance, 3.4 Proactive Insights (+25 more)

### Community 20 - "Product Group"
Cohesion: 0.22
Nodes (8): Accessibility & Inclusion, Anti-references, Brand Personality, Design Principles, Product, Product Purpose, Register, Users

### Community 22 - "Readme Group"
Cohesion: 0.25
Nodes (7): About Laravel, Agentic Development, Code of Conduct, Contributing, Learning Laravel, License, Security Vulnerabilities

### Community 26 - "Illuminate Foundation Testing Testcase Group"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Testing\TestCase, ExampleTest, TestCase

### Community 27 - "App Models Auditlog Auditlog Group"
Cohesion: 0.15
Nodes (5): AiSettingController, PublicCatalogSettingController, TokoController, Illuminate\Http\Request, Illuminate\View\View

### Community 28 - "App Models Kelompokproduk Kelompokproduk Group"
Cohesion: 0.20
Nodes (3): KelompokProdukController, KelompokProduk, DummyRetailSeeder

### Community 29 - "App Models Pelanggan Pelanggan Group"
Cohesion: 0.19
Nodes (9): CustomerServiceAgent, ErpCopilotAgent, ScanReceiptAgent, VoiceCommandAgent, Laravel\Ai\Concerns\RemembersConversations, Laravel\Ai\Contracts\Agent, Laravel\Ai\Contracts\HasStructuredOutput, Laravel\Ai\Contracts\HasTools (+1 more)

### Community 30 - "App Models Supplier Supplier Group"
Cohesion: 0.36
Nodes (3): SupplierController, Supplier, Illuminate\Http\RedirectResponse

### Community 31 - "Docs Readme Group"
Cohesion: 0.10
Nodes (19): 1. Install & Publish Laravel AI SDK, 2. Konfigurasi `config/ai.php`, 3. Buat Agent untuk AI Copilot Chat, 4. Migrasi AiAssistantController, 5. Migrasi GeminiScanService → Agent/Tool Vision, 6. Migrasi VoiceTransactionService → Agent Voice/Text, 7. Update AiSettingController, Catatan Keamanan & Edge Case (+11 more)

### Community 32 - "Partials Confirm Modal Group"
Cohesion: 0.50
Nodes (3): partials.ai-copilot-widget, partials.confirm-modal, partials.voice-transaction-modal

### Community 48 - "App Http Controllers Controller Group"
Cohesion: 0.19
Nodes (3): HowToUse, LookupStokPublic, Stringable

### Community 101 - "Laravel\Ai\Contracts\Tool"
Cohesion: 0.18
Nodes (3): ContextualHelp, LookupInfoToko, Laravel\Ai\Contracts\Tool

### Community 102 - "Laravel\Ai\Tools\Request"
Cohesion: 0.16
Nodes (3): LookupHargaPublic, LookupProdukPublic, Laravel\Ai\Tools\Request

### Community 103 - "Execution Prompt — Migrasi ERPlay AI ke Laravel AI SDK"
Cohesion: 0.14
Nodes (13): Deliverables, Execution Prompt — Migrasi ERPlay AI ke Laravel AI SDK, Langkah 1: Install & Publish Laravel AI SDK, Langkah 2: Konfigurasi `config/ai.php`, Langkah 3: Buat Agent Inti `ErpCopilotAgent`, Langkah 4: Refactor `AiAssistantService`, Langkah 5: Refactor `GeminiScanService` → Agent Vision, Langkah 6: Refactor `VoiceTransactionService` → Agent Voice/Text (+5 more)

### Community 104 - "Illuminate\Contracts\JsonSchema\JsonSchema"
Cohesion: 0.18
Nodes (3): ExplainFeature, ListFeaturesByRole, Illuminate\Contracts\JsonSchema\JsonSchema

### Community 109 - "CheckSubscription.php"
Cohesion: 0.43
Nodes (4): CheckSubscription, RoleMiddleware, Closure, Symfony\Component\HttpFoundation\Response

### Community 116 - "Illuminate\Database\Seeder"
Cohesion: 0.40
Nodes (3): DatabaseSeeder, SuperAdminSeeder, Illuminate\Database\Seeder

### Community 117 - "enterprise.blade.php"
Cohesion: 0.50
Nodes (3): partials.ai-copilot-widget, partials.confirm-modal, partials.voice-transaction-modal

## Knowledge Gaps
- **334 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+329 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **21 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Produk` connect `App Http Controllers Kategoriprodukcontroller Group` to `App Http Controllers Akuntansicontroller Group`, `App Http Controllers Authcontroller Group`, `Laravel\Ai\Tools\Request`, `Produk.php`, `App Http Controllers Pembeliancontroller Group`, `PublicCatalogController.php`, `LookupLowStock.php`, `LookupProductInfo.php`, `App Http Controllers Posscancontroller Group`, `App Http Controllers Controller Group`, `App Models Penjualan Penjualan Group`, `App Models Akunakuntansi Group`, `App Models Pembelian Pembelian Group`, `App Http Controllers Stockopnamecontroller Group`, `App Models Auditlog Auditlog Group`, `App Models Kelompokproduk Kelompokproduk Group`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Why does `Toko` connect `App Http Controllers Authcontroller Group` to `Laravel\Ai\Contracts\Tool`, `App Http Controllers Pembeliancontroller Group`, `PublicCatalogController.php`, `AuthController`, `Illuminate\Database\Seeder`, `CmsController`, `AiSdkService.php`, `App Models Auditlog Auditlog Group`, `App Models Kelompokproduk Kelompokproduk Group`, `App Models Pelanggan Pelanggan Group`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Are the 17 inferred relationships involving `Produk` (e.g. with `.instructions()` and `.handle()`) actually correct?**
  _`Produk` has 17 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _334 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `App Http Controllers Akuntansicontroller Group` be split into smaller, more focused modules?**
  _Cohesion score 0.13852813852813853 - nodes in this community are weakly interconnected._
- **Should `App Http Controllers Kategoriprodukcontroller Group` be split into smaller, more focused modules?**
  _Cohesion score 0.12923076923076923 - nodes in this community are weakly interconnected._
- **Should `Docs 05 Modules Group` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._