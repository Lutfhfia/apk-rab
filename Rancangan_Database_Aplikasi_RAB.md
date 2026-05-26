# Rancangan Database — Aplikasi Rancangan Anggaran Biaya (RAB)

> Database: MySQL | Engine: InnoDB | Charset: utf8mb4_unicode_ci

---

## 1. Keamanan dan Hak Akses

### Tabel `users`

Tabel ini digunakan untuk menyimpan data pengguna yang dapat mengakses aplikasi.

- `id` BIGINT, Primary Key
- `name` VARCHAR
- `email` VARCHAR, Unique
- `password` VARCHAR
- `role` ENUM: `admin_keuangan`, `manajer_operasional`, `direktur`
- `is_active` BOOLEAN, default `true`
- `remember_token` VARCHAR, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP
- `deleted_at` TIMESTAMP, nullable

Contoh role:
- Admin Keuangan
- Manajer Operasional
- Direktur

---

## 2. Data Induk

### Tabel `expense_types`

Tabel ini menyimpan empat jenis pengeluaran utama pada aplikasi RAB.

- `id` BIGINT, Primary Key
- `name` VARCHAR
- `code` VARCHAR, Unique
- `description` TEXT, nullable
- `is_active` BOOLEAN, default `true`
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP
- `deleted_at` TIMESTAMP, nullable

Data awal:

| code | name |
|---|---|
| `operasional` | Biaya Operasional |
| `petty_cash` | Petty Cash |
| `gaji` | Biaya Gaji |
| `bulanan` | Biaya Bulanan |

### Tabel `settings`

Tabel ini digunakan untuk menyimpan konfigurasi sistem.

- `id` BIGINT, Primary Key
- `key` VARCHAR, Unique
- `value` TEXT
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Contoh:
- `company_name`
- `company_address`
- `report_signer_name`
- `report_signer_position`

---

## 3. Tabel Utama RAB

### Tabel `rabs`

Tabel ini merupakan tabel utama untuk menyimpan dokumen pengajuan RAB.

- `id` BIGINT, Primary Key
- `rab_number` VARCHAR, Unique
- `request_date` DATE
- `period_month` VARCHAR, nullable
- `period_year` VARCHAR, nullable
- `user_id` BIGINT, Foreign Key ke `users.id`
- `expense_type_id` BIGINT, Foreign Key ke `expense_types.id`
- `description` TEXT, nullable
- `total_amount` DECIMAL(15,2), default `0`
- `status` ENUM:
  - `draft`
  - `diajukan`
  - `disetujui_manajer`
  - `disetujui_direktur`
  - `disetujui`
  - `ditolak`
  - `selesai`
- `submitted_at` TIMESTAMP, nullable
- `approved_by_manager_at` TIMESTAMP, nullable
- `approved_by_director_at` TIMESTAMP, nullable
- `completed_at` TIMESTAMP, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP
- `deleted_at` TIMESTAMP, nullable

Catatan:
- Satu RAB hanya memiliki satu jenis pengeluaran.
- Jenis pengeluaran menentukan tabel rincian yang digunakan.
- Hanya RAB berstatus `selesai` yang masuk ke laporan final.

---

## 4. Tabel Rincian Berdasarkan Jenis Pengeluaran

Aplikasi RAB memiliki empat jenis pengeluaran. Karena setiap jenis pengeluaran memiliki struktur field yang berbeda, maka rincian RAB dipisahkan ke dalam empat tabel berbeda.

---

### 4.1 Tabel `operational_expense_items`

Tabel ini menyimpan rincian untuk jenis **Biaya Operasional**.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`
- `need_name` VARCHAR
- `description` TEXT, nullable
- `volume` DECIMAL(12,2)
- `unit` VARCHAR
- `unit_price` DECIMAL(15,2)
- `total` DECIMAL(15,2)
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Rumus:

```text
total = volume × unit_price
```

Contoh data:
- pembelian alat kerja;
- biaya kegiatan operasional;
- kebutuhan lapangan;
- perawatan kendaraan.

---

### 4.2 Tabel `petty_cash_items`

Tabel ini menyimpan rincian untuk jenis **Petty Cash**.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`
- `expense_name` VARCHAR
- `description` TEXT, nullable
- `transaction_date` DATE
- `nominal` DECIMAL(15,2)
- `receipt_path` VARCHAR, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Rumus:

```text
total petty cash = jumlah seluruh nominal
```

Contoh data:
- parkir;
- konsumsi kecil;
- transportasi ringan;
- pembelian kebutuhan mendadak.

---

### 4.3 Tabel `salary_expense_items`

