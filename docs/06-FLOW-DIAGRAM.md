# 06 – Flow Diagram

Diagram alur proses bisnis utama dalam sistem.

---

## 1. Alur Login & Autentikasi

```
┌─────────────────────────────────────────────────────────────────┐
│  Browser                                                        │
└────────────────────────────┬────────────────────────────────────┘
                             │ GET /login
                             ▼
                    ┌──────────────────┐
                    │  Halaman Login   │
                    └────────┬─────────┘
                             │ POST /login (username + password)
                             ▼
                    ┌──────────────────────────┐
                    │  Auth::attempt()         │
                    └────────┬─────────────────┘
                  ┌──────────┴──────────┐
                GAGAL                BERHASIL
                  │                    │
                  ▼                    ▼
         ┌──────────────┐    ┌─────────────────────┐
         │ redirect back│    │ Cek is_active ?      │
         │ + error msg  │    └────────┬─────────────┘
         └──────────────┘         NONAKTIF    AKTIF
                                     │           │
                                     ▼           ▼
                              ┌──────────┐  ┌──────────────────┐
                              │  Logout  │  │ Cek Subscription │
                              │ + error  │  └────────┬─────────┘
                              └──────────┘      EXPIRED    AKTIF
                                                   │           │
                                                   ▼           ▼
                                           ┌──────────┐ ┌──────────┐
                                           │/langganan │ │/dashboard│
                                           └──────────┘ └──────────┘
```

---

## 2. Alur Pendaftaran Toko Baru

```
GET /register
    │
    ▼
Form: nama_toko, nama_lengkap, username, password, password_confirmation
    │
    │ POST /register
    ▼
┌─────────────────────────────────────────┐
│  Validasi:                              │
│  - username UNIQUE di tabel users       │
│  - password confirmed (min 6 char)      │
└────────────────────┬────────────────────┘
                     │ VALID
                     ▼
┌─────────────────────────────────────────┐
│  1. Toko::create({nama_toko, alamat...})│
│  2. Role::where('Owner')->first()       │
│  3. User::create({toko_id, role_id...}) │
│  4. Auth::login($user)                  │
└────────────────────┬────────────────────┘
                     │
                     ▼
              redirect /dashboard
```

---

## 3. Alur Transaksi POS Custom (Quick-Entry)

```
┌──────────────────────────────────────────────────────────────────────┐
│  Page Load: /pos/custom                                              │
│  → autofocus ke input "Nama/Kode Pelanggan"                          │
└───────────────────────────────┬──────────────────────────────────────┘
                                │
              ┌─────────────────▼─────────────────┐
              │  Input Pelanggan (live-search)     │
              │  AJAX GET /api/pelanggan/search    │
              └───────────────┬───────────────────┘
                   ┌──────────┴──────────┐
               ENTER tanpa        Pilih dari
               ketik apa-apa      dropdown
                   │                   │
                   ▼                   ▼
         ┌──────────────┐    ┌──────────────────────┐
         │status='Umum' │    │ status = dari data DB │
         └──────┬───────┘    └──────────┬────────────┘
                └────────────┬──────────┘
                             │ STATE TERKUNCI
                             │ pelangganState = {id, nama, status}
                             │
                             ▼
              ┌─────────────────────────────────────┐
              │  Input Produk (live-search, min 2)  │
              │  AJAX GET /api/produk/search        │
              │  → tampil dropdown dengan harga     │
              │    berdasarkan pelangganState.status │
              └──────────────┬──────────────────────┘
                             │ Arrow + Enter
                             ▼
              ┌─────────────────────────────────────┐
              │  Produk terpilih                    │
              │  Harga Tier otomatis ambil dari     │
              │  pelangganState.status              │
              │  Badge: [Motoris Mode: Rp 8.500]    │
              └──────────────┬──────────────────────┘
                             │ fokus ke input Qty
                             ▼
              ┌─────────────────────────────────────┐
              │  Input Qty (default=1, ter-blok)    │
              │  Real-time validasi: qty > stok?    │
              │  ┌──────────────────────────────┐  │
              │  │ YA: background merah, warning │  │
              │  │     teks, Enter disabled      │  │
              │  │ TIDAK: normal, Enter enabled  │  │
              │  └──────────────────────────────┘  │
              └──────────────┬──────────────────────┘
                             │ Enter (qty valid)
                             ▼
              ┌─────────────────────────────────────┐
              │  Item ditambah ke keranjang (JS)    │
              │  Reset: fokus kembali ke input      │
              │          Pencarian Produk           │
              └──────────────┬──────────────────────┘
                             │ (ulangi per produk)
                             │
                             │ F9 / Klik Bayar
                             ▼
              ┌─────────────────────────────────────┐
              │  Modal Pembayaran                   │
              │  ┌─────────────────────────────┐   │
              │  │ Tunai: input nominal, hitung │   │
              │  │        kembalian. Disabled   │   │
              │  │        jika nominal < total  │   │
              │  │ Kredit: langsung proses      │   │
              │  │ Digital: input no. referensi │   │
              │  └─────────────────────────────┘   │
              └──────────────┬──────────────────────┘
                             │ POST /penjualan/store (JSON)
                             ▼
              ┌─────────────────────────────────────┐
              │  Backend PenjualanController@store  │
              │  ┌─────────────────────────────┐   │
              │  │ 1. Validate payload         │   │
              │  │ 2. Resolve customer status  │   │
              │  │ 3. DB::beginTransaction     │   │
              │  │ 4. Harga dari DB (bukan UI) │   │
              │  │ 5. lockForUpdate per produk │   │
              │  │ 6. INSERT penjualan         │   │
              │  │ 7. INSERT penjualan_detail  │   │
              │  │ 8. UPDATE produk.stok       │   │
              │  │ 9. INSERT log_stok          │   │
              │  │ 10. INSERT arus_kas (Tunai) │   │
              │  │ 11. DB::commit()            │   │
              │  └─────────────────────────────┘   │
              │  → JSON {success, invoice, kembalian}│
              └──────────────┬──────────────────────┘
                             │ 
                             ▼
              ┌─────────────────────────────────────┐
              │  UI: Tampilkan sukses + link struk   │
              │  Reset keranjang → siap transaksi    │
              │  berikutnya                         │
              └─────────────────────────────────────┘
```

