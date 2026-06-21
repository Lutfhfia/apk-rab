{{-- Shared Dashboard Charts Partial --}}
{{-- Used by admin, manajer, and direktur dashboards --}}
@php($showCashflowChart = $showCashflowChart ?? true)

{{-- REKAYASA POSISI - Charts Row 1: Perbandingan Pengeluaran per Periode (SEKARANG DI PALING ATAS) --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 w-full mb-6">
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
                <option value="">Air & Listrik (Default)</option>
                {{-- TAMBAHAN AKSES: Opsi Semua Kategori berada di bawah default --}}
                <option value="semua" {{ $comparisonExpenseTypeId === 'semua' ? 'selected' : '' }}>Semua Kategori
                </option>
                @foreach ($expenseTypes as $type)
                    <option value="{{ $type->id }}"
                        {{ (string) $comparisonExpenseTypeId === (string) $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                Filter
            </button>
        </form>
    </div>

    <div class="h-80 relative w-full">
        <canvas id="comparisonChart"></canvas>
    </div>
</div>

{{-- Charts Row 2: Anggaran Diajukan vs Realisasi --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 w-full mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-sm font-bold text-gray-800">
            Anggaran Diajukan vs Realisasi
        </h3>
        <span
            class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full uppercase">
            RAB Selesai
        </span>
    </div>

    <div class="h-80 relative w-full">
        <canvas id="budgetChart"></canvas>
    </div>
</div>

{{-- Charts Row 3: Distribusi Status & Pengeluaran Berdasarkan Kategori (Grid 2 Kolom) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full mb-6">
    {{-- Card Distribusi Status --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-gray-800">
                RAB Selesai
            </h3>

            <form method="GET" class="flex gap-2" data-dashboard-filter="status">
                <select name="status_period" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                    <option value="1" {{ request('status_period', 1) == 1 ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="3" {{ request('status_period') == 3 ? 'selected' : '' }}>3 Bulan Terakhir
                    </option>
                    <option value="6" {{ request('status_period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir
                    </option>
                    <option value="9" {{ request('status_period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir
                    </option>
                    <option value="12" {{ request('status_period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir
                    </option>
                    <option value="semua" {{ request('status_period') === 'semua' ? 'selected' : '' }}>Semua Periode
                    </option>
                </select>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                    Filter
                </button>
            </form>
        </div>

        <div class="h-80 relative w-full flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    {{-- Card Kategori Pengeluaran --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-gray-800">
                Pengeluaran Berdasarkan Kategori
            </h3>

            <form method="GET" class="flex gap-2" data-dashboard-filter="category">
                <select name="category_period" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                    <option value="1" {{ request('category_period', 1) == 1 ? 'selected' : '' }}>Bulan Ini
                    </option>
                    <option value="3" {{ request('category_period') == 3 ? 'selected' : '' }}>3 Bulan Terakhir
                    </option>
                    <option value="6" {{ request('category_period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir
                    </option>
                    <option value="9" {{ request('category_period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir
                    </option>
                    <option value="12" {{ request('category_period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir
                    </option>
                    <option value="semua" {{ request('category_period') === 'semua' ? 'selected' : '' }}>Semua Periode
                    </option>
                </select>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                    Filter
                </button>
            </form>
        </div>

        <div class="h-80 relative w-full">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

{{-- Charts Row 4: Perkembangan Arus Kas (Full Width) --}}
@if ($showCashflowChart)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 w-full mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-gray-800">
                Perkembangan Arus Kas
            </h3>

            <form method="GET" class="flex gap-2" data-dashboard-filter="cashflow">
                <select name="cashflow_period" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs">
                    <option value="1" {{ request('cashflow_period', 1) == 1 ? 'selected' : '' }}>Bulan Ini
                    </option>
                    <option value="3" {{ request('cashflow_period') == 3 ? 'selected' : '' }}>3 Bulan Terakhir
                    </option>
                    <option value="6" {{ request('cashflow_period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir
                    </option>
                    <option value="9" {{ request('cashflow_period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir
                    </option>
                    <option value="12" {{ request('cashflow_period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir
                    </option>
                </select>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold">
                    Filter
                </button>
            </form>
        </div>

        <div class="h-80 relative w-full">
            <canvas id="cashflowChart"></canvas>
        </div>
    </div>
@endif
