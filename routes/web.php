<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\ApprovalRabController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RabDiscussionController;
use App\Http\Controllers\RabNotificationController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsActive;

Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// ROUTE AUTENTIKASI
// ==========================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', function() { return redirect()->route('login'); });

Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

use App\Http\Controllers\ProfileController;

// ==========================================
// ROUTES YANG MEMERLUKAN LOGIN
// ==========================================
Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {

    // ── PROFIL (Modal Based) ──
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/rab/{rab}/discussions', [RabDiscussionController::class, 'store'])->name('rab.discussions.store');
    Route::get('/rab-notifications/{notification}', [RabNotificationController::class, 'open'])->name('rab.notifications.open');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    Route::middleware([CheckRole::class . ':admin_keuangan,manajer_keuangan'])->group(function () {
        Route::get('/rab/{rab}/export-pdf', [RabController::class, 'exportPdf'])->name('rab.export-pdf');
    });

    Route::middleware([CheckRole::class . ':manajer_keuangan,direktur'])->group(function () {
        Route::get('/report', [ReportExportController::class, 'index'])->name('report.index');
    });

    Route::middleware([CheckRole::class . ':manajer_keuangan'])->group(function () {
        Route::get('/report/export-pdf', [ReportExportController::class, 'exportPdf'])->name('report.export-pdf');
    });

    // ── ADMIN KEUANGAN ──
    Route::middleware([CheckRole::class . ':admin_keuangan'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

        // RAB Management
        Route::resource('/rab', RabController::class);
        Route::post('/rab/{rab}/submit', [RabController::class, 'submit'])->name('rab.submit');

        // Payment Upload
        Route::get('/rab/{rab}/payment/create', [PaymentController::class, 'create'])->name('rab.payment.create');
        Route::post('/rab/{rab}/payment', [PaymentController::class, 'store'])->name('rab.payment.store');

    });

    // ── MANAJER KEUANGAN ──
    Route::middleware([CheckRole::class . ':manajer_keuangan'])->group(function () {
        Route::get('/manajer/dashboard', [DashboardController::class, 'manajer'])->name('manajer.dashboard');

        // Daftar RAB (read-only list)
        Route::get('/manajer/rab', [RabController::class, 'listForApprover'])->name('manajer.rab.index');

        // View RAB detail (for approval)
        Route::get('/manajer/rab/{rab}', [RabController::class, 'show'])->name('manajer.rab.show');
        Route::post('/rab/{rab}/approve-manager', [ApprovalRabController::class, 'approveByManager'])->name('rab.approve.manager');
        Route::post('/rab/{rab}/reject-manager', [ApprovalRabController::class, 'reject'])->name('rab.reject.manager');

        // Arus Kas
        Route::get('/manajer/cash-flow', [CashFlowController::class, 'index'])->name('manajer.cash-flow.index');
        Route::post('/manajer/cash-flow', [CashFlowController::class, 'store'])->name('manajer.cash-flow.store');
    });

    // ── DIREKTUR ──
    Route::middleware([CheckRole::class . ':direktur'])->group(function () {
        Route::get('/direktur/dashboard', [DashboardController::class, 'direktur'])->name('direktur.dashboard');

        // Daftar RAB (read-only list)
        Route::get('/direktur/rab', [RabController::class, 'listForApprover'])->name('direktur.rab.index');

        // View RAB detail (for approval)
        Route::get('/direktur/rab/{rab}', [RabController::class, 'show'])->name('direktur.rab.show');
        Route::post('/rab/{rab}/approve-director', [ApprovalRabController::class, 'approveByDirector'])->name('rab.approve.director');
        Route::post('/rab/{rab}/reject-director', [ApprovalRabController::class, 'reject'])->name('rab.reject.director');

        // User Management
        Route::get('/direktur/users', [UserManagementController::class, 'index'])->name('direktur.users.index');
        Route::get('/direktur/users/create', [UserManagementController::class, 'create'])->name('direktur.users.create');
        Route::post('/direktur/users', [UserManagementController::class, 'store'])->name('direktur.users.store');
        Route::get('/direktur/users/{id}/edit', [UserManagementController::class, 'edit'])->name('direktur.users.edit');
        Route::put('/direktur/users/{id}', [UserManagementController::class, 'update'])->name('direktur.users.update');
        Route::patch('/direktur/users/{id}/toggle', [UserManagementController::class, 'toggleActive'])->name('direktur.users.toggle');
        Route::delete('/direktur/users/{id}', [UserManagementController::class, 'destroy'])->name('direktur.users.destroy');

        // Arus Kas (Read Only)
        Route::get('/direktur/cash-flow', [CashFlowController::class, 'index'])->name('direktur.cash-flow.index');
    });

});

// ── FILE VIEWER ──
Route::get('/file/{path}', function($path) {
    $path = str_replace('..', '', $path);
    \Illuminate\Support\Facades\Log::info("Requesting file: " . $path);
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        \Illuminate\Support\Facades\Log::info("File exists: " . $path);
        if (request()->query('download') == 1) {
            return response()->download(\Illuminate\Support\Facades\Storage::disk('public')->path($path));
        }
        return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path));
    }
    \Illuminate\Support\Facades\Log::error("File MISSING: " . $path);
    return response('<div style="font-family:sans-serif; text-align:center; padding: 50px; color:#666;"><h2>Maaf, File Tidak Ditemukan (404)</h2><p>File gambar atau dokumen ini fisik-nya sudah tidak ada di dalam folder server (mungkin terhapus saat ujicoba).</p><p>Silakan buat transaksi/pembayaran baru untuk mengujinya.</p></div>', 404);
})->where('path', '.*')->name('file.show');
