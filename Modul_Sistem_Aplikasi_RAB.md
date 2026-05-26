# Modul Sistem — Aplikasi Rancangan Anggaran Biaya (RAB)

## PT Sertifikasi Bermutu Ketenagalistrikan

---

## Alur Kerja Operasional Sistem

Aplikasi Rancangan Anggaran Biaya (RAB) berbasis website dirancang untuk membantu proses pengajuan, persetujuan, realisasi pembayaran, pencatatan arus kas, dan pembuatan laporan RAB secara digital. Sistem ini digunakan untuk menggantikan proses manual yang sebelumnya dilakukan menggunakan spreadsheet, pencatatan terpisah, serta komunikasi langsung antarbagian.

Aplikasi ini memiliki tiga aktor utama, yaitu **Admin Keuangan**, **Manajer Operasional**, dan **Direktur**. Admin Keuangan bertugas membuat dan mengelola pengajuan RAB. Manajer Operasional bertugas melakukan pemeriksaan dan persetujuan tahap pertama. Direktur bertugas memberikan persetujuan akhir sebelum RAB dapat direalisasikan pembayarannya.

Secara umum, alur kerja sistem adalah sebagai berikut:

```text
┌────────────────────┐
│  Admin Keuangan    │
│  Membuat RAB       │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│  Sistem            │
│  Menyimpan Draft   │
│  atau Mengajukan   │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ Manajer Operasional│
│  Review RAB        │
│  Setujui / Tolak   │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│  Direktur          │
│  Persetujuan Akhir │
│  Setujui / Tolak   │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│  Admin Keuangan    │
│  Upload Bukti Bayar│
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│  Sistem            │
│  Update Arus Kas   │
│  Status Selesai    │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│  Export Laporan    │
│  PDF / Rekap Bulan │
└────────────────────┘
```

---

## 1. Modul Keamanan dan Manajemen Akses

Modul keamanan dan manajemen akses merupakan modul yang mengatur proses masuk pengguna ke dalam sistem serta membatasi hak akses berdasarkan role masing-masing pengguna.

### 1.1 Login Sistem

Setiap pengguna wajib melakukan login menggunakan email dan password yang telah terdaftar. Setelah login berhasil, sistem akan membaca role pengguna dan menampilkan menu sesuai dengan hak aksesnya.

Alur login:

```text
Pengguna membuka halaman login
        ↓
Pengguna memasukkan email dan password
        ↓
Sistem memvalidasi data login
        ↓
Jika valid, pengguna diarahkan ke dashboard sesuai role
        ↓
Jika tidak valid, sistem menampilkan pesan kesalahan
```

### 1.2 Role Pengguna

Sistem memiliki tiga role utama, yaitu:

| No | Role | Fungsi Utama |
|---|---|---|
| 1 | Admin Keuangan | Membuat RAB, mengajukan RAB, mengelola pembayaran, arus kas, dan laporan |
| 2 | Manajer Operasional | Melakukan pemeriksaan dan approval tahap pertama |
| 3 | Direktur | Melakukan approval akhir terhadap RAB yang telah disetujui manajer |

### 1.3 Hak Akses Admin Keuangan

Admin Keuangan memiliki hak akses sebagai berikut:

- membuka dashboard admin;
- membuat RAB baru;
- menyimpan RAB sebagai draft;
- mengedit RAB berstatus draft, ditolak (revisi), atau diajukan (sebelum ditinjau oleh atasan);
- menghapus RAB berstatus draft atau diajukan (sebelum ditinjau oleh atasan);
- mengajukan RAB ke Manajer Operasional;
- melihat detail RAB;
- melihat status pengajuan RAB;
- mengunggah bukti pembayaran (maksimal 100KB, format Gambar/PDF);
- mencatat arus kas;
- melihat riwayat RAB;
- melakukan export laporan;
- mengelola data pengguna apabila diberi akses tambahan.

### 1.4 Hak Akses Manajer Operasional

Manajer Operasional memiliki hak akses sebagai berikut:

