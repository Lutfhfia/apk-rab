@extends('layouts.app')
@section('title', 'Validasi dan Rekap Nota / LPJ')
@section('page-title', 'Validasi dan Rekap Nota / LPJ')
@section('page-subtitle', 'Validasi nota belanja / LPJ, riwayat keputusan, dan rekap LPJ per periode')

@section('sidebar-menu')
    @include($routePrefix . '._sidebar')
@endsection

@section('content')
    @php
        $tabClasses = 'px-4 py-2.5 text-sm font-bold border-b-2 transition';
        $activeClasses = 'border-emerald-500 text-emerald-700';
        $inactiveClasses = 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-200';
        $baseRecapParams = request()->except(['mode']);
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-5">
        <div class="px-4 sm:px-5 pt-4 border-b border-gray-100">
            <nav class="flex flex-wrap gap-1">
                @if(!auth()->user()->isDirektur())
                <a href="{{ route($routePrefix . '.receipts.index', ['tab' => 'pending']) }}"
                    class="{{ $tabClasses }} {{ $activeTab === 'pending' ? $activeClasses : $inactiveClasses }}">Menunggu
                    Validasi</a>
                <a href="{{ route($routePrefix . '.receipts.index', ['tab' => 'history']) }}"
                    class="{{ $tabClasses }} {{ $activeTab === 'history' ? $activeClasses : $inactiveClasses }}">Riwayat
                    Validasi</a>
                @endif
                <a href="{{ route($routePrefix . '.receipts.index', ['tab' => 'recap']) }}"
                    class="{{ $tabClasses }} {{ $activeTab === 'recap' ? $activeClasses : $inactiveClasses }}">Rekap
                    LPJ</a>
            </nav>
        </div>
    </div>

    @if ($activeTab === 'pending')
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h2 class="text-lg font-bold text-gray-800">Nota Belanja / LPJ Menunggu Validasi</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-4">
            <form method="GET" action="{{ route('manajer.receipts.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <input type="hidden" name="tab" value="pending">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Cari RAB / Toko / Vendor</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                        placeholder="Ketik nomor RAB, nama toko, atau vendor...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Urutan</label>
                    <select name="sort"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        <option value="desc" {{ request('sort', 'desc') === 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort') === 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="md:col-span-5 flex justify-end gap-2">
                    <a href="{{ route($routePrefix . '.receipts.index', ['tab' => 'pending']) }}"
                        class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium flex items-center">Reset</a>
                    <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-bold transition">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/95 shadow-sm">
                        <tr class="text-xs text-gray-500 border-b border-gray-100">
                            <th class="py-3.5 px-5 font-bold">No. RAB</th>
                            <th class="py-3.5 px-5 font-bold">Toko / Vendor</th>
                            <th class="py-3.5 px-5 font-bold">Tanggal Nota</th>
                            <th class="py-3.5 px-5 font-bold">Nominal</th>
                            <th class="py-3.5 px-5 font-bold">Uploader</th>
                            <th class="py-3.5 px-5 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        @forelse($receipts as $receipt)
                            <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-gray-800">{{ $receipt->rab->rab_number ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $receipt->rab->expenseType->name ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-semibold text-gray-800">{{ $receipt->store_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-400">No. Nota: {{ $receipt->receipt_number ?: '-' }}
                                    </div>
                                </td>
                                <td class="py-4 px-5">{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                                <td class="py-4 px-5 font-bold text-emerald-700">Rp
                                    {{ number_format($receipt->total_amount, 0, ',', '.') }}</td>
                                <td class="py-4 px-5">{{ $receipt->uploader->name ?? '-' }}</td>
                                <td class="py-4 px-5">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button"
                                            onclick="openRabProofModal(@js(route('file.show', ['path' => $receipt->receipt_file], false)), 'Nota LPJ {{ $receipt->rab->rab_number ?? '' }}')"
                                            class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-lg text-xs font-bold transition">Lihat</button>
                                        @if(auth()->user()->isManajer())
                                        <form method="POST"
                                            action="{{ route('rab.receipts.approve', [$receipt->rab, $receipt]) }}"
                                            onsubmit="return confirm('Validasi nota LPJ ini?')">
                                            @csrf
                                            <button type="submit"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-xs font-bold transition">Setujui</button>
                                        </form>
                                        <button type="button" onclick="openRejectPaymentModal('{{ $receipt->id }}')"
                                            class="bg-red-50 text-red-700 hover:bg-red-100 px-3 py-2 rounded-lg text-xs font-bold transition">Tolak</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">Tidak ada nota belanja / LPJ yang
                                    menunggu validasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($receipts->hasPages())
                <div class="p-5 border-t border-gray-100">{{ $receipts->links() }}</div>
            @endif
        </div>

        @foreach ($receipts as $receipt)
            <div id="rejectPaymentModal-{{ $receipt->id }}"
                class="fixed inset-0 bg-black/60 z-[60] hidden items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-lg font-extrabold text-gray-800">Tolak Nota Belanja / LPJ</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $receipt->rab->rab_number ?? '-' }} - Rp
                            {{ number_format($receipt->total_amount, 0, ',', '.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('rab.receipts.reject', [$receipt->rab, $receipt]) }}">
                        @csrf
                        <div class="p-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Penolakan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="notes" rows="4" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none resize-none"
                                placeholder="Jelaskan alasan penolakan nota belanja / LPJ ini..."></textarea>
                        </div>
                        <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                            <button type="button" onclick="closeRejectPaymentModal('{{ $receipt->id }}')"
                                class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-xl text-sm font-bold transition">Batal</button>
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition">Konfirmasi
                                Tolak</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    @if ($activeTab === 'history')
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h2 class="text-lg font-bold text-gray-800">Riwayat Validasi Nota Belanja / LPJ</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-4">
            <form method="GET" action="{{ route('manajer.receipts.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <input type="hidden" name="tab" value="history">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Cari RAB / Toko / Vendor</label>
                    <input type="text" name="history_search" value="{{ request('history_search') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                        placeholder="Ketik nomor RAB, toko, atau vendor...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="history_start_date" value="{{ request('history_start_date') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="history_end_date" value="{{ request('history_end_date') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Urutan</label>
                    <select name="history_sort"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        <option value="desc" {{ request('history_sort', 'desc') === 'desc' ? 'selected' : '' }}>Terbaru
                        </option>
                        <option value="asc" {{ request('history_sort') === 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="md:col-span-5 flex justify-end gap-2">
                    <a href="{{ route($routePrefix . '.receipts.index', ['tab' => 'history']) }}"
                        class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium flex items-center">Reset</a>
                    <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-bold transition">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs text-gray-500 border-b border-gray-100">
                            <th class="py-3.5 px-5 font-bold">No. RAB</th>
                            <th class="py-3.5 px-5 font-bold">Toko / Vendor</th>
                            <th class="py-3.5 px-5 font-bold">Nominal</th>
                            <th class="py-3.5 px-5 font-bold">Status</th>
                            <th class="py-3.5 px-5 font-bold">Validator</th>
                            <th class="py-3.5 px-5 font-bold">Lampiran</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        @forelse($historyReceipts as $receipt)
                            <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-gray-800">{{ $receipt->rab->rab_number ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $receipt->receipt_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-semibold text-gray-800">{{ $receipt->store_name ?: '-' }}</div>
                                    @if ($receipt->notes)
                                        <div class="text-xs text-red-600 mt-1">{{ $receipt->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-5 font-bold text-emerald-700">Rp
                                    {{ number_format($receipt->total_amount, 0, ',', '.') }}</td>
                                <td class="py-4 px-5">
                                    <span
                                        class="{{ $receipt->status->badgeClasses() }} text-[10px] font-bold px-3 py-1.5 rounded-lg">{{ $receipt->status->label() }}</span>
                                </td>
                                <td class="py-4 px-5">
                                    <div>{{ $receipt->validator->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $receipt->validated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <button type="button"
                                        onclick="openRabProofModal(@js(route('file.show', ['path' => $receipt->receipt_file], false)), 'Nota LPJ {{ $receipt->rab->rab_number ?? '' }}')"
                                        class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-lg text-xs font-bold transition">Lihat</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">Belum ada riwayat validasi nota
                                    belanja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($historyReceipts->hasPages())
                <div class="p-5 border-t border-gray-100">{{ $historyReceipts->links() }}</div>
            @endif
        </div>
    @endif

    @if ($activeTab === 'recap')
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Rekap LPJ / Nota Belanja</h2>
                <p class="text-sm text-gray-500 mt-1">Periode {{ $periodLabel }}</p>
            </div>
            @if(!auth()->user()->isDirektur())
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rab.payments.recap.pdf', array_merge($baseRecapParams, ['tab' => 'recap', 'mode' => 'preview'])) }}"
                    target="_blank"
                    class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-bold transition">Preview
                    PDF</a>
                <a href="{{ route('rab.payments.recap.pdf', array_merge($baseRecapParams, ['tab' => 'recap', 'mode' => 'download'])) }}"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">Download
                    PDF</a>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase">Total Nominal</p>
                <p class="text-xl font-extrabold text-emerald-700 mt-1">Rp
                    {{ number_format($totalValidAmount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase">Jumlah RAB</p>
                <p class="text-xl font-extrabold text-gray-800 mt-1">{{ $totalRabCount }}</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase">Jumlah Nota</p>
                <p class="text-xl font-extrabold text-gray-800 mt-1">{{ $totalValidPaymentCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-4">
            <form method="GET" action="{{ route($routePrefix . '.receipts.index') }}" class="space-y-4">
                <input type="hidden" name="tab" value="recap">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2">Periode Rekap</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($allowedRanges as $option)
                            <label class="cursor-pointer">
                                <input type="radio" name="range" value="{{ $option }}" class="peer sr-only"
                                    {{ $range === $option ? 'checked' : '' }}>
                                <span
                                    class="block px-4 py-2 rounded-lg border text-sm font-bold peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 border-gray-200 text-gray-600">{{ $option }}
                                    Bulan</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Bulan Akhir</label>
                        <select name="month"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            @foreach ($months as $monthNumber => $monthName)
                                <option value="{{ $monthNumber }}"
                                    {{ (int) $month === $monthNumber ? 'selected' : '' }}>{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Tahun</label>
                        <input type="number" name="year" value="{{ $year }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date"
                            value="{{ request('start_date', $startDate->toDateString()) }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date"
                            value="{{ request('end_date', $endDate->toDateString()) }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Cari</label>
                        <input type="text" name="recap_search" value="{{ request('recap_search') }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                            placeholder="No. RAB / toko / vendor">
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route($routePrefix . '.receipts.index', ['tab' => 'recap']) }}"
                        class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium">Reset</a>
                    <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-bold transition">Terapkan
                        Filter</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div>
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Daftar Nota Belanja / LPJ Valid
                </h3>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs text-gray-500 border-b border-gray-100">
                                    <th class="py-3.5 px-5 font-bold">No. RAB</th>
                                    <th class="py-3.5 px-5 font-bold">Kategori</th>
                                    <th class="py-3.5 px-5 font-bold">Tanggal Nota</th>
                                    <th class="py-3.5 px-5 font-bold">Toko / Vendor</th>
                                    <th class="py-3.5 px-5 font-bold">No. Nota</th>
                                    <th class="py-3.5 px-5 font-bold">Nominal</th>
                                    <th class="py-3.5 px-5 font-bold">Status</th>
                                    <th class="py-3.5 px-5 font-bold">Lampiran</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-600">
                                @forelse($recapReceipts as $receipt)
                                    <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors">
                                        <td class="py-4 px-5 font-bold text-gray-800">
                                            {{ $receipt->rab->rab_number ?? '-' }}</td>
                                        <td class="py-4 px-5">{{ $receipt->rab->expenseType->name ?? '-' }}</td>
                                        <td class="py-4 px-5">{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                                        <td class="py-4 px-5">{{ $receipt->store_name ?: '-' }}</td>
                                        <td class="py-4 px-5">{{ $receipt->receipt_number ?: '-' }}</td>
                                        <td class="py-4 px-5 font-bold text-emerald-700">Rp
                                            {{ number_format($receipt->total_amount, 0, ',', '.') }}</td>
                                        <td class="py-4 px-5">
                                            <span
                                                class="{{ $receipt->status->badgeClasses() }} text-[10px] font-bold px-3 py-1.5 rounded-lg">{{ $receipt->status->label() }}</span>
                                        </td>
                                        <td class="py-4 px-5">
                                            <button type="button"
                                                onclick="openRabProofModal(@js(route('file.show', ['path' => $receipt->receipt_file], false)), 'Nota LPJ {{ $receipt->rab->rab_number ?? '' }}')"
                                                class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-lg text-xs font-bold transition">Lihat</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-12 text-center text-gray-400">Tidak ada data rekap
                                            nota belanja untuk filter ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="rabProofModal" class="fixed inset-0 bg-black/60 z-[70] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 id="rabProofTitle" class="text-base font-bold text-gray-800">Nota LPJ</h3>
                <button type="button" onclick="closeRabProofModal()"
                    class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition"
                    aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="bg-gray-100 min-h-[65vh] p-4 flex items-center justify-center overflow-auto">
                <iframe id="rabProofFrame" src=""
                    class="hidden w-full min-h-[65vh] bg-white rounded-xl border-0"></iframe>
                <img id="rabProofImage" src="" alt="Nota LPJ"
                    class="hidden max-w-full max-h-[70vh] object-contain rounded-xl bg-white shadow">
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <a id="rabProofOpenLink" href="#" download
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition">Unduh
                    Lampiran</a>
                <button type="button" onclick="closeRabProofModal()"
                    class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2 rounded-lg text-sm font-bold transition">Tutup</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openRejectPaymentModal(id) {
            const modal = document.getElementById('rejectPaymentModal-' + id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeRejectPaymentModal(id) {
            const modal = document.getElementById('rejectPaymentModal-' + id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function openRabProofModal(url, title) {
            const modal = document.getElementById('rabProofModal');
            const frame = document.getElementById('rabProofFrame');
            const image = document.getElementById('rabProofImage');
            document.getElementById('rabProofTitle').innerText = title || 'Nota LPJ';
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

        // Escape key listener to close modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeRabProofModal();
            }
        });

        function closeRabProofModal() {
            const modal = document.getElementById('rabProofModal');
            if (!modal || modal.classList.contains('hidden')) return;
            document.getElementById('rabProofFrame').src = '';
            document.getElementById('rabProofImage').src = '';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
@endpush
