<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\RabPayment;
use App\Models\RabReceipt;
use App\Models\CashFlow;
use App\Enums\RabReceiptStatus;
use App\Models\AuditLog;
use App\Enums\PaymentValidationStatus;
use App\Enums\RabStatus;
use App\Enums\CashFlowType;
use App\Enums\UserRole;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function validationIndex(Request $request)
    {
        $user = auth()->user();
        abort_unless($user?->isManajer() || $user?->isAdmin() || $user?->isDirektur(), 403);

        $activeTab = in_array($request->input('tab'), ['pending', 'history', 'recap'], true)
            ? $request->input('tab')
            : 'pending';

        if ($user?->isDirektur()) {
            $activeTab = 'recap';
        }

        $pendingSort = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $pendingQuery = RabReceipt::with(['rab.user', 'rab.expenseType', 'uploader', 'validator'])
            ->where('status', RabReceiptStatus::MENUNGGU_VALIDASI->value)
            ->whereHas('rab'); // Pastikan RAB-nya masih ada

        if ($request->filled('start_date')) {
            $pendingQuery->whereDate('receipt_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $pendingQuery->whereDate('receipt_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $pendingQuery->where(function ($q) use ($request) {
                $q->where('store_name', 'like', '%' . $request->search . '%')
                    ->orWhere('receipt_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('rab', fn ($rabQuery) => $rabQuery->where('rab_number', 'like', '%' . $request->search . '%'));
            });
        }

        if ($pendingSort === 'asc') {
            $pendingQuery->orderBy('receipt_date', 'asc')->orderBy('id', 'asc');
        } else {
            $pendingQuery->orderBy('receipt_date', 'desc')->orderBy('id', 'desc');
        }

        $receipts = $pendingQuery->paginate(15, ['*'], 'pending_page')->withQueryString();

        $historySort = $request->input('history_sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $historyQuery = RabReceipt::with(['rab.user', 'rab.expenseType', 'uploader', 'validator'])
            ->whereIn('status', [RabReceiptStatus::VALID->value, RabReceiptStatus::DITOLAK->value])
            ->whereHas('rab');

        if ($request->filled('history_start_date')) {
            $historyQuery->whereDate('receipt_date', '>=', $request->history_start_date);
        }
        if ($request->filled('history_end_date')) {
            $historyQuery->whereDate('receipt_date', '<=', $request->history_end_date);
        }

        if ($request->filled('history_search')) {
            $historyQuery->where(function ($q) use ($request) {
                $q->where('store_name', 'like', '%' . $request->history_search . '%')
                    ->orWhere('receipt_number', 'like', '%' . $request->history_search . '%')
                    ->orWhere('notes', 'like', '%' . $request->history_search . '%')
                    ->orWhereHas('rab', fn ($rabQuery) => $rabQuery->where('rab_number', 'like', '%' . $request->history_search . '%'));
            });
        }

        if ($historySort === 'asc') {
            $historyQuery->orderBy('validated_at', 'asc')->orderBy('id', 'asc');
        } else {
            $historyQuery->orderBy('validated_at', 'desc')->orderBy('id', 'desc');
        }

        $historyReceipts = $historyQuery->paginate(15, ['*'], 'history_page')->withQueryString();
        $recap = $this->buildRecapData($request);

        $routePrefix = $user?->isManajer() ? 'manajer' : ($user?->isDirektur() ? 'direktur' : 'admin');

        return view('rab-receipts.index', array_merge(
            compact('activeTab', 'receipts', 'historyReceipts', 'routePrefix'),
            $recap
        ));
    }

    public function recapPdf(Request $request)
    {
        abort_unless(auth()->user()?->isManajer() || auth()->user()?->isDirektur(), 403);

        $recap = $this->buildRecapData($request);
        $companyName = Setting::getValue('company_name', 'PT Sertifikasi Bermutu Ketenagalistrikan');
        $printedBy = auth()->user()->name ?? '-';
        $printDate = Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i');

        $pdf = Pdf::loadView('rab-receipts.pdf', array_merge($recap, compact(
            'companyName',
            'printedBy',
            'printDate'
        )))->setPaper('a4', 'landscape');

        $filename = 'Rekap_LPJ_Nota_' . str_replace([' ', '/', '-'], '_', $recap['periodLabel']) . '.pdf';

        if ($request->input('mode') === 'preview') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }



    /**
     * Menampilkan form input pencairan dana untuk RAB yang disetujui.
     */
    public function create(Rab $rab)
    {
        abort_unless(auth()->user()?->isManajer(), 403);

        if ($rab->status !== RabStatus::DISETUJUI) {
            return back()->with('error', 'Hanya RAB berstatus Disetujui yang dapat dicairkan dananya.');
        }
        return view('payments.create', compact('rab'));
    }

    /**
     * Menyimpan data dan bukti pencairan dana transfer dari Manager ke Admin, serta mencatat ke arus kas.
     */
    public function store(Request $request, Rab $rab)
    {
        abort_unless(auth()->user()?->isManajer(), 403);

        if ($rab->status !== RabStatus::DISETUJUI) {
            return back()->with('error', 'Hanya RAB berstatus Disetujui yang dapat dicairkan dananya.');
        }

        // Normalisasi input nominal pembayaran/realisasi
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

        $existingPayment = $rab->payment()->latest()->first();

        if ($existingPayment) {
            return back()->with('error', 'Dana untuk RAB ini sudah dicairkan.');
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
                'validation_status' => PaymentValidationStatus::VALID, // Automatically VALID
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            $lastBalance = CashFlow::orderBy('id', 'desc')->value('balance') ?? 0;

            // Record as DANA_MASUK (Kredit) immediately
            CashFlow::create([
                'rab_id' => $rab->id,
                'payment_id' => $payment->id,
                'transaction_date' => $payment->payment_date,
                'type' => CashFlowType::DANA_MASUK,
                'description' => "Pencairan dana transfer ke Admin untuk RAB {$rab->rab_number}",
                'debit' => 0,
                'credit' => $payment->paid_amount,
                'balance' => $lastBalance + $payment->paid_amount,
                'proof_file_path' => $payment->proof_file_path,
                'created_by' => auth()->id(),
            ]);

            AuditLog::log('upload_payment', "Pencairan dana transfer ke Admin untuk RAB {$rab->rab_number} oleh " . auth()->user()->name, rabId: $rab->id);

            // Notify Admin Keuangan (owner of RAB)
            $rab->notifyUser(
                $rab->user_id,
                'Dana RAB Dicairkan',
                "Manajer Keuangan telah mencairkan dana untuk RAB {$rab->rab_number} sebesar Rp " . number_format($payment->paid_amount, 0, ',', '.') . ". Silakan belanjakan sesuai RAB dan unggah nota LPJ."
            );

            DB::commit();
            return back()->with('success', 'Dana RAB berhasil dicairkan dan tercatat di Arus Kas (Dana Masuk).');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menghapus format ribuan (titik) dan mengganti koma desimal menjadi titik desimal.
     */
    private function normalizeMoney($value): string
    {
        return str_replace(',', '.', str_replace('.', '', (string) $value));
    }

    private function buildRecapData(Request $request): array
    {
        $allowedRanges = [1, 3, 6, 9, 12];
        $range = (int) $request->input('range', 1);
        $range = in_array($range, $allowedRanges, true) ? $range : 1;

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = $year >= 2000 && $year <= 2100 ? $year : now()->year;

        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $startDate = Carbon::create($year, $month, 1)->subMonths($range - 1)->startOfMonth();

        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $status = RabReceiptStatus::VALID->value;
        $statusOptions = [
            RabReceiptStatus::VALID->value => 'Disetujui / Siap Export',
        ];

        $recapQuery = RabReceipt::with(['rab.expenseType', 'uploader', 'validator'])
            ->whereDate('receipt_date', '>=', $startDate->toDateString())
            ->whereDate('receipt_date', '<=', $endDate->toDateString())
            ->whereNotNull('status')
            ->orderBy('receipt_date')
            ->orderBy('id');

        $recapQuery->where('status', RabReceiptStatus::VALID->value)
            ->whereHas('rab');

        if ($request->filled('recap_search')) {
            $recapQuery->where(function ($q) use ($request) {
                $q->where('store_name', 'like', '%' . $request->recap_search . '%')
                    ->orWhereHas('rab', fn ($rabQuery) => $rabQuery->where('rab_number', 'like', '%' . $request->recap_search . '%'));
            });
        }

        $recapReceipts = $recapQuery->get();
        $validReceipts = $recapReceipts->where('status', RabReceiptStatus::VALID);

        $periodLabel = $startDate->translatedFormat('d F Y') . ' - ' . $endDate->translatedFormat('d F Y');
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $rabIds = $recapReceipts->pluck('rab_id')->unique();

        return [
            'recapReceipts' => $recapReceipts,
            'allowedRanges' => $allowedRanges,
            'range' => $range,
            'month' => $month,
            'year' => $year,
            'months' => $months,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => $periodLabel,
            'recapStatus' => $status,
            'statusOptions' => $statusOptions,
            'totalValidAmount' => $validReceipts->sum('total_amount'),
            'totalSelectedAmount' => $recapReceipts->sum('total_amount'),
            'totalRabCount' => $rabIds->count(),
            'totalPaymentCount' => $recapReceipts->count(),
            'totalValidPaymentCount' => $validReceipts->count(),
        ];
    }
}
