# Panduan Logika Approval RAB & Strategi Sidang Tugas Akhir
## PT Sertifikasi Bermutu Ketenagalistrikan (PT SBK)

Dokumen ini menjelaskan rancangan logika alur kerja **Opsi A (Sistem Antrean Bersama / Pool System)** yang digunakan dalam Aplikasi Rancangan Anggaran Biaya (RAB) Anda. Dokumen ini dirancang sebagai panduan teknis sekaligus **"cheat sheet" (contekan cerdas)** bagi Anda saat menghadapi pertanyaan kritis dari dosen pembimbing atau dosen penguji saat sidang.

---

## 1. Arsitektur Alur Logika Persetujuan (Opsi A)

Pada **Opsi A (Pool System)**, proses approval bersifat dinamis dan mandiri. Semua manajer operasional memiliki wewenang yang sejajar untuk memproses antrean persetujuan.

```text
  [ Admin Keuangan ]
         │ (1. Mengajukan RAB)
         ▼
   [ Status: DIAJUKAN ] ◄────► (Admin masih bisa Edit & Hapus sebelum ditindaklanjuti atasan)
         │
         ├─────────────────────────────────────────┐
         ▼ (Bisa diakses oleh)                     ▼ (Bisa diakses oleh)
  [ Dashboard Manajer 1 ]                   [ Dashboard Manajer 2 ]
         │                                         │
         ├─► (2a. Klik Setujui Lebih Dulu) ◄───────┤ (2b. Klik Setujui Terlambat)
         ▼                                         ▼
[ Lock RAB & Ubah Status ]               [ Ditolak Validasi State ]
         │ (Status: DISETUJUI_MANAJER)             (Mendapat Pesan Info/Peringatan)
         ▼
  [ Dashboard Direktur ]
         │ (3. Persetujuan Akhir)
         ▼
   [ Status: DISETUJUI ]
         │ (4. Bayar & Upload Bukti)
         ▼
   [ Status: SELESAI ]
```

### Karakteristik Utama Sistem:
1. **Admins (Admin 1, Admin 2, dst.):** Membuat draf RAB. Admin memiliki wewenang penuh untuk mengedit status `DRAFT`, `DITOLAK`, dan `DIAJUKAN` serta menghapus status `DRAFT` dan `DIAJUKAN` (selama belum disetujui/ditolak oleh Manajer/Direktur). RAB disimpan dengan pencatatan `user_id` pembuat untuk akuntabilitas kepemilikan dokumen.
2. **Managers (Manajer 1, Manajer 2, dst.):** Semua RAB berstatus `diajukan` dikumpulkan di satu dashboard antrean yang sama. Manajer pertama yang memproses (klik Approve/Reject) akan mengunci data tersebut.
3. **Directors (Direktur):** Menerima limpahan RAB yang sudah berstatus `disetujui_manajer` untuk diberikan keputusan akhir.

---

## 2. Pemetaan Integritas Data & Akuntabilitas (Skema Database)

Meskipun sistem bersifat antrean bersama (*pool*), **sistem Anda tetap memenuhi asas transparansi audit dan akuntabilitas keuangan yang ketat.**

| Tabel | Kolom Kunci | Fungsi untuk Akuntabilitas & Keamanan |
| :--- | :--- | :--- |
| **`rabs`** | `user_id`<br>`status`<br>`approved_by_manager_at`<br>`approved_by_director_at` | Mengetahui siapa Admin pembuatnya, kapan disetujui Manajer, dan kapan disetujui Direktur. |
| **`rab_approvals`** | `user_id`<br>`role`<br>`status`<br>`notes` | **Kunci Utama:** Mencatat secara spesifik `user_id` Manajer/Direktur yang memproses approval beserta catatan keputusannya. |
| **`audit_logs`** | `user_id`<br>`action`<br>`old_values`<br>`new_values` | Mencatat riwayat setiap aktivitas perubahan state status, alamat IP, dan waktu terjadinya perubahan secara permanen. |

---

## 3. Keamanan Teknis Terhadap Konflik Data (Race Conditions)

Dalam sistem multi-user, ada risiko di mana **Manajer 1** dan **Manajer 2** menekan tombol *"Ya, Setujui"* di detik yang sama. Pada kode Anda di `app/Http/Controllers/ApprovalRabController.php`, hal ini telah diantisipasi secara elegan melalui **State-Check** dan **Database Transactions**:

```php
// 1. Validasi Lapis Pertama: State Check
if ($rab->status === RabStatus::DISETUJUI_MANAJER || $rab->status === RabStatus::DISETUJUI) {
    return back()->with('info', 'RAB ini sudah disetujui sebelumnya.');
}

if ($rab->status !== RabStatus::DIAJUKAN) {
    return back()->with('error', 'RAB ini tidak dalam status yang dapat disetujui.');
}
```

