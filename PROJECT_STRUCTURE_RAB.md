# Struktur Project Aplikasi RAB - Laravel

## 📐 Arsitektur Project

Project aplikasi Rancangan Anggaran Biaya (RAB) menggunakan arsitektur **Layered Architecture** pada backend Laravel. Struktur ini memisahkan tanggung jawab antara Controller, Service, Repository, Model, Request Validation, dan View agar kode lebih rapi, mudah diuji, serta mudah dikembangkan.

Aplikasi ini berfokus pada alur kerja:
1. Admin Keuangan membuat RAB.
2. Admin memilih jenis pengeluaran.
3. Sistem menampilkan tabel rincian sesuai jenis pengeluaran.
4. Manajer Operasional melakukan approval tahap pertama.
5. Direktur melakukan approval akhir.
6. Admin mengunggah bukti pembayaran.
7. Sistem mencatat arus kas.
8. Admin melakukan export laporan.

---

## 🗂️ Backend Structure Laravel

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── LogoutController.php
│   │   │
│   │   ├── DashboardController.php
│   │   ├── RabController.php
│   │   ├── RabDetailController.php
│   │   ├── ApprovalRabController.php
│   │   ├── PaymentController.php
│   │   ├── CashFlowController.php
│   │   ├── ReportController.php
│   │   └── UserController.php
│   │
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   └── EnsureUserIsActive.php
│   │
│   ├── Requests/
│   │   ├── Rab/
│   │   │   ├── StoreRabRequest.php
│   │   │   ├── UpdateRabRequest.php
│   │   │   ├── StoreOperationalExpenseRequest.php
│   │   │   ├── StorePettyCashRequest.php
│   │   │   ├── StoreSalaryExpenseRequest.php
│   │   │   └── StoreMonthlyExpenseRequest.php
│   │   │
│   │   ├── Approval/
│   │   │   └── StoreApprovalRequest.php
│   │   │
│   │   ├── Payment/
│   │   │   └── StorePaymentRequest.php
│   │   │
│   │   └── User/
│   │       ├── StoreUserRequest.php
│   │       └── UpdateUserRequest.php
│   │
│   └── Resources/
│       ├── RabResource.php
│       ├── PaymentResource.php
│       └── CashFlowResource.php
│
├── Services/
│   ├── Dashboard/
│   │   └── DashboardService.php
│   │
│   ├── Rab/
│   │   ├── RabService.php
│   │   ├── RabNumberService.php
│   │   ├── RabCalculationService.php
│   │   └── RabStatusService.php
│   │
│   ├── Approval/
│   │   └── ApprovalService.php
│   │
│   ├── Payment/
│   │   └── PaymentService.php
│   │
│   ├── CashFlow/
│   │   └── CashFlowService.php
│   │
│   ├── Report/
│   │   ├── ReportService.php
│   │   └── PdfExportService.php
│   │
│   └── User/
│       └── UserService.php
│
├── Repositories/
│   ├── Contracts/
│   │   ├── RabRepositoryInterface.php
│   │   ├── RabItemRepositoryInterface.php
│   │   ├── ApprovalRepositoryInterface.php
│   │   ├── PaymentRepositoryInterface.php
│   │   ├── CashFlowRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   │
│   └── Eloquent/
│       ├── RabRepository.php
│       ├── RabItemRepository.php
│       ├── ApprovalRepository.php
│       ├── PaymentRepository.php
│       ├── CashFlowRepository.php
│       └── UserRepository.php
│
├── Models/
│   ├── User.php
│   ├── Rab.php
│   ├── OperationalExpenseItem.php
│   ├── PettyCashItem.php
│   ├── SalaryExpenseItem.php
│   ├── MonthlyExpenseItem.php
│   ├── RabApproval.php
│   ├── RabPayment.php
│   ├── CashFlow.php
│   ├── AuditLog.php
│   └── Setting.php
│
├── Enums/
│   ├── UserRole.php
│   ├── RabStatus.php
│   ├── ExpenseType.php
│   ├── ApprovalStatus.php
│   └── CashFlowType.php
│
├── DTOs/
│   ├── Rab/
│   │   ├── CreateRabDTO.php
│   │   ├── OperationalExpenseDTO.php
│   │   ├── PettyCashDTO.php
│   │   ├── SalaryExpenseDTO.php
│   │   └── MonthlyExpenseDTO.php
│   │
│   ├── Approval/
│   │   └── ApprovalDTO.php
│   │
│   └── Payment/
│       └── PaymentDTO.php
│
├── Actions/
│   ├── Rab/
│   │   ├── CreateRabAction.php
│   │   ├── SubmitRabAction.php
│   │   ├── UpdateRabAction.php
│   │   └── CalculateRabTotalAction.php
│   │
│   ├── Approval/
│   │   ├── ApproveByManagerAction.php
│   │   ├── ApproveByDirectorAction.php
│   │   └── RejectRabAction.php
│   │
│   ├── Payment/
│   │   └── UploadPaymentProofAction.php
│   │
│   └── CashFlow/
│       └── CreateCashFlowFromPaymentAction.php
│
├── Traits/
│   ├── HasAuditLog.php
│   ├── HasStatusBadge.php
│   └── GeneratesRabNumber.php
│
├── Observers/
│   ├── RabObserver.php
│   ├── RabPaymentObserver.php
│   └── CashFlowObserver.php
│
├── Events/
│   ├── RabSubmitted.php
│   ├── RabApprovedByManager.php
│   ├── RabApprovedByDirector.php
│   ├── RabRejected.php
│   ├── RabPaid.php
│   └── ReportExported.php
│
├── Listeners/
│   ├── CreateAuditLog.php
│   ├── SendRabNotification.php
│   └── UpdateDashboardSummary.php
│
├── Jobs/
│   ├── GenerateRabReportJob.php
│   └── GenerateMonthlyReportJob.php
│
└── Providers/
    ├── AppServiceProvider.php
    └── RepositoryServiceProvider.php
