# 📂 Dokumentasi Proyek – Retail ERP By Arul

Folder ini berisi dokumentasi teknis lengkap untuk proyek **Retail ERP By Arul**. Dibuat untuk memudahkan pengembang manusia maupun AI agent memahami konteks proyek secara cepat dan menyeluruh.

---

## 📑 Daftar Dokumen

| File | Isi |
|---|---|
| [`01-PROJECT-OVERVIEW.md`](./01-PROJECT-OVERVIEW.md) | Gambaran besar proyek, tech stack, dan filosofi desain |
| [`02-PRD.md`](./02-PRD.md) | Product Requirements Document (PRD) lengkap dan terstruktur |
| [`03-DATABASE-SCHEMA.md`](./03-DATABASE-SCHEMA.md) | Skema database lengkap, semua tabel, kolom, FK, dan index |
| [`04-ARCHITECTURE.md`](./04-ARCHITECTURE.md) | Arsitektur aplikasi: Multi-Tenancy, RBAC, Global Scope, alur data |
| [`05-MODULES.md`](./05-MODULES.md) | Panduan detail setiap modul: Controller, Model, View, Route |
| [`06-FLOW-DIAGRAM.md`](./06-FLOW-DIAGRAM.md) | Diagram alur proses bisnis (POS, Pembelian, Auth, dll) |
| [`07-API-REFERENCE.md`](./07-API-REFERENCE.md) | Referensi semua endpoint route: URL, method, parameter, akses |
| [`08-CONVENTIONS.md`](./08-CONVENTIONS.md) | Konvensi koding, naming, aturan wajib untuk agent AI |
| [`09-DEVELOPMENT-GUIDE.md`](./09-DEVELOPMENT-GUIDE.md) | Panduan setup lokal, seeder, troubleshooting, cara kerja deploy |

---

## 🚀 Cara Membaca Dokumen Ini (Untuk AI Agent)

1. Mulai dari **`01-PROJECT-OVERVIEW.md`** untuk memahami konteks bisnis dan teknologi.
2. Baca **`04-ARCHITECTURE.md`** untuk memahami pola Multi-Tenancy dan RBAC — **WAJIB** sebelum menyentuh kode apapun.
3. Gunakan **`03-DATABASE-SCHEMA.md`** sebagai referensi saat menulis query atau migration.
4. Baca **`08-CONVENTIONS.md`** sebelum menulis kode baru — berisi aturan wajib yang harus diikuti.
5. Gunakan **`05-MODULES.md`** dan **`07-API-REFERENCE.md`** saat mengerjakan fitur spesifik.
