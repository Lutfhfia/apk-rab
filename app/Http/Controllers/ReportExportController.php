<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\CashFlow;
use App\Models\ReportExport;
use App\Models\Setting;
use App\Enums\RabStatus;
use App\Enums\CashFlowType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportExportController extends Controller
{
    /**
     * Halaman utama Export Laporan Arus Kas.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_if($user?->isAdmin(), 403, 'Admin Keuangan tidak memiliki akses ke laporan arus kas.');

        $canPreview = $user?->isManajer() || $user?->isDirektur();
        $canExportCashFlowPdf = $user?->isManajer();
        $sidebarRole = $user?->isManajer() ? 'manajer' : ($user?->isDirektur() ? 'direktur' : 'admin');

        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);
        $search = $request->input('search');
        $range = $request->input('range', 1);
        $reportNumber = $request->input('report_number') ?: $this->previewReportNumber((int) $month, (int) $year);

        // Hitung rentang periode
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $startDate = Carbon::create($year, $month, 1)->subMonths($range - 1)->startOfMonth();

        // Ambil semua transaksi arus kas pada periode
        $cfQuery = CashFlow::with(['rab', 'rab.expenseType'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($search) {
            $cfQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('rab', function ($sub) use ($search) {
                      $sub->where('rab_number', 'like', "%{$search}%");
                  });
            });
        }

        $cashFlows = $cfQuery->get();

        // Hitung ringkasan
        $totalTransaksi  = $cashFlows->count();
        $totalUangMasuk  = $cashFlows->sum('debit');
        $totalUangKeluar = $cashFlows->sum('credit');

        // Saldo awal: ambil balance terakhir SEBELUM startDate
        $saldoAwalRow = CashFlow::where('transaction_date', '<', $startDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $saldoAwal = $saldoAwalRow ? (float) $saldoAwalRow->balance : 0;

        // Saldo akhir
        $saldoAkhir = $saldoAwal + $totalUangMasuk - $totalUangKeluar;

        // Data untuk preview
        $showPreview = $request->has('preview') && $canPreview;

        // Build period label
        $bulanList = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        if ($range == 1) {
            $periodLabel = $bulanList[(int)$month] . ' ' . $year;
        } else {
            $periodLabel = $startDate->translatedFormat('F Y') . ' - ' . $endDate->translatedFormat('F Y');
        }

        // Ambil juga data RAB selesai untuk referensi di tabel sumber
        $rabs = Rab::with(['user', 'expenseType', 'payment'])
            ->where('status', RabStatus::SELESAI)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('completed_at')
                        ->whereBetween('completed_at', [$startDate, $endDate]);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNull('completed_at');
                    $cursor = $startDate->copy();
                    $sub->where(function ($inner) use ($cursor, $endDate) {
                        while ($cursor <= $endDate) {
                            $m = $cursor->month;
                            $y = $cursor->year;
                            $inner->orWhere(function ($q2) use ($m, $y) {
                                $q2->where('period_month', $m)->where('period_year', $y);
                            });
                            $cursor->addMonth();
                        }
                    });
                });
            })
            ->latest('completed_at')
            ->get();

        // Printer info
        $printedBy = auth()->user()->name ?? 'Admin Keuangan';

        return view('reports.index', compact(
            'cashFlows',
            'rabs',
            'month',
            'year',
            'search',
            'range',
            'totalTransaksi',
            'totalUangMasuk',
            'totalUangKeluar',
            'saldoAkhir',
            'saldoAwal',
            'showPreview',
            'periodLabel',
            'startDate',
            'endDate',
            'printedBy',
            'reportNumber',
            'canPreview',
            'canExportCashFlowPdf',
            'sidebarRole'
        ));
    }

    /**
     * Export laporan arus kas ke PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_unless(auth()->user()?->isManajer(), 403, 'Hanya Manajer Keuangan yang dapat mengunduh PDF arus kas.');

        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);
        $range = $request->input('range', 1);

        // Hitung rentang periode
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $startDate = Carbon::create($year, $month, 1)->subMonths($range - 1)->startOfMonth();

        // Ambil semua transaksi arus kas pada periode
        $cashFlows = CashFlow::with(['rab', 'rab.expenseType'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $totalUangMasuk  = $cashFlows->sum('debit');
        $totalUangKeluar = $cashFlows->sum('credit');

        // Saldo awal
        $saldoAwalRow = CashFlow::where('transaction_date', '<', $startDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $saldoAwal = $saldoAwalRow ? (float) $saldoAwalRow->balance : 0;
        $saldoAkhir = $saldoAwal + $totalUangMasuk - $totalUangKeluar;

        // Settings
        $companyName    = Setting::getValue('company_name', 'PT Sertifikasi Bermutu Ketenagalistrikan');
        $companyAddress = Setting::getValue('company_address', '-');
        $companyPhone   = Setting::getValue('company_phone', '-');
        $companyEmail   = Setting::getValue('company_email', '-');
        $signerName     = 'Rahmad Hidayad';
        $signerPosition = 'Manajer Keuangan';

        // Nomor laporan
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $reportCount = ReportExport::whereYear('created_at', $year)->count() + 1;
        $reportNumber = $request->input('report_number')
            ?: str_pad($reportCount, 3, '0', STR_PAD_LEFT) . '/LAP-AK/SBK/' . $romanMonths[(int) $month] . '/' . $year;

        // Period label
        if ($range == 1) {
            $periodLabel = Carbon::create($year, $month, 1)->translatedFormat('F Y');
        } else {
            $periodLabel = $startDate->translatedFormat('F Y') . ' - ' . $endDate->translatedFormat('F Y');
        }

        // Printer info
        $printedBy = auth()->user()->name ?? 'Admin Keuangan';
        $printDate = Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y');

        $data = compact(
            'cashFlows', 'totalUangMasuk', 'totalUangKeluar', 'saldoAkhir', 'saldoAwal',
            'companyName', 'companyAddress', 'companyPhone', 'companyEmail',
            'signerName', 'signerPosition', 'reportNumber', 'periodLabel',
            'month', 'year', 'range', 'printedBy', 'printDate'
        );

        // Simpan record export
        ReportExport::create([
            'exported_by'    => auth()->id(),
            'report_type'    => 'laporan_arus_kas_bulanan',
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'file_path'      => null,
            'format'         => 'pdf',
            'total_debit'    => $totalUangMasuk,
            'total_credit'   => $totalUangKeluar,
            'ending_balance' => $saldoAkhir,
        ]);

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Laporan_Arus_Kas_' . str_replace(' ', '_', $periodLabel) . '.pdf';

        return $pdf->download($filename);
    }

    private function previewReportNumber(int $month, int $year): string
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return 'XXX/LAP-AK/SBK/' . $romanMonths[$month] . '/' . $year;
    }
}
