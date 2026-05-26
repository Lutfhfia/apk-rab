@extends('layouts.app')
@section('title', 'Manajemen RAB')
@section('page-title', 'Manajemen RAB')
@section('page-subtitle', 'Daftar seluruh Rancangan Anggaran Biaya')

@section('sidebar-menu')
@include('admin._sidebar')
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h2 class="text-lg font-bold text-gray-800">Daftar RAB</h2>
    </div>
    <button type="button" onclick="openCreateRabModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center transition shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Buat RAB Baru
    </button>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Diajukan</p>
        <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $statusCounts['diajukan'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Disetujui</p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ ($statusCounts['disetujui_manajer'] ?? 0) + ($statusCounts['disetujui_direktur'] ?? 0) + ($statusCounts['disetujui'] ?? 0) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Ditolak</p>
        <p class="text-2xl font-extrabold text-red-600 mt-1">{{ $statusCounts['ditolak'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Selesai</p>
        <p class="text-2xl font-extrabold text-green-600 mt-1">{{ $statusCounts['selesai'] ?? 0 }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-4">
    <form method="GET" action="{{ route('rab.index') }}" class="flex flex-col gap-4">
        {{-- Preserve active status tab --}}
        <input type="hidden" name="status" value="{{ request('status', \App\Enums\RabStatus::DIAJUKAN->value) }}">

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
            <a href="{{ route('rab.index') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium">Reset</a>
        </div>
    </form>
</div>

@php
    $statusTabs = [
        \App\Enums\RabStatus::DIAJUKAN,
        \App\Enums\RabStatus::SELESAI,
        \App\Enums\RabStatus::DITOLAK,
        \App\Enums\RabStatus::DISETUJUI,
        \App\Enums\RabStatus::DISETUJUI_MANAJER,
    ];
    $activeStatus = request('status', \App\Enums\RabStatus::DIAJUKAN->value);
@endphp
<div class="flex gap-2 overflow-x-auto pb-2 mb-6">
    @foreach($statusTabs as $statusTab)
    <a href="{{ route('rab.index', array_merge(request()->except('page'), ['status' => $statusTab->value])) }}"
       class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition {{ $activeStatus === $statusTab->value ? $statusTab->badgeClasses() . ' border-transparent' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
        {{ $statusTab->label() }}
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
                    <th class="py-3.5 px-5 font-bold">Jenis Pengeluaran</th>
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
                            {{-- === AKSI BERDASARKAN STATUS === --}}

                            <a href="{{ route('rab.export-pdf', $rab) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Simpan PDF RAB">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>

                            {{-- DRAFT: Edit, Hapus --}}
                            @if($rab->status === \App\Enums\RabStatus::DRAFT)
                                <button type="button" onclick="openEditRabModal('editRabModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('rab.destroy', $rab) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus draft RAB ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition" title="Hapus Draft RAB">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>

                            {{-- DIAJUKAN: Detail, Edit, WhatsApp, Hapus --}}
                            @elseif($rab->status === \App\Enums\RabStatus::DIAJUKAN)
                                <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button type="button" onclick="openEditRabModal('editRabModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @php
                                    $waText = urlencode($rab->buildWhatsAppSubmissionMessage(route('manajer.rab.show', $rab)));
                                @endphp
                                <a href="https://wa.me/?text={{ $waText }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition" title="Kirim ke WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('rab.destroy', $rab) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengajuan RAB ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition" title="Hapus Pengajuan RAB">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>

                            {{-- DISETUJUI_MANAJER / DISETUJUI_DIREKTUR: Detail --}}
                            @elseif(in_array($rab->status, [\App\Enums\RabStatus::DISETUJUI_MANAJER, \App\Enums\RabStatus::DISETUJUI_DIREKTUR]))
                                <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>

                            {{-- DISETUJUI: Detail, Upload Bukti --}}
                            @elseif($rab->status === \App\Enums\RabStatus::DISETUJUI)
                                <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button type="button" onclick="openPaymentModal('paymentModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition" title="Upload Bukti Bayar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                </button>

                            {{-- DITOLAK: Edit, Detail --}}
                            @elseif($rab->status === \App\Enums\RabStatus::DITOLAK)
                                <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button type="button" onclick="openEditRabModal('editRabModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                            {{-- SELESAI: Detail, PDF --}}
                            @elseif($rab->status === \App\Enums\RabStatus::SELESAI)
                                <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-400">Belum ada data RAB.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rabs->hasPages())
    <div class="p-5 border-t border-gray-100">{{ $rabs->links() }}</div>
    @endif
</div>

{{-- Modal Detail RAB dari halaman daftar --}}
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
            <button type="button" onclick="closeRabModal('rabDetailModal-{{ $rab->id }}')" class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition flex-shrink-0" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- BODY (scrollable) --}}
        <div class="p-5 flex-1 overflow-y-auto min-h-0">

            {{-- Info Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
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
                    <table class="w-full text-sm text-left min-w-[1200px]">
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
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalVal, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-gray-400">Belum ada rincian item.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @elseif($rab->expenseType?->code === 'bulanan')
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
                                {{ $approval->status->label() }} oleh {{ $approval->user->name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $approval->user ? $approval->user->role->label() : (\App\Enums\UserRole::tryFrom($approval->role)?->label() ?? $approval->role) }} &bull; {{ $approval->created_at->format('d/m/Y H:i') }}</p>
                            @if($approval->notes)
                            <p class="text-sm text-gray-600 mt-2 italic">"{{ $approval->notes }}"</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Edit History --}}
            @if($rab->auditLogs->count() > 0)
            <div class="border border-gray-100 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800">Riwayat Pengeditan</h4>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($rab->auditLogs->sortByDesc('created_at') as $log)
                    <div class="rounded-lg bg-slate-50 border border-slate-100 p-3">
                        <p class="text-sm font-bold text-slate-700">{{ str_replace('_', ' ', strtoupper($log->action)) }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $log->description }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $log->user->name ?? '-' }} &bull; {{ $log->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Payment Info --}}
            @if($rab->payment)
            <div class="border border-gray-100 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800">Informasi Pembayaran</h4>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Tanggal Bayar</p><p class="text-sm font-semibold mt-1">{{ $rab->payment->payment_date->format('d/m/Y') }}</p></div>
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Nominal</p><p class="text-sm font-semibold mt-1 text-emerald-600">Rp {{ number_format($rab->payment->paid_amount, 0, ',', '.') }}</p></div>
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Metode</p><p class="text-sm font-semibold mt-1">{{ $rab->payment->payment_method }}</p></div>
                        @if($rab->payment->proof_file_path)
                        <div><p class="text-xs font-bold text-gray-400 uppercase">Bukti Bayar</p>
                            <button type="button" onclick="openRabProofModal(@js(route('file.show', ['path' => $rab->payment->proof_file_path], false)), 'Bukti Bayar {{ $rab->rab_number }}')" class="text-sm font-bold text-blue-600 hover:underline mt-1 inline-block">Lihat Lampiran</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Catatan Diskusi --}}
            <div class="border border-gray-100 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
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

        {{-- FOOTER (Admin Action Buttons) --}}
        <div class="p-5 border-t border-gray-100 bg-white shrink-0">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                {{-- Download PDF --}}
                <a href="{{ route('rab.export-pdf', $rab) }}" target="_blank" class="text-red-600 hover:text-red-800 font-bold text-sm flex items-center transition bg-red-50 hover:bg-red-100 px-4 py-2.5 rounded-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Simpan / Cetak PDF
                </a>

                <button type="button" onclick="closeRabModal('rabDetailModal-{{ $rab->id }}')" class="w-full sm:w-auto bg-gray-100 text-gray-600 px-8 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition text-center">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Sertakan Modal Edit RAB (Hanya untuk yang bisa diedit) --}}
@if($rab->status === \App\Enums\RabStatus::DRAFT || $rab->status === \App\Enums\RabStatus::DITOLAK || $rab->status === \App\Enums\RabStatus::DIAJUKAN)
    @include('rab._edit_modal')
@endif

{{-- Sertakan Modal Upload Bukti (Hanya untuk yang disetujui) --}}
@if($rab->status === \App\Enums\RabStatus::DISETUJUI)
    @include('payments._create_modal')
@endif

@endforeach

{{-- Modal Bukti Bayar --}}
<div id="rabProofModal" class="fixed inset-0 bg-black/60 z-[60] hidden items-center justify-center p-4">
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

{{-- Modal Popup Setelah Ajukan RAB --}}
@if(session('submitted_rab_id'))
<div id="submitSuccessModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl text-center animate-fade-in">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-xl font-extrabold text-gray-800 mb-2">RAB Berhasil Diajukan!</h3>
        <p class="text-sm text-gray-500 mb-1">No: <span class="font-bold text-gray-700">{{ session('submitted_rab_number') }}</span></p>
        <p class="text-sm text-gray-500 mb-1">Jenis: {{ session('submitted_rab_type') }}</p>
        <p class="text-lg font-extrabold text-emerald-600 mb-6">Rp {{ number_format(session('submitted_rab_total'), 0, ',', '.') }}</p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="document.getElementById('submitSuccessModal').style.display='none'" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold transition">
                Kembali ke Daftar RAB
            </button>
            @php
                $waTextModal = $submittedRabForWhatsApp
                    ? urlencode($submittedRabForWhatsApp->buildWhatsAppSubmissionMessage(route('manajer.rab.show', $submittedRabForWhatsApp)))
                    : null;
            @endphp
            @if($waTextModal)
            <a href="https://wa.me/?text={{ $waTextModal }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Kirim ke WhatsApp
            </a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Sertakan Modal Buat RAB --}}