- membuka dashboard manajer;
- melihat daftar RAB yang menunggu persetujuan;
- melihat detail pengajuan RAB;
- memeriksa rincian item RAB;
- menyetujui RAB;
- menolak RAB;
- memberikan catatan revisi;
- melihat riwayat approval.

### 1.5 Hak Akses Direktur

Direktur memiliki hak akses sebagai berikut:

- membuka dashboard direktur;
- melihat daftar RAB yang telah disetujui manajer;
- melihat detail RAB;
- memberikan persetujuan akhir;
- menolak RAB;
- memberikan catatan keputusan;
- melihat ringkasan laporan pengajuan.

---

## 2. Modul Dashboard

Modul dashboard berfungsi untuk menampilkan ringkasan data pengajuan dan realisasi RAB secara cepat. Dashboard membantu pengguna mengetahui kondisi terbaru tanpa harus membuka data satu per satu.

### 2.1 Dashboard Admin Keuangan

Dashboard Admin Keuangan menampilkan informasi:

- total RAB diajukan;
- total RAB dibayarkan;
- jumlah RAB menunggu approval;
- jumlah RAB ditolak atau revisi;
- total nilai pengajuan;
- total realisasi pembayaran;
- grafik perbandingan anggaran dan realisasi;
- daftar status RAB terbaru;
- peringatan apabila terdapat data yang perlu diperiksa.

Dashboard ini digunakan oleh Admin Keuangan untuk memantau seluruh proses RAB dari tahap pembuatan sampai laporan.

### 2.2 Dashboard Manajer Operasional

Dashboard Manajer Operasional berfokus pada proses pemeriksaan dan approval.

Informasi yang ditampilkan:

- jumlah RAB menunggu approval manajer;
- jumlah RAB yang telah disetujui;
- jumlah RAB yang ditolak;
- total nilai RAB yang diajukan;
- daftar RAB terbaru yang perlu ditinjau.

### 2.3 Dashboard Direktur

Dashboard Direktur menampilkan ringkasan RAB yang membutuhkan persetujuan akhir.

Informasi yang ditampilkan:

- jumlah RAB menunggu persetujuan akhir;
- jumlah RAB yang telah disetujui;
- jumlah RAB yang ditolak;
- total nilai pengajuan;
- daftar RAB yang telah lolos review manajer.

---

## 3. Modul Manajemen RAB

Modul Manajemen RAB merupakan modul utama dalam aplikasi. Modul ini digunakan oleh Admin Keuangan untuk membuat, menyimpan, mengedit, dan mengajukan RAB.

### 3.1 Alur Pembuatan RAB

```text
Admin Keuangan login
        ↓
Masuk ke menu Manajemen RAB
        ↓
Klik tombol Buat RAB
        ↓
Sistem menampilkan form pembuatan RAB
        ↓
Admin mengisi data umum RAB
        ↓
Admin memilih jenis pengeluaran
        ↓
Sistem menampilkan tabel rincian sesuai jenis pengeluaran
        ↓
Admin mengisi rincian item
        ↓
Sistem menghitung subtotal dan total otomatis
        ↓
Admin memilih Simpan Draft atau Ajukan RAB
```

### 3.2 Data Umum RAB

Data umum RAB yang diisi oleh Admin Keuangan meliputi:

- nomor RAB;
- tanggal pengajuan;
- nama pengaju;
- jenis pengeluaran;
- periode;
- keterangan;
- total pengajuan;
- status RAB.

Nomor RAB dapat dibuat secara otomatis oleh sistem agar tidak terjadi duplikasi dokumen.

Contoh format nomor RAB:

```text
001/RAB/SBK/II/2026
```

### 3.3 Status RAB

Status digunakan untuk menunjukkan posisi RAB dalam alur kerja sistem.

| No | Status | Keterangan |
|---|---|---|
| 1 | Draft | RAB masih disimpan sementara oleh Admin Keuangan |
| 2 | Diajukan | RAB telah dikirim ke Manajer Operasional |
| 3 | Disetujui Manajer | RAB telah disetujui oleh Manajer Operasional |
| 4 | Disetujui Direktur | RAB telah disetujui oleh Direktur |
| 5 | Disetujui | RAB siap diproses pembayaran |
| 6 | Ditolak | RAB ditolak oleh Manajer atau Direktur |
| 7 | Selesai | RAB telah dibayarkan dan bukti pembayaran telah diunggah |