---

## 4. Alur Pembelian Barang (Restock)

```
GET /pembelian/create
    │ (supplier dropdown + produk array)
    ▼
Form Faktur Pembelian:
 - Pilih Supplier
 - Input Nomor Faktur
 - Pilih Tanggal Beli
 - Tambah Item (Produk + Qty + Harga Beli)
 - Pilih Metode: Tunai / Kredit
    │
    │ POST /pembelian/store (JSON)
    ▼
┌─────────────────────────────────────────────────┐
│  PembelianController@store                      │
│                                                 │
│  DB::beginTransaction()                         │
│                                                 │
│  Per Item:                                      │
│  ┌─────────────────────────────────────────┐   │
│  │ 1. Produk::lockForUpdate()->find(id)   │   │
│  │ 2. INSERT pembelian_detail             │   │
│  │ 3. UPDATE produk.stok += qty           │   │
│  │ 4. UPDATE produk.harga_modal = beli    │   │
│  │    (Last Cost Strategy)               │   │
│  │ 5. INSERT log_stok (Masuk_Barang)     │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  INSERT arus_kas (Keluar, Pembelian Stok)       │
│  DB::commit()                                   │
└──────────────────────────┬──────────────────────┘
                           │
                           ▼
              JSON {success: true, message}
              UI: redirect ke list pembelian
```

---

## 5. Alur Subscription Check

```
Request masuk ke route yang dilindungi subscription
    │
    ▼
CheckSubscription@handle
    │
    ├── User adalah Super Admin?
    │       └── YA → lewat (bypass)
    │
    └── BUKAN Super Admin:
            │
            ├── Ambil $toko = user->toko
            │
            ├── $isExpired = toko->berakhir_pada && berakhir_pada->isPast()
            ├── $isStatusBlocked = status IN ['Kedaluwarsa', 'Nonaktif']
            │
            ├── Expired DAN status masih Aktif?
            │       └── YA → UPDATE status_langganan = 'Kedaluwarsa'
            │
            ├── $isExpired OR $isStatusBlocked?
            │       └── YA → redirect /langganan/expired
            │
            └── TIDAK → $next($request) (lanjutkan)
```

---

## 6. Alur Stock Opname

```
GET /inventory/opname
    │
    ▼
Tampilkan daftar produk + stok sistem saat ini
    │
User input stok fisik aktual per produk
    │
    │ POST /inventory/opname
    ▼
Per produk yang diubah:
    ├── Hitung selisih: delta = stok_fisik - stok_sistem
    ├── UPDATE produk.stok = stok_fisik
    └── INSERT log_stok (tipe: Penyesuaian_Stok, jumlah: delta)
```

---

## 7. Alur CMS Kelola Langganan Toko (Super Admin)

```
GET /cms/toko
    │ (Hanya Super Admin, dikecualikan dari subscription check)
    ▼
Tabel semua toko:
 - nama_toko
 - status_langganan
 - berakhir_pada
 - jumlah users
 - Tombol "Edit Langganan"
    │
    │ PUT /cms/toko/{id}/subscription
    │ { status_langganan, berakhir_pada }
    ▼
Toko::findOrFail(id)
    ->update({status_langganan, berakhir_pada})
    │
    ▼
redirect back() + flash success
```
