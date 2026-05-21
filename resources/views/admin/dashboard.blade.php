@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Aplikasi Monitoring Anggaran')

@section('sidebar-menu')
@include('admin._sidebar')
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header "Control Panel Keuangan" --}}
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-2">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.642 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.358-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
            <h2 class="text-xl font-bold text-gray-800">Control Panel Keuangan</h2>
        </div>
        <div class="flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Data diperbarui: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->format('H:i') }} WIB
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-blue-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">TOTAL DIAJUKAN</p>
            <h3 class="text-2xl font-extrabold text-gray-800">Rp {{ number_format($totalNilaiPengajuan, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-emerald-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">TOTAL DIBAYARKAN</p>
            <h3 class="text-2xl font-extrabold text-emerald-500">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-amber-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">WAITING APPROVAL</p>
            <h3 class="text-2xl font-extrabold text-amber-500">{{ $waitingApproval }} <span class="text-sm font-bold text-amber-500 uppercase">RAB</span></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">DITOLAK/REVISI</p>
            <h3 class="text-2xl font-extrabold text-red-500">{{ $totalDitolak }} <span class="text-sm font-bold text-red-500 uppercase">RAB</span></h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/50 backdrop-blur-sm p-4 rounded-xl flex flex-wrap items-end justify-between gap-4 border border-gray-100">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-4 w-full md:w-auto">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Jenis Pengeluaran:</label>
                <select name="expense_type_id" class="border border-gray-300 rounded-lg text-sm px-3 py-2 w-56 focus:outline-none focus:border-blue-500">
                    <option value="">-- Semua Jenis Pengeluaran --</option>
                    @foreach(\App\Models\ExpenseType::all() as $type)
                        <option value="{{ $type->id }}" {{ request('expense_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Periode Analisis:</label>
                <select name="period" class="border border-gray-300 rounded-lg text-sm px-3 py-2 w-48 focus:outline-none focus:border-blue-500">
                    <option value="1" {{ request('period') == 1 ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="3" {{ request('period', 3) == 3 ? 'selected' : '' }}>3 Bulan Terakhir</option>
                    <option value="6" {{ request('period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="9" {{ request('period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir</option>
                    <option value="12" {{ request('period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-5 py-2 rounded-lg flex items-center shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Terapkan Filter
            </button>
        </form>
    </div>

    @include('components.dashboard-charts', ['showCashflowChart' => false])

    {{-- Top 5 Pengeluaran --}}
    @include('components.dashboard-top-spenders')


</div>
@endsection

@push('scripts')
@include('components.dashboard-charts-scripts')
@endpush
