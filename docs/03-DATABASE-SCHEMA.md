# 03 – Database Schema

Database Name: `db_retail_mini_erp`
Engine: MySQL | Charset: utf8mb4

---

## Entity Relationship Overview

```
toko (Tenant Root)
  ├── users         (staff per toko)
  │     └── role   (hak akses)
  ├── pelanggan     (customer data)
  ├── supplier      (vendor data)
  ├── kelompok_produk
  │     └── kategori_produk
  │           └── produk       (inventory items)
  ├── pembelian     (purchase orders)
  │     └── pembelian_detail
  ├── penjualan     (sales transactions)
  │     └── penjualan_detail
  ├── arus_kas      (cash flow ledger)
  └── log_stok      (stock audit trail)
```

---

## Tabel-Tabel Database

### 1. `toko` (Tenant Root)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK, auto-increment |
| `nama_toko` | varchar(100) | No | - | Nama toko |
| `alamat` | text | No | - | Alamat toko |
| `no_telepon` | varchar(20) | No | - | Nomor telepon |
| `logo` | varchar(255) | Yes | NULL | Path file logo |
| `slogan_struk` | varchar(150) | Yes | NULL | Teks footer struk |
| `status_langganan` | varchar(50) | No | `'Aktif'` | Aktif/Kedaluwarsa/Nonaktif |
| `berakhir_pada` | date | Yes | NULL | Tanggal habis langganan |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Foreign Keys**: Tidak ada (root entity)
**Cascade**: Semua tabel yang ber-FK ke `toko(id)` ON DELETE CASCADE

---

### 2. `roles`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `nama_role` | varchar(50) | No | - | Super Admin / Owner / Gudang / Kasir |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Nilai Roles (Seeder):**
- `Super Admin`
- `Owner`
- `Gudang`
- `Kasir`

---

### 3. `users`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `role_id` | bigint UNSIGNED | No | - | FK → `roles.id` |
| `nama_lengkap` | varchar(255) | No | - | Nama lengkap user |
| `username` | varchar(50) | No | - | UNIQUE, digunakan untuk login |
| `password` | varchar(255) | No | - | Hashed (bcrypt) |
| `is_active` | tinyint(1) | No | 1 | 1=aktif, 0=nonaktif |
| `remember_token` | varchar(100) | Yes | NULL | Laravel remember me token |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Foreign Keys:**
- `toko_id` → `toko(id)` ON DELETE CASCADE
- `role_id` → `roles(id)`

**Global Scope**: `WHERE users.toko_id = auth()->user()->toko_id`

---

### 4. `pelanggan`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `kode_pelanggan` | varchar(20) | No | - | UNIQUE per toko |
| `nama_pelanggan` | varchar(150) | No | - | Nama pelanggan |
| `no_telepon` | varchar(20) | Yes | NULL | - |
| `alamat` | text | Yes | NULL | - |
| `status_pelanggan` | enum | No | - | Umum/Member/Rekan/Motoris |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Foreign Keys:**
- `toko_id` → `toko(id)` ON DELETE CASCADE

---

### 5. `supplier`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `nama_supplier` | varchar(150) | No | - | - |
| `no_telepon` | varchar(20) | Yes | NULL | - |
| `alamat` | text | Yes | NULL | - |
| `nama_kontak` | varchar(100) | Yes | NULL | PIC supplier |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

---

### 6. `kelompok_produk` (Hierarki Level 1)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `nama_kelompok` | varchar(100) | No | - | e.g., Sembako, Alat Tulis |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

---

### 7. `kategori_produk` (Hierarki Level 2)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `kelompok_id` | bigint UNSIGNED | No | - | FK → `kelompok_produk.id` CASCADE |
| `nama_kategori` | varchar(100) | No | - | e.g., Minyak Goreng, Buku |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

---