Alur status utama:

```text
Draft → Diajukan → Disetujui Manajer → Disetujui Direktur → Disetujui → Selesai
```

Alur apabila ditolak:

```text
Diajukan → Ditolak → Revisi oleh Admin → Diajukan kembali
```

---

## 4. Modul Jenis Pengeluaran dan Tabel Dinamis

Modul ini merupakan salah satu bagian penting dalam aplikasi RAB. Sistem tidak menggunakan satu tabel rincian yang sama untuk semua jenis pengeluaran. Setiap jenis pengeluaran memiliki struktur tabel yang berbeda sesuai kebutuhan data transaksi.

Jenis pengeluaran dalam sistem terdiri dari empat jenis, yaitu:

| No | Jenis Pengeluaran |
|---|---|
| 1 | Biaya Operasional |
| 2 | Petty Cash |
| 3 | Biaya Gaji |
| 4 | Biaya Bulanan |

Alur kerja pemilihan jenis pengeluaran:

```text
Admin memilih jenis pengeluaran
        ↓
Sistem membaca jenis pengeluaran yang dipilih
        ↓
Sistem menampilkan form rincian sesuai jenis pengeluaran
        ↓
Admin mengisi data pada tabel yang sesuai
        ↓
Sistem menghitung subtotal dan total
        ↓
Data disimpan sebagai rincian RAB
```

---

### 4.1 Jenis Pengeluaran Biaya Operasional

Biaya Operasional digunakan untuk mencatat kebutuhan operasional perusahaan yang berkaitan dengan aktivitas kerja sehari-hari atau kegiatan teknis perusahaan.

Contoh penggunaan:

- biaya operasional kegiatan;
- pembelian kebutuhan kantor;
- biaya transportasi operasional;
- kebutuhan lapangan;
- perawatan kendaraan;
- pembelian perlengkapan kerja.

#### Struktur Tabel Biaya Operasional

| No | Field | Keterangan |
|---|---|---|
| 1 | Nama Kebutuhan | Nama barang, jasa, atau kebutuhan operasional |
| 2 | Keterangan | Penjelasan tambahan terkait kebutuhan |
| 3 | Volume | Jumlah kebutuhan |
| 4 | Satuan | Satuan barang atau jasa, misalnya unit, pcs, liter, paket |
| 5 | Harga Satuan | Harga per satuan barang atau jasa |
| 6 | Total | Hasil perhitungan volume dikalikan harga satuan |

#### Rumus Perhitungan

```text
Total = Volume × Harga Satuan
```

#### Alur Input Biaya Operasional

```text
Admin memilih jenis Biaya Operasional
        ↓
Sistem menampilkan tabel Biaya Operasional
        ↓
Admin mengisi nama kebutuhan, keterangan, volume, satuan, dan harga satuan
        ↓
Sistem menghitung total otomatis
        ↓
Admin dapat menambahkan item operasional lain
        ↓
Sistem menjumlahkan seluruh total item
```

#### Karakteristik Biaya Operasional

- digunakan untuk kebutuhan kerja yang bersifat umum;
- dapat terdiri dari banyak item;
- nominal pengeluaran dapat bervariasi;
- membutuhkan approval Manajer dan Direktur;
- setelah dibayar, masuk ke arus kas sebagai uang keluar.

---

### 4.2 Jenis Pengeluaran Petty Cash

Petty Cash digunakan untuk mencatat pengeluaran kecil atau pengeluaran harian yang nominalnya relatif lebih kecil dan bersifat cepat.

Contoh penggunaan:

- parkir;
- konsumsi ringan;
- pembelian alat tulis kecil;
- biaya transportasi kecil;
- kebutuhan mendadak;
- pengeluaran operasional harian.

#### Struktur Tabel Petty Cash

