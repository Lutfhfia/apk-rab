{{-- Shared Dashboard Charts Partial --}}
{{-- Used by admin, manajer, and direktur dashboards --}}
@php($showCashflowChart = $showCashflowChart ?? true)

{{-- Charts Row 3: Comparison (Moved to Top) --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-sm font-bold text-gray-800">
            Perbandingan Pengeluaran per Periode
        </h3>

        <form method="GET" class="flex flex-wrap gap-2" data-dashboard-filter="comparison">
            <select name="comparison_period" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                <option value="3" {{ $comparisonPeriod == 3 ? 'selected' : '' }}>3 Bulan</option>
                <option value="6" {{ $comparisonPeriod == 6 ? 'selected' : '' }}>6 Bulan</option>
                <option value="9" {{ $comparisonPeriod == 9 ? 'selected' : '' }}>9 Bulan</option>
                <option value="12" {{ $comparisonPeriod == 12 ? 'selected' : '' }}>12 Bulan</option>
            </select>

            <select name="comparison_expense_type_id" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                <option value="">Semua Jenis</option>
                @foreach($expenseTypes as $type)
                    <option value="{{ $type->id }}" {{ (string)$comparisonExpenseTypeId === (string)$type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                Filter
            </button>
        </form>
    </div>

    <div class="h-80 relative w-full">
        <canvas id="comparisonChart"></canvas>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-sm font-bold text-gray-800">
            Distribusi Status RAB
        </h3>

        <form method="GET" class="flex gap-2" data-dashboard-filter="status">
            <select name="status_period" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                <option value="1" {{ request('status_period', 1) == 1 ? 'selected' : '' }}>Bulan Ini</option>
                <option value="3" {{ request('status_period') == 3 ? 'selected' : '' }}>3 Bulan Terakhir</option>
                <option value="6" {{ request('status_period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir</option>
                <option value="9" {{ request('status_period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir</option>
                <option value="12" {{ request('status_period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir</option>
                <option value="semua" {{ request('status_period') === 'semua' ? 'selected' : '' }}>Semua Periode</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                Filter
            </button>
        </form>
    </div>

    <div class="h-80 relative w-full flex items-center justify-center">
        <canvas id="statusChart"></canvas>
    </div>
</div>

{{-- Charts Row 2: Perkembangan Arus Kas (Full Width) --}}
@if($showCashflowChart)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 w-full mt-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-sm font-bold text-gray-800">
            Perkembangan Arus Kas
        </h3>

        <form method="GET" class="flex gap-2" data-dashboard-filter="cashflow">
            <select name="cashflow_period" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                <option value="1" {{ request('cashflow_period', 1) == 1 ? 'selected' : '' }}>Bulan Ini</option>
                <option value="3" {{ request('cashflow_period') == 3 ? 'selected' : '' }}>3 Bulan Terakhir</option>
                <option value="6" {{ request('cashflow_period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir</option>
                <option value="9" {{ request('cashflow_period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir</option>
                <option value="12" {{ request('cashflow_period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                Filter
            </button>
        </form>
    </div>

    <div class="h-80 relative w-full">
        <canvas id="cashflowChart"></canvas>
    </div>
</div>
@endif
