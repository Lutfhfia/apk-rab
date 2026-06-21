<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\ExpenseType;
use App\Models\AuditLog;
use App\Models\OperationalExpenseItem;
use App\Models\PettyCashItem;
use App\Models\SalaryExpenseItem;
use App\Models\MonthlyExpenseItem;
use App\Enums\RabStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RabController extends Controller
{
    /**
     * Display a listing of RABs.
     */
    public function index(Request $request)
    {
        $query = Rab::with([
            'user',
            'expenseType',
            'operationalExpenseItems',
            'pettyCashItems',
            'salaryExpenseItems',
            'monthlyExpenseItems',
            'approvals.user',
            'discussions.user',
            'payment.paidBy',
            'auditLogs.user',
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', RabStatus::DIAJUKAN);
        }

        // Filter by expense type
        if ($request->filled('expense_type')) {
            $query->where('expense_type_id', $request->expense_type);
        }

        // Search by RAB number
        if ($request->filled('search')) {
            $query->where('rab_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('request_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('request_date', '<=', $request->end_date);
        }

        $sortDir = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        $rabs = $query->orderBy('created_at', $sortDir)->paginate(15);
        
        if ($request->filled('open_rab_id')) {
            $openedRab = Rab::with([
                'user',
                'expenseType',
                'operationalExpenseItems',
                'pettyCashItems',
                'salaryExpenseItems',
                'monthlyExpenseItems',
                'approvals.user',
                'discussions.user',
                'payment.paidBy',
                'auditLogs.user',
            ])->find($request->open_rab_id);
            
            if ($openedRab && !$rabs->contains('id', $openedRab->id)) {
                $rabs->getCollection()->push($openedRab);
            }
        }
        $expenseTypes = ExpenseType::where('is_active', true)->get();
        $rabNumber = Rab::generateNumber();

        $statusCounts = Rab::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('rab.index', compact('rabs', 'expenseTypes', 'rabNumber', 'statusCounts'));
    }

    /**
     * Display RAB listing for Manajer/Direktur (read-only, non-draft only).
     */
    public function listForApprover(Request $request)
    {
        $query = Rab::with([
                'user',
                'expenseType',
                'operationalExpenseItems',
                'pettyCashItems',
                'salaryExpenseItems',
                'monthlyExpenseItems',
                'approvals.user',
                'discussions.user',
                'payment.paidBy',
                'auditLogs.user',
            ])
            ->where('status', '!=', RabStatus::DRAFT);

        // Determine route prefix based on user role
        $role = auth()->user()->isManajer() ? 'manajer' : 'direktur';

        // Default status depends on role: show what needs THEIR review first
        $defaultStatus = $role === 'direktur'
            ? RabStatus::DISETUJUI_MANAJER
            : RabStatus::DIAJUKAN;

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', $defaultStatus);
        }

        // Filter by expense type
        if ($request->filled('expense_type')) {
            $query->where('expense_type_id', $request->expense_type);
        }

        // Search by RAB number or description
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('rab_number', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('request_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('request_date', '<=', $request->end_date);
        }

        $sortDir = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        $rabs = $query->orderBy('created_at', $sortDir)->paginate(15);
        
        if ($request->filled('open_rab_id')) {
            $openedRab = Rab::with([
                'user',
                'expenseType',
                'operationalExpenseItems',
                'pettyCashItems',
                'salaryExpenseItems',
                'monthlyExpenseItems',
                'approvals.user',
                'discussions.user',
                'payment.paidBy',
                'auditLogs.user',
            ])->find($request->open_rab_id);
            
            if ($openedRab && !$rabs->contains('id', $openedRab->id)) {
                $rabs->getCollection()->push($openedRab);
            }
        }
        $expenseTypes = ExpenseType::where('is_active', true)->get();

        $statusCounts = Rab::select('status', DB::raw('count(*) as count'))
            ->where('status', '!=', RabStatus::DRAFT)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('rab.list-approver', compact('rabs', 'expenseTypes', 'role', 'statusCounts', 'defaultStatus'));

    }

    public function create()
    {
        return redirect()->route('rab.index')
            ->with('info', 'Fitur pembuatan RAB tersedia melalui tombol "Buat RAB Baru" di halaman ini.');
    }

    /**
     * Store a newly created RAB.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rab_number' => 'nullable|string',
            'rab_sequence' => 'nullable|integer|min:1',
            'rab_month' => 'required|string',
            'rab_year' => 'required|string',
            'request_date' => 'required|date',
            'expense_type_id' => 'required|exists:expense_types,id',
            'period_month' => 'nullable|string',
            'description' => 'nullable|string',
            'action' => 'required|in:draft,submit',
        ]);

        $expenseType = ExpenseType::findOrFail($request->expense_type_id);

        // Validate expense items based on type
        $this->validateExpenseItems($request, $expenseType->code);

        // Auto-derive period_year from request_date
        $periodYear = date('Y', strtotime($request->request_date));

        DB::beginTransaction();

        try {
            $sequence = (int) ($request->rab_sequence ?: Rab::nextSequence());
            $rabNumber = Rab::buildNumber($sequence, $request->rab_month, $request->rab_year);

            while (Rab::withTrashed()->where('rab_number', $rabNumber)->exists()) {
                $sequence++;
                $rabNumber = Rab::buildNumber($sequence, $request->rab_month, $request->rab_year);
            }

            $rab = Rab::create([
                'rab_number' => $rabNumber,
                'request_date' => $request->request_date,
                'period_month' => $request->period_month,
                'period_year' => $periodYear,
                'user_id' => auth()->id(),
                'expense_type_id' => $request->expense_type_id,
                'description' => $request->description,
                'status' => $request->action === 'submit' ? RabStatus::DIAJUKAN : RabStatus::DRAFT,
                'submitted_at' => $request->action === 'submit' ? now() : null,
            ]);

            // Store expense items
            $this->storeExpenseItems($rab, $request, $expenseType->code);

            // Recalculate total
            $rab->recalculateTotal();

            // Audit log
            AuditLog::log(
                $request->action === 'submit' ? 'submit_rab' : 'create_rab',
                $request->action === 'submit'
                    ? "RAB {$rab->rab_number} diajukan oleh " . auth()->user()->name
                    : "RAB {$rab->rab_number} dibuat sebagai draft oleh " . auth()->user()->name,
                rabId: $rab->id
            );
            if ($request->action === 'submit') {
                $rab->notifyRole(
                    UserRole::MANAJER_OPERASIONAL->value,
                    'RAB baru perlu diperiksa',
                    'Admin Keuangan ' . auth()->user()->name . " mengajukan RAB {$rab->rab_number} untuk " . ($rab->expenseType->name ?? '-'),
                    null,
                    $rab->buildWhatsAppSubmissionMessage(route('manajer.rab.show', $rab))
                );
            }

            DB::commit();

            if ($request->action === 'submit') {
                return redirect()->route('rab.index')
                    ->with('success', 'RAB berhasil diajukan!')
                    ->with('submitted_rab_id', $rab->id)
                    ->with('submitted_rab_number', $rab->rab_number)
                    ->with('submitted_rab_total', $rab->total_amount)
                    ->with('submitted_rab_type', $rab->expenseType->name ?? '-');
            }

            return redirect()->route('rab.index', ['status' => RabStatus::DRAFT->value])
                ->with('success', 'RAB berhasil disimpan sebagai draft!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function show(Rab $rab)
    {
        $rab->load([
            'user',
            'expenseType',
            'approvals.user',
            'discussions.user',
            'payment.paidBy',
            'operationalExpenseItems',
            'pettyCashItems',
            'salaryExpenseItems',
            'monthlyExpenseItems',
        ]);

        // Load the appropriate expense items
        $expenseItems = $rab->getExpenseItems();

        $route = 'rab.index';
        if (auth()->user()?->isManajer()) {
            $route = 'manajer.rab.index';
        } elseif (auth()->user()?->isDirektur()) {
            $route = 'direktur.rab.index';
        }

        return redirect()->route($route, ['status' => $rab->status->value, 'open_rab_id' => $rab->id])
            ->with('info', 'Detail RAB dibuka dalam popup.');
    }

    public function edit(Rab $rab)
    {
        // Only allow Admin Keuangan to edit
        if (auth()->user()->role !== UserRole::ADMIN_KEUANGAN) {
            abort(403, 'Hanya Admin Keuangan yang dapat mengedit RAB.');
        }

        // Only allow editing draft, rejected, or submitted (diajukan) RABs
        if (!in_array($rab->status, [RabStatus::DRAFT, RabStatus::DITOLAK, RabStatus::DIAJUKAN])) {
            return back()->with('error', 'RAB ini tidak dapat diedit.');
        }

        return redirect()->route('rab.index', ['status' => $rab->status->value])
            ->with('info', 'Silakan klik tombol Edit pada RAB yang bersangkutan di daftar ini.');
    }

    /**
     * Update the specified RAB.
     */
    public function update(Request $request, Rab $rab)
    {
        // Only allow Admin Keuangan to update
        if (auth()->user()->role !== UserRole::ADMIN_KEUANGAN) {
            abort(403, 'Hanya Admin Keuangan yang dapat mengedit RAB.');
        }

        // Only allow updating draft, rejected, or submitted (diajukan) RABs
        if (!in_array($rab->status, [RabStatus::DRAFT, RabStatus::DITOLAK, RabStatus::DIAJUKAN])) {
            return back()->with('error', 'RAB ini tidak dapat diedit.');
        }

        $request->validate([
            'rab_number' => 'required|string|unique:rabs,rab_number,' . $rab->id,
            'request_date' => 'required|date',
            'expense_type_id' => 'required|exists:expense_types,id',
            'period_month' => 'nullable|string',
            'description' => 'nullable|string',
            'action' => 'required|in:draft,submit',
        ]);

        $expenseType = ExpenseType::findOrFail($request->expense_type_id);
        $this->validateExpenseItems($request, $expenseType->code);

        // Auto-derive period_year from request_date
        $periodYear = date('Y', strtotime($request->request_date));

        DB::beginTransaction();

        try {
            $oldValues = $rab->toArray();

            $rab->update([
                'rab_number' => $request->rab_number,
                'request_date' => $request->request_date,
                'period_month' => $request->period_month,
                'period_year' => $periodYear,
                'expense_type_id' => $request->expense_type_id,
                'description' => $request->description,
                'status' => $request->action === 'submit' ? RabStatus::DIAJUKAN : RabStatus::DRAFT,
                'submitted_at' => $request->action === 'submit' ? now() : $rab->submitted_at,
            ]);

            // Delete old items and store new ones
            $rab->operationalExpenseItems()->delete();
            $rab->pettyCashItems()->delete();
            $rab->salaryExpenseItems()->delete();
            $rab->monthlyExpenseItems()->delete();

            $this->storeExpenseItems($rab, $request, $expenseType->code);
            $rab->recalculateTotal();

            // Audit log
            AuditLog::log(
                'update_rab',
                "RAB {$rab->rab_number} diperbarui oleh " . auth()->user()->name,
                rabId: $rab->id,
                oldValues: $oldValues,
                newValues: $rab->fresh()->toArray()
            );
            if ($request->action === 'submit') {
                $rab->notifyRole(
                    UserRole::MANAJER_OPERASIONAL->value,
                    'RAB revisi perlu diperiksa',
                    'Admin Keuangan ' . auth()->user()->name . " mengajukan kembali RAB {$rab->rab_number} untuk " . ($rab->expenseType->name ?? '-'),
                    null,
                    $rab->buildWhatsAppSubmissionMessage(route('manajer.rab.show', $rab))
                );
            }

            DB::commit();

            if ($request->action === 'submit') {
                return redirect()->route('rab.index')
                    ->with('success', 'RAB berhasil diajukan kembali!')
                    ->with('submitted_rab_id', $rab->id)
                    ->with('submitted_rab_number', $rab->rab_number)
                    ->with('submitted_rab_total', $rab->total_amount)
                    ->with('submitted_rab_type', $rab->expenseType->name ?? '-');
            }

            return redirect()->route('rab.show', $rab)
                ->with('success', 'RAB berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified RAB (soft delete).
     */
    public function destroy(Rab $rab)
    {
        // Only allow Admin Keuangan to delete
        if (auth()->user()->role !== UserRole::ADMIN_KEUANGAN) {
            abort(403, 'Hanya Admin Keuangan yang dapat menghapus RAB.');
        }

        if (!in_array($rab->status, [RabStatus::DRAFT, RabStatus::DITOLAK])) {
            return back()->with('error', 'Hanya RAB berstatus Draft atau Ditolak yang dapat dihapus.');
        }

        $rab->delete();

        AuditLog::log(
            'delete_rab',
            "RAB {$rab->rab_number} dihapus oleh " . auth()->user()->name,
            rabId: $rab->id
        );

        return redirect()->route('rab.index')->with('success', 'RAB berhasil dihapus.');
    }

    /**
     * Submit a draft RAB.
     */
    public function submit(Rab $rab)
    {
        if ($rab->status !== RabStatus::DRAFT) {
            return back()->with('error', 'Hanya RAB berstatus Draft yang dapat diajukan.');
        }

        $rab->update([
            'status' => RabStatus::DIAJUKAN,
            'submitted_at' => now(),
        ]);

        AuditLog::log(
            'submit_rab',
            "RAB {$rab->rab_number} diajukan oleh " . auth()->user()->name,
            rabId: $rab->id
        );


        $rab->notifyRole(
            UserRole::MANAJER_OPERASIONAL->value,
            'RAB baru perlu diperiksa',
            'Admin Keuangan ' . auth()->user()->name . " mengajukan RAB {$rab->rab_number} untuk " . ($rab->expenseType->name ?? '-')
        );

        return redirect()->route('rab.index')
            ->with('success', 'RAB berhasil diajukan!')
            ->with('submitted_rab_id', $rab->id)
            ->with('submitted_rab_number', $rab->rab_number)
            ->with('submitted_rab_total', $rab->total_amount)
            ->with('submitted_rab_type', $rab->expenseType->name ?? '-');
    }

    // ── Private Helpers ──

    private function validateExpenseItems(Request $request, string $expenseCode): void
    {
        switch ($expenseCode) {
            case 'operasional':
                $request->validate([
                    'op_groups' => 'required|array|size:5',
                    'op_groups.*.name' => 'required|string',
                    'op_groups.*.items' => 'required|array|min:1',
                    'op_groups.*.items.*.item_name' => 'required|string',
                    'op_groups.*.items.*.volume' => 'required|numeric|min:0.01',
                    'op_groups.*.items.*.unit' => 'required|string',
                    'op_groups.*.items.*.unit_price' => 'required|numeric|min:0',
                ]);
                break;
            case 'petty_cash':
                $request->validate([
                    'items' => 'required|array|min:1',
                    'items.*.expense_name' => 'required|string',
                    'items.*.transaction_date' => 'required|date',
                    'items.*.volume' => 'required|numeric|min:0.01',
                    'items.*.unit' => 'required|string',
                    'items.*.unit_price' => 'required|numeric|min:0',
                ]);
                break;
            case 'gaji':
                $request->validate([
                    'items' => 'required|array|min:1',
                    'items.*.employee_name' => 'required|string',
                    'items.*.position' => 'required|string',
                    'items.*.bank_account_number' => 'required|string',
                    'items.*.attendance_days' => 'required|integer|min:0',
                    'items.*.base_salary' => 'required|numeric|min:0',
                ]);
                break;
            case 'bulanan':
                $request->validate([
                    'items' => 'required|array|min:1',
                    'items.*.payment_name' => 'required|string',
                    'items.*.total_expense' => 'required|numeric|min:0',
                    'items.*.transaction_date' => 'required|date',
                ]);
                break;
        }
    }

    private function storeExpenseItems(Rab $rab, Request $request, string $expenseCode): void
    {
        if ($expenseCode === 'operasional') {
            foreach ($request->op_groups as $group) {
                foreach ($group['items'] as $item) {
                    OperationalExpenseItem::create([
                        'rab_id' => $rab->id,
                        'group_name' => $group['name'],
                        'item_name' => $item['item_name'],
                        'volume' => $item['volume'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['volume'] * $item['unit_price'],
                        'note' => $item['note'] ?? null,
                    ]);
                }
            }
            return;
        }

        foreach ($request->items as $item) {
            switch ($expenseCode) {
                case 'petty_cash':
                    $nominal = ($item['volume'] ?? 1) * ($item['unit_price'] ?? 0);
                    $adminFee = $item['admin_fee'] ?? 0;
                    PettyCashItem::create([
                        'rab_id' => $rab->id,
                        'expense_name' => $item['expense_name'],
                        'description' => $item['description'] ?? null,
                        'volume' => $item['volume'] ?? 1,
                        'unit' => $item['unit'] ?? 'pcs',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'transaction_date' => $item['transaction_date'],
                        'nominal' => $nominal,
                        'admin_fee' => $adminFee,
                        'total' => $nominal + $adminFee,
                    ]);
                    break;
                case 'gaji':
                    $attendanceDays = (int) ($item['attendance_days'] ?? 0);
                    $baseSalary = (float) ($item['base_salary'] ?? 0);
                    $mealDaily = (float) ($item['meal_allowance_daily'] ?? 0);
                    $transportDaily = (float) ($item['transport_daily'] ?? 20000);
                    $overtime = (float) ($item['overtime'] ?? 0);
                    $totalSalary = $baseSalary + ($attendanceDays * $mealDaily) + ($attendanceDays * $transportDaily) + $overtime;

                    SalaryExpenseItem::create([
                        'rab_id' => $rab->id,
                        'employee_name' => $item['employee_name'],
                        'position' => $item['position'] ?? null,
                        'bank_account_number' => $item['bank_account_number'] ?? '',
                        'bank_name' => $item['bank_name'] ?? '',
                        'attendance_days' => $attendanceDays,
                        'base_salary' => $baseSalary,
                        'meal_allowance_daily' => $mealDaily,
                        'transport_daily' => $transportDaily,
                        'overtime' => $overtime,
                        'total_salary' => $totalSalary,
                        'salary_nominal' => $totalSalary,
                        'notes' => $item['notes'] ?? null,
                        'description' => $item['description'] ?? null,
                    ]);
                    break;
                case 'bulanan':
                    $totalExpense = (float) ($item['total_expense'] ?? 0);
                    $adminFee = (float) ($item['admin_fee'] ?? 0);
                    MonthlyExpenseItem::create([
                        'rab_id' => $rab->id,
                        'payment_name' => $item['payment_name'],
                        'registration_number' => $item['registration_number'] ?? null,
                        'account_name' => $item['account_name'] ?? null,
                        'period' => $item['period'] ?? null,
                        'description' => $item['description'] ?? null,
                        'total_expense' => $totalExpense,
                        'bill_nominal' => $totalExpense,
                        'admin_fee' => $adminFee,
                        'total_payment' => $totalExpense + $adminFee,
                        'transaction_date' => $item['transaction_date'] ?? null,
                    ]);
                    break;
            }
        }
    }

    /**
     * Export RAB to PDF.
     */
    public function exportPdf(Rab $rab)
    {
        abort_unless(
            auth()->user()?->isAdmin() || auth()->user()?->isManajer(),
            403,
            'Hanya Admin Keuangan dan Manajer Operasional yang dapat mengunduh PDF RAB.'
        );

        $rab->load(['user', 'expenseType', 'payment']);
        $expenseItems = $rab->getExpenseItems();

        $companyName    = \App\Models\Setting::getValue('company_name', 'PT Sertifikasi Bermutu Ketenagalistrikan');
        $companyAddress = \App\Models\Setting::getValue('company_address', '-');
        $companyPhone   = \App\Models\Setting::getValue('company_phone', '-');
        $companyEmail   = \App\Models\Setting::getValue('company_email', '-');
        
        // Ambil nama Manajer Operasional (prioritaskan yang meng-approve, jika belum ada ambil dari database user)
        $managerApproval = $rab->approvals->where('role', \App\Enums\UserRole::MANAJER_OPERASIONAL->value)->where('status', \App\Enums\ApprovalStatus::APPROVED)->first();
        if ($managerApproval && $managerApproval->user) {
            $signerName = $managerApproval->user->name;
        } else {
            $manager = \App\Models\User::where('role', \App\Enums\UserRole::MANAJER_OPERASIONAL->value)->first();
            $signerName = $manager ? $manager->name : 'Mery Eryanti';
        }
        
        $signerPosition = 'Manajer Operasional';

        $data = compact(
            'rab', 'expenseItems',
            'companyName', 'companyAddress', 'companyPhone', 'companyEmail',
            'signerName', 'signerPosition'
        );

        $pdf = Pdf::loadView('rab.pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download('RAB_' . str_replace('/', '_', $rab->rab_number) . '.pdf');
    }
}
