<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap LPJ Nota Belanja - {{ $periodLabel }}</title>
    <style>
        @page { margin: 90px 32px 70px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #333; margin: 0; }
        header { position: fixed; top: -78px; left: -32px; right: -32px; height: 72px; border-bottom: 3px solid #065f46; padding: 12px 32px; }
        footer { position: fixed; bottom: -55px; left: -32px; right: -32px; height: 45px; border-top: 2px solid #065f46; padding: 8px 32px; font-size: 8px; color: #666; text-align: center; }
        .company { font-size: 15px; font-weight: bold; color: #065f46; text-transform: uppercase; }
        .subtitle { font-size: 9px; color: #555; margin-top: 4px; }
        h1 { font-size: 15px; margin: 0 0 4px 0; text-transform: uppercase; color: #111827; }
        .meta { margin-bottom: 12px; line-height: 1.6; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { border: 1px solid #d1d5db; padding: 7px 8px; }
        .summary .label { background: #f3f4f6; font-weight: bold; width: 18%; }
        .summary .value { font-weight: bold; color: #065f46; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #065f46; color: #fff; border: 1px solid #065f46; padding: 5px 4px; font-size: 8px; text-align: left; }
        table.data td { border: 1px solid #d1d5db; padding: 5px 4px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .status { font-weight: bold; }
        .signature { width: 240px; margin-left: auto; text-align: center; margin-top: 28px; page-break-inside: avoid; }
        .signature .space { height: 52px; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <header>
        <div class="company">{{ $companyName }}</div>
        <div class="subtitle">Laporan Rekap Nota Belanja / LPJ</div>
    </header>

    <footer>
        Dokumen ini dihasilkan otomatis oleh Sistem Informasi RAB berdasarkan data nota belanja/LPJ yang tervalidasi pada sistem.
    </footer>

    <main>
        <h1>Laporan Rekap Nota Belanja / LPJ</h1>
        <div class="meta">
            <div><strong>Periode:</strong> {{ $periodLabel }}</div>
            <div><strong>Status:</strong> {{ $statusOptions[$recapStatus] ?? 'Disetujui / Valid' }}</div>
            <div><strong>Dicetak oleh:</strong> {{ $printedBy }} pada {{ $printDate }}</div>
        </div>

        <table class="summary">
            <tr>
                <td class="label">Total Nominal</td>
                <td class="value">Rp {{ number_format($totalValidAmount, 0, ',', '.') }}</td>
                <td class="label">Jumlah RAB</td>
                <td class="value">{{ $totalRabCount }}</td>
                <td class="label">Jumlah Nota</td>
                <td class="value">{{ $totalValidPaymentCount }}</td>
            </tr>
        </table>

        <h2 style="font-size: 11px; margin: 14px 0 6px 0; color: #065f46; border-bottom: 1px solid #065f46; padding-bottom: 2px;">Daftar Nota Belanja / LPJ</h2>
        <table class="data" style="margin-bottom: 16px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">No</th>
                    <th style="width: 18%;">No. RAB</th>
                    <th style="width: 14%;">Kategori</th>
                    <th style="width: 10%;">Tanggal Nota</th>
                    <th style="width: 20%;">Toko / Vendor</th>
                    <th style="width: 12%;">No. Nota</th>
                    <th class="text-right" style="width: 12%;">Nominal</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recapReceipts as $index => $receipt)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $receipt->rab->rab_number ?? '-' }}</td>
                    <td>{{ $receipt->rab->expenseType->name ?? '-' }}</td>
                    <td>{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                    <td>{{ $receipt->store_name ?: '-' }}</td>
                    <td>{{ $receipt->receipt_number ?: '-' }}</td>
                    <td class="text-right">Rp {{ number_format($receipt->total_amount, 0, ',', '.') }}</td>
                    <td class="status">{{ $receipt->status->label() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center muted">Tidak ada data nota belanja untuk periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature">
            <div>Palembang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
            <div>Manajer Keuangan</div>
            <div class="space"></div>
            <div><strong><u>{{ $printedBy }}</u></strong></div>
        </div>
    </main>
</body>
</html>
