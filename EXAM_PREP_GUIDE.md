# 📚 PANDUAN PERSIAPAN UJIAN APLIKASI RAB

**Tanggal:** 2026-06-10  
**Status:** Ready for Exam Preparation  
**Tujuan:** Membantu Anda memahami dan mendemonstrasikan aplikasi RAB dengan percaya diri

---

## 🎯 QUICK START: 3 Langkah Paling Penting

### 1️⃣ **Pahami Alur Bisnis (5 menit)**
```
Admin Buat RAB → Admin Submit → Manajer Review → Direktur Review → Admin Bayar → Selesai
   DRAFT         DIAJUKAN    DISETUJUI_MNJ  DISETUJUI      SELESAI
```

### 2️⃣ **Pahami 5 Jenis Pengeluaran**
- 🏢 **Operasional** - Biaya kerja sehari-hari  
- 💵 **Petty Cash** - Pengeluaran kecil cepat
- 👥 **Gaji** - Pembayaran karyawan
- 📅 **Bulanan** - Biaya rutin (listrik, internet, sewa)
- 🏦 **PNBP** - Pembayaran Negara Bukan Pajak

### 3️⃣ **Pahami 3 Role Pengguna**
| Role | Bisa Apa | Tidak Bisa |
|------|---------|-----------|
| **Admin Keuangan** | Buat RAB, submit, bayar | Approve |
| **Manajer Keuangan** | Lihat RAB, approve/reject, kelola cash flow | Buat RAB |
| **Direktur** | Lihat RAB, final approval, manage user | Buat RAB, bayar |

---

## 🎬 DEMO SCRIPT: Skenario Ujian

### **Skenario 1: Membuat dan Submit RAB (Admin)**
**Time: 5-7 menit**

```
LANGKAH:
1. Login sebagai Admin Keuangan
2. Dashboard → Lihat statistik
3. Sidebar → "Buat RAB Baru"
4. Isi form:
   - Tanggal Pengajuan: [hari ini]
   - Periode: [pilih bulan/tahun]
   - Jenis Pengeluaran: Operasional
   - Deskripsi: "Pembelian alat kantor"
5. Tambah Item Operasional:
   - Item: Meja Kayu
   - Volume: 5
   - Satuan: Buah
   - Harga/Unit: 500000
   - Total otomatis: 2.500.000
6. Klik "Simpan RAB"
7. Klik "Submit ke Manajer" 
   → RAB berubah status DIAJUKAN

✅ YANG DITUNJUKKAN:
- Form dinamis berubah saat ganti jenis pengeluaran
- Kalkulasi otomatis
- Validasi form bekerja
- Status tracking
```

### **Skenario 2: Approval oleh Manajer**
**Time: 3-5 menit**

```
LANGKAH:
1. Logout → Login sebagai Manajer Keuangan
2. Dashboard → Lihat card "Perlu Review Manajer"
3. Klik card → List RAB dengan status "Diajukan"
4. Klik detail RAB
   → Modal membuka dengan:
   - Info pembuat
   - Rincian item (tabel lengkap)
   - Info pembayaran (jika ada)
   - Catatan diskusi
5. Klik "Setujui" atau "Tolak"
   - Jika tolak: isi alasan penolakan
   - Jika setujui: langsung selesai
6. RAB berubah status

✅ YANG DITUNJUKKAN:
- Multi-stage approval
- Discussion panel berfungsi
- Status tracking real-time
- Permission control (Manajer hanya approve)
```

### **Skenario 3: Final Approval oleh Direktur**
**Time: 2-3 menit**

```
LANGKAH:
1. Logout → Login sebagai Direktur
2. Dashboard → Lihat card "Perlu Review Direktur"
3. Klik list RAB status "Disetujui Manajer"
4. Lihat detail & setujui
   → RAB berubah ke "DISETUJUI"

✅ YANG DITUNJUKKAN:
- Hierarchical approval
- Direktur hanya lihat, jangan bisa edit
```

### **Skenario 4: Upload Bukti Pembayaran (Admin)**
**Time: 3-5 menit**

```
LANGKAH:
1. Login Admin → Dashboard
2. Sidebar → Cari RAB dengan status "DISETUJUI"
3. Klik "Upload Bukti Pembayaran"
   - Tanggal Bayar: [hari ini]
   - Nominal: [jumlah dari RAB]
   - Metode: [Transfer/Tunai]
   - Bukti Pembayaran: [Upload gambar/PDF]
4. Submit
   → RAB berubah status "SELESAI"
   → Bukti bisa dilihat modal viewer

✅ YANG DITUNJUKKAN:
- File upload handling
- Modal file viewer (PDF/Gambar)
- Download functionality
- Status completion
```

### **Skenario 5: Export Laporan (Manajer)**
**Time: 2-3 menit**