| No | Field | Keterangan |
|---|---|---|
| 1 | Nama Pengeluaran | Nama transaksi petty cash |
| 2 | Keterangan | Penjelasan singkat transaksi |
| 3 | Tanggal | Tanggal transaksi |
| 4 | Nominal | Jumlah uang yang digunakan |
| 5 | Bukti | Bukti transaksi apabila tersedia |

#### Rumus Perhitungan

```text
Total Petty Cash = Jumlah seluruh nominal pengeluaran petty cash
```

#### Alur Input Petty Cash

```text
Admin memilih jenis Petty Cash
        ↓
Sistem menampilkan tabel Petty Cash
        ↓
Admin mengisi nama pengeluaran, keterangan, tanggal, nominal, dan bukti
        ↓
Sistem menghitung total petty cash
        ↓
Admin menyimpan atau mengajukan RAB
```

#### Karakteristik Petty Cash

- digunakan untuk pengeluaran kecil;
- bersifat cepat dan rutin;
- tetap dicatat sebagai RAB;
- tetap melalui approval sesuai alur sistem;
- setelah selesai, tercatat dalam arus kas dan laporan.

---

### 4.3 Jenis Pengeluaran Biaya Gaji

Biaya Gaji digunakan untuk mencatat kebutuhan pembayaran gaji, honorarium, atau pembayaran kepada pegawai/karyawan.

Jenis pengeluaran ini memiliki struktur berbeda karena melibatkan data penerima dan nomor rekening tujuan.

#### Struktur Tabel Biaya Gaji

| No | Field | Keterangan |
|---|---|---|
| 1 | Nama Pegawai | Nama penerima gaji |
| 2 | Jabatan | Jabatan atau posisi pegawai |
| 3 | Nomor Rekening | Nomor rekening tujuan pembayaran |
| 4 | Nama Bank | Nama bank penerima |
| 5 | Nominal Gaji | Jumlah gaji yang akan dibayarkan |
| 6 | Keterangan | Catatan tambahan apabila diperlukan |

#### Rumus Perhitungan

```text
Total Biaya Gaji = Jumlah seluruh nominal gaji
```

#### Alur Input Biaya Gaji

```text
Admin memilih jenis Biaya Gaji
        ↓
Sistem menampilkan tabel Biaya Gaji
        ↓
Admin mengisi nama pegawai, jabatan, nomor rekening, bank, nominal gaji, dan keterangan
        ↓
Sistem menjumlahkan seluruh nominal gaji
        ↓
Admin menyimpan atau mengajukan RAB
```

#### Karakteristik Biaya Gaji

- berisi data penerima pembayaran;
- membutuhkan nomor rekening tujuan;
- bersifat rutin sesuai periode pembayaran;
- data bersifat sensitif sehingga hanya dapat diakses oleh role tertentu;
- setelah disetujui dan dibayar, masuk ke arus kas sebagai uang keluar.

---

### 4.4 Jenis Pengeluaran Biaya Bulanan

Biaya Bulanan digunakan untuk mencatat pembayaran rutin perusahaan yang dilakukan setiap bulan.

Contoh penggunaan:

- tagihan listrik;
- tagihan internet;
- tagihan air;
- biaya sewa;
- langganan aplikasi;
- biaya keamanan;
- biaya kebersihan;
- pembayaran rutin perusahaan lainnya.

#### Struktur Tabel Biaya Bulanan

| No | Field | Keterangan |
|---|---|---|
| 1 | Nama Pembayaran | Nama tagihan atau pembayaran bulanan |
| 2 | Periode | Periode pembayaran, misalnya Januari 2026 |
| 3 | Keterangan | Penjelasan tambahan terkait pembayaran |
| 4 | Nominal Tagihan | Nilai utama tagihan |
| 5 | Biaya Admin | Biaya tambahan pembayaran apabila ada |
| 6 | Total Pembayaran | Hasil nominal tagihan ditambah biaya admin |

#### Rumus Perhitungan

```text
Total Pembayaran = Nominal Tagihan + Biaya Admin
```

#### Alur Input Biaya Bulanan

