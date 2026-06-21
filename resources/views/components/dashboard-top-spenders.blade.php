@php
    $user = auth()->user();
    if ($user->isAdmin()) {
        $indexUrl = route('rab.index');
    } elseif ($user->isManajer()) {
        $indexUrl = route('manajer.rab.index');
    } else {
        $indexUrl = route('direktur.rab.index');
    }
@endphp
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <h2 class="text-sm font-bold text-gray-800">Top 5 Pengeluaran Terbesar</h2>
        </div>
        <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Berdasarkan
            Nilai Anggaran</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="text-gray-500 border-b border-gray-100 bg-gray-50/50 text-xs uppercase tracking-wider">
                    <th class="py-3 px-4 font-bold text-center">Peringkat</th>
                    <th class="py-3 px-4 font-bold">No. RAB</th>
                    <th class="py-3 px-4 font-bold">Kategori</th>
                    <th class="py-3 px-4 font-bold text-right">Nilai Anggaran</th>
                    <th class="py-3 px-4 font-bold text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-600">
                @forelse($topSpenders as $index => $rab)
                    <tr class="border-b border-gray-50 hover:bg-amber-50/30 transition-colors">
                        <td class="py-3 px-4 font-bold text-center">
                            @if ($index === 0)
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-400 text-white shadow-sm text-xs">1</span>
                            @elseif($index === 1)
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-300 text-white shadow-sm text-xs">2</span>
                            @elseif($index === 2)
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white shadow-sm text-xs">3</span>
                            @else
                                <span class="text-gray-400 text-xs">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-800">
                            <a href="{{ $indexUrl }}?status={{ $rab->status->value }}&open_rab_id={{ $rab->id }}"
                                class="text-blue-600 hover:text-blue-700 underline decoration-dotted transition-all">{{ $rab->rab_number }}</a>
                        </td>
                        <td class="py-3 px-4">
                            <span
                                class="border border-gray-300 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase">{{ $rab->expenseType->name ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-4 font-extrabold text-red-500 text-right">Rp
                            {{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="{{ $rab->status->badgeClasses() }} text-[10px] font-bold px-2 py-0.5 rounded-lg">{{ $rab->status->label() }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">
                            Belum ada data pengeluaran untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
