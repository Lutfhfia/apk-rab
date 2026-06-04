{{-- Modal Buat RAB --}}
@php
    $showCreateRabErrors = $errors->any() && old('form_context') === 'rab_create';
    $rabNumberParts = \App\Models\Rab::parseNumberParts($rabNumber ?? null);
@endphp
<div id="createRabModal" class="fixed inset-0 bg-black/50 z-[60] {{ $showCreateRabErrors ? 'flex' : 'hidden' }} items-center justify-center p-4">
    <div class="bg-gray-50 rounded-2xl shadow-2xl w-full max-w-[95vw] max-h-[95vh] flex flex-col overflow-hidden animate-fade-in">

        {{-- Header Modal --}}
        <div class="p-5 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Buat RAB Baru</h3>
                <p class="text-sm text-gray-500">Formulir pembuatan Rancangan Anggaran Biaya</p>
            </div>
            <button type="button" onclick="closeCreateRabModal()" class="h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 flex-1 overflow-y-auto min-h-0">
            <form method="POST" action="{{ route('rab.store') }}" id="rabForm">
                @csrf
                <input type="hidden" name="form_context" value="rab_create">
                <input type="hidden" name="action" id="rabFormAction" value="submit">
                <input type="hidden" name="rab_number" id="rabNumberInput" value="{{ old('rab_number', $rabNumber ?? '') }}">

                {{-- Data Umum RAB --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h3 class="text-base font-bold text-gray-800 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Data Umum RAB
                    </h3>

                    @if($showCreateRabErrors)
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
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">No. Urut</label>
                            <input type="number" name="rab_sequence" id="rabSequenceInput" value="{{ old('rab_sequence', $rabNumberParts['sequence']) }}" readonly class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-600 focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Bulan Surat *</label>
                            <input type="text" name="rab_month" id="rabMonthInput" value="{{ old('rab_month', $rabNumberParts['month']) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="V">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Tahun Surat *</label>
                            <input type="text" name="rab_year" id="rabYearInput" value="{{ old('rab_year', $rabNumberParts['year']) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="2026">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Nomor RAB</label>
                            <input type="text" id="rabNumberPreview" value="{{ old('rab_number', $rabNumber ?? '') }}" readonly class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-600 focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Pembuat</label>
                            <input type="text" value="{{ auth()->user()->role->label() }}-{{ auth()->user()->name }}" readonly class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-600">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Tanggal Pengajuan *</label>
                            <input type="date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Jenis Pengeluaran *</label>
                            <select name="expense_type_id" id="expenseTypeSelect" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="">-- Pilih Jenis --</option>
                                @foreach($expenseTypes as $type)
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}" {{ old('expense_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Periode Bulan</label>
                            <select name="period_month" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="">-- Pilih Bulan --</option>
                                @php
                                    $bulanList = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                                @endphp
                                @foreach($bulanList as $num => $bulan)
                                <option value="{{ $num }}" {{ old('period_month') == $num ? 'selected' : '' }}>{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Keterangan</label>
                            <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Dynamic Expense Table --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6" id="expenseTableContainer" style="display: none;">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-bold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <span id="expenseTableTitle">Rincian Pengeluaran</span>
                        </h3>
                        <button type="button" onclick="addRow()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Tambah Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="expenseTable">
                            <thead id="expenseTableHead"></thead>
                            <tbody id="expenseTableBody"></tbody>
                            <tfoot>
                                <tr class="bg-gray-50 border-t-2 border-gray-200">
                                    <td colspan="20" class="py-4 px-4 text-right font-bold text-gray-700">TOTAL:</td>
                                    <td class="py-4 px-4 font-extrabold text-gray-800 text-lg" id="grandTotal">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Operational Expense Tables (5 Groups) --}}
                <div id="operationalTableContainer" style="display: none;">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Rincian Biaya Operasional
                    </h3>

                    <div id="operationalGroupsWrapper" class="space-y-6">
                        <!-- Groups will be injected by JS -->
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-emerald-200 p-6 mt-6 flex justify-between items-center">
                        <span class="font-bold text-gray-700 text-lg">TOTAL KESELURUHAN RAB:</span>
                        <span class="font-extrabold text-emerald-600 text-2xl" id="operationalGrandTotal">Rp 0</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end space-x-3 mt-8">
                    <button type="button" onclick="closeCreateRabModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold transition">Batal</button>
                    <button type="submit" onclick="document.getElementById('rabFormAction').value = 'draft'" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-xl text-sm font-bold transition submit-btn">Simpan Draft</button>
                    <button type="submit" onclick="document.getElementById('rabFormAction').value = 'submit'" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition flex items-center submit-btn">
                        <svg class="w-4 h-4 mr-2 icon-submit" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span class="text-submit">Ajukan RAB</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
