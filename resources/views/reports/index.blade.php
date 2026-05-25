@extends('layouts.app')
@section('title', 'Export Laporan Arus Kas')
@section('page-title', 'Laporan Arus Kas')
@section('page-subtitle', 'Laporan arus kas bulanan berbasis transaksi RAB')
@section('sidebar-menu')
    @if ($sidebarRole === 'manajer')
        @include('manajer._sidebar')
    @elseif($sidebarRole === 'direktur')
        @include('direktur._sidebar')
    @else
        @include('admin._sidebar')
    @endif
@endsection

@php
    $bulanList = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
@endphp

@section('content')
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        {{-- Transaksi Selesai --}}
        <div
            class="group bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-200 transition-all duration-300 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Transaksi</p>
                <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $totalTransaksi }} <span
                        class="text-base text-gray-400">Entri</span></p>
            </div>
        </div>

        {{-- Total Pemasukan --}}
        <div
            class="group bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all duration-300 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-20 h-20 bg-blue-50 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Uang Masuk</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">Rp
                    {{ number_format($totalUangMasuk, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Total Pengeluaran --}}
        <div
            class="group bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-red-200 transition-all duration-300 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-20 h-20 bg-red-50 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10">
                <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Uang Keluar</p>
                <p class="text-2xl font-extrabold text-red-600 mt-1">Rp {{ number_format($totalUangKeluar, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Saldo Akhir --}}
        <div
            class="group bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-200 transition-all duration-300 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-20 h-20 bg-indigo-50 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Saldo Akhir</p>
                <p class="text-2xl font-extrabold text-indigo-600 mt-1">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">Filter Laporan</h3>
        <form method="GET" action="{{ route('report.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[160px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Bulan Akhir</label>
                <select name="month"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    @foreach ($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ (int) $month === $num ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Tahun</label>
                <select name="year"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Rentang Periode</label>
                <select name="range"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    <option value="1" {{ (int) $range === 1 ? 'selected' : '' }}>1 Bulan</option>
                    <option value="3" {{ (int) $range === 3 ? 'selected' : '' }}>3 Bulan</option>
                    <option value="6" {{ (int) $range === 6 ? 'selected' : '' }}>6 Bulan</option>
                    <option value="9" {{ (int) $range === 9 ? 'selected' : '' }}>9 Bulan</option>
                    <option value="12" {{ (int) $range === 12 ? 'selected' : '' }}>12 Bulan</option>
                </select>
            </div>
            <div class="min-w-[240px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Nomor Surat</label>
                <input type="text" name="report_number" value="{{ $reportNumber }}"
                    placeholder="Contoh: 001/LAP-AK/SBK/V/2026"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Cari Transaksi</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="No. RAB atau keterangan..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div class="min-w-[100px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Urutan</label>
                <div class="flex border border-gray-200 rounded-lg overflow-hidden h-[38px] bg-white">
                    <input type="hidden" name="sort" id="sort_input" value="{{ request('sort', 'asc') }}">
                    <button type="button" onclick="setSort('desc')" id="btn_sort_desc" class="flex-1 flex items-center justify-center transition-colors {{ request('sort') === 'desc' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-50' }}" title="Terbaru (Urutan Ke Bawah)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" onclick="setSort('asc')" id="btn_sort_asc" class="flex-1 flex items-center justify-center border-l border-gray-200 transition-colors {{ request('sort', 'asc') === 'asc' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-50' }}" title="Terlama (Urutan Ke Atas)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 18l7-7 7 7"/></svg>
                    </button>
                </div>
            </div>
            <button type="submit"
                class="bg-slate-700 hover:bg-slate-800 text-white px-5 py-2 rounded-lg text-sm font-bold transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter Data
            </button>
            @if ($canPreview)
                <button type="submit" name="preview" value="1"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview Surat
                </button>
            @endif
            @if ($canExportCashFlowPdf && $cashFlows->count() > 0)
                <button type="submit" formaction="{{ route('report.export-pdf') }}" formmethod="GET"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF Surat
                </button>
            @endif
        </form>
    </div>

    {{-- Rincian Arus Kas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800">Rincian Arus Kas</h2>
                <p class="text-xs text-gray-400 mt-0.5">Transaksi uang masuk & keluar: {{ $periodLabel }}</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sticky-table w-full text-left text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-100 bg-gray-50/50">
                        <th class="py-3.5 px-4 font-bold">No</th>
                        <th class="py-3.5 px-4 font-bold">Tanggal</th>
                        <th class="py-3.5 px-4 font-bold">No. RAB</th>
                        <th class="py-3.5 px-4 font-bold">Keterangan</th>
                        <th class="py-3.5 px-4 font-bold text-right">Uang Masuk</th>
                        <th class="py-3.5 px-4 font-bold text-right">Uang Keluar</th>
                        <th class="py-3.5 px-4 font-bold text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Saldo Awal --}}
                    <tr class="bg-blue-50/50 border-b border-gray-100">
                        <td class="py-3 px-4 text-gray-400 italic">-</td>
                        <td class="py-3 px-4 text-gray-600 italic">-</td>
                        <td class="py-3 px-4 text-gray-600 italic">-</td>
                        <td class="py-3 px-4 font-bold text-gray-700 italic">Saldo Awal Periode</td>
                        <td class="py-3 px-4 text-right text-gray-400">-</td>
                        <td class="py-3 px-4 text-right text-gray-400">-</td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800">Rp
                            {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                    </tr>
                    @php $runBal = $saldoAwal; @endphp
                    @forelse($cashFlows as $i => $cf)
                        @php
                            $runBal = $runBal + (float) $cf->debit - (float) $cf->credit;
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors">
                            <td class="py-3 px-4 text-gray-500">{{ $i + 1 }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $cf->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 font-bold text-gray-800">{{ $cf->rab ? $cf->rab->rab_number : '-' }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $cf->description }}</td>
                            <td
                                class="py-3 px-4 text-right font-semibold {{ $cf->debit > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ $cf->debit > 0 ? 'Rp ' . number_format($cf->debit, 0, ',', '.') : '-' }}</td>
                            <td
                                class="py-3 px-4 text-right font-semibold {{ $cf->credit > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ $cf->credit > 0 ? 'Rp ' . number_format($cf->credit, 0, ',', '.') : '-' }}</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-800">Rp
                                {{ number_format($runBal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Tidak ada transaksi arus kas pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($cashFlows->count() > 0)
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="text-sm font-bold">
                            <td colspan="4" class="py-4 px-4 text-right text-gray-600">TOTAL</td>
                            <td class="py-4 px-4 text-right text-emerald-700">Rp
                                {{ number_format($totalUangMasuk, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-right text-red-700">Rp
                                {{ number_format($totalUangKeluar, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-right text-indigo-700">Rp
                                {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if ($cashFlows->count() > 0)
            <div class="p-5 bg-gray-50 border-t border-gray-100 flex flex-wrap justify-end items-center gap-8">
                <div class="text-right">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Total Pemasukan</p>
                    <p class="text-lg font-black text-emerald-600">Rp {{ number_format($totalUangMasuk, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Total Pengeluaran</p>
                    <p class="text-lg font-black text-red-600">Rp {{ number_format($totalUangKeluar, 0, ',', '.') }}</p>
                </div>
                <div class="text-right pl-8 border-l-2 border-gray-200">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Total Saldo Akhir</p>
                    <p class="text-2xl font-black text-indigo-700">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Preview Export PDF --}}
    @if ($showPreview && $cashFlows->count() > 0)
        <div id="preview-section" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-800">Preview Export PDF</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Periksa isi laporan sebelum mengunduh</p>
                </div>
                @if ($canExportCashFlowPdf)
                    <a href="{{ route('report.export-pdf', ['month' => $month, 'year' => $year, 'range' => $range, 'report_number' => $reportNumber]) }}"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF Surat
                    </a>
                @endif
            </div>

            <div class="p-8 bg-gray-100 flex justify-center">
                <div class="bg-white shadow-2xl mx-auto w-full max-w-[21cm] min-h-[29.7cm] relative flex flex-col"
                    style="font-family: 'Times New Roman', Times, serif;">
                    @if (file_exists(public_path('foto/header_sbk.png')))
                        <div class="w-full">
                            <img src="{{ asset('foto/header_sbk.png') }}"
                                class="w-full h-auto object-cover max-h-[130px] object-top">
                        </div>
                    @else
                        <div class="p-12 pb-0">
                            {{-- Kop Surat --}}
                            <div class="border-b-4 border-[#1e3a8a] pb-4 mb-6 relative">
                                <div class="flex items-center">
                                    @if (file_exists(public_path('foto/logo_sbk.png')))
                                        <img src="{{ asset('foto/logo_sbk.png') }}" alt="Logo"
                                            class="h-20 w-20 object-contain mr-6">
                                    @endif
                                    <div>
                                        <h1
                                            class="text-2xl font-black text-[#1e3a8a] uppercase tracking-tight leading-none">
                                            {{ \App\Models\Setting::getValue('company_name', 'PT Sertifikasi Bermutu Ketenagalistrikan') }}
                                        </h1>
                                        <p class="text-[11px] text-gray-600 mt-2 max-w-md leading-relaxed">Ruko Springhill,
                                            Jl. Letjen Tni Dr. H. Ibnu Sutowo No.Blok D 28, Talang Klp., Kec. Alang-Alang
                                            Lebar, Kota Palembang, Sumatera Selatan 30961</p>
                                        <div class="mt-2 text-[10px] font-bold text-gray-800 flex items-center space-x-4">
                                            <span>📱 0838 5436 4212</span>
                                            <span>✉️ sertifikasibermutuketenagalistrikan@gmail.com</span>
                                            <span>🌐 www.lsksertifikasibermutuketenagalist.com</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    @endif

                    <div class="px-12 flex-1 relative mt-2">
                        {{-- Judul Laporan --}}
                        <div class="text-left mb-4">
                            <h2 class="text-lg font-black text-gray-900 uppercase">LAPORAN ARUS KAS</h2>
                            <p class="text-xs text-gray-700 font-medium">Periode: {{ $periodLabel }}</p>
                        </div>

                        {{-- Tabel Arus Kas --}}
                        <table class="w-full text-[9px] border-collapse mb-6">
                            <thead>
                                <tr class="bg-[#1e3a8a] text-white">
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-center font-bold">NO</th>
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-left font-bold">TANGGAL</th>
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-left font-bold">NO. RAB</th>
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-left font-bold">KETERANGAN</th>
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-right font-bold">UANG MASUK</th>
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-right font-bold">UANG KELUAR</th>
                                    <th class="border border-[#1e3a8a] py-1 px-1.5 text-right font-bold">SALDO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-blue-50/50 font-bold italic">
                                    <td class="border border-gray-200 py-1.5 px-1.5 text-center">-</td>
                                    <td class="border border-gray-200 py-1.5 px-1.5">-</td>
                                    <td class="border border-gray-200 py-1.5 px-1.5">-</td>
                                    <td class="border border-gray-200 py-1.5 px-1.5">Saldo Awal Periode</td>
                                    <td class="border border-gray-200 py-1.5 px-1.5 text-right">-</td>
                                    <td class="border border-gray-200 py-1.5 px-1.5 text-right">-</td>
                                    <td class="border border-gray-200 py-1.5 px-1.5 text-right">
                                        {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                                </tr>
                                @php $prevBal = $saldoAwal; @endphp
                                @foreach ($cashFlows as $i => $cf)
                                    @php
                                        $prevBal = $prevBal + (float) $cf->debit - (float) $cf->credit;
                                    @endphp
                                    <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-gray-50/50' }}">
                                        <td class="border border-gray-200 py-1.5 px-1.5 text-center text-gray-500">
                                            {{ $i + 1 }}</td>
                                        <td class="border border-gray-200 py-1.5 px-1.5 text-gray-600">
                                            {{ $cf->transaction_date->format('d/m/Y') }}</td>
                                        <td class="border border-gray-200 py-1.5 px-1.5 text-gray-700 font-medium">
                                            {{ $cf->rab ? $cf->rab->rab_number : '-' }}</td>
                                        <td class="border border-gray-200 py-1.5 px-1.5 text-gray-800">
                                            {{ Str::limit($cf->description, 35) }}</td>
                                        <td
                                            class="border border-gray-200 py-1.5 px-1.5 text-right {{ $cf->debit > 0 ? 'text-emerald-700 font-bold' : 'text-gray-400' }}">
                                            {{ $cf->debit > 0 ? number_format($cf->debit, 0, ',', '.') : '-' }}</td>
                                        <td
                                            class="border border-gray-200 py-1.5 px-1.5 text-right {{ $cf->credit > 0 ? 'text-red-700 font-bold' : 'text-gray-400' }}">
                                            {{ $cf->credit > 0 ? number_format($cf->credit, 0, ',', '.') : '-' }}</td>
                                        <td
                                            class="border border-gray-200 py-1.5 px-1.5 text-right font-black text-gray-900">
                                            {{ number_format($prevBal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-[#1e3a8a] text-white font-black">
                                <tr>
                                    <td colspan="4"
                                        class="border border-[#1e3a8a] py-1.5 px-1.5 text-right text-[10px]">TOTAL</td>
                                    <td class="border border-[#1e3a8a] py-1.5 px-1.5 text-right text-[10px]">
                                        {{ number_format($totalUangMasuk, 0, ',', '.') }}</td>
                                    <td class="border border-[#1e3a8a] py-1.5 px-1.5 text-right text-[10px]">
                                        {{ number_format($totalUangKeluar, 0, ',', '.') }}</td>
                                    <td class="border border-[#1e3a8a] py-1.5 px-1.5 text-right text-[10px]">
                                        {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="flex justify-between items-start mb-12">
                            {{-- Rekapitulasi --}}
                            <div class="w-1/2">
                                <div class="mb-3 text-[11px] font-bold leading-relaxed text-[#1e3a8a]">
                                    RINGKASAN MUTASI REKENING<br>
                                    PT Sertifikasi Bermutu Ketenagalistrikan<br>
                                    <span class="text-gray-800">Periode : {{ $periodLabel }}</span><br>
                                    <span class="text-gray-800">No. Rekening : 8881003328</span><br>
                                    <span class="text-gray-800">Mata Uang : IDR</span>
                                </div>
                                <table class="w-full text-[11px] border-collapse border border-gray-200">
                                    <thead>
                                        <tr class="bg-[#1e3a8a] text-white">
                                            <th colspan="2" class="py-2 px-3 text-center font-black tracking-widest">
                                                REKAPITULASI</th>
                                        </tr>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-200 py-2 px-3 text-left">URAIAN</th>
                                            <th class="border border-gray-200 py-2 px-3 text-right">JUMLAH (RP)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border border-gray-200 py-2 px-3 text-gray-600 font-medium">Saldo
                                                Awal Periode</td>
                                            <td class="border border-gray-200 py-2 px-3 text-right font-black">
                                                {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-200 py-2 px-3 text-gray-600 font-medium">Total
                                                Uang Masuk</td>
                                            <td
                                                class="border border-gray-200 py-2 px-3 text-right text-emerald-700 font-black">
                                                {{ number_format($totalUangMasuk, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>

                                            <td class="border border-gray-200 py-2 px-3 text-gray-600 font-medium">Total
                                                Uang Keluar</td>
                                            <td
                                                class="border border-gray-200 py-2 px-3 text-right text-red-700 font-black">
                                                {{ number_format($totalUangKeluar, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td
                                                class="border border-gray-200 py-2 px-3 text-gray-900 font-black uppercase">
                                                Saldo Akhir Periode</td>
                                            <td
                                                class="border border-gray-200 py-2 px-3 text-right text-[#1e3a8a] font-black text-sm">
                                                {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Tanda Tangan --}}
                            <div class="w-1/3 text-center relative">
                                <p class="text-[11px] text-gray-600">Palembang,
                                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                                <p class="text-[12px] font-black text-gray-900 mt-1">Mengetahui</p>
                                <p class="text-[10px] font-bold text-gray-500 mt-1">Manajer Keuangan</p>
                                <div class="h-24"></div>
                                <p
                                    class="text-[12px] font-black text-gray-900 border-b-2 border-gray-900 pb-0.5 inline-block">
                                    Mery Eryanti</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    @if (file_exists(public_path('foto/footer_sbk.png')))
                        <div class="w-full mt-auto text-center overflow-hidden">
                            <img src="{{ asset('foto/footer_sbk.png') }}"
                                class="w-full h-[90px] object-cover object-bottom">
                        </div>
                    @else
                        <div class="w-full mt-auto">
                            <div class="h-2 bg-[#1e3a8a]"></div>
                            <div class="h-1 bg-[#B8860B]"></div>
                            <div class="p-6 text-center">
                                <p class="text-[9px] text-gray-400 italic mt-2 font-medium tracking-tight">Dokumen ini
                                    dihasilkan secara otomatis oleh Sistem Informasi RAB PT Sertifikasi Bermutu
                                    Ketenagalistrikan berdasarkan data RAB, transaksi uang masuk, transaksi uang keluar, dan
                                    bukti pembayaran yang telah diinput oleh Admin Keuangan.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($showPreview && $cashFlows->count() > 0)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const previewSection = document.getElementById('preview-section');
                if (previewSection) {
                    previewSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        </script>
    @endif

    <script>
        window.setSort = function(val) {
            const sortInput = document.getElementById('sort_input');
            if (sortInput) {
                sortInput.value = val;
                sortInput.closest('form').submit();
            }
        };
    </script>
@endsection
