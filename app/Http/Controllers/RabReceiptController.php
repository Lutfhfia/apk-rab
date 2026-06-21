<?php

namespace App\Http\Controllers;

use App\Enums\RabReceiptStatus;
use App\Enums\RabStatus;
use App\Enums\UserRole;
use App\Enums\CashFlowType;
use App\Models\AuditLog;
use App\Models\Rab;
use App\Models\RabReceipt;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RabReceiptController extends Controller
{
    public function adminInputIndex(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $query = Rab::with([
            'user',
            'expenseType',
            'operationalExpenseItems',
            'pettyCashItems',
            'salaryExpenseItems',
            'monthlyExpenseItems',
            'pnbpExpenseItems',
            'approvals.user',
            'discussions.user',
            'payment.paidBy',
            'payment.validator',
            'receipts.uploader',
            'receipts.validator',
            'auditLogs.user',
        ])
            ->whereHas('payment') // Hanya RAB yang sudah dicairkan
            ->where('user_id', auth()->id()) // Hanya RAB milik admin ini
            ->latest('id');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('rab_number', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $rabs = $query->paginate(15);

        // Filter based on user preference if they want tabs, but for now we just show all "Dicairkan"
        // and sort or visually distinct them by LPJ Status in the view.
        // Actually, the user asked for tabs: [Menunggu Upload Nota] [Menunggu Validasi] [Ditolak] [Valid]
        // Since we compute lpj_status via accessor, filtering by DB query is tricky because it's a computed property based on relations.
        // Let's filter via Collection if tab is selected, but pagination will break.
        // Alternatively, we can use whereHas or whereDoesntHave for receipts.
        
        $tab = $request->input('tab', 'semua');
        if ($tab === 'belum_upload') {
            $query->whereDoesntHave('receipts');
        } elseif ($tab === 'menunggu_validasi') {
            $query->whereHas('receipts', function ($q) {
                $q->where('status', RabReceiptStatus::MENUNGGU_VALIDASI->value);
            });
        } elseif ($tab === 'ditolak') {
            $query->whereHas('receipts', function ($q) {
                $q->where('status', RabReceiptStatus::DITOLAK->value);
            })->whereDoesntHave('receipts', function ($q) {
                $q->whereIn('status', [RabReceiptStatus::VALID->value, RabReceiptStatus::MENUNGGU_VALIDASI->value]);
            });
        } elseif ($tab === 'valid') {
            $query->whereHas('receipts', function ($q) {
                $q->where('status', RabReceiptStatus::VALID->value);
            });
        }

        $rabs = $query->paginate(15)->withQueryString();

        return view('admin.input-nota.index', compact('rabs', 'tab'));
    }

    public function store(Request $request, Rab $rab)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (!in_array($rab->status, [RabStatus::DISETUJUI, RabStatus::SELESAI])) {
            return back()->with('error', 'Nota LPJ hanya dapat diunggah untuk RAB yang sudah disetujui.');
        }

        if (!$rab->payment()->exists()) {
            return back()->with('error', 'Nota LPJ hanya dapat diunggah setelah Manajer Keuangan mentransfer dana.');
        }


        $request->merge([
            'total_amount' => $this->normalizeMoney($request->total_amount),
        ]);

        $request->validate([
            'receipt_date' => 'required|date',
            'store_name' => 'required|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:1',
            'receipt_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate receipt number within the same RAB (only among active receipts)
        if ($request->filled('receipt_number')) {
            $duplicate = $rab->receipts()
                ->where('receipt_number', $request->receipt_number)
                ->where('status', '!=', RabReceiptStatus::DITOLAK->value)
                ->exists();
            if ($duplicate) {
                return back()->with('error', 'Nomor Nota sudah ada untuk RAB ini. Silakan gunakan nomor lain.');
            }
        }

        // Always create a new receipt, allowing multiple uploads per RAB
        $path = $request->file('receipt_file')->store('rab-receipts', 'public');

        $receipt = RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => auth()->id(),
            'receipt_date' => $request->receipt_date,
            'store_name' => $request->store_name,
            'receipt_number' => $request->receipt_number,
            'total_amount' => $request->total_amount,
            'receipt_file' => $path,
            'status' => RabReceiptStatus::MENUNGGU_VALIDASI,
            'notes' => $request->notes,
        ]);

        AuditLog::log(
            'upload_receipt_lpj',
            "Nota LPJ {$receipt->store_name} untuk RAB {$rab->rab_number} diunggah oleh " . auth()->user()->name,
            rabId: $rab->id
        );

        $rab->notifyRole(
            UserRole::MANAJER_KEUANGAN->value,
            'Nota LPJ perlu divalidasi',
            'Admin Keuangan ' . auth()->user()->name . " mengunggah nota LPJ untuk RAB {$rab->rab_number}.",
            null
        );

        return redirect()->route('admin.input-nota.index', [
            'tab' => 'menunggu_validasi',
            'open_rab_id' => $rab->id
        ])->with('success', 'Nota LPJ berhasil diunggah dan menunggu validasi Manajer Keuangan.');
    }

    public function approve(\App\Models\Rab $rab, RabReceipt $receipt)
    {
        abort_unless(auth()->user()?->isManajer(), 403);

        if ($receipt->status !== RabReceiptStatus::MENUNGGU_VALIDASI) {
            return back()->with('error', 'Nota LPJ ini sudah diproses.');
        }

        DB::beginTransaction();

        try {
            $receipt = RabReceipt::with('rab.payment')->whereKey($receipt->id)->lockForUpdate()->firstOrFail();

            if ($receipt->status !== RabReceiptStatus::MENUNGGU_VALIDASI) {
                DB::rollBack();
                return back()->with('error', 'Nota LPJ ini sudah diproses.');
            }

            $lastBalance = CashFlow::orderBy('id', 'desc')->value('balance') ?? 0;
            if ((float) $receipt->total_amount > (float) $lastBalance) {
                DB::rollBack();
                return back()->with('error', 'Saldo kas/bank tidak mencukupi untuk memvalidasi nota LPJ ini. Saldo tersedia: Rp ' . number_format($lastBalance, 0, ',', '.'));
            }

            // Create cash flow record for Dana Keluar (Debit)
            CashFlow::create([
                'rab_id' => $receipt->rab_id,
                'payment_id' => $receipt->rab->payment ? $receipt->rab->payment->id : null,
                'transaction_date' => $receipt->receipt_date,
                'type' => CashFlowType::DANA_KELUAR,
                'description' => "Realisasi belanja / LPJ RAB {$receipt->rab->rab_number}: {$receipt->store_name}",
                'debit' => $receipt->total_amount,
                'credit' => 0,
                'balance' => $lastBalance - $receipt->total_amount,
                'proof_file_path' => $receipt->receipt_file,
                'created_by' => $receipt->uploaded_by,
            ]);

            $receipt->update([
                'status' => RabReceiptStatus::VALID,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            // Check if this is the last receipt by seeing if all other receipts are either VALID or DITOLAK
            $pendingReceipts = $receipt->rab->receipts()
                ->where('status', RabReceiptStatus::MENUNGGU_VALIDASI->value)
                ->exists();

            // Mark RAB as SELESAI only if this was the last pending receipt
            if (!$pendingReceipts) {
                $receipt->rab->update([
                    'status' => RabStatus::SELESAI,
                    'completed_at' => now(),
                ]);
            }

            AuditLog::log(
                'validate_receipt_lpj',
                "Nota LPJ {$receipt->store_name} untuk RAB {$receipt->rab->rab_number} divalidasi oleh " . auth()->user()->name,
                rabId: $receipt->rab_id
            );

            // Kirim notifikasi ke uploader
            $receipt->rab->notifyUser(
                $receipt->uploaded_by,
                'Nota LPJ Disetujui',
                "Nota LPJ dari {$receipt->store_name} untuk RAB {$receipt->rab->rab_number} senilai Rp " . number_format($receipt->total_amount, 0, ',', '.') . " telah disetujui oleh Manajer Keuangan."
            );

            // Kirim notifikasi ke Direktur
            $receipt->rab->notifyRole(
                UserRole::DIREKTUR->value,
                'Nota LPJ Disetujui',
                "Nota LPJ dari {$receipt->store_name} untuk RAB {$receipt->rab->rab_number} senilai Rp " . number_format($receipt->total_amount, 0, ',', '.') . " telah disetujui oleh Manajer Keuangan."
            );

            DB::commit();
            return back()->with('success', 'Nota LPJ berhasil divalidasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, \App\Models\Rab $rab, RabReceipt $receipt)
    {
        abort_unless(auth()->user()?->isManajer(), 403);

        if ($receipt->status !== RabReceiptStatus::MENUNGGU_VALIDASI) {
            return back()->with('error', 'Nota LPJ ini sudah diproses.');
        }

        $request->validate([
            'notes' => 'required|string',
        ]);

        $receipt->update([
            'status' => RabReceiptStatus::DITOLAK,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'notes' => $request->notes,
        ]);

        AuditLog::log(
            'reject_receipt_lpj',
            "Nota LPJ {$receipt->store_name} untuk RAB {$receipt->rab->rab_number} ditolak oleh " . auth()->user()->name,
            rabId: $receipt->rab_id
        );

        // Kirim notifikasi ke uploader
        $receipt->rab->notifyUser(
            $receipt->uploaded_by,
            'Nota LPJ Ditolak',
            "Nota LPJ dari {$receipt->store_name} untuk RAB {$receipt->rab->rab_number} senilai Rp " . number_format($receipt->total_amount, 0, ',', '.') . " ditolak oleh Manajer Keuangan. Catatan: {$receipt->notes}"
        );

        // Kirim notifikasi ke Direktur
        $receipt->rab->notifyRole(
            UserRole::DIREKTUR->value,
            'Nota LPJ Ditolak',
            "Nota LPJ dari {$receipt->store_name} untuk RAB {$receipt->rab->rab_number} senilai Rp " . number_format($receipt->total_amount, 0, ',', '.') . " ditolak oleh Manajer Keuangan. Catatan: {$receipt->notes}"
        );

        return back()->with('success', 'Nota LPJ ditolak dan catatan penolakan berhasil disimpan.');
    }

    private function normalizeMoney($value): string
    {
        return str_replace(',', '.', str_replace('.', '', (string) $value));
    }
}
