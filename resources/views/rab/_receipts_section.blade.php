@php
    $receipts = $rab->receipts()->orderByDesc('created_at')->get();

    $hasValidOrPending = $receipts->contains(function ($receipt) {
        return in_array($receipt->status->value, [
            \App\Enums\RabReceiptStatus::VALID->value,
            \App\Enums\RabReceiptStatus::MENUNGGU_VALIDASI->value
        ]);
    });

    $canUploadReceipt =
        auth()->user()?->isAdmin() && 
        in_array($rab->status, [\App\Enums\RabStatus::DISETUJUI, \App\Enums\RabStatus::SELESAI]) && 
        $rab->payment()->exists() &&
        !$hasValidOrPending;

    $canValidateReceipt = auth()->user()?->isManajer();
    $totalAmount = $receipts->sum('total_amount');
    $totalValidated = $receipts->where('status', \App\Enums\RabReceiptStatus::VALID->value)->sum('total_amount');

    // Check if the latest receipt was rejected to display a banner
    $latestReceipt = $receipts->first();
    $isLatestRejected = $latestReceipt && $latestReceipt->status === \App\Enums\RabReceiptStatus::DITOLAK;
@endphp

<div class="border border-gray-100 rounded-xl overflow-hidden mb-5">
    <div
        class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h4 class="text-sm font-bold text-gray-800">Nota Belanja / LPJ</h4>
            <p class="text-xs text-gray-400 mt-0.5">
                @if ($receipts->count() === 0)
                    Belum Upload
                @else
                    {{ $receipts->count() }} nota (Valid: Rp {{ number_format($totalValidated, 0, ',', '.') }})
                @endif
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if ($receipts->count() === 0)
                <span
                    class="bg-gray-100 text-gray-600 text-[10px] font-bold px-3 py-1.5 rounded-lg self-start sm:self-auto">Belum
                    Upload</span>
            @else
                <span
                    class="bg-emerald-100 text-emerald-600 text-[10px] font-bold px-3 py-1.5 rounded-lg self-start sm:self-auto">{{ $receipts->count() }}
                    Nota</span>
            @endif
        </div>
    </div>

    <div class="p-4 space-y-4">


        {{-- Upload Form --}}
        @if ($canUploadReceipt)
            @if ($isLatestRejected)
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">⚠️</span>
                        <div class="flex-1">
                            <h5 class="text-sm font-bold text-rose-900">Nota LPJ Sebelumnya Ditolak</h5>
                            <p class="text-xs text-rose-700 mt-1">
                                Nota yang Anda upload sebelumnya ditolak oleh Manajer Keuangan dengan alasan:
                                <span class="font-semibold block mt-1.5 bg-white border border-rose-100 rounded-lg p-2.5 text-gray-800">{{ $latestReceipt->notes }}</span>
                            </p>
                            <p class="text-xs text-rose-600 mt-2 font-bold">Silakan perbaiki data dan upload ulang nota bukti LPJ yang baru di bawah ini.</p>
                        </div>
                    </div>
                </div>
            @endif
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                <h5 class="text-sm font-bold text-emerald-900 mb-3">📋 Upload Nota Belanja / LPJ Baru</h5>
                <p class="text-xs text-emerald-700 mb-3">Input nota belanja, kwitansi, atau invoice sebagai bukti
                    realisasi pengeluaran dana. Setiap nota akan divalidasi oleh Manajer Keuangan.</p>
                <form method="POST" action="{{ route('rab.receipts.store', $rab) }}" enctype="multipart/form-data"
                    id="receiptForm-{{ $rab->id }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Nota *</label>
                            <input type="date" name="receipt_date" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Toko / Vendor *</label>
                            <input type="text" name="store_name" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                placeholder="Nama vendor">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">No. Nota</label>
                            <input type="text" name="receipt_number"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                placeholder="Opsional">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Nominal *</label>
                            <input type="text" name="total_amount" required inputmode="numeric"
                                class="receipt-money-input-{{ $rab->id }} w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                placeholder="1.500.000"
                                oninput="window['formatReceiptMoney_{{ $rab->id }}'](this)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">File Nota *</label>
                            <input type="file" name="receipt_file" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-xs file:font-bold file:text-emerald-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Catatan Penggunaan Dana</label>
                        <textarea name="notes" rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none resize-none"
                            placeholder="Jelaskan penggunaan dana atau keterangan tambahan (opsional)"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">📤
                            Upload Nota</button>
                    </div>
                </form>
                <script>
                (function() {
                    function formatMoney(el) {
                        let val = el.value.replace(/[^0-9]/g, '');
                        if (val === '') {
                            el.value = '';
                            return;
                        }
                        el.value = Number(val).toLocaleString('id-ID');
                    }
                    window['formatReceiptMoney_{{ $rab->id }}'] = formatMoney;

                    function parseMoney(val) {
                        if (!val) return '';
                        return String(val).replace(/\./g, '').replace(/,/g, '.');
                    }

                    const form = document.getElementById('receiptForm-{{ $rab->id }}');
                    if (form) {
                        form.addEventListener('submit', function () {
                            const input = this.querySelector('.receipt-money-input-{{ $rab->id }}');
                            if (input) {
                                input.value = parseMoney(input.value);
                            }
                        });
                    }
                })();
                </script>
            </div>
        @elseif(auth()->user()?->isAdmin() && !in_array($rab->status, [\App\Enums\RabStatus::DISETUJUI, \App\Enums\RabStatus::SELESAI]))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-sm text-amber-800">
                    <span class="font-bold">⏳ Menunggu Persetujuan</span><br>
                    Nota belanja dapat diunggah setelah RAB disetujui oleh Direktur.
                </p>
            </div>
        @elseif(auth()->user()?->isAdmin() && !$rab->payment()->exists())
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800">
                    <span class="font-bold">💳 Menunggu Dana Masuk</span><br>
                    Nota belanja dapat diunggah setelah Manajer Keuangan mencairkan dana dan mencatat Dana Masuk di menu
                    Arus Kas.
                </p>
            </div>
        @endif

        {{-- List of Receipts --}}
        @if ($receipts->count() > 0)
            <div class="space-y-3">
                @foreach ($receipts as $receipt)
                    <div class="rounded-xl border border-gray-100 bg-white p-4 hover:shadow-md transition">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-extrabold text-gray-800">{{ $receipt->store_name }}</p>
                                    <span
                                        class="{{ $receipt->status->badgeClasses() }} text-[10px] font-bold px-2.5 py-1 rounded-lg">{{ $receipt->status->label() }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $receipt->receipt_date->format('d/m/Y') }} &bull;
                                    No. Nota: {{ $receipt->receipt_number ?: '-' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Upload oleh {{ $receipt->uploader->name ?? '-' }} &bull;
                                    {{ $receipt->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-sm font-bold text-emerald-700 mt-2">Rp
                                    {{ number_format($receipt->total_amount, 0, ',', '.') }}</p>
                                @if ($receipt->notes && $receipt->status === \App\Enums\RabReceiptStatus::DITOLAK)
                                    <p class="text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg p-3 mt-2">
                                        <span class="font-bold">Alasan ditolak:</span> {{ $receipt->notes }}
                                    </p>
                                @elseif($receipt->notes)
                                    <p class="text-sm text-gray-600 mt-2">{{ $receipt->notes }}</p>
                                @endif
                                @if ($receipt->validator)
                                    <p class="text-xs text-gray-400 mt-2">Diperiksa oleh
                                        {{ $receipt->validator->name }} pada
                                        {{ $receipt->validated_at?->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <button type="button"
                                    onclick="openRabProofModal(@js(route('file.show', ['path' => $receipt->receipt_file], false)), 'Nota LPJ {{ $rab->rab_number }}')"
                                    class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-lg text-xs font-bold transition">Lihat</button>
                                @if ($canValidateReceipt && $receipt->status === \App\Enums\RabReceiptStatus::MENUNGGU_VALIDASI)
                                    <form method="POST"
                                        action="{{ route('rab.receipts.approve', [$receipt->rab, $receipt]) }}"
                                        onsubmit="return confirm('Validasi nota LPJ ini?')" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-xs font-bold transition">Setujui</button>
                                    </form>
                                    <button type="button"
                                        onclick="openRejectReceiptModal({{ $receipt->id }}, '{{ route('rab.receipts.reject', [$receipt->rab, $receipt]) }}')"
                                        class="bg-red-50 text-red-700 hover:bg-red-100 px-3 py-2 rounded-lg text-xs font-bold transition">Tolak</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-sm text-gray-400 py-6">Belum ada nota belanja atau dokumen LPJ untuk RAB ini.
            </div>
        @endif
    </div>
</div>

<script>
    function openRejectReceiptModal(receiptId, rejectRoute) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Tolak Nota LPJ</h3>
            <form method="POST" action="${rejectRoute}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Alasan Penolakan *</label>
                    <textarea name="notes" rows="3" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none resize-none" placeholder="Jelaskan alasan penolakan nota..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-bold">Batal</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold">Tolak</button>
                </div>
            </form>
        </div>
    `;
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
</script>
