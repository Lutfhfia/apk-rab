@extends('layouts.app')
@section('title', 'Daftar RAB')
@section('page-title', 'Daftar RAB')
@section('page-subtitle', 'Semua RAB yang telah diajukan')

@section('sidebar-menu')
@if($role === 'manajer')
    @include('manajer._sidebar')
@else
    @include('direktur._sidebar')
@endif
@endsection

@section('content')
@php
    // Default status per role (passed from controller)
    $defaultStatusValue = $defaultStatus->value;
    $activeStatus = request('status', $defaultStatusValue);

    // Counts
    $countDiajukan        = $statusCounts['diajukan'] ?? 0;
    $countDisetujuiManajer = $statusCounts['disetujui_manajer'] ?? 0;
    $countDisetujui       = ($statusCounts['disetujui'] ?? 0) + ($statusCounts['disetujui_direktur'] ?? 0);
    $countDitolak         = $statusCounts['ditolak'] ?? 0;
    $countSelesai         = $statusCounts['selesai'] ?? 0;

    // "Perlu Direview" count for current role
    $countPerluReview = $role === 'direktur' ? $countDisetujuiManajer : $countDiajukan;
    $perluReviewStatus = $role === 'direktur'
        ? \App\Enums\RabStatus::DISETUJUI_MANAJER->value
        : \App\Enums\RabStatus::DIAJUKAN->value;
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <h2 class="text-lg font-bold text-gray-800">Daftar RAB</h2>
</div>

