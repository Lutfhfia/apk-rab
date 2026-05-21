<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAB {{ $rab->rab_number }}</title>
    <style>
        @page { margin: 160px 40px 100px 40px; size: landscape; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }

        header { position: fixed; top: -160px; left: -40px; right: -40px; height: 130px; text-align: center; overflow: hidden; }
        footer { position: fixed; bottom: -100px; left: -40px; right: -40px; height: 90px; text-align: center; overflow: hidden; }

        main { position: relative; }

        /* Header */
        .header { width: 100%; border-bottom: 4px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 20px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 65px; vertical-align: middle; }
        .header-logo img { width: 60px; height: 60px; }
        .header-text { padding-left: 15px; vertical-align: middle; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-tagline { font-size: 9px; font-weight: bold; color: #555; margin-top: 1px; }
        .company-address { font-size: 9px; color: #666; margin-top: 3px; }

        /* Report Info */
        .report-info { text-align: center; margin-bottom: 15px; }
        .report-info h2 { font-size: 15px; font-weight: bold; color: #333; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 2px; }
        .report-info .divider { width: 50px; height: 3px; background-color: #1e3a8a; margin: 4px auto; }
        .report-info p { font-size: 11px; color: #666; margin: 2px 0; }
        .report-number { font-size: 9px; color: #999; }

        /* Meta */
        .report-meta { margin-bottom: 15px; font-size: 9px; }
        .report-meta table { width: 55%; border-collapse: collapse; }
        .report-meta td { padding: 1.5px 0; color: #555; }
        .report-meta .label { font-weight: bold; width: 120px; color: #333; }

        /* Data Table */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th { background-color: #1e3a8a; color: white; padding: 6px 5px; font-weight: bold; text-align: left; border: 1px solid #1e3a8a; font-size: 8px; text-transform: uppercase; }
        table.data-table td { padding: 5px; border: 1px solid #ddd; vertical-align: middle; font-size: 9px; }
        table.data-table tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .total-row { background-color: #1e3a8a; color: white; font-weight: bold; }
        .total-row td { border-color: #1e3a8a; color: white; font-size: 10px; }

        /* Signature */
        .signature-section { width: 100%; margin-top: 25px; }
        .signature-box { float: right; width: 220px; text-align: center; }
        .signature-date { margin-bottom: 6px; font-size: 10px; }
        .signature-label { font-weight: bold; margin-bottom: 2px; font-size: 10px; }
        .signature-name { font-weight: bold; text-decoration: underline; font-size: 11px; }
        .signature-role { font-size: 10px; margin-bottom: 48px; }

        /* Footer */
        .line-blue { height: 6px; background-color: #1e3a8a; width: 100%; }
        .line-gold { height: 4px; background-color: #B8860B; width: 100%; }
        .footer-content { padding: 6px 40px; text-align: center; font-size: 8px; color: #666; }
        .contact-item { display: inline-block; margin: 0 8px; font-weight: bold; color: #333; }
        .contact-icon { font-size: 10px; margin-right: 3px; }

        .header-contact { margin-top: 5px; font-size: 9px; color: #333; font-weight: bold; }
        .header-contact-item { display: inline-block; margin-right: 15px; }

        .clear { clear: both; }
    </style>
</head>
<body>
    <header>
        @if(file_exists(public_path('foto/header_sbk.png')))
            <img src="{{ public_path('foto/header_sbk.png') }}" style="width: 100%; height: 100%; object-fit: cover;">
        @else
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td class="header-logo">
                            @if(file_exists(public_path('foto/logo_sbk.png')))
                                <img src="{{ public_path('foto/logo_sbk.png') }}" alt="Logo">
                            @endif
                        </td>
                        <td class="header-text">
                            <div class="company-name">{{ \App\Models\Setting::getValue('company_name', 'PT Sertifikasi Bermutu Ketenagalistrikan') }}</div>
                            <div class="company-address">Ruko Springhill, Jl. Letjen Tni Dr. H. Ibnu Sutowo No.Blok D 28, Talang Klp., Kec. Alang-Alang Lebar, Kota Palembang, Sumatera Selatan 30961</div>
                            <div class="header-contact">
                                <span class="header-contact-item"><span class="contact-icon">📱</span> 0838 5436 4212</span>
                                <span class="header-contact-item"><span class="contact-icon">✉️</span> sertifikasibermutuketenagalistrikan@gmail.com</span>
                                <span class="header-contact-item"><span class="contact-icon">🌐</span> www.lsksertifikasibermutuketenagalist.com</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endif
    </header>

    <footer>
        @if(file_exists(public_path('foto/footer_sbk.png')))
            <img src="{{ public_path('foto/footer_sbk.png') }}" style="width: 100%; height: 100%; object-fit: cover;">
        @else
            <div class="line-blue"></div>
            <div class="line-gold"></div>
            <div class="footer-content">
                <div style="font-style: italic;">
                    Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi RAB PT Sertifikasi Bermutu Ketenagalistrikan
                </div>
            </div>
        @endif
    </footer>

    <main>
        {{-- Title --}}
        <div class="report-info">
            <h2>Rancangan Anggaran Biaya</h2>
            <div class="divider"></div>
            <p>{{ $rab->rab_number }}</p>
        </div>

        {{-- Meta Info --}}
        <div class="report-meta">
            <table>
                <tr>
                    <td class="label">Jenis Pengeluaran</td>
                    <td>: {{ $rab->expenseType->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pengajuan</td>
                    <td>: {{ $rab->request_date->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Periode</td>
                    <td>: {{ $rab->period_month_name }} {{ $rab->request_date->format('Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Pembuat</td>
                    <td>: {{ ($rab->user->role->label() ?? 'Admin') }} - {{ $rab->user->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td>: {{ $rab->status->label() }}</td>
                </tr>
                @if($rab->description)
                <tr>
                    <td class="label">Keterangan</td>
                    <td>: {{ $rab->description }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Data Table --}}
        @if($rab->expenseType->code === 'operasional')
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">No</th>
                    <th>Item</th>
                    <th style="width:18%;">Rp / Unit</th>
                    <th class="text-right" style="width:15%;">Jumlah (Rp)</th>
                    <th style="width:15%;">Ket</th>
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
                    // Get items that do not match the 5 standard groups (e.g. old data where group_name is null)
                    $otherItems = $expenseItems->filter(function($item) use ($operationalGroups) {
                        return !in_array($item->group_name, $operationalGroups);
                    });
                @endphp
                @foreach($operationalGroups as $gIdx => $groupName)
                    @php
                        $groupItems = $expenseItems->where('group_name', $groupName);
                    @endphp
                    @if($groupItems->count() > 0)
                    <tr>
                        <td class="text-center font-bold">{{ $gIdx + 1 }}.</td>
                        <td colspan="4" class="font-bold text-uppercase" style="background-color: #f0f4f8;">{{ strtoupper($groupName) }}</td>
                    </tr>
                    @foreach($groupItems as $item)
                    <tr>
                        <td></td>
                        <td>{{ $item->volume }} {{ $item->unit }} {{ $item->item_name }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td>{{ $item->note ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @endif
                @endforeach

                @if($otherItems->count() > 0)
                <tr>
                    <td class="text-center font-bold">*</td>
                    <td colspan="4" class="font-bold text-uppercase" style="background-color: #f0f4f8;">ITEM OPERASIONAL (UMUM)</td>
                </tr>
                @foreach($otherItems as $item)
                <tr>
                    <td></td>
                    <td>
                        @if($item->volume > 0)
                            {{ $item->volume }} {{ $item->unit }} 
                        @endif
                        {{ $item->item_name }}
                    </td>
                    <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    <td>{{ $item->note ?? '-' }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right" style="padding: 7px 5px;">TOTAL KESELURUHAN</td>
                    <td class="text-right" style="padding: 7px 5px;">Rp {{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">No</th>
                    @if($rab->expenseType->code === 'petty_cash')
                    <th>Nama Pengeluaran</th><th>Keterangan</th><th style="width:6%;">Jml</th><th style="width:6%;">Satuan</th><th class="text-right" style="width:11%;">Harga Satuan (Rp)</th><th class="text-right" style="width:10%;">Admin (Rp)</th><th class="text-right" style="width:11%;">Total (Rp)</th><th style="width:9%;">Tanggal</th>
                    @elseif($rab->expenseType->code === 'gaji')
                    <th>Nama</th><th>Jabatan</th><th>No. Rek</th><th style="width:5%;">Hadir</th><th class="text-right" style="width:10%;">Gaji Pokok (Rp)</th><th class="text-right" style="width:9%;">Makan/Hari (Rp)</th><th class="text-right" style="width:9%;">Transport/Hari (Rp)</th><th class="text-right" style="width:9%;">Lembur (Rp)</th><th class="text-right" style="width:10%;">Total Gaji (Rp)</th><th>Catatan</th>
                    @elseif($rab->expenseType->code === 'bulanan')
                    <th>Keterangan</th><th>No.Regist/ID</th><th>A/N</th><th class="text-right" style="width:12%;">Total Pengeluaran (Rp)</th><th class="text-right" style="width:10%;">Biaya Admin (Rp)</th><th class="text-right" style="width:12%;">Subtotal (Rp)</th><th style="width:9%;">Tanggal</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($expenseItems as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    @if($rab->expenseType->code === 'petty_cash')
                    <td>{{ $item->expense_name }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td class="text-center">{{ $item->volume }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->unit_price > 0 ? $item->unit_price : $item->nominal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->admin_fee, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total > 0 ? $item->total : $item->nominal, 0, ',', '.') }}</td>
                    <td>{{ $item->transaction_date->format('d/m/Y') }}</td>
                    @elseif($rab->expenseType->code === 'gaji')
                    <td>{{ $item->employee_name }}</td>
                    <td>{{ $item->position ?? '-' }}</td>
                    <td>{{ $item->bank_account_number }}</td>
                    <td class="text-center">{{ $item->attendance_days }}</td>
                    <td class="text-right">{{ number_format($item->base_salary > 0 ? $item->base_salary : $item->salary_nominal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->meal_allowance_daily, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->transport_daily, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->overtime, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total_salary > 0 ? $item->total_salary : $item->salary_nominal, 0, ',', '.') }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                    @elseif($rab->expenseType->code === 'bulanan')
                    <td>{{ $item->payment_name }}</td>
                    <td>{{ $item->registration_number ?? '-' }}</td>
                    <td>{{ $item->account_name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->total_expense > 0 ? $item->total_expense : $item->bill_nominal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->admin_fee, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total_payment > 0 ? $item->total_payment : $item->total_expense, 0, ',', '.') }}</td>
                    <td>{{ $item->transaction_date ? $item->transaction_date->format('d/m/Y') : '-' }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if($rab->expenseType->code === 'petty_cash')
                <tr class="total-row">
                    <td colspan="7" class="text-right" style="padding: 7px 5px;">TOTAL</td>
                    <td class="text-right" style="padding: 7px 5px;">{{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @elseif($rab->expenseType->code === 'gaji')
                <tr class="total-row">
                    <td colspan="9" class="text-right" style="padding: 7px 5px;">TOTAL</td>
                    <td class="text-right" style="padding: 7px 5px;">{{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @elseif($rab->expenseType->code === 'bulanan')
                <tr class="total-row">
                    <td colspan="6" class="text-right" style="padding: 7px 5px;">TOTAL</td>
                    <td class="text-right" style="padding: 7px 5px;">{{ number_format($rab->total_amount, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endif
            </tfoot>
        </table>
        @endif

        {{-- Signature --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-date">Palembang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="signature-label">Mengetahui</div>
                <div class="signature-role">{{ $signerPosition }}</div>
                <div class="signature-name">{{ $signerName }}</div>
            </div>
        </div>

        <div class="clear"></div>

        <div class="clear"></div>
    </main>
</body>
</html>
