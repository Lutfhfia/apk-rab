{{-- Modal Upload Bukti Pembayaran --}}
<div id="paymentModal-{{ $rab->id }}" class="fixed inset-0 bg-black/50 z-[60] hidden items-center justify-center p-4">
    <div class="bg-gray-50 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[95vh] flex flex-col overflow-hidden animate-fade-in">
        <div class="p-5 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Upload Bukti Pembayaran</h3>
                <p class="text-sm text-gray-500">RAB {{ $rab->rab_number }}</p>
            </div>
            <button type="button" onclick="closePaymentModal('paymentModal-{{ $rab->id }}')" class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto grow">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Informasi RAB
                    </h3>
                    <span class="{{ $rab->status->badgeClasses() }} text-xs font-bold px-3 py-1.5 rounded-lg">{{ $rab->status->label() }}</span>
                </div>
                <p class="text-sm text-gray-600">Total Pengajuan: <span class="font-bold text-emerald-600 text-base">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</span></p>
            </div>

            <form method="POST" action="{{ route('rab.payment.store', $rab) }}" enctype="multipart/form-data" id="paymentForm-{{ $rab->id }}">
                @csrf
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h3 class="text-base font-bold text-gray-800 mb-5">Data Pembayaran</h3>

                    @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
                        <ul class="text-sm text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tanggal Pembayaran *</label>
                                <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Nominal Dibayarkan *</label>
                                <input type="text" name="paid_amount" value="{{ number_format((float) old('paid_amount', $rab->total_amount), 0, ',', '.') }}" required inputmode="numeric" class="money-input w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-right focus:ring-2 focus:ring-emerald-400 focus:outline-none" oninput="window['formatPaymentMoney_{{ $rab->id }}'](this)">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Metode Pembayaran *</label>
                            <select name="payment_method" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Cek/Giro">Cek/Giro</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Rekening Tujuan</label>
                                <input type="text" name="recipient_account" value="{{ old('recipient_account') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="No. Rekening">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Nama Penerima</label>
                                <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Nama penerima">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Bukti Transfer * (JPG, PNG, PDF max 100KB)</label>
                            <input type="file" name="proof_file" required accept=".jpg,.jpeg,.png,.pdf" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs" onchange="window['validateProofFileSize_{{ $rab->id }}'](this)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Catatan</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closePaymentModal('paymentModal-{{ $rab->id }}')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold transition">Batal</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition">Upload & Selesaikan RAB</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
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
    window['formatPaymentMoney_{{ $rab->id }}'] = formatMoney;

    function parseMoney(val) {
        if (!val) return '';
        return String(val).replace(/\./g, '').replace(/,/g, '.');
    }

    function validateFileSize(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        const maxSize = 100 * 1024; // 100KB in bytes
        if (file.size > maxSize) {
            alert('Ukuran file bukti transfer maksimal adalah 100KB. File yang Anda pilih berukuran: ' + (file.size / 1024).toFixed(1) + 'KB.');
            input.value = ''; // clear input
        }
    }
    window['validateProofFileSize_{{ $rab->id }}'] = validateFileSize;

    const form = document.getElementById('paymentForm-{{ $rab->id }}');
    if (form) {
        form.addEventListener('submit', function () {
            this.querySelectorAll('.money-input').forEach(input => {
                input.value = parseMoney(input.value);
            });
        });
    }
})();
</script>
@endpush
