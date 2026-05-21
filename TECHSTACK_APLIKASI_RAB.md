# Tech Stack — Aplikasi Rancangan Anggaran Biaya (RAB)

## PT Sertifikasi Bermutu Ketenagalistrikan

---

# 1. Core System (Backend & Frontend)

| Komponen | Teknologi | Versi | Keterangan |
|---|---|---|---|
| Framework Backend | Laravel | 12.x | Framework utama untuk pengembangan aplikasi berbasis web |
| Bahasa Backend | PHP | 8.3+ | Bahasa pemrograman backend aplikasi |
| Frontend UI | Laravel Blade | — | Template engine untuk tampilan aplikasi |
| Styling Framework | Tailwind CSS | 4.x | Framework CSS utility-first |
| JavaScript | Vanilla JavaScript | ES6+ | Dynamic form & interaksi halaman |
| Build Tool | Vite | 6.x | Asset bundler dan hot reload |
| Authentication | Laravel Auth | Built-in | Sistem login dan autentikasi pengguna |
| Routing | Laravel Route | Built-in | Routing aplikasi |
| ORM | Eloquent ORM | Built-in | Relasi database dan query builder |
| Queue System | Laravel Queue | Built-in | Proses background task |
| File Upload | Laravel Storage | Built-in | Penyimpanan file bukti pembayaran |
| Middleware | Laravel Middleware | Built-in | Pembatasan akses berdasarkan role |

---

# 2. Database & Storage

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Relational Database | MySQL | Penyimpanan data utama aplikasi |
| Database Engine | InnoDB | Mendukung foreign key dan transaction |
| Charset | utf8mb4_unicode_ci | Mendukung karakter Unicode |
| Local Storage | Laravel Storage | Menyimpan file bukti pembayaran |
| File Storage Path | storage/app/public | Penyimpanan dokumen upload |

Database digunakan untuk menyimpan:
- data pengguna;
- data RAB;
- rincian pengeluaran;
- approval;
- pembayaran;
- arus kas;
- laporan;
- audit trail.

---

# 3. Sistem Dynamic Expense Table

Salah satu fitur utama aplikasi adalah tabel rincian dinamis berdasarkan jenis pengeluaran.

## Jenis Pengeluaran

| Jenis Pengeluaran | Struktur Tabel |
|---|---|
| Biaya Operasional | Tabel kebutuhan operasional |
| Petty Cash | Tabel pengeluaran kecil |
| Biaya Gaji | Tabel data pegawai dan rekening |
| Biaya Bulanan | Tabel pembayaran rutin |

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| JavaScript | Mengubah form secara dinamis |
| Blade Partial | Memisahkan template tabel |
| Laravel Validation | Validasi field sesuai jenis pengeluaran |

Alur kerja:

```text
Admin memilih jenis pengeluaran
        ↓
JavaScript membaca pilihan
        ↓
Sistem menampilkan tabel yang sesuai
        ↓
Admin mengisi rincian pengeluaran
        ↓
Sistem menghitung subtotal otomatis
```

---

# 4. Approval Workflow System

Sistem approval digunakan untuk proses persetujuan bertingkat.

## Approval Flow

```text
Admin Keuangan
        ↓
Manajer Keuangan
        ↓
Direktur
        ↓
Pembayaran
        ↓
Selesai
```

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| Laravel Enum | Mengatur status RAB |
| Laravel Middleware | Membatasi approval berdasarkan role |
| Eloquent Relationship | Relasi approval dan user |
| Laravel Event | Trigger perubahan status |

## Status Sistem

| Status | Keterangan |
|---|---|
| Draft | RAB masih disimpan sementara |
| Diajukan | Menunggu approval manajer |
| Disetujui Manajer | Menunggu approval direktur |
| Disetujui Direktur | Approval akhir selesai |
| Disetujui | Siap pembayaran |
| Ditolak | RAB perlu revisi |
| Selesai | Pembayaran telah dilakukan |

---

# 5. Cash Flow Management System

Modul arus kas digunakan untuk mencatat:
- dana masuk;
- dana keluar;
- saldo akhir;
- transaksi pembayaran RAB.

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| MySQL Transaction | Menjaga konsistensi data |
| Laravel Service Layer | Mengelola business logic arus kas |
| Eloquent ORM | Relasi transaksi dan pembayaran |

## Perhitungan Saldo

```text
Saldo Akhir =
Saldo Awal + Total Dana Masuk - Total Dana Keluar
```

---

# 6. Reporting & PDF Export

Modul laporan digunakan untuk menghasilkan:
- laporan bulanan;
- laporan arus kas;
- detail RAB;
- rekap pembayaran.

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| barryvdh/laravel-dompdf | Export PDF |
| Laravel Blade | Template laporan |
| Tailwind CSS | Styling tampilan laporan |
| Laravel Query Builder | Filter data laporan |

## Format Export

| Format | Keterangan |
|---|---|
| PDF | Dokumen resmi laporan |
| Excel *(opsional)* | Rekap data |