### 8. `produk`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `kategori_id` | bigint UNSIGNED | No | - | FK → `kategori_produk.id` RESTRICT |
| `barcode` | varchar(50) | No | - | Index + UNIQUE(toko_id, barcode) |
| `nama_produk` | varchar(150) | No | - | - |
| `satuan` | varchar(20) | No | `'Pcs'` | Pcs/Pack/Dus/Bks/Pch/dll |
| `harga_modal` | decimal(12,2) | No | 0 | Harga beli (Last Cost) |
| `harga_jual_umum` | decimal(12,2) | No | 0 | Harga umum |
| `harga_jual_member` | decimal(12,2) | No | 0 | Harga member |
| `harga_jual_rekan` | decimal(12,2) | No | 0 | Harga rekan |
| `harga_jual_motoris` | decimal(12,2) | No | 0 | Harga motoris |
| `stok` | integer | No | 0 | Stok saat ini |
| `stok_minimum` | integer | No | 5 | Threshold low-stock |
| `gambar` | varchar(255) | Yes | NULL | Path gambar produk |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Indexes:**
- `barcode` (single-column index untuk barcode scanner)
- UNIQUE (`toko_id`, `barcode`) — barcode unik per toko

**Foreign Keys:**
- `toko_id` → `toko(id)` ON DELETE CASCADE
- `kategori_id` → `kategori_produk(id)` ON DELETE RESTRICT

---

### 9. `pembelian` (Purchase Order Header)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `supplier_id` | bigint UNSIGNED | No | - | FK → `supplier.id` |
| `user_id` | bigint UNSIGNED | No | - | FK → `users.id` (pembuat faktur) |
| `nomor_faktur` | varchar(100) | No | - | Nomor faktur dari supplier |
| `total_pembelian` | decimal(12,2) | No | - | Total nilai pembelian |
| `metode_pembayaran` | enum | No | - | Tunai/Kredit |
| `status_pembayaran` | enum | No | - | Lunas/Hutang |
| `tanggal_beli` | date | No | - | Tanggal transaksi pembelian |
| `jatuh_tempo` | date | Yes | NULL | Untuk metode Kredit |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

---

### 10. `pembelian_detail` (Purchase Order Line Items)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `pembelian_id` | bigint UNSIGNED | No | - | FK → `pembelian.id` CASCADE |
| `produk_id` | bigint UNSIGNED | No | - | FK → `produk.id` |
| `harga_beli_satuan` | decimal(12,2) | No | - | Harga beli per unit |
| `qty` | integer | No | - | Jumlah beli |
| `subtotal` | decimal(12,2) | No | - | `harga_beli_satuan × qty` |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

---

### 11. `penjualan` (Sales Transaction Header)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `pelanggan_id` | bigint UNSIGNED | Yes | NULL | FK → `pelanggan.id` (nullable, walk-in) |
| `user_id` | bigint UNSIGNED | No | - | FK → `users.id` (kasir) |
| `nomor_invoice` | varchar(50) | No | - | GLOBALLY UNIQUE, format: INV/YYYYMMDD/XXXX |
| `total_harga` | decimal(12,2) | No | - | Total sebelum diskon |
| `diskon` | decimal(12,2) | No | 0 | Diskon header |
| `total_bayar` | decimal(12,2) | No | - | Total setelah diskon |
| `nominal_uang` | decimal(12,2) | No | 0 | Uang yang diterima |
| `kembalian` | decimal(12,2) | No | 0 | Kembalian |
| `metode_pembayaran` | enum | No | - | Tunai/Kredit/Digital Payment |
| `status_pembayaran` | enum | No | - | Lunas/Belum Lunas |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Global Scope**: `WHERE penjualan.toko_id = auth()->user()->toko_id`

---

