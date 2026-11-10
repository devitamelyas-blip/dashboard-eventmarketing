# Project Devon — Event Marketing & Evaluation Management Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=flat-square&logo=inertia)](https://inertiajs.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v4-06B6D4?style=flat-square&logo=tailwindcss)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![Pest Tests](https://img.shields.io/badge/Tests-Pest%20PHP%20(117%20Passed)-brightgreen?style=flat-square&logo=pest)](https://pestphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](LICENSE)

An enterprise event marketing management and automated post-event evaluation platform designed for multi-branch organizations. The platform streamlines event proposals, attendee feedback collection via secure QR-code surveys, automated KPI evaluation engines (Sales ROI, Brand Awareness, and Relationship), and executive decision workflows with complete branch-level data isolation.

---

## 🌟 Key Features

### 1. Multi-Stage Event Lifecycle
- **Stage 1 (Pre-Event Proposal)**: PIC cabang mengajukan rencana kegiatan, anggaran biaya tim, estimasi proposal, target leads, dan kuota peserta.
- **Stage 2 (Post-Event Realization)**: Pelaporan realisasi biaya aktual, kehadiran peserta, serta perolehan prospek tervalidasi setelah acara selesai.
- **Stage 3 (Sales Reconciliation H+90)**: Khusus event dengan sasaran *Sales*, sistem memfasilitasi rekonsiliasi omzet penjualan aktual hingga 90 hari pasca-event disertai unggah dokumen bukti (invoice/PO).
- **Stage 4 (Executive Verification & Decision)**: Verifikasi kelayakan oleh tim Marketing dan pencatatan keputusan direksi berbasis otorisasi khusus.

### 2. Automated Evaluation Engine (Pure Service Architecture)
Sistem dilengkapi mesin analitik independen (`app/Services/Evaluation/`) tanpa *side-effect* untuk mengevaluasi parameter keberhasilan secara objektif:
- **Sales Evaluation**: Kalkulasi rasio omzet terhadap biaya (standar kelayakan minimum $\ge 10.0\times$), pemantauan *countdown* jatuh tempo H+90, dan deteksi toleransi batas.
- **Brand Awareness Evaluation**: Pengukuran *unaided brand recall* ($\ge 60\%$), *average understanding uplift* ($\ge 0.50$ poin), dan relevansi target audiens ($\ge 70\%$) berbasis instrumen survei tervalidasi.
- **Relationship Evaluation**: Pengukuran kepuasan kemitraan ($\ge 4.0/5.0$) dan relevansi profil peserta ($\ge 75\%$).
- **Cost & Budget Variance Analysis**: Audit deviasi anggaran tim (*over-budget* vs *hemat/sesuai*).

### 3. Public Survey Gateway & Token Security
- Pengumpulan respon peserta via rute publik bertoken acak ramah privasi (`/survei/{token}`) tanpa mengekspos ID internal database.
- Generator tautan dan QR Code otomatis untuk materi cetak dan *standing banner* di lokasi acara.
- Dukungan login survei fleksibel via Google OAuth (Socialite) atau identitas langsung.

### 4. Role-Based Access Control (RBAC) & Multi-Branch Isolation
Didukung oleh `spatie/laravel-permission` dengan 4 tingkatan akses:
- **Administrator**: Kontrol penuh sistem, manajemen pengguna, konfigurasi master cabang, dan *audit activity logs*.
- **Direksi**: Akses dashboard eksekutif lintas cabang, review metrik evaluasi komprehensif, dan penginputan keputusan resmi.
- **Marketing (Pusat)**: Verifikasi kepatuhan data event, pengelolaan instrumen survei, generator QR code, serta agregasi laporan nasional.
- **PIC (Cabang)**: Akses terisolasi khusus event cabang masing-masing (*branch data isolation policy*) untuk pendaftaran dan pelaporan kegiatan.

### 5. Enterprise Data Hardening & Privacy Compliance
- **PII Anonymization (`devon:anonymize-respondents`)**: Artisan command terjadwal untuk menyamarkan data identitas sensitif responden lawas sesuai prinsip retensi privasi tanpa merusak integritas KPI historis.
- **H+90 Reminder Automation (`devon:remind-h90`)**: Pengingat otomatis via email kepada PIC ketika batas waktu rekonsiliasi omzet telah jatuh tempo.
- **Audit Trail**: Pencatatan histori aktivitas perubahan status dan verifikasi menggunakan `spatie/laravel-activitylog`.
- **Security Headers**: Dilengkapi middleware pengamanan HTTP (*X-Frame-Options*, *X-Content-Type-Options*, *Referrer-Policy*).

---

## 🛠 Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.3 / 8.4) |
| **Frontend Architecture** | Inertia.js 2.x, Vue 3 (Composition API & `<script setup>`) |
| **Styling & UI** | Tailwind CSS v4 |
| **Database** | PostgreSQL 16 (Staging / Production), MySQL 8.x (Local Dev) |
| **Authentication & RBAC** | Laravel Breeze, Spatie Laravel Permission |
| **Audit Logging** | Spatie Laravel Activitylog |
| **QR Code Engine** | SimpleSoftwareIO / BaconQrCode |
| **Testing Suite** | Pest PHP 5 (117 Feature & Unit Tests, 500+ Assertions) |
| **Containerization** | Docker & Docker Compose (PHP-FPM, Nginx, PostgreSQL, Mailpit) |