## Ketentuan Laporan

Hanya RAB berstatus:

```text
Selesai
```

yang masuk ke laporan final.

---

# 7. Role & Permission Management

Aplikasi menggunakan sistem pembagian hak akses berdasarkan role pengguna.

## Role Pengguna

| Role | Fungsi |
|---|---|
| Admin Keuangan | Membuat dan mengelola RAB |
| Manajer Keuangan | Approval tahap pertama |
| Direktur | Approval akhir |

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| Laravel Middleware | Membatasi akses halaman |
| Spatie Laravel Permission *(opsional)* | Role & permission management |
| Session Authentication | Menyimpan login pengguna |

---

# 8. Dashboard Monitoring System

Dashboard digunakan untuk monitoring kondisi pengajuan dan realisasi anggaran.

## Informasi Dashboard

- total RAB diajukan;
- total RAB dibayarkan;
- waiting approval;
- total realisasi;
- grafik anggaran dan realisasi;
- daftar status terbaru.

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| Chart.js | Menampilkan grafik |
| Laravel Query Builder | Mengambil data statistik |
| Blade Component | Widget dashboard |

---

# 9. Security & Validation

Sistem menggunakan validasi dan keamanan untuk mencegah kesalahan data.

## Validasi Sistem

| Validasi | Teknologi |
|---|---|
| Form validation | Laravel Form Request |
| Role validation | Middleware |
| File validation | Laravel File Validation |
| Numeric validation | PHP Validation |

## Keamanan Sistem

| Keamanan | Teknologi |
|---|---|
| Password hashing | bcrypt |
| CSRF protection | Laravel CSRF |
| Session security | Laravel Session |
| Route protection | Auth Middleware |

---

# 10. Development Environment

| Tool | Versi |
|---|---|
| PHP | 8.3+ |
| Laravel | 12.x |
| Composer | 2.x |
| Node.js | 22+ |
| NPM | 10+ |
| MySQL | 8.x |
| Laragon | Latest |
| Visual Studio Code | Latest |
| Git | Latest |

---

# 11. Struktur Arsitektur Sistem

## Backend Architecture

```text
Route
 ↓
Controller
 ↓
Form Request Validation
 ↓
Service Layer
 ↓
Repository Layer
 ↓
Model
 ↓
Database
```

## Frontend Flow

```text
User memilih jenis pengeluaran
        ↓
JavaScript menampilkan tabel sesuai jenis
        ↓
User mengisi rincian
        ↓
Sistem menghitung subtotal otomatis
        ↓
Data dikirim ke backend Laravel
```

---

# 12. Package Tambahan Laravel

| Package | Fungsi |
|---|---|
| barryvdh/laravel-dompdf | Export PDF |
| spatie/laravel-permission | Role dan permission |
| maatwebsite/excel *(opsional)* | Export Excel |
| laravel/ui atau breeze | Authentication |
| intervention/image *(opsional)* | Pengolahan gambar upload |

---

# 13. Konsep Teknologi yang Digunakan

Aplikasi RAB menggunakan konsep:

- CRUD berbasis web;
- dynamic form system;
- role-based access control;
- approval workflow;
- cash flow monitoring;
- audit trail;
- reporting system;
- layered architecture.

---

# 14. Kelebihan Tech Stack yang Digunakan

## Laravel

- mudah dikembangkan;
- mendukung MVC;
- memiliki sistem authentication bawaan;
- mendukung queue, middleware, dan validation;
- cocok untuk aplikasi bisnis.

## Tailwind CSS

- mempercepat pembuatan UI;
- tampilan lebih modern;
- mudah dikustomisasi.

## MySQL

- stabil untuk transaksi;
- mendukung relasi kompleks;
- cocok untuk aplikasi enterprise sederhana.

## JavaScript Dynamic Form

- form lebih interaktif;
- tabel berubah otomatis sesuai jenis pengeluaran;
- meningkatkan user experience.

---

# 15. Ringkasan Tech Stack

```text
Frontend
- Blade
- Tailwind CSS
- JavaScript

Backend
- Laravel 12
- PHP 8.3

Database
- MySQL

Features
- Dynamic Expense Table
- Approval Workflow
- Cash Flow
- PDF Export
- Role Management
- Dashboard Monitoring
```

---

# Kesimpulan

Tech stack aplikasi Rancangan Anggaran Biaya (RAB) menggunakan Laravel sebagai framework utama backend, MySQL sebagai database, Blade dan Tailwind CSS sebagai frontend, serta JavaScript untuk fitur dynamic table berdasarkan jenis pengeluaran. Kombinasi teknologi ini dipilih karena mampu mendukung proses pengajuan, approval bertingkat, pembayaran, arus kas, dan laporan secara terstruktur, aman, dan mudah dikembangkan. Struktur teknologi yang digunakan juga sesuai untuk implementasi sistem informasi berbasis web pada lingkungan perusahaan.