```
LANGKAH:
1. Login Manajer → Menu "Laporan"
2. Filter:
   - Periode: Pilih bulan/tahun
   - Jenis Pengeluaran: Pilih salah satu
   - Status: Pilih "SELESAI"
3. Klik "Export PDF"
   → Download laporan dengan:
   - Tabel summary
   - Rincian per RAB
   - Chart visualisasi
   - Tanda tangan placeholder

✅ YANG DITUNJUKKAN:
- Report generation
- PDF export
- Data filtering & aggregation
```

---

## 💡 KEY CONCEPTS DIJELASKAN SIMPLE

### **Konsep 1: Pool System (Race Condition Prevention)**
**Masalah:** 2 orang buat RAB bersamaan, nomor RAB bentrok?

**Solusi:** Database punya tabel `setting` dengan counter, di-lock saat diakses.
```php
// Pseudocode:
LOCK tabel setting
currentNumber = setting.rab_counter
newNumber = currentNumber + 1
UPDATE setting SET rab_counter = newNumber
UNLOCK

// Hasilnya: Nomor RAB unik & terurut (RAB-001, RAB-002, RAB-003)
```

### **Konsep 2: Soft Delete**
**Apa:** Data tidak dihapus, hanya "disembunyikan" (deleted_at di-set)

**Kenapa:** Audit trail & recovery jika salah hapus

**Contoh:**
```sql
DELETE FROM users WHERE id = 5; -- Hard delete (pergi permanen)
UPDATE users SET deleted_at = NOW() WHERE id = 5; -- Soft delete (masih ada)
SELECT * FROM users WHERE deleted_at IS NULL; -- Query normal users
SELECT * FROM users->withTrashed(); -- Termasuk deleted
```

### **Konsep 3: Dynamic Expense Tables**
**Masalah:** Setiap jenis pengeluaran struktur beda, pake 1 tabel = banyak null?

**Solusi:** 5 tabel berbeda:
- `operational_expense_items` (nama_item, volume, satuan, harga)
- `petty_cash_items` (keterangan, jumlah)
- `salary_expense_items` (nama_karyawan, gaji)
- etc.

**Keuntungan:**
- ✅ No wasted columns
- ✅ Sesuai data structure masing-jenis
- ✅ Lebih efisien penyimpanan

### **Konsep 4: WhatsApp Notification**
**Flow:**
```
RAB di-submit 
  ↓
Trigger → Laravel Event
  ↓
Event Listener → Kirim HTTP ke Fonnte API
  ↓
Fonnte kirim WhatsApp ke Manajer
```

**Pesan contoh:**
```
Halo, ada RAB baru untuk direview:
📄 RAB-001 | Operasional | Rp 2.500.000
Pembuat: Admin Keuangan
Buka: [link aplikasi]
```

### **Konsep 5: Immutable Records**
**Prinsip:** Setelah approval → data tidak boleh diubah

**Implementasi:** 
- Form edit disable setelah status > DRAFT
- Database validation di controller
- Audit log track semua perubahan

---

## 🔍 TROUBLESHOOTING: Masalah Umum

### ❌ **RAB tidak muncul di list**
**Solusi:**
- Cek status filter (default ada status tertentu)
- Klik "Reset" filter
- Refresh halaman

### ❌ **Tombol Setujui/Tolak tidak ada**
**Solusi:**
- Pastikan login dengan role Manajer/Direktur
- Cek status RAB (hanya bisa approve status tertentu)

### ❌ **File bukti pembayaran error upload**
**Solusi:**
- Cek ukuran file < 5MB
- Format: JPG, PNG, PDF
- Check disk storage (`storage/app/public/`)

### ❌ **WhatsApp notifikasi tidak masuk**
**Solusi:**
- Cek API key Fonnte di `.env`
- Cek nomor WA di user profile
- Check server logs: `storage/logs/laravel.log`

### ❌ **PDF export error**
**Solusi:**
- Cek DomPDF installed (`vendor/barryvdh/laravel-dompdf`)
- Cek font tersedia
- Check temp directory permission

---

## 📋 CHECKLIST SEBELUM UJIAN

- [ ] **Install & Setup**
  - [ ] Laravel 11 berjalan
  - [ ] Database migrate sempurna
  - [ ] Seeder jalan (php artisan db:seed)
  - [ ] Storage link buat public files

- [ ] **Test Akun**
  - [ ] Login Admin Keuangan ✅
  - [ ] Login Manajer Keuangan ✅
  - [ ] Login Direktur ✅
  - [ ] Reset password berfungsi ✅

- [ ] **Database Populated**
  - [ ] expense_types ada 5 ✅
  - [ ] 3 users dengan role berbeda ✅
  - [ ] Sample RAB sudah ada (atau siap bikin) ✅