```text
Admin memilih jenis Biaya Bulanan
        ↓
Sistem menampilkan tabel Biaya Bulanan
        ↓
Admin mengisi nama pembayaran, periode, keterangan, nominal tagihan, dan biaya admin
        ↓
Sistem menghitung total pembayaran otomatis
        ↓
Admin menyimpan atau mengajukan RAB
```

#### Karakteristik Biaya Bulanan

- digunakan untuk pembayaran rutin;
- umumnya memiliki pola transaksi berulang;
- dapat membantu perusahaan memantau pengeluaran rutin;
- dapat dimasukkan ke laporan bulanan setelah status selesai;
- setelah dibayar, masuk ke arus kas sebagai uang keluar.

---

## 5. Modul Detail RAB

Modul Detail RAB digunakan untuk menampilkan informasi lengkap dari satu pengajuan RAB. Modul ini dapat diakses oleh Admin Keuangan, Manajer Operasional, dan Direktur sesuai hak akses masing-masing.

### 5.1 Informasi yang Ditampilkan

Detail RAB menampilkan:

- nomor RAB;
- tanggal pengajuan;
- nama pengaju;
- jenis pengeluaran;
- periode;
- status RAB;
- tabel rincian sesuai jenis pengeluaran;
- total pengajuan;
- catatan;
- riwayat approval;
- riwayat perubahan data;
- bukti pembayaran apabila sudah selesai.

### 5.2 Fungsi Detail RAB untuk Admin Keuangan

Admin Keuangan dapat:

- melihat isi lengkap RAB;
- mengedit RAB apabila status masih draft, ditolak, atau diajukan (sebelum ditinjau oleh atasan);
- menghapus RAB apabila status masih draft atau diajukan (sebelum ditinjau oleh atasan);
- mengajukan RAB;
- melihat catatan penolakan;
- mengunggah bukti pembayaran (maksimal 100KB, format Gambar/PDF) apabila RAB telah disetujui;
- mencetak atau mengexport data apabila status selesai.

### 5.3 Fungsi Detail RAB untuk Manajer Operasional

Manajer Operasional dapat:

- melihat detail RAB yang diajukan;
- memeriksa tabel rincian pengeluaran;
- memeriksa total pengajuan;
- menyetujui RAB;
- menolak RAB;
- memberikan catatan revisi.

### 5.4 Fungsi Detail RAB untuk Direktur

Direktur dapat:

- melihat detail RAB yang telah disetujui manajer;
- melihat riwayat approval manajer;
- memeriksa total pengajuan;
- menyetujui RAB;
- menolak RAB;
- memberikan catatan keputusan.

---

## 6. Modul Approval RAB

Modul Approval RAB mengatur proses persetujuan bertingkat dari Manajer Operasional dan Direktur.

### 6.1 Alur Approval

```text
Admin mengajukan RAB
        ↓
Status menjadi Diajukan
        ↓
Manajer Operasional memeriksa RAB
        ↓
Jika disetujui, RAB diteruskan ke Direktur
        ↓
Jika ditolak, RAB kembali ke Admin untuk revisi
        ↓
Direktur memeriksa RAB
        ↓
Jika disetujui, RAB siap dibayar
        ↓
Jika ditolak, RAB kembali ke Admin untuk revisi
```

### 6.2 Approval oleh Manajer Operasional

Manajer Operasional melakukan pemeriksaan terhadap:

- kelengkapan data RAB;
- kesesuaian jenis pengeluaran;
- rincian item pengeluaran;
- total pengajuan;
- keterangan pengajuan;
- kebutuhan anggaran.

Aksi yang dapat dilakukan:

| Aksi | Dampak Sistem |
|---|---|
| Setujui | Status berubah menjadi Disetujui Manajer dan diteruskan ke Direktur |
| Tolak | Status berubah menjadi Ditolak dan Admin wajib melakukan revisi |

Jika menolak, Manajer wajib mengisi catatan penolakan agar Admin mengetahui bagian yang harus diperbaiki.

### 6.3 Approval oleh Direktur

Direktur melakukan pemeriksaan akhir terhadap RAB yang telah disetujui oleh Manajer Operasional.

