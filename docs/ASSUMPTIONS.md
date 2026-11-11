# Sistem Evaluasi Event Marketing — Spesifikasi Teknis & Aturan Bisnis

Dokumen ini mendokumentasikan keputusan arsitektural, spesifikasi teknis, dan aturan bisnis yang diterapkan pada platform evaluasi event marketing PT Kencana Maju Bersama.

---

## 1. Kode Event & Penomoran
- PIC cabang menginput kode event pada form pengajuan (dinormalisasi otomatis oleh sistem: *uppercase* & *trimmed*).
- Kode event bersifat unik (*unique constraint*) di tingkat database. Apabila kode telah terdaftar sebelumnya, validasi form akan menolak pengajuan dengan pesan kesalahan spesifik.
- Format kode mendukung prefix identifikasi lingkungan/lingkup pengujian (misal: `DEMO-`, `UAT-`, atau format operasional cabang).

## 2. Portofolio & Kategori Event
- Sistem mendeteksi portofolio event secara otomatis berdasarkan prefix kode event:
  - Dimulai dengan `DEMO-` $\rightarrow$ `EventPortfolio::DEMO`
  - Dimulai dengan `UAT-` $\rightarrow$ `EventPortfolio::UAT`
  - Pola lainnya $\rightarrow$ `EventPortfolio::OPERATIONAL`
- Event dengan portofolio Demo atau UAT diproteksi agar tidak diverifikasi sebagai data operasional nyata (pencegahan logic di tingkat service & controller verifikasi).

## 3. Jenis Event (*Type*) & Sasaran Utama (*Goal*)
- **Jenis Event (Type)**:
  - `gathering_internal` ("Gathering / Internal Event")
  - `join_external` ("Join Event / External Event")
- **Sasaran Utama (Goal)** (tepat satu per event):
  - `sales` — Target rasio omzet penjualan terhadap biaya
  - `awareness` — Target brand recall & kenaikan pemahaman produk
  - `relationship` — Target skor kepuasan kemitraan & relevansi audiens

## 4. Otorisasi & Hak Keputusan Direksi
- Hak input keputusan direksi dibatasi secara ketat menggunakan Role-Based Access Control (RBAC) melalui permission `record-director-decision` (Spatie Permission), yang secara default dialokasikan untuk role `direksi` dan `admin`.
- Nilai keputusan direksi disimpan terpisah dari kalkulasi mesin evaluasi otomatis guna menjaga independensi data antara analitik sistem dan pertimbangan pimpinan.

## 5. Nilai Numerik & Integritas Finansial
- Seluruh kolom finansial (`proposal_cost`, `team_budget`, `actual_team_cost`, `revenue`) disimpan dengan presisi `decimal(15,2)`.
- Nilai `NULL` merepresentasikan data yang belum dilaporkan, dan dibedakan secara tegas dari nilai `0` (nol rupiah).
- Penanganan *division by zero* pada mesin kalkulasi evaluasi selalu mengembalikan nilai aman (`null`) untuk mencegah *runtime error* pada metrik rasio biaya dan omzet.

## 6. Database & Konfigurasi Lingkungan
- Sistem mendukung arsitektur multi-database (PostgreSQL 16 untuk container Docker / production, serta MySQL 8.x untuk lingkungan development lokal).
- Seluruh kredensial dan konfigurasi koneksi dikelola melalui file environment (`.env`) berbasis template `.env.example`.

## 7. Instrumen Survei & Kepatuhan Privasi Responden
- Setiap event memiliki token survei acak yang unik (`survey_token`), diakses publik melalui rute `/survei/{survey_token}` tanpa mengekspos ID internal database.
- Instrumen survei (`RECALL_V2`) menempatkan pertanyaan ingatan merek spontan (*unaided recall*) sebelum pertanyaan terarah (*aided recall*) guna menjaga validitas data penelitian pasar.
- Data pribadi responden (nama, instansi, nomor telepon) disimpan terisolasi pada tabel `respondents`, dan dilindungi oleh otorisasi khusus tim marketing/admin.
- Modul dashboard eksekutif dan ekspor laporan hanya menyajikan metrik teragregasi tanpa menampilkan identitas individual responden.