```

---

## 🎨 Frontend / View Structure

Jika aplikasi menggunakan Laravel Blade, struktur tampilan dapat dibuat sebagai berikut.

```text
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   ├── auth.blade.php
│   │   └── dashboard.blade.php
│   │
│   ├── auth/
│   │   └── login.blade.php
│   │
│   ├── dashboard/
│   │   ├── admin.blade.php
│   │   ├── manager.blade.php
│   │   └── director.blade.php
│   │
│   ├── rab/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── partials/
│   │       ├── operational-table.blade.php
│   │       ├── petty-cash-table.blade.php
│   │       ├── salary-table.blade.php
│   │       └── monthly-table.blade.php
│   │
│   ├── approvals/
│   │   ├── manager.blade.php
│   │   ├── director.blade.php
│   │   └── history.blade.php
│   │
│   ├── payments/
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   │
│   ├── cash-flows/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   │
│   ├── reports/
│   │   ├── index.blade.php
│   │   ├── preview.blade.php
│   │   └── pdf/
│   │       ├── monthly-report.blade.php
│   │       └── rab-detail.blade.php
│   │
│   └── users/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
│
├── css/
│   └── app.css
│
└── js/
    ├── app.js
    ├── rab-form.js
    ├── dynamic-expense-table.js
    ├── cash-flow.js
    └── report-filter.js
```

---

## 🔄 Data Flow Pattern

### Backend Flow

```text
Route → Controller → Form Request → Service → Repository → Model → Database
                                  ↓
                             Event / Audit Log
```

Contoh alur pembuatan RAB:

```text
Admin klik Simpan RAB
        ↓
Route menerima request
        ↓
RabController memanggil StoreRabRequest
        ↓
Request melakukan validasi
        ↓
RabService menjalankan business logic
        ↓
RabRepository menyimpan data RAB
        ↓
Item pengeluaran disimpan sesuai jenis tabel
        ↓
AuditLog mencatat aktivitas
        ↓
Response dikembalikan ke halaman detail RAB
```

### Frontend Flow

```text
User memilih jenis pengeluaran
        ↓
JavaScript membaca pilihan
        ↓
Sistem menampilkan tabel rincian sesuai jenis
        ↓
User mengisi item pengeluaran
        ↓
Sistem menghitung subtotal dan total
        ↓
