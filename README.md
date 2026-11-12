# Event Marketing: Platform Evaluasi & Manajemen Event

> **Laravel 12 · Vue 3 · Inertia.js · Tailwind CSS v4 · PostgreSQL · Pest · Docker**

Platform web multi-cabang untuk mengelola siklus event pemasaran dan mengevaluasi hasilnya secara otomatis dan objektif.

---

## Ringkasan

Sistem internal untuk PT Kencana Maju Bersama yang menggantikan evaluasi event berbasis spreadsheet. Platform ini mencakup pengajuan event, survei peserta lewat QR code, perhitungan KPI otomatis, verifikasi tim marketing, dan keputusan direksi, dengan isolasi data per cabang.

---

## Masalah

Evaluasi event sebelumnya memakai 8 sheet terpisah dan formulir yang tersebar. Akibatnya:

- **Rumus finansial mudah terhapus atau bergeser.**
- **Tiap cabang bisa melihat dan mengubah data cabang lain.**
- **Rekonsiliasi omzet H+90 sering terlambat dan tanpa bukti yang sah.**
- **Data pribadi responden survei terekspos ke pihak yang hanya butuh angka agregat.**

---

## Solusi

Saya merancang dan membangun aplikasi monolith modern dari nol dengan empat pilar:

- **Siklus event 4 tahap**: proposal pra-event, realisasi pasca-event, rekonsiliasi omzet H+90 dengan unggah bukti invoice, lalu verifikasi dan keputusan direksi.
- **Mesin evaluasi berupa pure service**: logika Sales ROI, Brand Awareness, Relationship, dan Cost Variance dibuat deterministik tanpa side-effect dan aman dari pembagian nol, sehingga bisa diuji terisolasi.
- **Isolasi data cabang**: `spatie/laravel-permission` dan Laravel Policies dengan 4 peran (Admin, Direksi, Marketing, PIC). PIC hanya melihat event cabangnya sendiri.
- **Survei publik bertoken**: token acak 40 karakter (`/survei/{token}`) terhubung ke QR code event. Data responden disimpan di tabel terpisah dan disamarkan berkala lewat scheduled command.

---

## Fitur Utama

- **Dashboard eksekutif lintas cabang** dengan grafik visual analisis performa anggaran rencana vs realisasi biaya cabang
- **Penilaian KPI otomatis**: rasio omzet ≥ 10×, brand recall ≥ 60%, relevansi audiens ≥ 70%, kepuasan mitra ≥ 4,0/5
- **In-App Notification Center**: ikon lonceng notifikasi real-time di navbar untuk pengajuan proposal, laporan selesai, status verifikasi, dan keputusan direksi
- **Manajemen Dokumen Pendukung Terisolasi**: unggah dan unduh dokumen proposal PDF, presensi kehadiran, foto dokumentasi, dan bukti invoice omzet pada penyimpanan privat
- **Pengingat email H+90 otomatis** (Laravel Scheduler)
- **Audit trail lengkap dengan penampil log** (Spatie Activitylog)
- **Ekspor CSV kompatibel Excel (UTF-8 BOM)**: rekap event, ekspor respon survei mentah untuk riset pasar, dan halaman cetak A4 landscape satu halaman untuk rapat direksi
- **Security headers dan manajemen master data cabang**

---

## Hasil

- **Kalkulasi evaluasi otomatis**, menghilangkan penilaian subjektif dan risiko rumus rusak.
- **Rekonsiliasi lebih cepat**: PIC mendapat pengingat tepat waktu dan mengunggah invoice langsung dari browser.
- **Tata kelola transparan**: setiap verifikasi dan keputusan tercatat di log audit.
- **Kualitas terjaga**: 142 tes Pest dengan 607 assertion (100% lulus).

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | Laravel 12, PHP 8.3+ |
| **Frontend** | Vue 3 (Composition API), Inertia.js 2, Tailwind CSS v4 |
| **Database** | PostgreSQL 16 (produksi), MySQL 8 (lokal) |
| **Auth & RBAC** | Laravel Breeze, Spatie Permission |
| **Audit** | Spatie Activitylog |
| **QR** | BaconQrCode |
| **Testing** | Pest PHP |
| **Infra** | Docker Compose (PHP-FPM, Nginx, PostgreSQL, Mailpit) |

---

## Peran Saya

**Full-stack developer**: analisis kebutuhan bisnis, perancangan arsitektur, implementasi backend dan frontend, penulisan tes, dan containerization.

---

<details>
<summary><strong>🛠 Panduan Menjalankan & Akun Demo Lokal</strong></summary>

### Menjalankan Server Lokal
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve --port=8088
```
Akses aplikasi di: `http://localhost:8088`

### Akun Demo Default (Password: `password`)
- **Admin**: `admin@kencana.id`
- **Direksi**: `direksi@kencana.id`
- **Marketing**: `marketing@kencana.id`
- **PIC Surabaya**: `pic.surabaya@kencana.id`
- **PIC Jakarta**: `pic.jakarta@kencana.id`
- **PIC Bandung**: `pic.bandung@kencana.id`

### Menjalankan Pengujian
```bash
php artisan test
```
</details>
