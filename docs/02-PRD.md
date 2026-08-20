# 02 – Product Requirements Document (PRD)

> Versi terstruktur dan diperluas dari `prd.md` di root proyek.

---

## 1. Latar Belakang & Tujuan Proyek

**Retail ERP By Arul** adalah sistem ERP (Enterprise Resource Planning) berbasis web yang dirancang untuk toko ritel skala kecil–menengah di Indonesia. Sistem ini hadir dalam arsitektur **Multi-Tenant (SaaS-Ready)**, artinya satu instalasi bisa melayani banyak toko sekaligus dengan data yang terisolasi sempurna.

### Masalah yang Diselesaikan
- Kasir masih menggunakan catatan manual / aplikasi spreadsheet yang rawan kesalahan
- Tidak ada visibilitas stok real-time
- Tidak ada pencatatan keuangan (kas masuk/keluar) yang terstruktur
- Tidak ada laporan analitik untuk pemilik toko

### Tujuan Utama
1. Menyediakan POS (Point of Sale) yang cepat, aman, dan keyboard-driven
2. Mengelola inventaris dengan multi-tier pricing
3. Mencatat semua pergerakan stok secara otomatis (audit trail)
4. Menyediakan laporan keuangan dan analitik real-time
5. Multi-tenant: satu platform untuk banyak toko

---

## 2. Scope & Modul Fitur

### MODULE 1 – Tenant & RBAC (Role-Based Access Control)

#### 1.1 Manajemen Toko (Tenant)
- Setiap toko adalah "tenant" yang memiliki data terisolasi
- Data toko: `nama_toko`, `alamat`, `no_telepon`, `logo`, `slogan_struk`
- Fitur langganan (subscription): `status_langganan`, `berakhir_pada`
  - Status: `Aktif`, `Kedaluwarsa`, `Nonaktif`
  - Jika langganan habis → redirect ke halaman `/langganan/expired`
  - Auto-update status ke `Kedaluwarsa` jika tanggal sudah lewat

#### 1.2 Manajemen User & Role
- Setiap user terikat pada satu `toko_id` dan satu `role_id`
- Login menggunakan `username` (bukan email)
- Fitur **Soft Toggle `is_active`**: jika `is_active = 0`, user tidak bisa login
- Pendaftaran toko baru via halaman `/register` → otomatis membuat user dengan Role `Owner`

#### 1.3 Roles & Hak Akses

| Role | Modul yang Bisa Diakses |
|---|---|
| **Super Admin** | Semua modul + CMS Tenant Management (lintas toko) |
| **Owner** | Semua modul toko + laporan keuangan & analitik |
| **Admin Toko** | Semua modul toko kecuali CMS |
| **Gudang** | Produk, Pembelian, Stock Opname |
| **Kasir** | POS, Rekap Penjualan |

> **Super Admin** dikecualikan dari pengecekan subscription (bebas akses).

---

### MODULE 2 – Master Barang & Inventaris

#### 2.1 Hierarki Produk (2-Level)
```
Kelompok Produk (Level 1 / Departemen)
  └── Kategori Produk (Level 2 / Sub-Kategori)
        └── Produk (Item)
```
**Contoh:**
- Kelompok: `Sembako` → Kategori: `Minyak Goreng`, `Mie Instan`
- Kelompok: `Alat Tulis` → Kategori: `Buku`, `Pulpen`

#### 2.2 Data Produk
| Field | Tipe | Keterangan |
|---|---|---|
| `barcode` | string(50) | Unique per toko, indexed |
| `nama_produk` | string(150) | - |
| `satuan` | string(20) | Default: `Pcs` |
| `harga_modal` | decimal(12,2) | Diperbarui via Last Cost saat pembelian |
| `harga_jual_umum` | decimal(12,2) | Untuk pelanggan umum |
| `harga_jual_member` | decimal(12,2) | Untuk member |
| `harga_jual_rekan` | decimal(12,2) | Untuk rekan bisnis |
| `harga_jual_motoris` | decimal(12,2) | Untuk motoris |
| `stok` | integer | Stok saat ini |
| `stok_minimum` | integer | Threshold warning |
| `gambar` | string | Path gambar (opsional) |

