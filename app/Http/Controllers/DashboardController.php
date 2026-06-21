<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\CashFlow;
use App\Models\RabPayment;
use App\Models\RabReceipt;
use App\Enums\RabReceiptStatus;
use App\Models\ExpenseType;
use App\Enums\RabStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menyusun data grafik umum yang digunakan oleh seluruh halaman dashboard.
     */
    private function buildChartData(Request $request)
    {
        $expenseTypes = ExpenseType::where('is_active', true)->get();

        $budgetExpenseTypeId = $request->input('budget_expense_type_id', $request->input('expense_type_id'));
        $budgetPeriod = (int) $request->input('budget_period', $request->input('period', 3));
        $budgetPeriod = in_array($budgetPeriod, [3, 6, 9, 12], true) ? $budgetPeriod : 3;

        $statusPeriod = $request->input('status_period', '1');
        $categoryPeriod = $request->input('category_period', '1');
        $cashflowPeriod = $request->input('cashflow_period', '1');

        $comparisonExpenseTypeId = $request->input('comparison_expense_type_id', null);
        $comparisonPeriod = (int) $request->input('comparison_period', 3);
        $comparisonPeriod = in_array($comparisonPeriod, [3, 6, 9, 12], true) ? $comparisonPeriod : 3;

        // Cek jika default (tidak diisi)
        $isDefaultComparison = !$request->filled('comparison_expense_type_id') || $request->input('comparison_expense_type_id') === '';

        $expenseTypeId = $budgetExpenseTypeId;
        $period = $budgetPeriod;

        $startDate = Carbon::now()->subMonths($budgetPeriod - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfDay();

        $baseRabQuery = Rab::whereBetween('request_date', [$startDate, $endDate]);

        if ($budgetExpenseTypeId) {
            $baseRabQuery->where('expense_type_id', $budgetExpenseTypeId);
        }

        $totalDiajukan = (clone $baseRabQuery)->count();
        $totalDibayar = (clone $baseRabQuery)->where('status', RabStatus::SELESAI)->count();

        $waitingApproval = Rab::whereIn('status', [RabStatus::DIAJUKAN, RabStatus::DISETUJUI_MANAJER])->count();
        $totalDitolak = Rab::where('status', RabStatus::DITOLAK)->count();

        $rabQuery = (clone $baseRabQuery)->where('status', RabStatus::SELESAI);
        $totalNilaiPengajuan = (clone $rabQuery)->sum('total_amount');

        $rabIds = (clone $rabQuery)->pluck('id');
        $totalRealisasi = RabReceipt::whereIn('rab_id', $rabIds)->where('status', RabReceiptStatus::VALID)->sum('total_amount');

        // Chart 1: Tren Anggaran vs Realisasi
        $chartLabels = [];
        $chartAnggaran = [];
        $chartRealisasi = [];

        $rabsForChart = (clone $rabQuery)
            ->with(['receipts' => function($q) {
                $q->where('status', RabReceiptStatus::VALID);
            }])
            ->orderBy('request_date')
            ->get();
        $groupedData = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            $groupedData[$date->translatedFormat('M Y')] = ['anggaran' => 0, 'realisasi' => 0];
        }

        foreach ($rabsForChart as $rab) {
            $key = $rab->request_date->translatedFormat('M Y');
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = ['anggaran' => 0, 'realisasi' => 0];
            }
            $groupedData[$key]['anggaran'] += (float) $rab->total_amount;
            $groupedData[$key]['realisasi'] += (float) $rab->receipts->sum('total_amount');
        }

        foreach ($groupedData as $label => $data) {
            $chartLabels[] = $label;
            $chartAnggaran[] = $data['anggaran'];
            $chartRealisasi[] = $data['realisasi'];
        }

        // Chart 2: Distribusi Status RAB
        $statusQuery = Rab::where('status', RabStatus::SELESAI);
        if ($statusPeriod !== 'semua') {
            $sMonths = (int) $statusPeriod;
            $sStart = Carbon::now()->subMonths(max(0, $sMonths - 1))->startOfMonth();
            $statusQuery->where('request_date', '>=', $sStart);
        }
        $statusRabs = $statusQuery->get();

        $statusLabels = ['Selesai'];
        $statusData = [$statusRabs->count()];

        // Chart 3: Pengeluaran Berdasarkan Kategori
        $catQuery = Rab::where('status', RabStatus::SELESAI);
        if ($categoryPeriod !== 'semua') {
            $cMonths = (int) $categoryPeriod;
            $cStart = Carbon::now()->subMonths(max(0, $cMonths - 1))->startOfMonth();
            $catQuery->where('request_date', '>=', $cStart);
        }

        $categoryDataRaw = $catQuery
            ->join('expense_types', 'rabs.expense_type_id', '=', 'expense_types.id')
            ->selectRaw('expense_types.name as category_name, SUM(rabs.total_amount) as total')
            ->groupBy('expense_types.id', 'expense_types.name')
            ->get();

        $categoryLabels = $categoryDataRaw->pluck('category_name')->toArray();
        $categoryData = $categoryDataRaw->pluck('total')->toArray();

        // Chart 4: Perkembangan Arus Kas
        $cfGrouped = [];

        if ($cashflowPeriod === '1') {
            $cfStart = Carbon::now()->startOfMonth();
            $cfEnd = Carbon::now()->endOfMonth();
            $cashflowQuery = CashFlow::whereBetween('transaction_date', [$cfStart, $cfEnd])
                ->where(function ($query) {
                    $query->whereNull('rab_id')
                        ->orWhereHas('rab', fn ($rabQuery) => $rabQuery->where('status', RabStatus::SELESAI));
                })
                ->orderBy('transaction_date')
                ->get();

            for ($week = 1; $week <= 5; $week++) {
                $cfGrouped['Minggu ' . $week] = ['in' => 0, 'out' => 0];
            }

            foreach ($cashflowQuery as $cf) {
                $key = 'Minggu ' . min((int) ceil(Carbon::parse($cf->transaction_date)->day / 7), 5);
                $cfGrouped[$key]['in'] += (float) $cf->debit;
                $cfGrouped[$key]['out'] += (float) $cf->credit;
            }
        } else {
            $cfMonths = (int) $cashflowPeriod;
            $cfStart = Carbon::now()->subMonths(max(0, $cfMonths - 1))->startOfMonth();
            $cfEnd = Carbon::now()->endOfMonth();

            $cashflowQuery = CashFlow::whereBetween('transaction_date', [$cfStart, $cfEnd])
                ->where(function ($query) {
                    $query->whereNull('rab_id')
                        ->orWhereHas('rab', fn ($rabQuery) => $rabQuery->where('status', RabStatus::SELESAI));
                })
                ->orderBy('transaction_date')
                ->get();

            for ($date = $cfStart->copy(); $date <= $cfEnd; $date->addMonth()) {
                $cfGrouped[$date->translatedFormat('M Y')] = ['in' => 0, 'out' => 0];
            }

            foreach ($cashflowQuery as $cf) {
                $key = Carbon::parse($cf->transaction_date)->translatedFormat('M Y');
                if (isset($cfGrouped[$key])) {
                    $cfGrouped[$key]['in'] += (float) $cf->debit;
                    $cfGrouped[$key]['out'] += (float) $cf->credit;
                }
            }
        }

        $cfLabels = [];
        $cfIn = [];
        $cfOut = [];
        $cfBalance = [];

        $lastCfBeforePeriod = CashFlow::where('transaction_date', '<', $cfStart)
            ->where(function ($query) {
                $query->whereNull('rab_id')
                    ->orWhereHas('rab', fn ($rabQuery) => $rabQuery->where('status', RabStatus::SELESAI));
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->first();

        $runningCashBalance = $lastCfBeforePeriod ? (float) $lastCfBeforePeriod->balance : 0;

        foreach ($cfGrouped as $label => $data) {
            $runningCashBalance += $data['in'] - $data['out'];
            $cfLabels[] = $label;
            $cfIn[] = $data['in'];
            $cfOut[] = $data['out'];
            $cfBalance[] = $runningCashBalance;
        }

        // Chart 5: Perbandingan Pengeluaran per Periode
        $comparisonLabels = [];
        $periods = [];
        $comparisonStart = Carbon::now()->subMonths($comparisonPeriod - 1)->startOfMonth();

        for ($date = $comparisonStart->copy(); $date <= $endDate; $date->addMonth()) {
            $periods[] = $date->format('Y-m');
            $comparisonLabels[] = $date->translatedFormat('M Y');
        }

        // Pemetaan warna berdasarkan kode tipe pengeluaran (Warna sudah dibuat lebih kontras)
        $colorMap = [
            'gaji'        => '#2563eb', // Blue 600 (Biru Tua)
            'operasional' => '#9333ea', // Purple 600 (Ungu)
            'bulanan'     => '#ea580c', // Orange 600 (Oranye Gelap)
            'listrik'     => '#eab308', // Yellow 500 (Kuning)
            'air_pam'     => '#06b6d4', // Cyan 500 (Biru Muda/Cyan)
            'petty_cash'  => '#16a34a', // Green 600 (Hijau)
            'pnbp'        => '#dc2626', // Red 600 (Merah)
        ];

        // Warna cadangan jika ada kategori baru di luar daftar di atas
        $fallbackColors = [
            '#ec4899', // Pink
            '#14b8a6', // Teal
            '#6366f1', // Indigo
            '#84cc16', // Lime
            '#f43f5e', // Rose
            '#64748b', // Slate
            '#d946ef', // Fuchsia
        ];
        $comparisonDatasets = [];
        $comparisonTotalValues = array_fill_keys($periods, 0);

        // MODIFIKASI LOGIKA: Cek apakah user memilih satu kategori spesifik (bukan 'semua')
        if ($comparisonExpenseTypeId && $comparisonExpenseTypeId !== 'semua') {
            $selectedType = $expenseTypes->firstWhere('id', (int) $comparisonExpenseTypeId);
            $selectedCode = $selectedType ? $selectedType->code : 'other';
            $selectedName = $selectedType ? $selectedType->name : 'Pengeluaran';
            $selectedColor = $colorMap[$selectedCode] ?? $fallbackColors[0];

            $dataValues = [];
            foreach ($periods as $periodKey) {
                $dataValues[$periodKey] = 0;
            }

            $comparisonQuery = Rab::where('status', RabStatus::SELESAI)
                ->whereBetween('request_date', [$comparisonStart, $endDate])
                ->where('expense_type_id', $comparisonExpenseTypeId);

            $comparisonQuery->get()->each(function (Rab $rab) use (&$dataValues) {
                $key = $rab->request_date->format('Y-m');
                if (isset($dataValues[$key])) {
                    $dataValues[$key] += (float) $rab->total_amount;
                }
            });
            $comparisonTotalValues = $dataValues;

            $comparisonDatasets[] = [
                'type' => 'bar',
                'label' => $selectedName,
                'data' => array_values($dataValues),
                'backgroundColor' => $selectedColor,
                'borderRadius' => 5
            ];
        } else {
            // MODIFIKASI LOGIKA: Jika 'semua', gunakan seluruh jenis pengeluaran, jika default (empty) hanya air & listrik
            $idx = 0;
            $isAllCategories = ($comparisonExpenseTypeId === 'semua');

            $comparisonTypes = $isAllCategories
                ? $expenseTypes
                : $expenseTypes->whereIn('code', ['air_pam', 'listrik'])->values();

            foreach ($comparisonTypes as $type) {
                $typeCode = $type->code;
                $typeName = $type->name;
                $typeColor = $colorMap[$typeCode] ?? ($fallbackColors[$idx % count($fallbackColors)]);
                $idx++;

                $dataValues = [];
                foreach ($periods as $periodKey) {
                    $dataValues[$periodKey] = 0;
                }

                $comparisonQuery = Rab::where('status', RabStatus::SELESAI)
                    ->whereBetween('request_date', [$comparisonStart, $endDate])
                    ->where('expense_type_id', $type->id);

                $comparisonQuery->get()->each(function (Rab $rab) use (&$dataValues, &$comparisonTotalValues) {
                    $key = $rab->request_date->format('Y-m');
                    if (isset($dataValues[$key])) {
                        $dataValues[$key] += (float) $rab->total_amount;
                        $comparisonTotalValues[$key] += (float) $rab->total_amount;
                    }
                });

                $comparisonDatasets[] = [
                    'type' => 'bar',
                    'label' => $typeName,
                    'data' => array_values($dataValues),
                    'backgroundColor' => $typeColor,
                    'borderRadius' => 5
                ];
            }
        }

        $comparisonDatasets[] = [
            'type' => 'line',
            'label' => $comparisonExpenseTypeId === 'semua' ? 'Tren Semua Kategori' : (($isDefaultComparison) ? 'Tren Air & Listrik' : 'Tren Pengeluaran'),
            'data' => array_values($comparisonTotalValues),
            'borderColor' => '#111827',
            'backgroundColor' => '#111827',
            'borderWidth' => 2,
            'fill' => false,
            'tension' => 0.25,
            'pointRadius' => 5,
            'pointHoverRadius' => 7,
        ];

        $rabTerbaru = (clone $rabQuery)->with(['user', 'expenseType', 'payment'])
            ->latest()
            ->take(15)
            ->get();

        $topSpenders = (clone $rabQuery)
            ->with(['user', 'expenseType', 'payment'])
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        $rabMenungguManajer = Rab::with(['user', 'expenseType'])
            ->where('status', RabStatus::DIAJUKAN)
            ->latest()
            ->take(10)
            ->get();

        $rabMenungguDirektur = Rab::with(['user', 'expenseType'])
            ->where('status', RabStatus::DISETUJUI_MANAJER)
            ->latest()
            ->take(10)
            ->get();

        $waitingManajer = Rab::where('status', RabStatus::DIAJUKAN)->count();
        $waitingDirektur = Rab::where('status', RabStatus::DISETUJUI_MANAJER)->count();

        $totalDisetujuiManajer = Rab::whereIn('status', [
            RabStatus::DISETUJUI_MANAJER,
            RabStatus::DISETUJUI_DIREKTUR,
            RabStatus::DISETUJUI,
            RabStatus::SELESAI,
        ])->count();

        $totalDisetujuiDirektur = Rab::whereIn('status', [
            RabStatus::DISETUJUI_DIREKTUR,
            RabStatus::DISETUJUI,
            RabStatus::SELESAI,
        ])->count();

        $totalDitolakAll = Rab::where('status', RabStatus::DITOLAK)->count();

        return compact(
            'totalDiajukan', 'totalDibayar', 'waitingApproval', 'totalDitolak',
            'totalNilaiPengajuan', 'totalRealisasi', 'rabTerbaru',
            'chartLabels', 'chartAnggaran', 'chartRealisasi',
            'statusLabels', 'statusData',
            'categoryLabels', 'categoryData',
            'cfLabels', 'cfIn', 'cfOut', 'cfBalance',
            'expenseTypeId', 'period',
            'expenseTypes', 'budgetExpenseTypeId', 'budgetPeriod',
            'statusPeriod', 'categoryPeriod', 'cashflowPeriod',
            'comparisonExpenseTypeId', 'comparisonPeriod', 'comparisonLabels', 'comparisonDatasets',
            'rabTerbaru', 'topSpenders',
            'rabMenungguManajer', 'rabMenungguDirektur',
            'waitingManajer', 'waitingDirektur',
            'totalDisetujuiManajer', 'totalDisetujuiDirektur', 'totalDitolakAll'
        );
    }

    public function chartData(Request $request)
    {
        $data = $this->buildChartData($request);

        return response()->json([
            'budget' => [
                'labels' => $data['chartLabels'],
                'anggaran' => $data['chartAnggaran'],
                'realisasi' => $data['chartRealisasi'],
            ],
            'status' => [
                'labels' => $data['statusLabels'],
                'data' => $data['statusData'],
            ],
            'category' => [
                'labels' => $data['categoryLabels'],
                'data' => $data['categoryData'],
            ],
            'cashflow' => [
                'labels' => $data['cfLabels'],
                'in' => $data['cfIn'],
                'out' => $data['cfOut'],
                'balance' => $data['cfBalance'],
            ],
            'comparison' => [
                'labels' => $data['comparisonLabels'],
                'datasets' => $data['comparisonDatasets'],
            ],
        ]);
    }

    public function admin(Request $request)
    {
        $data = $this->buildChartData($request);
        return view('admin.dashboard', $data);
    }

    public function manajer(Request $request)
    {
        $data = $this->buildChartData($request);

        $data['rabMenunggu'] = $data['rabMenungguManajer'];
        $data['roleWaiting'] = $data['waitingManajer'];
        $data['roleDisetujui'] = $data['totalDisetujuiManajer'];
        $data['roleDitolak'] = $data['totalDitolakAll'];
        $data['roleNilai'] = Rab::where('status', RabStatus::DIAJUKAN)->sum('total_amount');

        return view('manajer.dashboard', $data);
    }

    public function direktur(Request $request)
    {
        $data = $this->buildChartData($request);

        $data['rabMenunggu'] = $data['rabMenungguDirektur'];
        $data['roleWaiting'] = $data['waitingDirektur'];
        $data['roleDisetujui'] = $data['totalDisetujuiDirektur'];
        $data['roleDitolak'] = $data['totalDitolakAll'];
        $data['roleNilai'] = Rab::where('status', RabStatus::DISETUJUI_MANAJER)->sum('total_amount');

        return view('direktur.dashboard', $data);
    }
}