Aksi yang dapat dilakukan:

| Aksi | Dampak Sistem |
|---|---|
| Setujui | Status berubah menjadi Disetujui dan RAB dapat diproses pembayaran |
| Tolak | Status berubah menjadi Ditolak dan dikembalikan kepada Admin |

Direktur dapat melihat riwayat approval sebelumnya sehingga proses pengambilan keputusan menjadi lebih transparan.

---

## 7. Modul Upload Bukti Pembayaran

Modul Upload Bukti Pembayaran digunakan oleh Admin Keuangan setelah RAB mendapat status **Disetujui**.

### 7.1 Alur Upload Bukti Pembayaran

```text
RAB berstatus Disetujui
        ↓
Admin membuka detail RAB
        ↓
Admin memilih menu Upload Bukti Pembayaran
        ↓
Sistem menampilkan form pembayaran
        ↓
Admin mengisi data pembayaran
        ↓
Admin mengunggah file bukti transfer
        ↓
Sistem menyimpan data pembayaran
        ↓
Status RAB berubah menjadi Selesai
        ↓
Data masuk ke Arus Kas dan Laporan
```

### 7.2 Data Pembayaran

Data pembayaran yang diisi meliputi:

- tanggal pembayaran;
- nominal dibayarkan;
- metode pembayaran;
- rekening tujuan atau penerima;
- file bukti transfer;
- catatan pembayaran.

### 7.3 Ketentuan Pembayaran

Ketentuan sistem pada tahap pembayaran:

- hanya RAB berstatus Disetujui yang dapat dibayar;
- bukti transfer wajib diunggah dengan ukuran berkas maksimal **100KB** (format Gambar/PDF);
- nominal pembayaran wajib diisi;
- setelah pembayaran disimpan, status berubah menjadi Selesai;
- RAB yang sudah selesai masuk ke laporan final;
- nominal pembayaran dicatat sebagai uang keluar pada arus kas.

---

## 8. Modul Arus Kas

Modul Arus Kas digunakan untuk mencatat dan memantau aliran dana masuk, dana keluar, dan saldo akhir perusahaan berdasarkan transaksi RAB.

### 8.1 Alur Arus Kas

```text
Admin mencatat dana masuk
        ↓
Sistem menyimpan data dana masuk
        ↓
RAB disetujui dan dibayarkan
        ↓
Sistem mencatat pembayaran sebagai dana keluar
        ↓
Sistem menghitung saldo akhir
        ↓
Data arus kas dapat dilihat dan diexport
```

### 8.2 Data yang Ditampilkan

Modul arus kas menampilkan:

- tanggal transaksi;
- keterangan transaksi;
- jenis transaksi;
- dana masuk;
- dana keluar;
- saldo;
- nomor RAB terkait;
- bukti transaksi.

### 8.3 Jenis Transaksi Arus Kas

| No | Jenis Transaksi | Keterangan |
|---|---|---|
| 1 | Dana Masuk | Dana yang diterima untuk kebutuhan RAB |
| 2 | Dana Keluar | Dana yang digunakan untuk membayar RAB |
| 3 | Saldo Awal | Saldo awal yang dicatat sebelum transaksi berjalan |
| 4 | Saldo Akhir | Hasil perhitungan dana masuk dikurangi dana keluar |

### 8.4 Rumus Saldo

```text
Saldo Akhir = Saldo Awal + Total Dana Masuk - Total Dana Keluar
```

Modul ini membantu perusahaan mengetahui posisi kas aktual berdasarkan transaksi yang tercatat dalam sistem.

---

## 9. Modul Export Laporan

Modul Export Laporan digunakan untuk menghasilkan laporan RAB dan arus kas dalam bentuk dokumen digital, seperti PDF atau Excel.

### 9.1 Alur Export Laporan

```text
Admin membuka menu Export Laporan
        ↓
Admin memilih filter laporan
        ↓
Sistem menampilkan data sesuai filter
        ↓
Admin melihat preview laporan
        ↓
Admin memilih Export PDF atau Excel
        ↓
Sistem menghasilkan file laporan
```

