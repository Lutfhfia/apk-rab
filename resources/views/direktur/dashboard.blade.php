@extends('layouts.app')
@section('title', 'Dashboard Direktur')
@section('page-title', 'Dashboard Direktur')
@section('page-subtitle', 'Persetujuan akhir dan monitoring keuangan')

@section('sidebar-menu')
@include('direktur._sidebar')
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="relative bg-gradient-to-r from-[#1E293B] via-[#334155] to-[#0F172A] rounded-2xl p-8 overflow-hidden shadow-lg">
        <div class="absolute top-0 right-0 w-72 h-72 bg-purple-500/10 rounded-full -translate-y-1/2 translate-x-1/4 blur-3xl"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <p class="text-purple-400 text-sm font-semibold mb-1">👋 Selamat Datang,</p>
                <h2 class="text-white text-2xl font-extrabold">{{ Auth::user()->name }}</h2>
                <p class="text-slate-400 text-sm mt-1">Persetujuan akhir & monitoring Rancangan Anggaran Biaya</p>
            </div>
            <div class="flex items-center text-sm text-slate-400">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->format('H:i') }} WIB
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-purple-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">TOTAL DIAJUKAN</p>
            <h3 class="text-2xl font-extrabold text-gray-800">Rp {{ number_format($totalNilaiPengajuan, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-emerald-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">TOTAL DIBAYARKAN</p>
            <h3 class="text-2xl font-extrabold text-emerald-500">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-amber-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">WAITING APPROVAL AKHIR</p>
            <h3 class="text-2xl font-extrabold text-amber-500">{{ $roleWaiting }} <span class="text-sm font-bold text-amber-500 uppercase">RAB</span></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-500 border-t border-r border-b border-gray-100 p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">DITOLAK/REVISI</p>
            <h3 class="text-2xl font-extrabold text-red-500">{{ $roleDitolak }} <span class="text-sm font-bold text-red-500 uppercase">RAB</span></h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/50 backdrop-blur-sm p-4 rounded-xl flex flex-wrap items-end justify-between gap-4 border border-gray-100">
        <form method="GET" action="{{ route('direktur.dashboard') }}" class="flex flex-wrap items-end gap-4 w-full md:w-auto">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Jenis Pengeluaran:</label>
                <select name="expense_type_id" class="border border-gray-300 rounded-lg text-sm px-3 py-2 w-56 focus:outline-none focus:border-purple-500">
                    <option value="">-- Semua Jenis Pengeluaran --</option>
                    @foreach(\App\Models\ExpenseType::all() as $type)
                        <option value="{{ $type->id }}" {{ request('expense_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Periode Analisis:</label>
                <select name="period" class="border border-gray-300 rounded-lg text-sm px-3 py-2 w-48 focus:outline-none focus:border-purple-500">
                    <option value="1" {{ request('period') == 1 ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="3" {{ request('period', 3) == 3 ? 'selected' : '' }}>3 Bulan Terakhir</option>
                    <option value="6" {{ request('period') == 6 ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="9" {{ request('period') == 9 ? 'selected' : '' }}>9 Bulan Terakhir</option>
                    <option value="12" {{ request('period') == 12 ? 'selected' : '' }}>12 Bulan Terakhir</option>
                </select>
            </div>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm px-5 py-2 rounded-lg flex items-center shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Terapkan Filter
            </button>
        </form>
    </div>

    {{-- Charts --}}
    @include('components.dashboard-charts')

    {{-- Top 5 Pengeluaran --}}
    @include('components.dashboard-top-spenders')

    {{-- RAB Waiting for Approval --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-800">RAB Menunggu Persetujuan Akhir</h2>
            <span class="bg-purple-100 text-purple-700 text-xs font-bold px-3 py-1 rounded-full">{{ $roleWaiting }} menunggu</span>
        </div>
        <div class="overflow-x-auto">
            <table class="sticky-table w-full text-left text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-100 bg-gray-50/50">
                        <th class="py-3.5 px-5 font-bold">No. RAB</th>
                        <th class="py-3.5 px-5 font-bold">Pengaju</th>
                        <th class="py-3.5 px-5 font-bold">Jenis</th>
                        <th class="py-3.5 px-5 font-bold">Total</th>
                        <th class="py-3.5 px-5 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rabMenunggu as $rab)
                    <tr class="border-b border-gray-50 hover:bg-purple-50/30 transition-colors">
                        <td class="py-4 px-5 font-bold text-gray-800">{{ $rab->rab_number }}</td>
                        <td class="py-4 px-5">
                            <div class="flex items-center space-x-3">
                                @if($rab->user && $rab->user->avatar)
                                <div class="avatar-clickable">
                                <img src="{{ asset('storage/' . $rab->user->avatar) }}" alt="{{ $rab->user->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                </div>
                                @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($rab->user->name ?? 'U', 0, 1)) }}
                                </div>
                                @endif
                                <span class="block text-sm font-semibold text-gray-700">{{ $rab->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-5"><span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase">{{ $rab->expenseType->name }}</span></td>
                        <td class="py-4 px-5 font-semibold">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                        <td class="py-4 px-5">
                            <a href="{{ route('direktur.rab.index', ['status' => $rab->status->value, 'open_rab_id' => $rab->id]) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition">Review</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-12 text-center text-gray-400">Tidak ada RAB yang menunggu persetujuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


</div>
@endsection

@push('scripts')
@include('components.dashboard-charts-scripts')
@endpush