Tabel ini menyimpan rincian untuk jenis **Biaya Gaji**.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`
- `employee_name` VARCHAR
- `position` VARCHAR, nullable
- `bank_account_number` VARCHAR
- `bank_name` VARCHAR
- `salary_nominal` DECIMAL(15,2)
- `description` TEXT, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Rumus:

```text
total biaya gaji = jumlah seluruh salary_nominal
```

Catatan:
- Nomor rekening digunakan sebagai identitas pembayaran.
- Data gaji bersifat sensitif sehingga akses harus dibatasi.

---

### 4.4 Tabel `monthly_expense_items`

Tabel ini menyimpan rincian untuk jenis **Biaya Bulanan**.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`
- `payment_name` VARCHAR
- `period` VARCHAR
- `description` TEXT, nullable
- `bill_nominal` DECIMAL(15,2)
- `admin_fee` DECIMAL(15,2), default `0`
- `total_payment` DECIMAL(15,2)
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Rumus:

```text
total_payment = bill_nominal + admin_fee
```

Contoh data:
- listrik;
- internet;
- air;
- sewa;
- langganan aplikasi;
- pembayaran rutin lainnya.

---

## 5. Approval RAB

### Tabel `rab_approvals`

Tabel ini menyimpan riwayat approval atau penolakan RAB.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`
- `user_id` BIGINT, Foreign Key ke `users.id`
- `role` ENUM: `manajer_operasional`, `direktur`
- `approval_level` ENUM: `manager`, `director`
- `status` ENUM: `approved`, `rejected`
- `notes` TEXT, nullable
- `approved_at` TIMESTAMP, nullable
- `rejected_at` TIMESTAMP, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Ketentuan:
- Approval tahap pertama dilakukan oleh Manajer Operasional.
- Approval akhir dilakukan oleh Direktur.
- Jika ditolak, field `notes` wajib diisi.

---

## 6. Pembayaran RAB

### Tabel `rab_payments`

Tabel ini menyimpan data pembayaran setelah RAB disetujui.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`
- `paid_by` BIGINT, Foreign Key ke `users.id`
- `payment_date` DATE
- `paid_amount` DECIMAL(15,2)
- `payment_method` VARCHAR
- `recipient_account` VARCHAR, nullable
- `recipient_name` VARCHAR, nullable
- `proof_file_path` VARCHAR
- `notes` TEXT, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Ketentuan:
- Hanya RAB berstatus `disetujui` yang dapat dibayar.
- Setelah bukti pembayaran disimpan, status RAB berubah menjadi `selesai`.
- Data pembayaran masuk ke arus kas sebagai dana keluar.

---

## 7. Arus Kas

### Tabel `cash_flows`

Tabel ini digunakan untuk mencatat dana masuk, dana keluar, dan saldo.

- `id` BIGINT, Primary Key
- `rab_id` BIGINT, Foreign Key ke `rabs.id`, nullable
- `payment_id` BIGINT, Foreign Key ke `rab_payments.id`, nullable
- `transaction_date` DATE
- `type` ENUM: `saldo_awal`, `dana_masuk`, `dana_keluar`
- `description` TEXT
- `debit` DECIMAL(15,2), default `0`
- `credit` DECIMAL(15,2), default `0`
- `balance` DECIMAL(15,2), default `0`
- `created_by` BIGINT, Foreign Key ke `users.id`
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Rumus:

```text
saldo akhir = saldo awal + total debit - total credit
```

Keterangan:
- `debit` digunakan untuk dana masuk.
- `credit` digunakan untuk dana keluar.
- Pembayaran RAB otomatis membuat transaksi dana keluar.

---

## 8. Laporan

### Tabel `report_exports`

Tabel ini menyimpan riwayat export laporan.

- `id` BIGINT, Primary Key
- `exported_by` BIGINT, Foreign Key ke `users.id`
- `report_type` VARCHAR
- `start_date` DATE
- `end_date` DATE
- `file_path` VARCHAR, nullable
- `format` ENUM: `pdf`, `excel`
- `total_debit` DECIMAL(15,2), default `0`
- `total_credit` DECIMAL(15,2), default `0`
- `ending_balance` DECIMAL(15,2), default `0`
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Ketentuan:
- Laporan final hanya mengambil RAB berstatus `selesai`.
- Export dapat difilter berdasarkan periode, jenis pengeluaran, dan status.

---

## 9. Riwayat dan Audit Trail

### Tabel `audit_logs`

Tabel ini mencatat aktivitas penting yang dilakukan pengguna.

- `id` BIGINT, Primary Key
- `user_id` BIGINT, Foreign Key ke `users.id`, nullable
- `rab_id` BIGINT, Foreign Key ke `rabs.id`, nullable
- `action` VARCHAR
- `description` TEXT
- `old_values` JSON, nullable
- `new_values` JSON, nullable
- `ip_address` VARCHAR, nullable
- `user_agent` TEXT, nullable
- `created_at` TIMESTAMP
- `updated_at` TIMESTAMP

Contoh action:
- `create_rab`
- `update_rab`
- `submit_rab`
- `approve_manager`
- `approve_director`
- `reject_rab`
- `upload_payment`
- `export_report`