### 9.2 Filter Laporan

Laporan dapat difilter berdasarkan:

- tanggal awal;
- tanggal akhir;
- bulan;
- tahun;
- nomor RAB;
- jenis pengeluaran;
- status RAB.

### 9.3 Ketentuan Data Laporan

Data yang masuk ke laporan final adalah RAB dengan status:

```text
Selesai
```

RAB yang masih berstatus Draft, Diajukan, Disetujui Manajer, Disetujui Direktur, Disetujui, atau Ditolak tidak masuk ke laporan final karena belum memiliki bukti pembayaran.

### 9.4 Isi Laporan

Laporan memuat:

- kop surat perusahaan;
- judul laporan;
- periode laporan;
- daftar transaksi;
- nomor RAB;
- jenis pengeluaran;
- tanggal transaksi;
- keterangan;
- debit;
- kredit;
- saldo;
- total dana masuk;
- total dana keluar;
- saldo akhir;
- tanda tangan atau nama penanggung jawab.

---

## 10. Modul Riwayat dan Audit Trail

Modul Riwayat dan Audit Trail digunakan untuk mencatat aktivitas penting yang dilakukan oleh pengguna dalam sistem.

### 10.1 Data Riwayat yang Dicatat

Sistem mencatat:

- waktu RAB dibuat;
- nama pengguna yang membuat RAB;
- waktu RAB diedit;
- perubahan data RAB;
- waktu RAB diajukan;
- waktu approval Manajer;
- waktu approval Direktur;
- waktu penolakan;
- catatan revisi;
- waktu upload bukti pembayaran;
- waktu RAB selesai;
- pengguna yang melakukan setiap tindakan.

### 10.2 Tujuan Audit Trail

Audit Trail bertujuan untuk:

- meningkatkan transparansi;
- memudahkan pelacakan dokumen;
- mengetahui perubahan data;
- mengurangi risiko manipulasi data;
- membantu proses evaluasi dan pemeriksaan internal.

---

## 11. Modul Kelola User

Modul Kelola User digunakan untuk mengatur akun pengguna yang dapat mengakses aplikasi.

### 11.1 Alur Kelola User

```text
Admin membuka menu Kelola User
        ↓
Sistem menampilkan daftar user
        ↓
Admin memilih tambah, edit, atau hapus user
        ↓
Admin menentukan role pengguna
        ↓
Sistem menyimpan perubahan data user
```

### 11.2 Data User

Data user meliputi:

- nama;
- email;
- password;
- role;
- status akun;
- tanggal dibuat;
- tanggal diperbarui.

### 11.3 Fungsi Kelola User

Fitur pada modul ini meliputi:

- tambah user;
- edit user;
- hapus user;
- ubah role;
- aktifkan akun;
- nonaktifkan akun;
- reset password.

---

## 12. Modul Notifikasi Status

Modul Notifikasi Status digunakan untuk memberikan informasi kepada pengguna mengenai perubahan status RAB.

### 12.1 Jenis Notifikasi

Notifikasi yang dapat ditampilkan meliputi:

- RAB berhasil disimpan sebagai draft;
- RAB berhasil diajukan;
- RAB menunggu approval Manajer;
- RAB disetujui Manajer;
- RAB menunggu approval Direktur;
- RAB disetujui Direktur;
- RAB ditolak dan perlu revisi;
- bukti pembayaran berhasil diunggah;
- RAB selesai;
- laporan berhasil diexport.

### 12.2 Media Notifikasi

Notifikasi dapat dikembangkan melalui:

- notifikasi dalam sistem;
- email;
- WhatsApp.

Untuk kebutuhan skripsi, notifikasi dalam sistem sudah cukup untuk menunjukkan perubahan status kepada pengguna.

---

## 13. Modul Validasi Data

Modul Validasi Data digunakan untuk memastikan data yang dimasukkan pengguna sudah sesuai sebelum disimpan atau diproses lebih lanjut.

### 13.1 Validasi Form RAB

Sistem melakukan validasi terhadap:

