@extends('layouts.app')
@section('title', 'Input Nota / LPJ')
@section('page-title', 'Input Nota / LPJ')
@section('page-subtitle', 'Daftar RAB yang sudah dicairkan dan perlu bukti nota realisasi pengeluaran')

@section('sidebar-menu')
    @include('admin._sidebar')
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-5">
        <div class="px-4 sm:px-5 pt-4 border-b border-gray-100">
            <nav class="flex flex-wrap gap-1">
                @php
                    $tabClasses = 'px-4 py-2.5 text-sm font-bold border-b-2 transition';
                    $activeClasses = 'border-emerald-500 text-emerald-700';
                    $inactiveClasses = 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-200';
                @endphp
                <a href="{{ route('admin.input-nota.index', ['tab' => 'semua']) }}"
                    class="{{ $tabClasses }} {{ $tab === 'semua' ? $activeClasses : $inactiveClasses }}">Semua</a>
                <a href="{{ route('admin.input-nota.index', ['tab' => 'belum_upload']) }}"
                    class="{{ $tabClasses }} {{ $tab === 'belum_upload' ? $activeClasses : $inactiveClasses }}">Menunggu Upload Nota</a>
                <a href="{{ route('admin.input-nota.index', ['tab' => 'menunggu_validasi']) }}"
                    class="{{ $tabClasses }} {{ $tab === 'menunggu_validasi' ? $activeClasses : $inactiveClasses }}">Menunggu Validasi</a>
                <a href="{{ route('admin.input-nota.index', ['tab' => 'ditolak']) }}"
                    class="{{ $tabClasses }} {{ $tab === 'ditolak' ? $activeClasses : $inactiveClasses }}">Ditolak</a>
                <a href="{{ route('admin.input-nota.index', ['tab' => 'valid']) }}"
                    class="{{ $tabClasses }} {{ $tab === 'valid' ? $activeClasses : $inactiveClasses }}">Valid</a>
            </nav>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-4">
        <form method="GET" action="{{ route('admin.input-nota.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-500 mb-1">Cari RAB / Deskripsi</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                    placeholder="Ketik nomor RAB atau deskripsi...">
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.input-nota.index', ['tab' => $tab]) }}"
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
                        <th class="py-3.5 px-5 font-bold">Kategori</th>
                        <th class="py-3.5 px-5 font-bold">Dana Dicairkan</th>
                        <th class="py-3.5 px-5 font-bold">Tanggal Cair</th>
                        <th class="py-3.5 px-5 font-bold">Status LPJ</th>
                        <th class="py-3.5 px-5 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-600">
                    @forelse($rabs as $rab)
                        <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors">
                            <td class="py-4 px-5">
                                <div class="font-bold text-gray-800">{{ $rab->rab_number }}</div>
                                <div class="text-xs text-gray-400 max-w-[200px] truncate" title="{{ $rab->description }}">{{ $rab->description ?: '-' }}</div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase">{{ $rab->expenseType->name ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-5 font-bold text-gray-800">
                                Rp {{ number_format($rab->payment->paid_amount ?? $rab->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-5">
                                {{ $rab->payment ? $rab->payment->payment_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-4 px-5">
                                @php
                                    $lpjStatus = $rab->lpj_status;
                                    $badgeClass = match($lpjStatus) {
                                        'Belum Upload' => 'bg-gray-100 text-gray-600 border border-gray-200',
                                        'Menunggu Validasi' => 'bg-amber-50 text-amber-600 border border-amber-200',
                                        'Ditolak' => 'bg-red-50 text-red-600 border border-red-200',
                                        'Valid' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                        default => 'bg-gray-100 text-gray-600 border border-gray-200',
                                    };
                                @endphp
                                <span class="text-[10px] font-bold px-3 py-1.5 rounded-lg {{ $badgeClass }}">
                                    {{ $lpjStatus }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                @php
                                    $btnText = 'Lihat Nota';
                                    $btnClass = 'bg-blue-50 text-blue-700 hover:bg-blue-100';
                                    
                                    if ($lpjStatus === 'Belum Upload') {
                                        $btnText = 'Upload Nota';
                                        $btnClass = 'bg-emerald-600 text-white hover:bg-emerald-700';
                                    } elseif ($lpjStatus === 'Ditolak') {
                                        $btnText = 'Upload Ulang';
                                        $btnClass = 'bg-red-600 text-white hover:bg-red-700';
                                    }
                                @endphp
                                <button type="button" onclick="openRabModal('rabDetailModal-{{ $rab->id }}')" 
                                   class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-bold transition {{ $btnClass }}">
                                    {{ $btnText }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">Tidak ada RAB yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rabs->hasPages())
            <div class="p-5 border-t border-gray-100">{{ $rabs->links() }}</div>
        @endif
    </div>

    {{-- Modal Detail RAB untuk input nota --}}
    @foreach($rabs as $rab)
        @include('rab._detail_modal')
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

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;

            document.querySelectorAll('[id^="rabDetailModal-"]').forEach(function(modal) {
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
    </script>
    @endpush
@endsection
