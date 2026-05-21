{{-- Modal Edit RAB --}}
<div id="editRabModal-{{ $rab->id }}" class="fixed inset-0 bg-black/50 z-[60] hidden items-center justify-center p-4">
    <div class="bg-gray-50 rounded-2xl shadow-2xl w-full max-w-[95vw] max-h-[95vh] flex flex-col overflow-hidden animate-fade-in">
        <div class="p-5 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Edit RAB: {{ $rab->rab_number }}</h3>
                <p class="text-sm text-gray-500">Perbarui data Rancangan Anggaran Biaya</p>
            </div>
            <button type="button" onclick="closeEditRabModal('editRabModal-{{ $rab->id }}')" class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 flex-1 overflow-y-auto min-h-0">
            <form method="POST" action="{{ route('rab.update', $rab) }}" id="rabForm-{{ $rab->id }}">
                @csrf
                @method('PUT')

    {{-- Info Penolakan, Riwayat, & Diskusi --}}
    @if($rab->status === \App\Enums\RabStatus::DITOLAK)
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-6">
        <div class="flex items-start space-x-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-red-800">RAB Ditolak - Perlu Perbaikan</h4>
                <p class="text-xs text-red-600 mt-0.5">Silakan periksa alasan penolakan dan sesuaikan data rincian pengajuan RAB Anda di bawah ini.</p>
            </div>
        </div>

        {{-- Alasan Penolakan Terakhir --}}
        @php
            $latestRejection = $rab->approvals->where('status', \App\Enums\ApprovalStatus::REJECTED)->sortByDesc('created_at')->first();
        @endphp
        @if($latestRejection && $latestRejection->notes)
        <div class="bg-white border border-red-100 rounded-lg p-4 mb-4 shadow-sm">
            <p class="text-xs font-bold text-red-500 uppercase tracking-wider">Catatan Penolakan Terakhir:</p>
            <p class="text-sm font-semibold text-gray-800 mt-1.5 leading-relaxed">"{{ $latestRejection->notes }}"</p>
            <p class="text-xs text-gray-400 mt-2 font-medium">Oleh: {{ $latestRejection->user->name ?? '-' }} ({{ $latestRejection->user ? $latestRejection->user->role->label() : (\App\Enums\UserRole::tryFrom($latestRejection->role)?->label() ?? $latestRejection->role) }}) &bull; {{ $latestRejection->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif

        {{-- Collapse/Tab Grid: Riwayat & Diskusi --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Riwayat Approval --}}
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Riwayat Approval</h5>
                </div>
                <div class="p-3 space-y-2.5 max-h-[220px] overflow-y-auto">
                    @forelse($rab->approvals as $approval)
                    <div class="p-3 rounded-lg text-xs {{ $approval->status === \App\Enums\ApprovalStatus::APPROVED ? 'bg-emerald-50/70 border border-emerald-100' : 'bg-red-50/70 border border-red-100' }}">
                        <div class="flex justify-between items-start">
                            <span class="font-bold {{ $approval->status === \App\Enums\ApprovalStatus::APPROVED ? 'text-emerald-800' : 'text-red-800' }}">{{ $approval->status->label() }}</span>
                            <span class="text-[10px] text-gray-400 font-medium">{{ $approval->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-gray-600 mt-1 font-semibold">{{ $approval->user->name ?? '-' }} <span class="text-[9px] text-gray-400 font-medium">({{ $approval->user ? $approval->user->role->label() : (\App\Enums\UserRole::tryFrom($approval->role)?->label() ?? $approval->role) }})</span></p>
                        @if($approval->notes)
                        <p class="text-gray-700 mt-1.5 italic font-medium">"{{ $approval->notes }}"</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada riwayat approval.</p>
                    @endforelse
                </div>
            </div>

            {{-- Catatan Diskusi --}}
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Catatan Diskusi ({{ $rab->discussions->count() }})</h5>
                </div>
                <div class="p-3 space-y-2.5 max-h-[220px] overflow-y-auto">
                    @forelse($rab->discussions->sortByDesc('created_at') as $discussion)
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100 text-xs">
                        <div class="flex justify-between items-start gap-1">
                            <span class="font-bold text-gray-800">{{ $discussion->user->name ?? '-' }} <span class="text-[9px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded uppercase ml-1">{{ $discussion->user->role->label() ?? '-' }}</span></span>
                            <span class="text-[10px] text-gray-400 font-medium">{{ $discussion->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-gray-700 mt-1.5 whitespace-pre-line leading-relaxed">{{ $discussion->message }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada catatan diskusi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Data Umum RAB --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Data Umum RAB
            </h3>
            <span class="{{ $rab->status->badgeClasses() }} text-xs font-bold px-3 py-1.5 rounded-lg">{{ $rab->status->label() }}</span>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
            <ul class="text-sm text-red-600 list-disc list-inside">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Nomor RAB</label>
                <input type="text" name="rab_number" value="{{ old('rab_number', $rab->rab_number) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Pembuat</label>
                <input type="text" value="{{ $rab->user->role->label() ?? 'Admin' }}-{{ $rab->user->name ?? '-' }}" readonly class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tanggal Pengajuan *</label>
                <input type="date" name="request_date" value="{{ old('request_date', $rab->request_date->format('Y-m-d')) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Jenis Pengeluaran *</label>
                <input type="text" value="{{ $rab->expenseType->name }}" readonly class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-600">
                <input type="hidden" name="expense_type_id" value="{{ $rab->expense_type_id }}" id="expenseTypeId-{{ $rab->id }}">
                <input type="hidden" id="expenseTypeCode-{{ $rab->id }}" value="{{ $rab->expenseType->code }}">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Periode Bulan</label>
                <select name="period_month" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    <option value="">-- Pilih Bulan --</option>
                    @php $bulanList=[1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember']; @endphp
                    @foreach($bulanList as $num => $bulan)
                    <option value="{{ $num }}" {{ old('period_month', $rab->period_month) == $num ? 'selected' : '' }}>{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Keterangan</label>
                <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">{{ old('description', $rab->description) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Dynamic Expense Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6" id="expenseTableContainer-{{ $rab->id }}" style="display: {{ $rab->expenseType->code === 'operasional' ? 'none' : 'block' }}">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span id="expenseTableTitle-{{ $rab->id }}">Rincian {{ $rab->expenseType->name }}</span>
            </h3>
            <button type="button" onclick="window['addRow_{{ $rab->id }}']()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Item
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="expenseTable-{{ $rab->id }}">
                <thead id="expenseTableHead-{{ $rab->id }}"></thead>
                <tbody id="expenseTableBody-{{ $rab->id }}"></tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="20" class="py-4 px-4 text-right font-bold text-gray-700">TOTAL:</td>
                        <td class="py-4 px-4 font-extrabold text-gray-800 text-lg" id="grandTotal-{{ $rab->id }}">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Operational Expense Tables (5 Groups) --}}
    <div id="operationalTableContainer-{{ $rab->id }}" class="mb-6" style="display: {{ $rab->expenseType->code === 'operasional' ? 'block' : 'none' }}">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Rincian Biaya Operasional
        </h3>

        <div id="operationalGroupsWrapper-{{ $rab->id }}" class="space-y-6">
            <!-- Groups will be injected by JS -->
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-emerald-200 p-6 mt-6 flex justify-between items-center">
            <span class="font-bold text-gray-700 text-lg">TOTAL KESELURUHAN RAB:</span>
            <span class="font-extrabold text-emerald-600 text-2xl" id="operationalGrandTotal-{{ $rab->id }}">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-end space-x-3">
        <button type="button" onclick="closeEditRabModal('editRabModal-{{ $rab->id }}')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold transition">Batal</button>
        <button type="submit" name="action" value="draft" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-xl text-sm font-bold transition">Perbarui Draft</button>
        <button type="submit" name="action" value="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Ajukan RAB
        </button>
    </div>
</form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
let currentType = document.getElementById('expenseTypeCode-{{ $rab->id }}').value;
let rowCount = 0;
const existingItems = @json($rab->getExpenseItems());
const ic = 'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none';
const nc = ic + ' text-right money-input';
const sc = ic;
const delBtn = `<button type="button" onclick="window['removeRow_{{ $rab->id }}'](this)" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>`;

const tableHead = document.getElementById('expenseTableHead-{{ $rab->id }}');
const tableBody = document.getElementById('expenseTableBody-{{ $rab->id }}');

const positionOptions = ['Direktur','Manajer','Admin','OB','Lainnya'].map(p => `<option value="${p}">${p}</option>`).join('');

function fmtNum(v) { return v ? Number(v).toLocaleString('id-ID') : '0'; }

const configs = {
    operasional: {
        headers: [],
        row: () => '',
        calcRow: () => 0
    },
    petty_cash: {
        headers: ['No','Nama Pengeluaran','Keterangan','Jumlah','Satuan','Harga Satuan','Admin','Total','Tanggal','Aksi'],
        row: (i, d={}) => {
            let dateVal = d.transaction_date ? String(d.transaction_date).substring(0,10) : '';
            return `
            <td class="py-3 px-3 text-center text-sm text-gray-500">${i}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][expense_name]" value="${d.expense_name||''}" required class="${ic}"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][description]" value="${d.description||''}" class="${ic}"></td>
            <td class="py-3 px-3"><input type="number" name="items[${i-1}][volume]" value="${d.volume||1}" required step="0.01" min="0.01" class="w-20 ${nc} calc-trigger"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][unit]" value="${d.unit||'pcs'}" required class="w-20 ${ic}"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][unit_price]" value="${fmtNum(d.unit_price)}" required class="w-32 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][admin_fee]" value="${fmtNum(d.admin_fee)}" class="w-28 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3 font-bold text-gray-800 row-total">Rp ${fmtNum(d.total)}</td>
            <td class="py-3 px-3"><input type="date" name="items[${i-1}][transaction_date]" value="${dateVal}" required class="${ic}"></td>
            <td class="py-3 px-3">${delBtn}</td>`;
        },
        calcRow: (row) => {
            const vol = parseFloat(row.querySelector('[name*="volume"]')?.value) || 0;
            const price = parseMoney(row.querySelector('[name*="unit_price"]')?.value);
            const admin = parseMoney(row.querySelector('[name*="admin_fee"]')?.value);
            return (vol * price) + admin;
        }
    },
    gaji: {
        headers: ['No','Nama','Jabatan','No. Rek','Hadir (hari)','Gaji Pokok','Uang Makan/Hari','Transport/Hari','Lembur','Total Gaji','Catatan','Aksi'],
        row: (i, d={}) => {
            const posOpts = ['Direktur','Manajer','Admin','OB','Lainnya'].map(p => `<option value="${p}" ${d.position===p?'selected':''}>${p}</option>`).join('');
            return `
            <td class="py-3 px-3 text-center text-sm text-gray-500">${i}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][employee_name]" value="${d.employee_name||''}" required class="${ic}"></td>
            <td class="py-3 px-3"><select name="items[${i-1}][position]" required class="${sc}"><option value="">-- Pilih --</option>${posOpts}</select></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][bank_account_number]" value="${d.bank_account_number||''}" required class="w-28 ${ic}"></td>
            <td class="py-3 px-3"><input type="number" name="items[${i-1}][attendance_days]" value="${d.attendance_days||0}" required min="0" class="w-16 ${nc} calc-trigger"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][base_salary]" value="${fmtNum(d.base_salary)}" required class="w-32 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][meal_allowance_daily]" value="${fmtNum(d.meal_allowance_daily)}" class="w-28 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][transport_daily]" value="${fmtNum(d.transport_daily||20000)}" class="w-28 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][overtime]" value="${fmtNum(d.overtime)}" class="w-28 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3 font-bold text-gray-800 row-total">Rp ${fmtNum(d.total_salary)}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][notes]" value="${d.notes||''}" class="${ic}"></td>
            <td class="py-3 px-3">${delBtn}</td>`;
        },
        calcRow: (row) => {
            const days = parseInt(row.querySelector('[name*="attendance_days"]')?.value) || 0;
            const base = parseMoney(row.querySelector('[name*="base_salary"]')?.value);
            const meal = parseMoney(row.querySelector('[name*="meal_allowance_daily"]')?.value);
            const transport = parseMoney(row.querySelector('[name*="transport_daily"]')?.value);
            const overtime = parseMoney(row.querySelector('[name*="overtime"]')?.value);
            return base + (days * meal) + (days * transport) + overtime;
        }
    },
    bulanan: {
        headers: ['No','Keterangan','No.Regist/ID','A/N','Total Pengeluaran','Biaya Admin','Subtotal','Tanggal','Aksi'],
        row: (i, d={}) => {
            let dateVal = d.transaction_date ? String(d.transaction_date).substring(0,10) : '';
            return `
            <td class="py-3 px-3 text-center text-sm text-gray-500">${i}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][payment_name]" value="${d.payment_name||''}" required class="${ic}"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][registration_number]" value="${d.registration_number||''}" class="w-28 ${ic}"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][account_name]" value="${d.account_name||''}" class="w-28 ${ic}"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][total_expense]" value="${fmtNum(d.total_expense||d.bill_nominal)}" required class="w-36 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][admin_fee]" value="${fmtNum(d.admin_fee)}" class="w-28 ${nc} calc-trigger money-input" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3 font-bold text-gray-800 row-total">Rp ${fmtNum(d.total_payment)}</td>
            <td class="py-3 px-3"><input type="date" name="items[${i-1}][transaction_date]" value="${dateVal}" required class="${ic}"></td>
            <td class="py-3 px-3">${delBtn}</td>`;
        },
        calcRow: (row) => {
            const expense = parseMoney(row.querySelector('[name*="total_expense"]')?.value);
            const admin = parseMoney(row.querySelector('[name*="admin_fee"]')?.value);
            return expense + admin;
        }
    }
};

function formatMoney(el) {
    let val = el.value.replace(/[^0-9]/g, '');
    if (val === '') { el.value = ''; return; }
    el.value = Number(val).toLocaleString('id-ID');
}
// Attach to global window object so onclick attributes can find them
window['formatMoney_{{ $rab->id }}'] = formatMoney;

function parseMoney(val) {
    if (!val) return 0;
    return parseFloat(String(val).replace(/\./g, '').replace(/,/g, '.')) || 0;
}
document.getElementById('rabForm-{{ $rab->id }}').addEventListener('submit', function() {
    this.querySelectorAll('.money-input').forEach(input => {
        input.value = parseMoney(input.value);
    });
});

const operationalGroups = [
    "Honor Pencari Peserta",
    "Uang Transport / Honor Peserta Uji Serkom",
    "Operasional Pembekalan",
    "Operasional Uji Serkom",
    "Honor Asesor"
];

function initTable() {
    if (currentType === 'operasional') {
        renderOperationalGroups();
        return;
    }
    const cfg = configs[currentType];
    if (!cfg) return;

    const table = document.getElementById('expenseTable-{{ $rab->id }}');
    if (table) {
        table.classList.remove('min-w-[900px]', 'min-w-[1000px]', 'min-w-[1200px]');
        if (currentType === 'petty_cash') {
            table.classList.add('min-w-[1000px]');
        } else if (currentType === 'gaji') {
            table.classList.add('min-w-[1200px]');
        } else if (currentType === 'bulanan') {
            table.classList.add('min-w-[900px]');
        }
    }

    tableHead.innerHTML = '<tr class="text-xs text-gray-500 border-b border-gray-200 bg-gray-50">' +
        cfg.headers.map(h => `<th class="py-3 px-3 font-bold whitespace-nowrap">${h}</th>`).join('') + '</tr>';

    existingItems.forEach(item => addRow(item));
    if(existingItems.length === 0) addRow();
}

function addRow(data = {}) {
    rowCount++;
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-50 hover:bg-blue-50/30 transition-colors';
    // Modify the raw HTML string to use the scoped formatMoney function
    let rowHtml = configs[currentType].row(rowCount, data);
    rowHtml = rowHtml.replace(/formatMoney\(this\)/g, `window['formatMoney_{{ $rab->id }}'](this)`);
    tr.innerHTML = rowHtml;
    tableBody.appendChild(tr);

    tr.querySelectorAll('.calc-trigger').forEach(input => {
        input.addEventListener('input', () => calculateRow(tr));
    });
}
window['addRow_{{ $rab->id }}'] = addRow;

function removeRow(btn) {
    const row = btn.closest('tr');
    row.remove();
    reindexRows();
    calculateGrandTotal();
}
window['removeRow_{{ $rab->id }}'] = removeRow;

function reindexRows() {
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach((row, idx) => {
        row.querySelector('td:first-child').textContent = idx + 1;
        row.querySelectorAll('input, select').forEach(input => {
            const name = input.name.replace(/items\[\d+\]/, `items[${idx}]`);
            input.name = name;
        });
    });
    rowCount = rows.length;
}

function calculateRow(row) {
    const total = configs[currentType].calcRow(row);
    const totalCell = row.querySelector('.row-total');
    if (totalCell) totalCell.textContent = 'Rp ' + total.toLocaleString('id-ID');
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grand = 0;
    tableBody.querySelectorAll('tr').forEach(row => {
        grand += configs[currentType].calcRow(row);
    });
    document.getElementById('grandTotal-{{ $rab->id }}').textContent = 'Rp ' + grand.toLocaleString('id-ID');
}

// ==========================================
// OPERASIONAL FUNCTIONS
// ==========================================
function renderOperationalGroups() {
    const wrapper = document.getElementById('operationalGroupsWrapper-{{ $rab->id }}');
    wrapper.innerHTML = '';

    operationalGroups.forEach((groupName, gIdx) => {
        const groupItems = existingItems.filter(item => item.group_name === groupName);

        const groupHtml = `
            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white" data-group-index="${gIdx}">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-2 text-xs">${gIdx + 1}</span>
                        ${groupName}
                        <input type="hidden" name="op_groups[${gIdx}][name]" value="${groupName}">
                    </h4>
                    <button type="button" onclick="window['addOperationalRow_{{ $rab->id }}'](${gIdx})" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold flex items-center bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Tambah Item
                    </button>
                </div>
                <div class="overflow-x-hidden md:overflow-x-auto p-3 md:p-0 bg-slate-50 md:bg-white">
                    <table class="w-full text-left op-table block md:table">
                        <thead class="hidden md:table-header-group bg-white border-b border-gray-100 text-xs text-gray-500">
                            <tr>
                                <th class="py-2 px-3 w-10 text-center">No</th>
                                <th class="py-2 px-3">Item</th>
                                <th class="py-2 px-3 w-24">Volume</th>
                                <th class="py-2 px-3 w-20">Satuan</th>
                                <th class="py-2 px-3 w-36">Rp/Unit</th>
                                <th class="py-2 px-3 w-32 text-right">Jumlah</th>
                                <th class="py-2 px-3">Keterangan</th>
                                <th class="py-2 px-3 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="opBody_{{ $rab->id }}_${gIdx}" class="block md:table-row-group">
                        </tbody>
                        <tfoot class="block md:table-footer-group bg-white md:bg-gray-50/50 mt-2 md:mt-0 rounded-xl md:rounded-none overflow-hidden shadow-sm md:shadow-none border border-gray-100 md:border-0">
                            <tr class="block md:table-row flex justify-between items-center p-4 md:p-0">
                                <td colspan="5" class="hidden md:table-cell py-3 px-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Subtotal:</td>
                                <td class="block md:hidden text-sm font-bold text-gray-500 uppercase">Subtotal</td>
                                <td class="block md:table-cell py-0 md:py-3 px-0 md:px-3 text-right font-extrabold text-blue-600 op-subtotal text-lg md:text-base">Rp 0</td>
                                <td colspan="2" class="hidden md:table-cell"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', groupHtml);

        if (groupItems.length > 0) {
            groupItems.forEach((item) => addOperationalRow(gIdx, item));
        } else {
            addOperationalRow(gIdx);
        }
    });
    calcOperationalGrandTotal();
}

function addOperationalRow(gIdx, data = {}) {
    const tbody = document.getElementById(`opBody_{{ $rab->id }}_${gIdx}`);
    const rIdx = tbody.children.length; // row index inside this group

    const tr = document.createElement('tr');
    tr.className = 'block md:table-row border border-gray-100 md:border-b md:border-x-0 md:border-t-0 border-gray-50 hover:bg-slate-50 transition-colors op-row rounded-xl md:rounded-none bg-white p-4 md:p-0 mb-4 md:mb-0 shadow-sm md:shadow-none';
    tr.innerHTML = `
        <td class="hidden md:table-cell py-2 px-3 text-center text-xs text-gray-400 op-row-num">${rIdx + 1}</td>
        <td class="block md:table-cell py-2 md:py-2 px-0 md:px-3">
            <div class="md:hidden flex justify-between items-center mb-3">
                <span class="text-sm font-bold text-gray-700">Item <span class="op-row-num-mobile">${rIdx + 1}</span></span>
                <button type="button" onclick="window['removeOperationalRow_{{ $rab->id }}'](this, ${gIdx})" class="text-red-500 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Nama Item</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][item_name]" value="${data.item_name||''}" required class="${ic}" placeholder="Nama item">
        </td>
        <td class="inline-block md:table-cell w-[48%] md:w-auto py-2 md:py-2 px-0 md:px-3 pr-1 md:pr-3">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Volume</label>
            <input type="number" name="op_groups[${gIdx}][items][${rIdx}][volume]" value="${data.volume||''}" required step="0.01" min="0.01" class="w-full ${nc} op-calc" placeholder="0">
        </td>
        <td class="inline-block md:table-cell w-[48%] md:w-auto py-2 md:py-2 px-0 md:px-3 pl-1 md:pl-3">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Satuan</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][unit]" value="${data.unit||''}" required class="w-full ${ic}" placeholder="pcs">
        </td>
        <td class="block md:table-cell py-2 md:py-2 px-0 md:px-3">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Rp / Unit</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][unit_price]" value="${fmtNum(data.unit_price)}" required class="w-full ${nc} money-input op-calc" placeholder="0" oninput="window['formatMoney_{{ $rab->id }}'](this)">
        </td>
        <td class="block md:table-cell py-3 md:py-2 px-4 md:px-3 mt-2 md:mt-0 bg-gray-50 md:bg-transparent rounded-lg md:rounded-none">
            <div class="flex justify-between md:justify-end items-center md:block">
                <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Jumlah</span>
                <span class="font-bold text-gray-800 op-row-total text-sm md:text-sm md:text-right block">Rp ${fmtNum(data.total)}</span>
            </div>
        </td>
        <td class="block md:table-cell py-2 md:py-2 px-0 md:px-3 mt-2 md:mt-0">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Keterangan</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][note]" value="${data.note||''}" class="w-full ${ic}" placeholder="Ket">
        </td>
        <td class="hidden md:table-cell py-2 px-3 text-center">
            <button type="button" onclick="window['removeOperationalRow_{{ $rab->id }}'](this, ${gIdx})" class="text-red-400 hover:text-red-600 transition p-1.5 rounded hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    tr.querySelectorAll('.op-calc').forEach(input => {
        input.addEventListener('input', () => calcOperationalGroup(gIdx));
    });

    if (data.id) calcOperationalGroup(gIdx);
}
window['addOperationalRow_{{ $rab->id }}'] = addOperationalRow;

function removeOperationalRow(btn, gIdx) {
    const tbody = document.getElementById(`opBody_{{ $rab->id }}_${gIdx}`);
    if (tbody.children.length <= 1) {
        alert('Minimal harus ada 1 item pada setiap kelompok!');
        return;
    }

    const row = btn.closest('tr');
    row.remove();

    // Reindex
    Array.from(tbody.children).forEach((tr, index) => {
        tr.querySelector('.op-row-num').textContent = index + 1;
        const mobileNum = tr.querySelector('.op-row-num-mobile');
        if (mobileNum) mobileNum.textContent = index + 1;

        tr.querySelectorAll('input').forEach(input => {
            const name = input.name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
            input.name = name;
        });
    });

    calcOperationalGroup(gIdx);
}
window['removeOperationalRow_{{ $rab->id }}'] = removeOperationalRow;

function calcOperationalGroup(gIdx) {
    const tbody = document.getElementById(`opBody_{{ $rab->id }}_${gIdx}`);
    let groupTotal = 0;

    Array.from(tbody.children).forEach(tr => {
        const vol = parseFloat(tr.querySelector('[name*="[volume]"]')?.value) || 0;
        const price = parseMoney(tr.querySelector('[name*="[unit_price]"]')?.value);
        const rowTotal = vol * price;

        tr.querySelector('.op-row-total').textContent = 'Rp ' + rowTotal.toLocaleString('id-ID');
        groupTotal += rowTotal;
    });

    const container = tbody.closest('[data-group-index]');
    container.querySelector('.op-subtotal').textContent = 'Rp ' + groupTotal.toLocaleString('id-ID');

    calcOperationalGrandTotal();
}

function calcOperationalGrandTotal() {
    let grand = 0;
    document.querySelectorAll('#operationalGroupsWrapper-{{ $rab->id }} .op-subtotal').forEach(el => {
        grand += parseMoney(el.textContent.replace('Rp ', ''));
    });
    document.getElementById('operationalGrandTotal-{{ $rab->id }}').textContent = 'Rp ' + grand.toLocaleString('id-ID');
}

// Automatically init the table when the script runs (which is when the modal is included)
initTable();

})();
</script>
@endpush