- nomor RAB tidak boleh kosong;
- tanggal pengajuan wajib diisi;
- jenis pengeluaran wajib dipilih;
- minimal terdapat satu item rincian;
- nominal harus berupa angka;
- total tidak boleh bernilai nol;
- data tidak boleh kosong pada field penting.

### 13.2 Validasi Jenis Pengeluaran

Sistem memastikan struktur tabel sesuai dengan jenis pengeluaran yang dipilih:

| Jenis Pengeluaran | Validasi Utama |
|---|---|
| Biaya Operasional | Nama kebutuhan, volume, satuan, dan harga satuan wajib diisi |
| Petty Cash | Nama pengeluaran, tanggal, dan nominal wajib diisi |
| Biaya Gaji | Nama pegawai, nomor rekening, bank, dan nominal gaji wajib diisi |
| Biaya Bulanan | Nama pembayaran, periode, nominal tagihan, dan biaya admin wajib diisi |

### 13.3 Validasi Approval

Sistem melakukan validasi sebagai berikut:

- hanya Manajer yang dapat melakukan approval tahap pertama;
- hanya Direktur yang dapat melakukan approval akhir;
- RAB yang ditolak wajib memiliki catatan;
- RAB draft tidak dapat langsung dibayar;
- RAB yang belum disetujui tidak dapat masuk ke pembayaran.

### 13.4 Validasi Pembayaran

Sistem melakukan validasi sebagai berikut:

- tanggal pembayaran wajib diisi;
- nominal pembayaran wajib diisi;
- metode pembayaran wajib dipilih;
- rekening tujuan wajib diisi;
- bukti transfer wajib diunggah;
- hanya RAB berstatus Disetujui yang dapat dibayar.

---

## 14. Keterhubungan Antar Modul

Seluruh modul dalam aplikasi saling terhubung dan membentuk satu alur kerja yang utuh.

```text
Login
 ↓
Dashboard
 ↓
Manajemen RAB
 ↓
Jenis Pengeluaran dan Tabel Dinamis
 ↓
Detail RAB
 ↓
Approval Manajer
 ↓
Approval Direktur
 ↓
Upload Bukti Pembayaran
 ↓
Arus Kas
 ↓
Export Laporan
 ↓
Riwayat dan Audit Trail
```

Penjelasan keterhubungan:

- Login menentukan role dan menu yang dapat diakses.
- Dashboard menampilkan ringkasan data sesuai role.
- Manajemen RAB digunakan untuk membuat dan mengajukan RAB.
- Jenis pengeluaran menentukan bentuk tabel rincian yang muncul.
- Detail RAB digunakan untuk melihat isi lengkap pengajuan.
- Approval Manajer menjadi tahap pemeriksaan pertama.
- Approval Direktur menjadi tahap persetujuan akhir.
- Upload bukti pembayaran dilakukan setelah RAB disetujui.
- Arus kas diperbarui setelah pembayaran tercatat.
- Export laporan mengambil data RAB yang telah selesai.
- Riwayat dan audit trail mencatat seluruh aktivitas penting dalam sistem.

---

## Kesimpulan Modul Sistem

Aplikasi Rancangan Anggaran Biaya berbasis website ini memiliki alur kerja yang dimulai dari pembuatan RAB oleh Admin Keuangan, pemilihan jenis pengeluaran, pengisian rincian sesuai struktur tabel, persetujuan oleh Manajer Operasional, persetujuan akhir oleh Direktur, upload bukti pembayaran, pencatatan arus kas, hingga export laporan.

Jenis pengeluaran dalam aplikasi terdiri dari empat jenis, yaitu **Biaya Operasional**, **Petty Cash**, **Biaya Gaji**, dan **Biaya Bulanan**. Setiap jenis pengeluaran memiliki struktur tabel yang berbeda sehingga sistem dapat menyesuaikan kebutuhan input data berdasarkan karakteristik transaksi. Dengan adanya fitur approval bertingkat, arus kas, riwayat aktivitas, dan export laporan, sistem ini tidak hanya berfungsi sebagai aplikasi input RAB, tetapi juga sebagai sistem monitoring dan pengendalian anggaran perusahaan.