### 12. `penjualan_detail` (Sales Line Items)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `penjualan_id` | bigint UNSIGNED | No | - | FK → `penjualan.id` CASCADE |
| `produk_id` | bigint UNSIGNED | No | - | FK → `produk.id` (NO CASCADE — historis) |
| `harga_satuan` | decimal(12,2) | No | - | Harga aktual saat transaksi (snapshot) |
| `qty` | integer | No | - | Jumlah terjual |
| `subtotal` | decimal(12,2) | No | - | `harga_satuan × qty` |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

> ⚠️ **PENTING**: `harga_satuan` adalah **snapshot historis** harga saat transaksi terjadi. Kolom ini TIDAK boleh di-link langsung ke `produk.harga_jual_*` yang bisa berubah di masa depan.

---

### 13. `arus_kas` (Cash Flow Ledger)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `user_id` | bigint UNSIGNED | No | - | FK → `users.id` |
| `tipe` | enum | No | - | `Masuk` / `Keluar` |
| `kategori` | varchar(50) | No | - | e.g., Penjualan, Pembelian Stok |
| `nominal` | decimal(12,2) | No | - | Selalu positif |
| `keterangan` | varchar(255) | Yes | NULL | Memo/deskripsi |
| `tanggal` | date | No | - | Tanggal transaksi kas |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**Kategori Umum:**
- `Masuk`: `Penjualan`
- `Keluar`: `Pembelian Stok`, `Biaya Operasional`

---

### 14. `log_stok` (Stock Audit Trail – Append Only)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | - | PK |
| `toko_id` | bigint UNSIGNED | No | - | FK → `toko.id` CASCADE |
| `produk_id` | bigint UNSIGNED | No | - | FK → `produk.id` |
| `tipe_perubahan` | enum | No | - | Lihat di bawah |
| `jumlah` | integer | No | - | Qty berubah (+/−) |
| `stok_awal` | integer | No | - | Snapshot stok sebelum |
| `stok_akhir` | integer | No | - | Snapshot stok sesudah |
| `keterangan` | varchar(255) | Yes | NULL | Referensi (no invoice, dll) |
| `created_at` | timestamp | Yes | NULL | - |
| `updated_at` | timestamp | Yes | NULL | - |

**`tipe_perubahan` Enum Values:**
- `Masuk_Barang` — dari Pembelian
- `Penjualan` — dari transaksi POS
- `Retur` — retur barang
- `Penyesuaian_Stok` — Stock Opname manual

---

## Migration Order (Penting!)

| Urutan | File Migration | Tabel yang Dibuat |
|---|---|---|
| 1 | `000001_create_toko_table` | `toko` |
| 2 | `000002_create_roles_table` | `roles` |
| 3 | `000003_create_users_table` | `users` |
| 4 | `000004_create_pelanggan_table` | `pelanggan` |
| 5 | `000005_create_supplier_table` | `supplier` |
| 6 | `000006_create_kelompok_produk_table` | `kelompok_produk` |
| 7 | `000007_create_kategori_produk_table` | `kategori_produk` |
| 8 | `000008_create_produk_table` | `produk` |
| 9 | `000009_create_pembelian_tables` | `pembelian`, `pembelian_detail` |
| 10 | `000010_create_penjualan_tables` | `penjualan`, `penjualan_detail` |
| 11 | `000011_create_finance_and_logs_tables` | `arus_kas`, `log_stok` |
| 12 | `add_gambar_to_produk_table` | ALTER: tambah kolom `gambar` |
| 13 | `add_subscription_to_toko_table` | ALTER: tambah `status_langganan`, `berakhir_pada` |

---

## Cascade Delete Rules

| Jika `toko` dihapus... | Semua data tenant terhapus (CASCADE) |
|---|---|
| Jika `kategori_produk` dihapus | Produk yang terkait DIBLOKIR (RESTRICT) |
| Jika `penjualan` dihapus | `penjualan_detail` ikut terhapus (CASCADE) |
| Jika `pembelian` dihapus | `pembelian_detail` ikut terhapus (CASCADE) |
| Jika `produk` dihapus | `penjualan_detail` DIPERTAHANKAN (referensi historis) |