{{-- Summary Cards (clickable, filter by status) --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    {{-- Card 1: Perlu Direview (role-specific) --}}
    <a href="{{ route($role . '.rab.index', ['status' => $perluReviewStatus]) }}" class="bg-white rounded-xl p-4 shadow-sm border-2 transition hover:shadow-md {{ $activeStatus === $perluReviewStatus ? 'border-blue-400 ring-2 ring-blue-100' : 'border-gray-100 hover:border-blue-200' }}">
        <p class="text-xs font-bold text-gray-400 uppercase">
            {{ $role === 'direktur' ? 'Perlu Review Direktur' : 'Perlu Review Manajer' }}
        </p>
        <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $countPerluReview }}</p>
    </a>
    {{-- Card 2: Disetujui --}}
    <a href="{{ route($role . '.rab.index', ['status' => \App\Enums\RabStatus::DISETUJUI->value]) }}" class="bg-white rounded-xl p-4 shadow-sm border-2 transition hover:shadow-md {{ $activeStatus === \App\Enums\RabStatus::DISETUJUI->value ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-gray-100 hover:border-emerald-200' }}">
        <p class="text-xs font-bold text-gray-400 uppercase">Disetujui</p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $countDisetujui }}</p>
    </a>
    {{-- Card 3: Ditolak --}}
    <a href="{{ route($role . '.rab.index', ['status' => \App\Enums\RabStatus::DITOLAK->value]) }}" class="bg-white rounded-xl p-4 shadow-sm border-2 transition hover:shadow-md {{ $activeStatus === \App\Enums\RabStatus::DITOLAK->value ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-100 hover:border-red-200' }}">
        <p class="text-xs font-bold text-gray-400 uppercase">Ditolak</p>
        <p class="text-2xl font-extrabold text-red-600 mt-1">{{ $countDitolak }}</p>
    </a>
    {{-- Card 4: Selesai --}}
    <a href="{{ route($role . '.rab.index', ['status' => \App\Enums\RabStatus::SELESAI->value]) }}" class="bg-white rounded-xl p-4 shadow-sm border-2 transition hover:shadow-md {{ $activeStatus === \App\Enums\RabStatus::SELESAI->value ? 'border-green-400 ring-2 ring-green-100' : 'border-gray-100 hover:border-green-200' }}">
        <p class="text-xs font-bold text-gray-400 uppercase">Selesai</p>
        <p class="text-2xl font-extrabold text-green-600 mt-1">{{ $countSelesai }}</p>
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-4">
    <form method="GET" action="{{ route($role . '.rab.index') }}" class="flex flex-col gap-4">
        {{-- Preserve active status tab --}}
        <input type="hidden" name="status" value="{{ $activeStatus }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:flex gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Cari No. RAB</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Jenis</label>
                <select name="expense_type" class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    <option value="">Semua Jenis</option>
                    @foreach($expenseTypes as $type)
                    <option value="{{ $type->id }}" {{ request('expense_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div class="min-w-[100px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Urutan</label>
                <div class="flex border border-gray-200 rounded-lg overflow-hidden h-[38px] bg-white">
                    <input type="hidden" name="sort" id="sort_input" value="{{ request('sort', 'desc') }}">
                    <button type="button" onclick="setSort('desc')" id="btn_sort_desc" class="flex-1 flex items-center justify-center transition-colors {{ request('sort', 'desc') === 'desc' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-50' }}" title="Terbaru (Urutan Ke Bawah)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" onclick="setSort('asc')" id="btn_sort_asc" class="flex-1 flex items-center justify-center border-l border-gray-200 transition-colors {{ request('sort') === 'asc' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-50' }}" title="Terlama (Urutan Ke Atas)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 18l7-7 7 7"/></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold transition">Filter</button>
            <a href="{{ route($role . '.rab.index') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium">Reset</a>
        </div>
    </form>
</div>

{{-- Status Tabs (ordered by relevance per role) --}}
@php
    if ($role === 'direktur') {
        // Direktur: paling penting = yang perlu dia approve (disetujui_manajer)
        $statusTabs = [
            \App\Enums\RabStatus::DISETUJUI_MANAJER,
            \App\Enums\RabStatus::DIAJUKAN,
            \App\Enums\RabStatus::DISETUJUI,
            \App\Enums\RabStatus::DITOLAK,
            \App\Enums\RabStatus::SELESAI,
        ];
    } else {
        // Manajer: paling penting = yang perlu dia approve (diajukan)
        $statusTabs = [
            \App\Enums\RabStatus::DIAJUKAN,
            \App\Enums\RabStatus::DISETUJUI_MANAJER,
            \App\Enums\RabStatus::DISETUJUI,
            \App\Enums\RabStatus::DITOLAK,
            \App\Enums\RabStatus::SELESAI,
        ];
    }
@endphp
<div class="flex gap-2 overflow-x-auto pb-2 mb-6">
    @foreach($statusTabs as $statusTab)
    @php
        $tabCount = $statusCounts[$statusTab->value] ?? 0;
        $isActive = $activeStatus === $statusTab->value;
    @endphp
    {{-- PENTING: Tab link hanya membawa status, TIDAK membawa open_rab_id --}}
    <a href="{{ route($role . '.rab.index', ['status' => $statusTab->value]) }}"
       class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition flex items-center gap-2 {{ $isActive ? $statusTab->badgeClasses() . ' border-transparent' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
        {{ $statusTab->label() }}
        <span class="text-[10px] {{ $isActive ? 'opacity-75' : 'bg-gray-100 text-gray-500' }} px-1.5 py-0.5 rounded-full font-bold">{{ $tabCount }}</span>
    </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto max-h-[65vh] overflow-y-auto relative">
        <table class="w-full text-left">
            <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm shadow-sm">
                <tr class="text-xs text-gray-500 border-b border-gray-100">
                    <th class="py-3.5 px-5 font-bold">No. RAB</th>
                    <th class="py-3.5 px-5 font-bold">Pembuat</th>
                    <th class="py-3.5 px-5 font-bold">Jenis</th>
                    <th class="py-3.5 px-5 font-bold">Tanggal</th>
                    <th class="py-3.5 px-5 font-bold">Total</th>
                    <th class="py-3.5 px-5 font-bold">Status</th>
                    <th class="py-3.5 px-5 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-600">
                @forelse($rabs as $rab)
                <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors">
                    <td class="py-4 px-5 font-bold text-gray-800">{{ $rab->rab_number }}</td>
                    <td class="py-4 px-5">
                        <div class="flex items-center space-x-3">
                            @if($rab->user && $rab->user->avatar)
                            <div class="avatar-clickable">
                            <img src="{{ asset('storage/' . $rab->user->avatar) }}" alt="{{ $rab->user->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            </div>
                            @else
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-xs">
                                {{ strtoupper(substr($rab->user->name ?? 'U', 0, 1)) }}
                            </div>
                            @endif
                            <div>
                                <span class="block text-xs text-gray-500">{{ $rab->user->role->label() ?? 'Admin' }}</span>
                                <span class="block text-sm font-semibold text-gray-700">{{ $rab->user->name ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-5"><span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase">{{ $rab->expenseType->name ?? '-' }}</span></td>
                    <td class="py-4 px-5">{{ $rab->request_date->format('d/m/Y') }}</td>
                    <td class="py-4 px-5 font-semibold">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                    <td class="py-4 px-5"><span class="{{ $rab->status->badgeClasses() }} text-[10px] font-bold px-3 py-1.5 rounded-lg">{{ $rab->status->label() }}</span></td>
                    <td class="py-4 px-5">
                        <div class="flex items-center space-x-1">
                            {{-- Detail button --}}
                            <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>

                            @if($role === 'manajer')
                            <a href="{{ route('rab.export-pdf', $rab) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Simpan PDF RAB">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            {{-- Cairkan Dana shortcut --}}
                            @if($rab->status === \App\Enums\RabStatus::DISETUJUI && !$rab->payment)
                            <button type="button" onclick="openPaymentModal('paymentModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition" title="Cairkan Dana">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </button>
                            @endif
                            @endif

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-400">Belum ada data RAB untuk status ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rabs->hasPages())
    <div class="p-5 border-t border-gray-100">{{ $rabs->links() }}</div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- Modal Detail RAB (per-item, rendered inside foreach) --}}
{{-- ============================================================ --}}
@foreach($rabs as $rab)
<div id="rabDetailModal-{{ $rab->id }}" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl max-h-[90vh] flex flex-col overflow-hidden">

        {{-- HEADER --}}
        <div class="p-5 border-b border-gray-100 flex items-start justify-between gap-4 shrink-0">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h3 class="text-lg font-extrabold text-gray-800">{{ $rab->rab_number }}</h3>
                    <span class="{{ $rab->status->badgeClasses() }} text-[10px] font-bold px-3 py-1.5 rounded-lg">{{ $rab->status->label() }}</span>
                </div>
                <p class="text-sm text-gray-500">{{ $rab->expenseType->name ?? '-' }} &bull; {{ $rab->request_date->format('d/m/Y') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Dibuat oleh: {{ $rab->user->name ?? 'User' }} pada {{ $rab->created_at->format('d M Y, H:i') }}</p>
            </div>
            <button type="button" onclick="closeRabModal('rabDetailModal-{{ $rab->id }}')" class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- BODY (scrollable) --}}
        <div class="p-5 flex-1 overflow-y-auto min-h-0">

            {{-- Info Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Pembuat</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $rab->user->name ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Periode</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $rab->period_label }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Jenis Pengeluaran</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $rab->expenseType?->name ?? '-' }}</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-emerald-600 uppercase">Total</p>
                    <p class="text-lg font-extrabold text-emerald-700 mt-1">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Description --}}
            @if($rab->description)
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-5">
                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Catatan / Alasan Pengajuan</p>
                <p class="text-sm text-gray-700 italic leading-relaxed">{{ $rab->description }}</p>
            </div>
            @endif

            {{-- Rejection Alert --}}
            @if($rab->status === \App\Enums\RabStatus::DITOLAK)
                @php
                    $latestRejection = $rab->approvals->where('status', \App\Enums\ApprovalStatus::REJECTED)->sortByDesc('created_at')->first();
                @endphp
                @if($latestRejection)
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h4 class="text-sm font-bold text-red-800">RAB Ditolak oleh {{ $latestRejection->user->name ?? 'Sistem' }}</h4>
                            <p class="text-sm text-red-700 mt-1">Alasan: {{ $latestRejection->notes }}</p>
                            <p class="text-xs text-red-500 mt-1">{{ $latestRejection->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            @endif
            {{-- ============================================= --}}
            {{-- Tabel Rincian Item (per Jenis Pengeluaran) --}}
            {{-- ============================================= --}}
            <div class="border border-gray-100 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Rincian Item RAB
                    </h4>
                </div>

                @if($rab->expenseType?->code === 'operasional')
                {{-- OPERASIONAL TABLE --}}
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-sm text-left min-w-[900px]">
                        <thead>
                            <tr class="text-xs text-gray-500 border-b-2 border-gray-200">
                                <th class="py-2 px-2 font-bold w-12 text-center">NO</th>
                                <th class="py-2 px-2 font-bold">ITEM</th>
                                <th class="py-2 px-2 font-bold w-20 text-center">VOLUME</th>
                                <th class="py-2 px-2 font-bold w-20 text-center">SATUAN</th>
                                <th class="py-2 px-2 font-bold w-32 text-right">RP / UNIT</th>
                                <th class="py-2 px-2 font-bold w-32 text-right">JUMLAH</th>
                                <th class="py-2 px-2 font-bold w-48">KET</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $operationalGroups = [
                                    "Honor Pencari Peserta",
                                    "Uang Transport / Honor Peserta Uji Serkom",
                                    "Operasional Pembekalan",
                                    "Operasional Uji Serkom",
                                    "Honor Asesor"
                                ];
                                $items = $rab->getExpenseItems();
                                $totalRAB = 0;
                                $otherItems = $items->filter(function($item) use ($operationalGroups) {
                                    return !in_array($item->group_name, $operationalGroups);
                                });
                            @endphp
                            @foreach($operationalGroups as $gIdx => $groupName)
                                @php $groupItems = $items->where('group_name', $groupName); @endphp
                                @if($groupItems->count() > 0)
                                <tr class="bg-blue-50/50">
                                    <td class="py-2 px-2 font-bold text-gray-800 text-center">{{ $gIdx + 1 }}.</td>
                                    <td colspan="6" class="py-2 px-2 font-bold text-gray-800 uppercase">{{ $groupName }}</td>
                                </tr>
                                @foreach($groupItems as $item)
                                    @php $totalRAB += $item->total; @endphp
                                    <tr class="border-b border-gray-100">
                                        <td></td>
                                        <td class="py-2 px-2 text-gray-700">{{ $item->item_name }}</td>
                                        <td class="py-2 px-2 text-center text-gray-600">{{ $item->volume }}</td>
                                        <td class="py-2 px-2 text-center text-gray-600">{{ $item->unit }}</td>
                                        <td class="py-2 px-2 text-right text-gray-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="py-2 px-2 font-bold text-gray-800 text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                        <td class="py-2 px-2 text-gray-500">{{ $item->note ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                @endif
                            @endforeach
                            @if($otherItems->count() > 0)
                                <tr class="bg-gray-100/70">
                                    <td class="py-2 px-2 font-bold text-gray-800 text-center">*</td>
                                    <td colspan="6" class="py-2 px-2 font-bold text-gray-800 uppercase">Item Operasional (Umum)</td>
                                </tr>
                                @foreach($otherItems as $item)
                                    @php $totalRAB += $item->total; @endphp
                                    <tr class="border-b border-gray-100">
                                        <td></td>
                                        <td class="py-2 px-2 text-gray-700">{{ $item->item_name }}</td>
                                        <td class="py-2 px-2 text-center text-gray-600">{{ $item->volume }}</td>
                                        <td class="py-2 px-2 text-center text-gray-600">{{ $item->unit }}</td>
                                        <td class="py-2 px-2 text-right text-gray-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="py-2 px-2 font-bold text-gray-800 text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                        <td class="py-2 px-2 text-gray-500">{{ $item->note ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td colspan="5" class="py-3 px-2 font-extrabold text-gray-800 text-right">TOTAL KESELURUHAN</td>
                                <td class="py-3 px-2 font-extrabold text-emerald-600 text-right text-base">Rp {{ number_format($totalRAB, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @elseif($rab->expenseType?->code === 'petty_cash')
                {{-- PETTY CASH TABLE --}}
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-sm text-left min-w-[1000px]">
                        <thead>
                            <tr class="text-xs text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 font-bold text-center w-12">No</th>
                                <th class="py-3 px-4 font-bold">Nama Pengeluaran</th>
                                <th class="py-3 px-4 font-bold">Keterangan</th>
                                <th class="py-3 px-4 font-bold text-center w-20">Jumlah</th>
                                <th class="py-3 px-4 font-bold text-center w-20">Satuan</th>
                                <th class="py-3 px-4 font-bold text-right w-32">Harga Satuan</th>
                                <th class="py-3 px-4 font-bold text-right w-28">Admin</th>
                                <th class="py-3 px-4 font-bold text-right w-32">Total</th>
                                <th class="py-3 px-4 font-bold text-center w-32">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rab->getExpenseItems() as $i => $item)
                            @php $totalVal = $item->total > 0 ? $item->total : $item->nominal; @endphp
                            <tr class="border-b border-gray-50">
                                <td class="py-3 px-4 text-gray-500 text-center">{{ $i + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $item->expense_name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->description ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600 text-center">{{ $item->volume }}</td>
                                <td class="py-3 px-4 text-gray-600 text-center">{{ $item->unit }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->admin_fee, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalVal, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-gray-500 text-center">{{ $item->transaction_date ? $item->transaction_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-400">Belum ada rincian item.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @elseif($rab->expenseType?->code === 'gaji')
                {{-- SALARY TABLE --}}
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-sm text-left min-w-[1300px]">
                        <thead>
                            <tr class="text-xs text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 font-bold text-center w-12">No</th>
                                <th class="py-3 px-4 font-bold">Nama Karyawan</th>
                                <th class="py-3 px-4 font-bold">Jabatan</th>
                                <th class="py-3 px-4 font-bold">No. Rek</th>
                                <th class="py-3 px-4 font-bold text-center w-24">Hadir (hari)</th>
                                <th class="py-3 px-4 font-bold text-right w-32">Gaji Pokok</th>
                                <th class="py-3 px-4 font-bold text-right w-28">Uang Makan/Hari</th>
                                <th class="py-3 px-4 font-bold text-right w-28">Transport/Hari</th>
                                <th class="py-3 px-4 font-bold text-right w-28">Lembur</th>
                                <th class="py-3 px-4 font-bold text-right w-28">Potongan</th>
                                <th class="py-3 px-4 font-bold text-right w-32">Total Gaji</th>
                                <th class="py-3 px-4 font-bold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rab->getExpenseItems() as $i => $item)
                            @php $totalVal = $item->total_salary > 0 ? $item->total_salary : $item->salary_nominal; @endphp
                            <tr class="border-b border-gray-50">
                                <td class="py-3 px-4 text-gray-500 text-center">{{ $i + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $item->employee_name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->position ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->bank_account_number ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600 text-center">{{ $item->attendance_days }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->meal_allowance_daily, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->transport_daily, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->overtime, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-red-500">Rp {{ number_format($item->deduction ?? 0, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalVal, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="py-8 text-center text-gray-400">Belum ada rincian item.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @elseif(in_array($rab->expenseType?->code, ['bulanan', 'listrik', 'air_pam'], true))
                {{-- MONTHLY TABLE --}}
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-sm text-left min-w-[900px]">
                        <thead>
                            <tr class="text-xs text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 font-bold text-center w-12">No</th>
                                <th class="py-3 px-4 font-bold">Keterangan</th>
                                <th class="py-3 px-4 font-bold">No. Regist/ID</th>
                                <th class="py-3 px-4 font-bold">Atas Nama</th>
                                <th class="py-3 px-4 font-bold text-right w-36">Total Pengeluaran</th>
                                <th class="py-3 px-4 font-bold text-right w-28">Biaya Admin</th>
                                <th class="py-3 px-4 font-bold text-right w-32">Subtotal</th>
                                <th class="py-3 px-4 font-bold text-center w-32">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rab->getExpenseItems() as $i => $item)
                            @php
                                $totalExpense = $item->total_expense > 0 ? $item->total_expense : $item->bill_nominal;
                                $totalPayment = $item->total_payment > 0 ? $item->total_payment : $item->total_expense;
                            @endphp
                            <tr class="border-b border-gray-50">
                                <td class="py-3 px-4 text-gray-500 text-center">{{ $i + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $item->payment_name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->registration_number ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->account_name ?? '-' }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->admin_fee, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalPayment, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-gray-500 text-center">{{ $item->transaction_date ? $item->transaction_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">Belum ada rincian item.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @elseif($rab->expenseType?->code === 'pnbp')
                {{-- PNBP TABLE --}}
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-sm text-left min-w-[900px]">
                        <thead>
                            <tr class="text-xs text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 font-bold text-center w-12">No</th>
                                <th class="py-3 px-4 font-bold">Nama & No. Agenda</th>
                                <th class="py-3 px-4 font-bold">Jenis Level</th>
                                <th class="py-3 px-4 font-bold text-right w-48">Tarif PNBP</th>
                                <th class="py-3 px-4 font-bold">Nama Perusahaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rab->getExpenseItems() as $i => $item)
                            <tr class="border-b border-gray-50">
                                <td class="py-3 px-4 text-gray-500 text-center">{{ $i + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">
                                    <div>{{ $item->item_name }}</div>
                                    <div class="text-xs text-gray-400 font-normal mt-0.5">No. Agenda: {{ $item->agenda_number }}</div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">Level {{ $item->level }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($item->tarif_pnbp, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->company_name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400">Belum ada rincian item.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-gray-400">
                    <p>Tidak ada rincian item untuk jenis pengeluaran ini.</p>
                </div>
                @endif
            </div>

            {{-- Approval History --}}
            @if($rab->approvals->count() > 0)
            <div class="border border-gray-100 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800">Riwayat Approval</h4>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($rab->approvals as $approval)
                    <div class="flex items-start p-3 rounded-lg {{ $approval->status === \App\Enums\ApprovalStatus::APPROVED ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
                        <div class="flex-1">
                            <p class="text-sm font-bold {{ $approval->status === \App\Enums\ApprovalStatus::APPROVED ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $approval->status->label() }} oleh {{ $approval->user->name ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $approval->user ? $approval->user->role->label() : ((\App\Enums\UserRole::tryFrom($approval->role))?->label() ?? $approval->role) }} &bull; {{ $approval->created_at->format('d/m/Y H:i') }}</p>
                            @if($approval->notes)
                            <p class="text-sm text-gray-600 mt-2 italic">"{{ $approval->notes }}"</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Payment Info --}}
            @if($rab->payment)
            <div class="border border-gray-100 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800">Informasi Pencairan Dana</h4>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Tanggal Transfer</p><p class="text-sm font-semibold mt-1">{{ $rab->payment->payment_date->format('d/m/Y') }}</p></div>
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Nominal</p><p class="text-sm font-semibold mt-1 text-emerald-600">Rp {{ number_format($rab->payment->paid_amount, 0, ',', '.') }}</p></div>
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Metode Transfer</p><p class="text-sm font-semibold mt-1">{{ $rab->payment->payment_method }}</p></div>
                        @if($rab->payment->proof_file_path)
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Bukti Transfer</p>
                            <button type="button" onclick="openRabProofModal(@js(route('file.show', ['path' => $rab->payment->proof_file_path], false)), 'Bukti Transfer {{ $rab->rab_number }}')" class="text-sm font-bold text-blue-600 hover:underline mt-1 inline-block">Lihat Lampiran</button>
                        </div>
                        @endif
                    </div>
                    @include('payments._validation_status', ['payment' => $rab->payment])
                </div>
            </div>
            @endif

            {{-- Nota Belanja / LPJ Section --}}
            @if(in_array($rab->status, [\App\Enums\RabStatus::DISETUJUI, \App\Enums\RabStatus::SELESAI]) || $rab->receipts->isNotEmpty())
                @include('rab._receipts_section')
            @endif

            {{-- Discussion Notes --}}
            <div class="border border-gray-100 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800">Catatan Diskusi ({{ $rab->discussions->count() }})</h4>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($rab->discussions->sortBy('created_at') as $discussion)
                    <div class="flex gap-3">
                        @if($discussion->user && $discussion->user->avatar)
                        <div class="avatar-clickable">
                        <img src="{{ asset('storage/' . $discussion->user->avatar) }}" alt="{{ $discussion->user->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 flex-shrink-0">
                        </div>
                        @else
                        <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($discussion->user->name ?? 'U', 0, 1)) }}
                        </div>
                        @endif
                        <div class="flex-1 bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <p class="text-sm font-bold text-gray-800">{{ $discussion->user->name ?? '-' }}</p>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded uppercase">{{ $discussion->user->role->label() ?? '-' }}</span>
                                <span class="text-xs text-gray-400">{{ $discussion->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $discussion->message }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-sm text-gray-400 py-4">Belum ada catatan diskusi.</div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('rab.discussions.store', $rab) }}" class="p-4 border-t border-gray-100 bg-gray-50">
                    @csrf
                    <div class="flex gap-2">
                        <textarea name="message" rows="1" required class="flex-1 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none resize-none" placeholder="Ketik catatan..."></textarea>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap">Kirim</button>
                    </div>
                </form>
            </div>

        </div>

        {{-- FOOTER (Approval Buttons) --}}
        @php
            $canApproveThisRab = ($role === 'manajer' && $rab->status->value === 'diajukan') ||
                    ($role === 'direktur' && $rab->status->value === 'disetujui_manajer');
        @endphp
        <div class="p-5 border-t border-gray-100 bg-white shrink-0">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">

                {{-- Download PDF --}}
                @if($role === 'manajer')
                <a href="{{ route('rab.export-pdf', $rab->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-sm flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download PDF
                </a>
                @else
                <div></div>
                @endif

                {{-- Approval Actions --}}
                @if($canApproveThisRab)
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="button" onclick="openRejectModal('{{ $rab->id }}')" class="flex-1 sm:flex-none bg-white border-2 border-red-200 text-red-600 hover:bg-red-50 px-8 py-2.5 rounded-xl font-bold transition flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak
                    </button>
                    <form action="{{ route($role === 'manajer' ? 'rab.approve.manager' : 'rab.approve.director', $rab->id) }}" method="POST" class="flex-1 sm:flex-none">
                        @csrf
                        <button type="submit" onclick="return confirm('Setujui RAB ini?')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold transition shadow-lg shadow-emerald-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui RAB
                        </button>
                    </form>
                </div>
                @else
                {{-- Cairkan Dana button for Manajer when RAB is approved but not yet paid --}}
                @if($role === 'manajer' && $rab->status === \App\Enums\RabStatus::DISETUJUI && !$rab->payment)
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="button" onclick="closeRabModal('rabDetailModal-{{ $rab->id }}')" class="flex-1 sm:flex-none bg-gray-100 text-gray-600 px-8 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">
                        Tutup
                    </button>
                    <button type="button" onclick="closeRabModal('rabDetailModal-{{ $rab->id }}'); openPaymentModal('paymentModal-{{ $rab->id }}')" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold transition shadow-lg shadow-emerald-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Cairkan Dana
                    </button>
                </div>
                @else
                <button type="button" onclick="closeRabModal('rabDetailModal-{{ $rab->id }}')" class="bg-gray-100 text-gray-600 px-8 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">
                    Tutup
                </button>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Include Pencairan Dana Modal for Manajer --}}
@if($role === 'manajer' && $rab->status === \App\Enums\RabStatus::DISETUJUI && !$rab->payment)
    @include('payments._create_modal')
@endif

@endforeach

{{-- ============================================================ --}}
{{-- Modal Tolak RAB (Reject) --}}
{{-- ============================================================ --}}
<div id="rejectRabModal" class="fixed inset-0 bg-black/60 z-[60] hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-lg font-extrabold text-gray-800">Tolak RAB</h3>
            <p class="text-sm text-gray-500 mt-1">Berikan catatan alasan penolakan. Catatan ini wajib diisi.</p>
        </div>
        <form id="rejectRabForm" method="POST" action="">
            @csrf
            <div class="p-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="4" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none resize-none" placeholder="Jelaskan alasan penolakan RAB ini..."></textarea>
            </div>
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="closeRejectModal()" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-xl text-sm font-bold transition">Batal</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-red-200">Konfirmasi Tolak</button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- Modal Bukti Bayar --}}
{{-- ============================================================ --}}
<div id="rabProofModal" class="fixed inset-0 bg-black/60 z-[70] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 id="rabProofTitle" class="text-base font-bold text-gray-800">Bukti Bayar</h3>
            <button type="button" onclick="closeRabProofModal()" class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="bg-gray-100 min-h-[65vh] p-4 flex items-center justify-center overflow-auto">
            <iframe id="rabProofFrame" src="" class="hidden w-full min-h-[65vh] bg-white rounded-xl border-0"></iframe>
            <img id="rabProofImage" src="" alt="Bukti Bayar" class="hidden max-w-full max-h-[70vh] object-contain rounded-xl bg-white shadow">
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
            <a id="rabProofOpenLink" href="#" download class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Bukti
            </a>
            <button type="button" onclick="closeRabProofModal()" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2 rounded-lg text-sm font-bold transition">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Detail Modal ──
    function openRabModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeRabModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    // ── Reject Modal ──
    function openRejectModal(rabId) {
        const modal = document.getElementById('rejectRabModal');
        const form = document.getElementById('rejectRabForm');
        const role = @js($role);
        const routeName = role === 'manajer' ? 'reject-manager' : 'reject-director';
        form.action = '/rab/' + rabId + '/' + routeName;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectRabModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    // ── Proof Modal ──
    function openRabProofModal(url, title) {
        const modal = document.getElementById('rabProofModal');
        const frame = document.getElementById('rabProofFrame');
        const image = document.getElementById('rabProofImage');
        document.getElementById('rabProofTitle').innerText = title || 'Bukti Transfer';
        document.getElementById('rabProofOpenLink').href = url + '?download=1';

        frame.classList.add('hidden');
        image.classList.add('hidden');
        frame.src = '';
        image.src = '';

        if (url.split('?')[0].toLowerCase().endsWith('.pdf')) {
            frame.src = url;
            frame.classList.remove('hidden');
        } else {
            image.src = url;
            image.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeRabProofModal() {
        const modal = document.getElementById('rabProofModal');
        document.getElementById('rabProofFrame').src = '';
        document.getElementById('rabProofImage').src = '';
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    // ── Link Interception (open modal instead of navigating) ──
    document.addEventListener('click', function(event) {
        const detailLink = event.target.closest('a[href]');
        if (!detailLink) return;

        const path = new URL(detailLink.href, window.location.origin).pathname;
        const match = path.match(/^\/(manajer|direktur)\/rab\/(\d+)$/);
        if (!match) return;

        const modal = document.getElementById('rabDetailModal-' + match[2]);
        if (!modal) return;

        event.preventDefault();
        openRabModal('rabDetailModal-' + match[2]);
    });

    // ── Escape Key ──
    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('[id^="rabDetailModal-"]').forEach(function(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
        closeRejectModal();
        closeRabProofModal();
        // Also close payment modals on Escape
        document.querySelectorAll('[id^="paymentModal-"]').forEach(function(m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });
        document.body.classList.remove('overflow-hidden');
    });

    // ── Click Outside Modal ──
    document.querySelectorAll('[id^="rabDetailModal-"]').forEach(function(modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeRabModal(modal.id);
            }
        });
    });

    document.getElementById('rejectRabModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeRejectModal();
        }
    });

    // ── Auto-open modal from URL param (only on first load via redirect) ──
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const openRabId = urlParams.get('open_rab_id');
        if (openRabId) {
            openRabModal('rabDetailModal-' + openRabId);
            // Clean up the URL so navigating tabs doesn't re-open the modal
            urlParams.delete('open_rab_id');
            const cleanUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState({}, '', cleanUrl);
        }
    });

    window.setSort = function(val) {
        const sortInput = document.getElementById('sort_input');
        if (sortInput) {
            sortInput.value = val;
            sortInput.closest('form').submit();
        }
    };

    // ── Payment Modal (Cairkan Dana) ──
    function openPaymentModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closePaymentModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endpush
