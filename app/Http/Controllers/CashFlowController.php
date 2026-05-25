<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Rab;
use App\Enums\CashFlowType;
use App\Enums\RabStatus;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        abort_if(auth()->user()?->isAdmin(), 403, 'Admin Keuangan tidak memiliki akses ke arus kas.');

        $query = CashFlow::with(['rab', 'payment', 'createdBy']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $sortDir = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        $cashFlows = $query
            ->orderBy('transaction_date', $sortDir)
            ->orderBy('id', $sortDir)
            ->paginate(20);
        $totalDebit = CashFlow::sum('debit');
        $totalCredit = CashFlow::sum('credit');
        $currentBalance = CashFlow::latest()->value('balance') ?? 0;

        $approvedRabs = Rab::where('status', RabStatus::DISETUJUI)->get();

        return view('cash-flows.index', compact('cashFlows', 'totalDebit', 'totalCredit', 'currentBalance', 'approvedRabs'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->isManajer(), 403, 'Hanya Manajer Keuangan yang dapat mencatat dana masuk.');

        $request->merge([
            'amount' => $this->normalizeMoney($request->amount),
        ]);

        $request->validate([
            'transaction_date' => 'required|date',
            'type' => 'required|in:saldo_awal,dana_masuk,dana_keluar',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'rab_id' => 'nullable|exists:rabs,id',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lastBalance = CashFlow::latest('id')->value('balance') ?? 0;

        if ($request->type === 'dana_keluar' && $request->amount > $lastBalance) {
            return back()->withInput()->withErrors(['amount' => 'Saldo tidak mencukupi untuk transaksi dana keluar ini. Saldo tersedia: Rp ' . number_format($lastBalance, 0, ',', '.')]);
        }

        $debit = in_array($request->type, ['saldo_awal', 'dana_masuk']) ? $request->amount : 0;
        $credit = $request->type === 'dana_keluar' ? $request->amount : 0;
        $newBalance = $lastBalance + $debit - $credit;

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('cashflow-proofs', 'public');
        }

        DB::beginTransaction();

        try {
            CashFlow::create([
                'rab_id' => $request->type === 'dana_keluar' ? $request->rab_id : null,
                'transaction_date' => $request->transaction_date,
                'type' => $request->type,
                'description' => $request->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'proof_file_path' => $proofPath,
                'created_by' => auth()->id(),
            ]);

            if ($request->type === 'dana_keluar' && $request->rab_id) {
                $rab = Rab::find($request->rab_id);
                if ($rab && $rab->status === RabStatus::DISETUJUI) {
                    $rab->update(['status' => RabStatus::SELESAI, 'completed_at' => now()]);
                }
            }

            AuditLog::log('create_cashflow', "Transaksi arus kas dicatat: {$request->description}");

            DB::commit();

            return redirect()->route('manajer.cash-flow.index')->with('success', 'Transaksi arus kas berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }

    private function normalizeMoney($value): string
    {
        return str_replace(',', '.', str_replace('.', '', (string) $value));
    }
}