#### 2.3 Fitur UI Produk
- **Chained Dropdown**: Memilih Kelompok → Dropdown Kategori otomatis terfilter via AJAX
- **Low Stock Warning**: Baris tabel berwarna merah pastel (`table-danger`) jika `stok <= stok_minimum`
- **Gambar Produk**: Bisa upload gambar ke storage

---

### MODULE 3 – Pelanggan & Multi-Tier Pricing

#### 3.1 Data Pelanggan
| Field | Keterangan |
|---|---|
| `kode_pelanggan` | Unique per toko (e.g., `PLG-0001`) |
| `nama_pelanggan` | - |
| `no_telepon` | - |
| `alamat` | - |
| `status_pelanggan` | `Umum`, `Member`, `Rekan`, `Motoris` |

#### 3.2 Logika Harga Berdasarkan Status

| Status Pelanggan | Kolom Harga yang Digunakan |
|---|---|
| `Umum` | `harga_jual_umum` |
| `Member` | `harga_jual_member` |
| `Rekan` | `harga_jual_rekan` |
| `Motoris` | `harga_jual_motoris` |

> **Walk-in Customer**: Pelanggan yang tidak dipilih (anonim) = `Umum` secara default.

---

### MODULE 4 – Supplier & Pembelian Barang (Restock)

#### 4.1 Data Supplier
Fields: `nama_supplier`, `no_telepon`, `alamat`, `nama_kontak`

#### 4.2 Faktur Pembelian
- Satu faktur = satu `Pembelian` header + banyak `PembelianDetail` (line item)
- **Metode Pembayaran**: `Tunai` atau `Kredit`
  - Kredit → `jatuh_tempo` wajib diisi, `status_pembayaran = Hutang`

#### 4.3 Automation Trigger (On Submit Faktur Pembelian)
1. ✅ **Tambah stok** produk (`produk.stok += qty`)
2. ✅ **Update harga modal** dengan strategi **Last Cost** (`produk.harga_modal = harga_beli_satuan`)
3. ✅ **Insert `log_stok`** dengan `tipe_perubahan = 'Masuk_Barang'`
4. ✅ **Insert `arus_kas`** (tipe `Keluar`, kategori `Pembelian Stok`) jika Tunai

---

### MODULE 5 – POS (Point of Sale) – Dual Mode

#### 5.1 Mode Standard POS (`/pos/standard`)
- Layout grid/list produk visual
- Cocok untuk layar sentuh atau penggunaan kasir umum

#### 5.2 Mode Custom Quick-Entry POS (`/pos/custom`) ← UTAMA
Dirancang untuk **zero-mouse, keyboard-driven** entry yang super cepat.

**Alur UX Wajib:**
```
[Page Load]
    │
    ▼
[INPUT: Nama/Kode Pelanggan] ←── autofocus, live-search dropdown
    │ (Enter tanpa ketik = Pelanggan Umum)
    │
    ▼
[State Terkunci: Status Pelanggan & Tier Harga]
    │ (Enter)
    ▼
[INPUT: Pencarian Produk] ←── ketik min. 2 char → AJAX dropdown
    │ (Arrow Up/Down + Enter)
    ▼
[State: Produk Dipilih, Harga Tier Otomatis Terambil]
    │
    ▼
[INPUT: Qty] ←── default 1, ter-highlight (langsung ketik)
    │             Real-time validation stok
    │ (Enter)
    ▼
[Item Ditambah ke Keranjang] ←── Fokus kembali ke Input Produk
    │
    ▼
[Ulangi untuk produk berikutnya...]
    │
    ▼ (F9 atau klik Bayar)
[MODAL PEMBAYARAN]
    │── Tunai: input nominal uang, hitung kembalian
    │── Kredit: set Belum Lunas
    │── Digital: input referensi transaksi
    │
    ▼ (Submit)
[POST /penjualan/store] ←── Backend validasi ulang harga
```

**Shortcut Keyboard Global:**
- `F8` → Paksa fokus ke input Pencarian Produk
- `F9` → Buka modal pembayaran / fokus ke input nominal

**Validasi Stok (Visual Only, No popup alert):**
- Jika qty > stok → Input qty merah pastel + teks warning + tombol Enter disabled
- Begitu qty dikurangi → Warning hilang otomatis

**Metode Pembayaran POS:**
- `Tunai` → Hitung kembalian, larang submit jika nominal < total
- `Kredit` → `status_pembayaran = Belum Lunas` (piutang)
- `Digital Payment` → Input opsional nomor referensi (QRIS, transfer, dll)

