<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\RabPayment;
use App\Models\CashFlow;
use App\Models\AuditLog;
use App\Enums\RabStatus;
use App\Enums\CashFlowType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create(Rab $rab)
    {
        if ($rab->status !== RabStatus::DISETUJUI) {
            return back()->with('error', 'Hanya RAB berstatus Disetujui yang dapat dibayar.');
        }
        return view('payments.create', compact('rab'));
    }

    public function store(Request $request, Rab $rab)
    {
        if ($rab->status !== RabStatus::DISETUJUI) {
            return back()->with('error', 'Hanya RAB berstatus Disetujui yang dapat dibayar.');
        }

        $request->merge([
            'paid_amount' => $this->normalizeMoney($request->paid_amount),
        ]);

        $request->validate([
            'payment_date' => 'required|date',
            'paid_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'recipient_account' => 'nullable|string',
            'recipient_name' => 'nullable|string',
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:1024',
            'notes' => 'nullable|string',
        ]);

        $lastBalance = CashFlow::orderBy('id', 'desc')->value('balance') ?? 0;
        if ((float) $request->paid_amount > (float) $lastBalance) {
            return back()->withInput()->with('error', 'Gagal: Saldo Kas/Bank saat ini tidak mencukupi untuk melakukan pembayaran ini. Saldo tersedia: Rp ' . number_format($lastBalance, 0, ',', '.'));
        }

        DB::beginTransaction();
        try {
            $proofPath = $request->file('proof_file')->store('payment-proofs', 'public');

            $payment = RabPayment::create([
                'rab_id' => $rab->id,
                'paid_by' => auth()->id(),
                'payment_date' => $request->payment_date,
                'paid_amount' => $request->paid_amount,
                'payment_method' => $request->payment_method,
                'recipient_account' => $request->recipient_account,
                'recipient_name' => $request->recipient_name,
                'proof_file_path' => $proofPath,
                'notes' => $request->notes,
            ]);

            $rab->update(['status' => RabStatus::SELESAI, 'completed_at' => now()]);

            // 2. Record DANA KELUAR (pembayaran kebutuhan RAB oleh Admin)
            $lastBalance2 = CashFlow::orderBy('id', 'desc')->value('balance') ?? 0;
            CashFlow::create([
                'rab_id' => $rab->id,
                'payment_id' => $payment->id,
                'transaction_date' => $request->payment_date,
                'type' => CashFlowType::DANA_KELUAR,
                'description' => "Pembayaran kebutuhan RAB {$rab->rab_number}",
                'debit' => 0,
                'credit' => $request->paid_amount,
                'balance' => $lastBalance2 - $request->paid_amount,
                'proof_file_path' => $proofPath,
                'created_by' => auth()->id(),
            ]);

            AuditLog::log('upload_payment', "Pembayaran RAB {$rab->rab_number} oleh " . auth()->user()->name, rabId: $rab->id);

            DB::commit();
            return back()->with('success', 'Upload bukti pembayaran berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function normalizeMoney($value): string
    {
        return str_replace(',', '.', str_replace('.', '', (string) $value));
    }
}