@include('rab._create_modal')

@endsection

@push('scripts')
<script>
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

    function openEditRabModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeEditRabModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

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

    function openRabProofModal(url, title) {
        const modal = document.getElementById('rabProofModal');
        const frame = document.getElementById('rabProofFrame');
        const image = document.getElementById('rabProofImage');
        document.getElementById('rabProofTitle').innerText = title || 'Bukti Bayar';
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

    document.addEventListener('click', function(event) {
        const detailLink = event.target.closest('a[href]');
        if (!detailLink) return;

        const path = new URL(detailLink.href, window.location.origin).pathname;
        const match = path.match(/^\/rab\/(\d+)$/);
        if (!match) return;

        const modal = document.getElementById('rabDetailModal-' + match[1]);
        if (!modal) return;

        event.preventDefault();
        openRabModal('rabDetailModal-' + match[1]);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('[id^="rabDetailModal-"]').forEach(function(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
        document.querySelectorAll('[id^="editRabModal-"]').forEach(function(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
        document.querySelectorAll('[id^="paymentModal-"]').forEach(function(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
        closeRabProofModal();
        document.body.classList.remove('overflow-hidden');
    });

    document.querySelectorAll('[id^="rabDetailModal-"]').forEach(function(modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeRabModal(modal.id);
            }
        });
    });

    // Auto-open modal if open_rab_id is in query string
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const openRabId = urlParams.get('open_rab_id');
        if (openRabId) {
            openRabModal('rabDetailModal-' + openRabId);
        }
    });

    window.setSort = function(val) {
        const sortInput = document.getElementById('sort_input');
        if (sortInput) {
            sortInput.value = val;
            sortInput.closest('form').submit();
        }
    };
</script>

{{-- Sertakan Scripts untuk Buat RAB --}}
@include('rab._create_scripts')
@endpush