User menyimpan draft atau mengajukan RAB
```

---

## 📋 Naming Conventions

### Backend Laravel

| Type | Convention | Example |
|---|---|---|
| Controller | PascalCase + Controller | `RabController` |
| Service | PascalCase + Service | `RabService` |
| Repository | PascalCase + Repository | `RabRepository` |
| Model | PascalCase Singular | `Rab`, `RabPayment` |
| Migration | snake_case | `create_rabs_table` |
| Request | PascalCase + Request | `StoreRabRequest` |
| Enum | PascalCase | `RabStatus` |
| DTO | PascalCase + DTO | `CreateRabDTO` |
| Event | PascalCase Past Tense | `RabSubmitted` |
| Job | PascalCase + Job | `GenerateMonthlyReportJob` |

### Frontend / Blade

| Type | Convention | Example |
|---|---|---|
| Blade page | kebab-case | `create.blade.php` |
| Partial view | kebab-case | `operational-table.blade.php` |
| JavaScript file | kebab-case | `dynamic-expense-table.js` |
| CSS class | kebab-case | `status-badge` |

---

## 🧩 Modul Utama dalam Project

### 1. Auth Module
Mengatur login, logout, dan validasi pengguna.

### 2. Dashboard Module
Menampilkan ringkasan RAB, total pengajuan, total pembayaran, waiting approval, dan grafik realisasi.

### 3. RAB Module
Mengelola pembuatan, pengeditan, pengajuan, dan detail RAB.

### 4. Dynamic Expense Table Module
Mengatur tampilan tabel sesuai empat jenis pengeluaran:
- Biaya Operasional
- Petty Cash
- Biaya Gaji
- Biaya Bulanan

### 5. Approval Module
Mengatur approval bertingkat oleh Manajer Operasional dan Direktur.

### 6. Payment Module
Mengatur upload bukti pembayaran setelah RAB disetujui.

### 7. Cash Flow Module
Mengatur pencatatan dana masuk, dana keluar, dan saldo akhir.

### 8. Report Module
Mengatur preview laporan dan export PDF/Excel.

### 9. User Management Module
Mengatur akun pengguna dan hak akses.

### 10. Audit Log Module
Mencatat aktivitas penting dalam sistem.

---

## 🧪 Testing Structure

```text
tests/
├── Feature/
│   ├── Auth/
│   │   └── LoginTest.php
│   │
│   ├── Rab/
│   │   ├── CreateRabTest.php
│   │   ├── SubmitRabTest.php
│   │   └── DynamicExpenseTableTest.php
│   │
│   ├── Approval/
│   │   ├── ManagerApprovalTest.php
│   │   └── DirectorApprovalTest.php
│   │
│   ├── Payment/
│   │   └── UploadPaymentProofTest.php
│   │
│   └── Report/
│       └── ExportReportTest.php
│
└── Unit/
    ├── Services/
    │   ├── RabCalculationServiceTest.php
    │   ├── RabStatusServiceTest.php
    │   └── CashFlowServiceTest.php
    │
    └── Enums/
        ├── RabStatusTest.php
        └── ExpenseTypeTest.php
```

---

## ✅ Best Practices

### Backend

1. Controller hanya menerima request dan mengembalikan response.
2. Business logic diletakkan pada Service.
3. Query database diletakkan pada Repository.
4. Validasi input menggunakan Form Request.
5. Status RAB dikelola menggunakan Enum.
6. Perubahan penting dicatat pada Audit Log.
7. Proses approval menggunakan transaksi database agar aman.
8. Upload bukti pembayaran disimpan pada storage khusus.

### Frontend

1. Form RAB dibuat dinamis berdasarkan jenis pengeluaran.
2. Perhitungan subtotal dilakukan otomatis di sisi tampilan dan divalidasi ulang di backend.
3. Tombol aksi ditampilkan sesuai role dan status.
4. Status RAB ditampilkan menggunakan badge agar mudah dibaca.
5. Halaman laporan memiliki filter periode dan status.

---

## 📦 File Organization Tips

### ✅ DO

- Pisahkan logic berdasarkan modul.
- Gunakan nama file yang jelas.
- Pisahkan tabel rincian berdasarkan jenis pengeluaran.
- Gunakan Enum untuk role, status, dan jenis transaksi.
- Catat setiap perubahan status pada audit log.

### ❌ DON'T

- Jangan menaruh semua logic di Controller.
- Jangan menggunakan satu tabel rincian untuk semua jenis pengeluaran jika field-nya berbeda.
- Jangan membiarkan RAB dibayar sebelum status Disetujui.
- Jangan memasukkan data Draft atau Diajukan ke laporan final.
- Jangan menghapus data transaksi penting secara permanen.

---

## 🔗 Route Group Example

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/rab', RabController::class);
    Route::post('/rab/{rab}/submit', [RabController::class, 'submit'])->name('rab.submit');

    Route::post('/rab/{rab}/approve-manager', [ApprovalRabController::class, 'approveByManager'])->name('rab.approve.manager');
    Route::post('/rab/{rab}/approve-director', [ApprovalRabController::class, 'approveByDirector'])->name('rab.approve.director');
    Route::post('/rab/{rab}/reject', [ApprovalRabController::class, 'reject'])->name('rab.reject');

    Route::post('/rab/{rab}/payment', [PaymentController::class, 'store'])->name('rab.payment.store');

    Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cash-flow.index');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/preview', [ReportController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::resource('/users', UserController::class);
});
```

---

## 📚 Ringkasan

Struktur project aplikasi RAB dirancang agar sesuai dengan kebutuhan sistem pengajuan, approval, pembayaran, arus kas, dan laporan. Pemisahan folder berdasarkan modul membuat sistem lebih mudah dikembangkan dan diperbaiki. Dengan menggunakan struktur Controller, Service, Repository, Model, Request, dan View, aplikasi menjadi lebih rapi, terstruktur, dan layak digunakan sebagai proyek skripsi.
