<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\CashFlow;
use App\Models\RabPayment;
use App\Models\ExpenseType; //dipakai untuk mengisi dropdown Jenis Pengeluaran pada filter card
use App\Enums\RabStatus;
use Illuminate\Http\Request;
use Carbon\Carbon; //dipakai untuk mengatur filter bulan, tahun, serta periode 3, 6, 9, dan 12 bulan.

class DashboardController extends Controller
{
    /**
     * Build common chart data used by all dashboards.
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
        $comparisonExpenseTypeId = $request->input('comparison_expense_type_id', $budgetExpenseTypeId);
        $comparisonPeriod = (int) $request->input('comparison_period', 3);
        $comparisonPeriod = in_array($comparisonPeriod, [3, 6, 9, 12], true) ? $comparisonPeriod : 3;

        // Backward-compatible aliases used by existing dashboard tables.
        $expenseTypeId = $budgetExpenseTypeId;
        $period = $budgetPeriod;

        $startDate = Carbon::now()->subMonths($budgetPeriod - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfDay();

        $rabQuery = Rab::where('status', '!=', RabStatus::DRAFT)
            ->whereBetween('request_date', [$startDate, $endDate]);

        if ($budgetExpenseTypeId) {
            $rabQuery->where('expense_type_id', $budgetExpenseTypeId);
        }

        $totalDiajukan = (clone $rabQuery)->count();
        $totalDibayar = (clone $rabQuery)->where('status', RabStatus::SELESAI)->count();
        $waitingApproval = (clone $rabQuery)->whereIn('status', [RabStatus::DIAJUKAN, RabStatus::DISETUJUI_MANAJER])->count();
        $totalDitolak = (clone $rabQuery)->where('status', RabStatus::DITOLAK)->count();

        $totalNilaiPengajuan = (clone $rabQuery)->sum('total_amount');
        
        $rabIds = (clone $rabQuery)->pluck('id');
        $totalRealisasi = RabPayment::whereIn('rab_id', $rabIds)->sum('paid_amount');

        // Chart 1: Tren Anggaran vs Realisasi (Line Chart)
        $chartLabels = [];
        $chartAnggaran = [];
        $chartRealisasi = [];

        $rabsForChart = (clone $rabQuery)->with('payment')->orderBy('request_date')->get();
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
            $groupedData[$key]['realisasi'] += $rab->payment ? (float) $rab->payment->paid_amount : 0;
        }

        foreach ($groupedData as $label => $data) {
            $chartLabels[] = $label;
            $chartAnggaran[] = $data['anggaran'];
            $chartRealisasi[] = $data['realisasi'];
        }

        // Chart 2: Distribusi Status RAB (Donut Chart)
        $statusQuery = Rab::where('status', '!=', RabStatus::DRAFT);
        if ($statusPeriod !== 'semua') {
            $sMonths = (int) $statusPeriod;
            $sStart = Carbon::now()->subMonths(max(0, $sMonths - 1))->startOfMonth();
            $statusQuery->where('request_date', '>=', $sStart);
        }
        $statusRabs = $statusQuery->get();

        $statusLabels = ['Diajukan', 'Disetujui', 'Menunggu Approval', 'Ditolak/Revisi'];
        $statusData = [0, 0, 0, 0];
        
        foreach ($statusRabs as $rab) {
            $status = $rab->status->value;
            if ($status === 'diajukan') {
                $statusData[0]++;
            } elseif (in_array($status, ['disetujui', 'disetujui_direktur', 'selesai'])) {
                $statusData[1]++;
            } elseif ($status === 'disetujui_manajer') {
                $statusData[2]++;
            } elseif ($status === 'ditolak') {
                $statusData[3]++;
            }
        }

        // Chart 3: Pengeluaran Berdasarkan Kategori (Horizontal Bar)
        $catQuery = Rab::where('status', '!=', RabStatus::DRAFT);
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

        // Chart 4: Perkembangan Arus Kas (Line/Bar Chart)
        $cfGrouped = [];
        
        if ($cashflowPeriod === '1') {
            $cfStart = Carbon::now()->startOfMonth();
            $cfEnd = Carbon::now()->endOfMonth();
            $cashflowQuery = CashFlow::whereBetween('transaction_date', [$cfStart, $cfEnd])
                ->orderBy('transaction_date')
                ->get();

            for ($week = 1; $week <= 5; $week++) {
                $cfGrouped['Minggu ' . $week] = ['in' => 0, 'out' => 0, 'balance' => 0];
            }

            foreach ($cashflowQuery as $cf) {
                $key = 'Minggu ' . min((int) ceil(Carbon::parse($cf->transaction_date)->day / 7), 5);
                $cfGrouped[$key]['in'] += (float) $cf->debit;
                $cfGrouped[$key]['out'] += (float) $cf->credit;
                $cfGrouped[$key]['balance'] = (float) $cf->balance;
            }
        } else {
            $cfMonths = (int) $cashflowPeriod;
            $cfStart = Carbon::now()->subMonths(max(0, $cfMonths - 1))->startOfMonth();
            $cfEnd = Carbon::now()->endOfMonth();

            $cashflowQuery = CashFlow::whereBetween('transaction_date', [$cfStart, $cfEnd])
                ->orderBy('transaction_date')
                ->get();

            for ($date = $cfStart->copy(); $date <= $cfEnd; $date->addMonth()) {
                $cfGrouped[$date->translatedFormat('M Y')] = ['in' => 0, 'out' => 0, 'balance' => 0];
            }

            foreach ($cashflowQuery as $cf) {
                $key = Carbon::parse($cf->transaction_date)->translatedFormat('M Y');
                if (isset($cfGrouped[$key])) {
                    $cfGrouped[$key]['in'] += (float) $cf->debit;
                    $cfGrouped[$key]['out'] += (float) $cf->credit;
                    $cfGrouped[$key]['balance'] = (float) $cf->balance;
                }
            }
        }

        $cfLabels = [];
        $cfIn = [];
        $cfOut = [];
        $cfBalance = [];

        foreach ($cfGrouped as $label => $data) {
            $cfLabels[] = $label;
            $cfIn[] = $data['in'];
            $cfOut[] = $data['out'];
            $cfBalance[] = $data['balance'];
        }

        // Chart 5: Pengeluaran bulanan dalam rentang 3, 6, 9, atau 12 bulan
        $comparisonLabels = [];
        $periods = [];
        $comparisonStart = Carbon::now()->subMonths($comparisonPeriod - 1)->startOfMonth();

        for ($date = $comparisonStart->copy(); $date <= $endDate; $date->addMonth()) {
            $periods[] = $date->format('Y-m');
            $comparisonLabels[] = $date->translatedFormat('M Y');
        }

        // Color mapping based on expense type code
        $colorMap = [
            'gaji' => '#3b82f6',        // Blue
            'operasional' => '#8b5cf6', // Purple
            'bulanan' => '#eab308',     // Amber
            'petty_cash' => '#10b981',   // Emerald
        ];
        $fallbackColors = ['#f97316', '#ec4899', '#14b8a6', '#6366f1', '#64748b'];

        $comparisonDatasets = [];
        $comparisonTotalValues = array_fill_keys($periods, 0);

        if ($comparisonExpenseTypeId) {
            $selectedType = $expenseTypes->firstWhere('id', (int) $comparisonExpenseTypeId);
            $selectedCode = $selectedType ? $selectedType->code : 'other';
            $selectedName = $selectedType ? $selectedType->name : 'Pengeluaran';
            $selectedColor = $colorMap[$selectedCode] ?? $fallbackColors[0];

            $dataValues = [];
            foreach ($periods as $periodKey) {
                $dataValues[$periodKey] = 0;
            }

            $comparisonQuery = Rab::where('status', '!=', RabStatus::DRAFT)
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
            $idx = 0;
            foreach ($expenseTypes as $type) {
                $typeCode = $type->code;
                $typeName = $type->name;
                $typeColor = $colorMap[$typeCode] ?? ($fallbackColors[$idx % count($fallbackColors)]);
                $idx++;

                $dataValues = [];
                foreach ($periods as $periodKey) {
                    $dataValues[$periodKey] = 0;
                }

                $comparisonQuery = Rab::where('status', '!=', RabStatus::DRAFT)
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
            'label' => $comparisonExpenseTypeId ? 'Tren Pengeluaran' : 'Tren Total Pengeluaran',
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
            ->where('status', '!=', RabStatus::DITOLAK)
            ->with(['user', 'expenseType', 'payment'])
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        // RAB Menunggu per role
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

        // Count specific role approvals
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
                'labels' => auth()->user()?->isAdmin() ? [] : $data['cfLabels'],
                'in' => auth()->user()?->isAdmin() ? [] : $data['cfIn'],
                'out' => auth()->user()?->isAdmin() ? [] : $data['cfOut'],
                'balance' => auth()->user()?->isAdmin() ? [] : $data['cfBalance'],
            ],
            'comparison' => [
                'labels' => $data['comparisonLabels'],
                'datasets' => $data['comparisonDatasets'],
            ],
        ]);
    }

    /**
     * Admin Keuangan Dashboard.
     */
    public function admin(Request $request)
    {
        $data = $this->buildChartData($request);
        return view('admin.dashboard', $data);
    }

    /**
     * Manajer Keuangan Dashboard.
     */
    public function manajer(Request $request)
    {
        $data = $this->buildChartData($request);

        // Override role-specific variables
        $data['rabMenunggu'] = $data['rabMenungguManajer'];
        $data['roleWaiting'] = $data['waitingManajer'];
        $data['roleDisetujui'] = $data['totalDisetujuiManajer'];
        $data['roleDitolak'] = $data['totalDitolakAll'];
        $data['roleNilai'] = Rab::where('status', RabStatus::DIAJUKAN)->sum('total_amount');

        return view('manajer.dashboard', $data);
    }

    /**
     * Direktur Dashboard.
     */
    public function direktur(Request $request)
    {
        $data = $this->buildChartData($request);

        // Override role-specific variables
        $data['rabMenunggu'] = $data['rabMenungguDirektur'];
        $data['roleWaiting'] = $data['waitingDirektur'];
        $data['roleDisetujui'] = $data['totalDisetujuiDirektur'];
        $data['roleDitolak'] = $data['totalDitolakAll'];
        $data['roleNilai'] = Rab::where('status', RabStatus::DISETUJUI_MANAJER)->sum('total_amount');

        return view('direktur.dashboard', $data);
    }
}