### Cara Kerja Proteksi Sistem:
* Ketika **Manajer 1** mengklik tombol, database membuka transaksi (`DB::beginTransaction()`), mengunci baris data (`select for update` secara implisit), mengubah status menjadi `DISETUJUI_MANAJER`, menulis ke tabel `rab_approvals`, lalu menyimpannya (`DB::commit()`).
* Ketika request **Manajer 2** masuk (bahkan selisih milidetik), status RAB sudah berubah. Validasi state akan membaca status terbaru tersebut, membatalkan transaksi baru, dan memberikan respon penolakan yang aman.

---

## 4. Panduan Menghadapi Sanggahan & Pertanyaan Dosen (Q&A)

Berikut adalah daftar pertanyaan tersulit yang biasa diajukan dosen beserta **jawaban akademis-profesional** yang bisa Anda gunakan:

### 💬 Pertanyaan 1: "Sistem antrean bersama ini bisa bikin manajer salah klik menyetujui anggaran yang bukan bagiannya. Bagaimana Anda menanganinya?"
> **💡 Jawaban Anda:**
> *"Benar sekali, Pak/Bu. Risiko salah klik pada sistem pool kami antisipasi dengan **dua cara**:*
> * *Pertama, sistem menyediakan informasi visual yang sangat jelas di tabel antrean, seperti **Nomor RAB**, **Jenis Pengeluaran (Biaya Operasional, Petty Cash, dll.)**, dan **Keterangan Kegiatan**, sehingga Manajer dapat menyaring secara visual sebelum memproses.*
> * *Kedua, arsitektur kode kami dirancang secara **modular**. Jika di masa depan perusahaan berkembang dan menerapkan SOP yang sangat ketat per divisi, kami hanya perlu menambahkan query filter berdasarkan role-divisi pada method `listForApprover()` di `RabController` tanpa perlu merombak struktur database yang ada."*

### 💬 Pertanyaan 2: "Kalau sistemnya rebutan, bagaimana Anda menjamin akuntabilitas jika suatu saat terjadi manipulasi dana oleh Manajer tertentu?"
> **💡 Jawaban Anda:**
> *"Sistem kami memegang prinsip **Strict Accountability** (Akuntabilitas Mutlak). Setiap kali tombol 'Setujui' atau 'Tolak' ditekan, sistem tidak hanya mengubah status RAB, tetapi juga menyisipkan baris baru ke tabel `rab_approvals` yang mengikat `user_id` dari manajer yang sedang login saat itu.*
> *Selain itu, ada tabel `audit_logs` yang merekam jejak aktivitas, waktu, dan IP address. Jadi, tidak ada ruang bagi pengguna untuk saling menyangkal (*Non-Repudiation*), karena bukti digital tercatat secara absolut di database."*

### 💬 Pertanyaan 3: "Bagaimana jika Manajer 1 menyetujui (Approve) tetapi Manajer 2 menolak (Reject) di saat yang sama?"
> **💡 Jawaban Anda:**
> *"Di tingkat basis data, transaksi bersifat **Atomic (ACID)**. Request yang pertama kali selesai diproses akan langsung mengubah status RAB (misal menjadi `DITOLAK` atau `DISETUJUI_MANAJER`).*
> *Request kedua yang masuk beberapa milidetik setelahnya akan langsung digagalkan oleh validasi kondisi status di tingkat controller, sehingga tidak akan pernah terjadi konflik di mana satu dokumen memiliki dua status keputusan yang bertolak belakang."*

### 💬 Pertanyaan 4: "Mengapa Anda memilih Opsi A (Pool System) dibanding Opsi B (Penunjukan Langsung oleh Admin)?"
> **💡 Jawaban Anda:**
> *"Opsi A dipilih untuk menyelaraskan dengan **Efisiensi Operasional** PT SBK saat ini. Dengan struktur organisasi yang ramping, sistem antrean bersama mencegah terjadinya *bottleneck* (kemacetan proses) apabila salah satu manajer sedang berhalangan hadir atau cuti, karena manajer lain dapat mem-back-up pekerjaan tersebut secara cepat demi kelancaran operasional perusahaan."*

---

## 5. Kesimpulan Presentasi Anda

Dengan menerapkan **Opsi A** yang didukung oleh **Database Transactions** dan **State-Machine Validation**, aplikasi RAB Anda telah memenuhi standar pengembangan perangkat lunak yang **Aman (Secure)**, **Akuntabel (Audit-Ready)**, dan **Skalabel (Scalable)**. 

Dosen Anda pasti akan terkesan karena Anda tidak hanya membangun kode, tetapi juga memikirkan aspek **Tata Kelola Bisnis (IT Governance)** dan **Integritas Data** dengan matang!