- [ ] **Features Test**
  - [ ] Dashboard chart load ✅
  - [ ] Create RAB form dinamis ✅
  - [ ] Submit RAB work ✅
  - [ ] Approval flow work ✅
  - [ ] Payment upload work ✅
  - [ ] PDF export work ✅
  - [ ] WhatsApp notification work ✅ (optional)

- [ ] **UI/UX Polish**
  - [ ] Halaman responsive (desktop + mobile) ✅
  - [ ] Modals muncul dengan smooth ✅
  - [ ] Validasi form jelas ✅
  - [ ] Loading state visible ✅

---

## 🎤 CARA JAWAB PERTANYAAN UJIAN

### **Q: Jelaskan alur bisnis RAB?**
**A:** 
> "RAB dimulai dari Admin membuat request di dashboard. Setelah diisi detail pengeluaran, Admin submit. Manajer kemudian review dan bisa approve atau reject. Jika approve, lanjut ke Direktur untuk final approval. Setelah Direktur setuju, Admin upload bukti pembayaran dan RAB selesai. Setiap tahap ada notifikasi WhatsApp."

### **Q: Kenapa pakai 5 tabel item terpisah?**
**A:** 
> "Karena setiap jenis pengeluaran (operasional, gaji, petty cash, etc.) punya struktur field yang berbeda. Operasional butuh: item, volume, satuan, harga. Sementara Gaji cukup: nama karyawan, nominal. Jika pake 1 tabel, banyak kolom null yang sia-sia. Ini disebut normalisasi database."

### **Q: Bagaimana prevent RAB number duplicate?**
**A:** 
> "Kami pakai Pool System - tabel `setting` jadi counter terpusat. Saat admin create RAB baru, database lock tabel itu, ambil counter, increment, unlock. Hasilnya nomor RAB selalu unik dan terurut. Ini solve race condition saat multi-user buat RAB bersamaan."

### **Q: Jelaskan fitur approval workflow?**
**A:** 
> "Ada 3 stage approval. Admin buat → Manajer review (bisa approve/reject) → Direktur final approval. Di setiap stage ada discussion panel untuk kolaborasi catatan. Semua activity di-track di table `rab_approvals`. RAB tidak bisa bayar kecuali sudah 'DISETUJUI'."

### **Q: User role & permission?**
**A:** 
> "3 role: Admin Keuangan punya full akses create/submit/pay. Manajer read-only untuk approve workflow dan manage cash flow. Direktur juga read-only, cuma bisa final approval dan manage user. Semua di-control via middleware `CheckRole` di route."

---

## 📚 REFERENSI FILE PENTING

| File | Tujuan |
|------|--------|
| [COMPREHENSIVE_OVERVIEW.md](COMPREHENSIVE_OVERVIEW.md) | Detail lengkap arsitektur |
| `app/Models/Rab.php` | Model utama RAB |
| `app/Http/Controllers/RabController.php` | Create, submit, show |
| `app/Http/Controllers/ApprovalRabController.php` | Approve, reject logic |
| `routes/web.php` | Semua route & permission |
| `database/migrations/` | Struktur database |
| `resources/views/rab/` | UI setiap halaman RAB |
| `.env` | Config (API key, database, etc) |

---

## 🚀 TIPS SAAT DEMO UJIAN

1. **Jangan terburu-buru** - Jelaskan sambil klik dengan pelan
2. **Tunjukkan database** - Open phpMyAdmin, tunjukkan tabel struktur
3. **Tunjukkan code** - Buka VS Code, tunjukkan controller/model penting
4. **Test semua scenario** - Pastikan sampai "SELESAI" status
5. **Siapkan data sample** - Jangan create RAB dari 0 (boros waktu)
6. **Backup answers** - Catat poin-poin penting di kertas
7. **Jangan panik** - Kalo ada error, troubleshoot sambil jelasin proses

---

## 📞 CRITICAL PHONE NUMBERS & CONTACTS

**Untuk Demo Ujian:**
- Admin Keuangan: `admin@rab.test` / password: `password`
- Manajer Keuangan: `manajer@rab.test` / password: `password`  
- Direktur: `direktur@rab.test` / password: `password`

**Untuk Troubleshooting:**
- Logs: `storage/logs/laravel.log`
- Database: phpMyAdmin `http://localhost/phpmyadmin`
- Server: `php artisan serve` (localhost:8000)

---

## 🎓 GOOD LUCK! 🍀

**Ingat:** Anda sudah punya aplikasi yang solid dengan fitur kompleks. Percaya diri aja waktu presentasi! Fokus jelaskan:
1. ✅ Business logic yang clear
2. ✅ Workflow yang terstruktur  
3. ✅ Code yang rapi & modular
4. ✅ Database yang normalized
5. ✅ User experience yang smooth

**Target:** Tunjukkan dari user perspective (demo), terus jelaskan dari developer perspective (code & database).

Semangat! 💪