---

## 🚀 Panduan Instalasi & Menjalankan

### Cara 1: Menjalankan di Lingkungan Lokal (Native)

1. **Clone repository**:
   ```bash
   git clone <repository-url>
   cd dashboard-eventmarketing
   ```

2. **Salin file konfigurasi environment**:
   ```bash
   cp .env.example .env
   ```

3. **Install dependensi backend**:
   ```bash
   composer install
   ```

4. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

5. **Sesuaikan koneksi database di `.env`**:
   Contoh untuk MySQL lokal:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=event_marketing
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan migrasi database dan seeder data awal**:
   ```bash
   php artisan migrate --seed
   ```

7. **Install dependensi frontend dan build aset**:
   ```bash
   npm install
   npm run build
   # Untuk development dengan hot-module-reload:
   # npm run dev
   ```

8. **Jalankan server aplikasi**:
   ```bash
   php artisan serve
   ```
   Aplikasi siap diakses di: `http://localhost:8000`

---

### Cara 2: Menjalankan Menggunakan Docker Compose

1. **Persiapkan environment**:
   ```bash
   cp .env.example .env
   ```

2. **Jalankan container (PHP-FPM, Nginx, PostgreSQL, Mailpit)**:
   ```bash
   docker compose up -d --build
   ```

3. **Inisialisasi aplikasi di dalam container**:
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   docker compose exec app npm run build
   ```

4. **Layanan yang berjalan**:
   - Web Application: `http://localhost:8080`
   - PostgreSQL Database: `localhost:5432`
   - Mailpit UI (Email Testing Inbox): `http://localhost:8025`

---

## 👥 Akun Default Seeder

Setelah menjalankan `php artisan migrate --seed`, akun demo berikut tersedia untuk pengujian:

| Role | Email | Password | Cakupan Akses |
|---|---|---|---|
| **Admin** | `admin@kencana.id` | `password` | Seluruh Modul & Manajemen User |
| **Direksi** | `direksi@kencana.id` | `password` | Dashboard Eksekutif & Keputusan |
| **Marketing** | `marketing@kencana.id` | `password` | Verifikasi, QR Code & Survei |
| **PIC Surabaya** | `pic.surabaya@kencana.id` | `password` | Input Event Cabang Surabaya |
| **PIC Jakarta** | `pic.jakarta@kencana.id` | `password` | Input Event Cabang Jakarta |
| **PIC Bandung** | `pic.bandung@kencana.id` | `password` | Input Event Cabang Bandung |

---

## 🧪 Pengujian & Kualitas Kode

Platform ini mengadopsi prinsip *test-driven reliability* dengan cakupan komprehensif pada kalkulasi bisnis evaluasi, isolasi hak akses cabang, serta flow survei publik.

Jalankan pengujian menggunakan Pest PHP:
```bash
php artisan test
```

Hasil uji otomatis:
```text
  PASS  Tests\Unit\Evaluation\AwarenessEvaluatorTest
  PASS  Tests\Unit\Evaluation\CostEvaluatorTest
  PASS  Tests\Unit\Evaluation\EventEvaluatorTest
  PASS  Tests\Unit\Evaluation\RelationshipEvaluatorTest
  PASS  Tests\Unit\Evaluation\SalesEvaluatorTest
  PASS  Tests\Feature\Auth\AuthenticationTest
  PASS  Tests\Feature\Events\EventWorkflowTest
  PASS  Tests\Feature\Survey\PublicSurveyTest
  PASS  Tests\Feature\Console\HardeningAndConsoleCommandsTest
  PASS  Tests\Feature\Verification\VerificationAndDecisionTest

  Tests:    117 passed (512 assertions)
  Duration: ~8.0s
```

---

## 📂 Struktur Arsitektur Direktori

```text
app/
├── Console/Commands/        # CLI automation (H+90 reminder, PII anonymization, data migration)
├── Enums/                   # Business domain enums (EventType, Goal, VerificationStatus, etc.)
├── Http/
│   ├── Controllers/         # REST & Inertia page controllers
│   ├── Middleware/          # Security headers, route protection
│   └── Requests/            # Form validation requests
├── Models/                  # Eloquent models (Event, EventReport, Respondent, SurveyResponse)
├── Policies/                # Authorization policies (Branch isolation & role checks)
└── Services/
    └── Evaluation/          # Pure computational evaluation engines (Zero side-effects)
resources/js/
├── Components/              # Reusable Vue components
├── Layouts/                 # App and Authenticated layouts
└── Pages/                   # Inertia Vue views (Dashboard, Events, Survey, Admin)
docs/
└── ASSUMPTIONS.md           # Domain specification & business rules
```

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi [MIT](LICENSE).