---

## 10. Relasi Antar Tabel

### 10.1 Relasi User

**Tabel `users`**

- hasMany ke `rabs`
- hasMany ke `rab_approvals`
- hasMany ke `rab_payments` sebagai `paid_by`
- hasMany ke `cash_flows` sebagai `created_by`
- hasMany ke `report_exports` sebagai `exported_by`
- hasMany ke `audit_logs`

---

### 10.2 Relasi Jenis Pengeluaran

**Tabel `expense_types`**

- hasMany ke `rabs`

**Tabel `rabs`**

- belongsTo ke `expense_types`
- belongsTo ke `users`
- hasMany ke `operational_expense_items`
- hasMany ke `petty_cash_items`
- hasMany ke `salary_expense_items`
- hasMany ke `monthly_expense_items`
- hasMany ke `rab_approvals`
- hasOne ke `rab_payments`
- hasMany ke `cash_flows`
- hasMany ke `audit_logs`

Catatan:
- Walaupun `rabs` memiliki relasi ke empat tabel rincian, dalam praktiknya satu RAB hanya akan menggunakan satu tabel rincian sesuai `expense_type_id`.

---

### 10.3 Relasi Rincian Pengeluaran

**Tabel `operational_expense_items`**

- belongsTo ke `rabs`

**Tabel `petty_cash_items`**

- belongsTo ke `rabs`

**Tabel `salary_expense_items`**

- belongsTo ke `rabs`

**Tabel `monthly_expense_items`**

- belongsTo ke `rabs`

---

### 10.4 Relasi Approval

**Tabel `rab_approvals`**

- belongsTo ke `rabs`
- belongsTo ke `users`

Satu RAB dapat memiliki lebih dari satu data approval, yaitu approval Manajer dan approval Direktur.

---

### 10.5 Relasi Pembayaran

**Tabel `rab_payments`**

- belongsTo ke `rabs`
- belongsTo ke `users` sebagai `paid_by`
- hasOne ke `cash_flows`

Satu RAB memiliki satu data pembayaran setelah disetujui.

---

### 10.6 Relasi Arus Kas

**Tabel `cash_flows`**

- belongsTo ke `rabs`, nullable
- belongsTo ke `rab_payments`, nullable
- belongsTo ke `users` sebagai `created_by`

Arus kas dapat berasal dari:
- saldo awal;
- dana masuk;
- dana keluar dari pembayaran RAB.

---

### 10.7 Relasi Laporan

**Tabel `report_exports`**

- belongsTo ke `users` sebagai `exported_by`

Laporan mengambil data dari:
- `rabs`
- `rab_payments`
- `cash_flows`

---

## 11. Ringkasan Struktur Database

```text
users
 ├── rabs
 │    ├── operational_expense_items
 │    ├── petty_cash_items
 │    ├── salary_expense_items
 │    ├── monthly_expense_items
 │    ├── rab_approvals
 │    ├── rab_payments
 │    ├── cash_flows
 │    └── audit_logs
 │
 ├── rab_approvals
 ├── rab_payments
 ├── cash_flows
 ├── report_exports
 └── audit_logs

expense_types
 └── rabs
```

---

## 12. Catatan Implementasi

1. Field `status` pada tabel `rabs` sebaiknya menggunakan Enum agar alur status konsisten.
2. Empat jenis pengeluaran dibuat dalam tabel berbeda karena struktur field-nya berbeda.
3. Perhitungan total tetap harus divalidasi di backend walaupun sudah dihitung otomatis di frontend.
4. RAB berstatus `draft`, `diajukan`, `ditolak`, atau `disetujui` belum boleh masuk laporan final.
5. RAB baru masuk laporan final setelah status berubah menjadi `selesai`.
6. Setiap perubahan status sebaiknya dicatat pada `audit_logs`.
7. Pembayaran RAB otomatis membuat data `cash_flows` dengan tipe `dana_keluar`.
8. Dana masuk dapat dicatat langsung ke tabel `cash_flows` dengan tipe `dana_masuk`.
9. Soft delete digunakan untuk data utama agar riwayat transaksi tidak hilang.
10. File bukti pembayaran disimpan di storage dan path-nya disimpan pada tabel `rab_payments`.

---

## Kesimpulan

Rancangan database aplikasi RAB disusun berdasarkan kebutuhan utama sistem, yaitu pengajuan RAB, pemilihan jenis pengeluaran, rincian tabel dinamis, approval bertingkat, upload bukti pembayaran, pencatatan arus kas, export laporan, dan audit trail. Struktur database ini mendukung empat jenis pengeluaran utama, yaitu **Biaya Operasional**, **Petty Cash**, **Biaya Gaji**, dan **Biaya Bulanan**. Pemisahan tabel rincian berdasarkan jenis pengeluaran membuat data lebih rapi, valid, dan sesuai dengan kebutuhan transaksi perusahaan.
