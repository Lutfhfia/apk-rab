@extends('layouts.app')
@section('title', 'Arus Kas')
@section('page-title', 'Arus Kas')
@section('page-subtitle', 'Pencatatan dana masuk, dana keluar, dan saldo')
@section('sidebar-menu')
@if(auth()->user()->isAdmin())
    @include('admin._sidebar')
@elseif(auth()->user()->isManajer())
    @include('manajer._sidebar')
@else
    @include('direktur._sidebar')
@endif
@endsection

@section('content')
{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Total Dana Masuk</p>
        <p class="text-xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Total Dana Keluar</p>
        <p class="text-xl font-extrabold text-red-600 mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase">Saldo Saat Ini</p>
        <p class="text-xl font-extrabold text-blue-600 mt-1">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
    </div>
</div>

@if(auth()->user()->isManajer())
{{-- Add Transaction --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="text-base font-bold text-gray-800 mb-4">Catat Transaksi Baru</h3>
    <form method="POST" action="{{ route('manajer.cash-flow.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="cashFlowForm">
        @csrf
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal *</label>
            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Jenis *</label>
            <select name="type" id="transactionType" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" onchange="toggleFields()">
                @php
                    $isClean = \App\Models\CashFlow::count() === 0;
                    $isStartOfMonth = date('j') == 1;
                @endphp
                @if($isClean || $isStartOfMonth)
                <option value="saldo_awal">Saldo Awal</option>
                @endif
                <option value="dana_masuk" {{ (!$isClean && !$isStartOfMonth) ? 'selected' : '' }}>Dana Masuk</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Keterangan *</label>
            <input type="text" name="description" id="description" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Deskripsi transaksi">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Nominal *</label>
            <input type="text" name="amount" value="{{ old('amount') ? number_format((float) old('amount'), 0, ',', '.') : '' }}" required inputmode="numeric" class="money-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="0" oninput="formatMoney(this)">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Bukti <span id="proofOptionalLabel">(Opsional)</span></label>
            <input type="file" name="proof_file" id="proofFile" accept=".jpg,.jpeg,.png,.pdf" class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 focus:outline-none">
        </div>
        <div class="md:col-span-2 lg:col-span-4 flex justify-end mt-2">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg text-sm font-bold transition">Simpan Transaksi</button>
        </div>
    </form>
</div>
@endif

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    @php
        $filterRoute = route('manajer.cash-flow.index');
        if (auth()->user()->isDirektur()) $filterRoute = route('direktur.cash-flow.index');
    @endphp
    <form method="GET" action="{{ $filterRoute }}" class="flex flex-col gap-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[140px]">
                <label class="block text-xs font-bold text-gray-500 mb-1">Jenis Transaksi</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    <option value="">Semua Jenis</option>
                    @foreach(\App\Enums\CashFlowType::cases() as $type)
                    <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
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
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold transition">Filter</button>
            <a href="{{ $filterRoute }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium">Reset</a>
        </div>
    </form>
</div>

{{-- Transactions Table --}}
<div id="riwayat-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-bold text-gray-800">Riwayat Arus Kas</h2>
    </div>
    <div class="overflow-auto max-h-[70vh]">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="text-xs text-gray-500 border-b border-gray-100">
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Tanggal</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Jenis</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">No RAB</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Keterangan</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Dana Masuk</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Dana Keluar</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Saldo Berjalan</th>
                    <th class="sticky top-0 z-10 bg-gray-50 py-3.5 px-5 font-bold shadow-sm">Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashFlows as $cf)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                    <td class="py-4 px-5">{{ $cf->transaction_date->format('d/m/Y') }}</td>
                    <td class="py-4 px-5">
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase
                            {{ $cf->type->value === 'dana_masuk' ? 'bg-emerald-100 text-emerald-700' : ($cf->type->value === 'dana_keluar' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $cf->type->label() }}
                        </span>
                    </td>
                    <td class="py-4 px-5">
                        @if($cf->rab)
                            @php
                                $rabLinkRoute = auth()->user()->isDirektur() ? 'direktur.rab.index' : 'manajer.rab.index';
                            @endphp
                            <a href="{{ route($rabLinkRoute, ['status' => $cf->rab->status->value, 'open_rab_id' => $cf->rab->id]) }}" class="text-blue-600 hover:underline">
                                {{ $cf->rab->rab_number }}
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="py-4 px-5">{{ $cf->description }}</td>
                    <td class="py-4 px-5 text-emerald-600 font-semibold">{{ $cf->debit > 0 ? 'Rp ' . number_format($cf->debit, 0, ',', '.') : '-' }}</td>
                    <td class="py-4 px-5 text-red-600 font-semibold">{{ $cf->credit > 0 ? 'Rp ' . number_format($cf->credit, 0, ',', '.') : '-' }}</td>
                    <td class="py-4 px-5 font-bold">Rp {{ number_format($cf->balance, 0, ',', '.') }}</td>
                    <td class="py-4 px-5">
                        @php
                            $proofPath = $cf->proofFilePath();
                        @endphp
                        @if($cf->proofFileExists())
                            <button type="button" onclick="openProofModal(@js(route('file.show', ['path' => $proofPath], false)), @js($cf->type->label()))" class="text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat Bukti
                            </button>
                        @elseif($proofPath)
                            <span class="inline-flex items-center text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg text-xs font-semibold" title="File bukti sudah tidak ditemukan di storage server.">
                                File hilang
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-12 text-center text-gray-400">Belum ada data arus kas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cashFlows->hasPages())
    <div class="p-5 border-t border-gray-100">{{ $cashFlows->appends(request()->query())->fragment('riwayat-table')->links() }}</div>
    @endif
</div>

{{-- Proof Modal --}}
<div id="proofModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[95vh] flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-base font-bold text-gray-800" id="proofModalTitle">Bukti Transaksi</h3>
            <button type="button" onclick="closeProofModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-0 flex-1 overflow-auto bg-gray-100/50 flex justify-center items-center min-h-[400px] sm:min-h-[600px] relative">
            <iframe id="proofIframe" src="" class="w-full h-full min-h-[70vh] border-0 hidden rounded-lg shadow-inner bg-white m-4"></iframe>
            <img id="proofImage" src="" class="hidden max-w-full max-h-[70vh] object-contain rounded-lg shadow bg-white m-4" alt="Bukti Transaksi">
            <div id="proofLoading" class="flex flex-col items-center justify-center text-gray-400 absolute inset-0 bg-gray-100/80 z-10">
                <svg class="animate-spin w-8 h-8 mb-3 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-sm font-medium">Memuat bukti...</span>
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
            <a id="proofDownloadLink" href="#" target="_blank" download class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh / Buka di Tab Baru
            </a>
            <button type="button" onclick="closeProofModal()" class="ml-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm px-5 py-2 rounded-lg text-sm font-bold transition">Tutup</button>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('transactionType').value;
        const proofOptionalLabel = document.getElementById('proofOptionalLabel');
        const proofFile = document.getElementById('proofFile');
        const desc = document.getElementById('description');

        if (type === 'dana_masuk') {
            proofOptionalLabel.innerText = '(Opsional)';
            proofOptionalLabel.classList.remove('text-red-500');
            proofFile.required = false;
            if (desc.value === 'Pembayaran RAB' || desc.value === 'Saldo Awal' || desc.value === '') desc.value = 'Dana Masuk';
        } else {
            // Saldo Awal
            proofOptionalLabel.innerText = '(Tidak Perlu)';
            proofOptionalLabel.classList.remove('text-red-500');
            proofFile.required = false;
            proofFile.value = '';
            if (desc.value === '' || desc.value === 'Dana Masuk') desc.value = 'Saldo Awal';
        }
    }

    function formatMoney(el) {
        let val = el.value.replace(/[^0-9]/g, '');
        if (val === '') {
            el.value = '';
            return;
        }
        el.value = Number(val).toLocaleString('id-ID');
    }

    function parseMoney(val) {
        if (!val) return '';
        return String(val).replace(/\./g, '').replace(/,/g, '.');
    }

    function openProofModal(url, typeLabel = '') {
        const modal = document.getElementById('proofModal');
        const iframe = document.getElementById('proofIframe');
        const image = document.getElementById('proofImage');
        const loading = document.getElementById('proofLoading');
        const downloadLink = document.getElementById('proofDownloadLink');
        const title = document.getElementById('proofModalTitle');

        if (typeLabel) {
            title.innerText = 'Bukti Transaksi - ' + typeLabel;
        } else {
            title.innerText = 'Bukti Transaksi';
        }

        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-95');

        downloadLink.href = url;

        loading.classList.remove('hidden');
        iframe.classList.add('hidden');
        image.classList.add('hidden');

        iframe.src = '';
        image.src = '';
        iframe.onload = null;
        iframe.onerror = null;
        image.onload = null;
        image.onerror = null;

        const cleanUrl = url.split('?')[0].toLowerCase();

        if (cleanUrl.endsWith('.pdf')) {
            iframe.onload = function () {
                loading.classList.add('hidden');
                iframe.classList.remove('hidden');
            };

            iframe.onerror = function () {
                loading.classList.add('hidden');
                alert('Gagal memuat dokumen PDF. Kemungkinan file fisik terhapus.');
            };

            iframe.src = url;
        } else if (
            cleanUrl.endsWith('.jpg') ||
            cleanUrl.endsWith('.jpeg') ||
            cleanUrl.endsWith('.png') ||
            cleanUrl.endsWith('.webp')
        ) {
            image.onload = function () {
                loading.classList.add('hidden');
                image.classList.remove('hidden');
            };

            image.onerror = function () {
                if (!image.src || modal.classList.contains('hidden')) return;
                loading.classList.add('hidden');
                alert('Gambar tidak ditemukan (404). Kemungkinan file sudah terhapus secara fisik dari komputer/server Anda.');
            };

            image.src = url;
        } else {
            loading.classList.add('hidden');
            window.open(url, '_blank');
        }
    }

    function closeProofModal() {
        const modal = document.getElementById('proofModal');
        const iframe = document.getElementById('proofIframe');
        const image = document.getElementById('proofImage');

        iframe.onload = null;
        iframe.onerror = null;
        image.onload = null;
        image.onerror = null;

        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            iframe.src = '';
            image.src = '';
        }, 300);
    }

    const cashFlowForm = document.getElementById('cashFlowForm');
    if (cashFlowForm) {
        cashFlowForm.addEventListener('submit', function () {
            document.querySelectorAll('.money-input').forEach(input => {
                input.value = parseMoney(input.value);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (cashFlowForm) toggleFields();
    });
</script>
@endsection