#### 5.3 Automation Trigger (On Submit Transaksi POS)
1. ✅ **INSERT `penjualan`** (header transaksi)
2. ✅ **INSERT `penjualan_detail[]`** (satu row per item keranjang)
3. ✅ **DECREMENT `produk.stok`** per item (`produk.stok -= qty`)
4. ✅ **INSERT `log_stok[]`** (`tipe_perubahan = 'Penjualan'`)
5. ✅ **INSERT `arus_kas`** (`tipe = 'Masuk'`, `kategori = 'Penjualan'`) untuk Tunai & Digital

> **PENTING**: Semua 5 langkah di atas dibungkus dalam `DB::beginTransaction()` dengan `lockForUpdate()` pada setiap produk untuk mencegah race condition.

---

### MODULE 6 – Rekap Penjualan (Sales History)

#### 6.1 Filter Tersedia
- **Filter Tanggal**: Start date & End date (default: hari ini)
- **Filter Pelanggan**: Dropdown pelanggan toko + opsi `Umum`
- **Filter Metode Bayar**: `Tunai`, `Kredit`, `Digital Payment`

#### 6.2 Kolom Tabel Rekap
| Kolom | Keterangan |
|---|---|
| No. Invoice | Format: `INV/YYYYMMDD/XXXX` |
| Tanggal/Waktu | `created_at` transaksi |
| Nama Pelanggan | atau `Umum` jika walk-in |
| Total Belanja | `total_bayar` |
| Metode Bayar | Tunai / Kredit / Digital |
| Status | Lunas / Belum Lunas |
| Kasir | Nama user yang memproses |
| Aksi | Cetak Struk & Lihat Detail |

---

### MODULE 7 – Keuangan & Laporan Analitik

#### 7.1 Buku Kas (`arus_kas`)
Pencatatan otomatis semua transaksi kas:
- **Masuk**: dari Penjualan Tunai & Digital
- **Keluar**: dari Pembelian Stok Tunai

Bisa juga diisi manual untuk biaya operasional lain.

#### 7.2 Dashboard Analytics (`/laporan/analytics`)
Hanya untuk: `Owner`, `Admin Toko`, `Super Admin`

| Laporan | Formula |
|---|---|
| **Total Revenue** | `SUM(penjualan.total_bayar)` bulan berjalan |
| **Total Piutang** | `SUM(total_bayar - nominal_uang)` kredit belum lunas |
| **Net Profit** | `SUM((harga_satuan - harga_modal) × qty)` per detail |
| **Performa Departemen** | Profit per kelompok produk (JOIN 4 tabel) |

#### 7.3 Rekap Penjualan (`/laporan/rekap-penjualan`)
Untuk: `Kasir`, `Owner`, `Admin Toko`, `Super Admin`
- Tabel riwayat transaksi dengan filter multi-kriteria

---

### MODULE 8 – Inventory Management

#### 8.1 Stock Opname (`/inventory/opname`)
- Input penyesuaian stok fisik vs sistem
- Trigger: INSERT `log_stok` (`tipe_perubahan = 'Penyesuaian_Stok'`)

#### 8.2 Log Stok (Audit Trail)
Tabel `log_stok` bersifat **append-only** (tidak pernah di-UPDATE atau DELETE).
Setiap mutasi stok selalu dicatat dengan `stok_awal` dan `stok_akhir` sebagai snapshot.

---

### MODULE 9 – CMS Tenant Management (Super Admin Only)

Route: `/cms/toko`

- Melihat semua toko yang terdaftar beserta jumlah user-nya
- Memperbarui `status_langganan` dan `berakhir_pada` setiap toko
- Hanya bisa diakses oleh role `Super Admin`

---

## 3. Non-Functional Requirements

| Aspek | Requirement |
|---|---|
| **Keamanan** | Harga tidak boleh diambil dari client; divalidasi ulang di backend |
| **Konsistensi Data** | Semua transaksi POS & Pembelian menggunakan DB Transaction |
| **Isolasi Data** | Global Scope di setiap model memastikan data terisolasi per toko |
| **Race Condition** | `lockForUpdate()` pada setiap produk saat transaksi POS/Pembelian |
| **Audit Trail** | `log_stok` adalah immutable append-only log |
| **Performa** | Barcode diindeks, query produk menggunakan filter `toko_id` |
