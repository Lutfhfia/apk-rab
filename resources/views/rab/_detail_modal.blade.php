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
                @endif
            </div>

            {{-- Payment Info (Dana Masuk dari Manajer) --}}
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
