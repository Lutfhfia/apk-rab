<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas - {{ $periodLabel }}</title>
    <style>
        @page { margin: 110px 40px 100px 40px; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        
        /* Colors */
        .primary-blue { color: #1e3a8a; }
        .bg-primary-blue { background-color: #1e3a8a; }

        header { position: fixed; top: -110px; left: -40px; right: -40px; height: 110px; text-align: center; overflow: hidden; }
        footer { position: fixed; bottom: -100px; left: -40px; right: -40px; height: 90px; text-align: center; overflow: hidden; }
        main { position: relative; }
        
        /* Header Section */
        .header { width: 100%; border-bottom: 4px solid #1e3a8a; padding-bottom: 15px; margin-bottom: 25px; position: relative; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 80px; vertical-align: middle; }
        .header-logo img { width: 75px; height: 75px; }
        .header-text { padding-left: 20px; vertical-align: middle; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-tagline { font-size: 10px; font-weight: bold; color: #555; margin-top: 2px; }
        .company-address { font-size: 10px; color: #666; margin-top: 5px; width: 80%; }

        /* Report Info */
        .report-info { text-align: left; margin-bottom: 15px; }
        .report-info h2 { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 2px; text-transform: uppercase; }
        .report-info .divider { width: 60px; height: 3px; background-color: #1e3a8a; margin: 5px auto; }
        .report-info p { font-size: 12px; color: #666; margin: 3px 0; }
        .report-number { font-size: 10px; color: #999; }

        /* Report Meta */
        .report-meta { margin-bottom: 20px; font-size: 10px; }
        .report-meta table { width: 50%; border-collapse: collapse; }
        .report-meta td { padding: 2px 0; color: #555; }
        .report-meta .label { font-weight: bold; width: 120px; color: #333; }

        /* Table Style */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th { background-color: #1e3a8a; color: white; padding: 4px; font-weight: bold; text-align: left; border: 1px solid #1e3a8a; font-size: 8px; text-transform: uppercase; }
        table.data-table td { padding: 3px 4px; border: 1px solid #ddd; vertical-align: middle; font-size: 9px; }
        table.data-table tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .masuk-text { color: #15803d; font-weight: 600; }
        .keluar-text { color: #b91c1c; font-weight: 600; }

        /* Recap Section */
        .recap-section { width: 55%; float: left; margin-bottom: 30px; }
        .recap-table { width: 100%; border-collapse: collapse; }
        .recap-table th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 7px 10px; text-align: left; font-size: 10px; }
        .recap-table td { border: 1px solid #ddd; padding: 7px 10px; font-size: 10px; }
        .recap-header { background-color: #1e3a8a; color: white; padding: 8px 10px; font-weight: bold; text-align: center; font-size: 11px; letter-spacing: 2px; }

        /* Signature Section */
        .signature-section { width: 100%; margin-top: 30px; clear: both; }
        .signature-box { float: right; width: 250px; text-align: center; }
        .signature-date { margin-bottom: 8px; font-size: 11px; }
        .signature-label { font-weight: bold; margin-bottom: 2px; font-size: 11px; }
        .signature-name { font-weight: bold; text-decoration: underline; font-size: 12px; }
        .signature-role { font-size: 11px; margin-bottom: 58px; }

        /* Footer Section */
        .line-blue { height: 6px; background-color: #1e3a8a; width: 100%; }
        .line-gold { height: 4px; background-color: #B8860B; width: 100%; }
        .footer-content { padding: 6px 40px; text-align: center; font-size: 8px; color: #666; }
        .contact-item { display: inline-block; margin: 0 8px; font-weight: bold; color: #333; }
        .contact-icon { font-size: 10px; margin-right: 3px; }

        .header-contact { margin-top: 5px; font-size: 9px; color: #333; font-weight: bold; }
        .header-contact-item { display: inline-block; margin-right: 15px; }

        .clear { clear: both; }
        .saldo-row { background-color: #eef2ff; font-weight: bold; font-style: italic; }
        .total-row { background-color: #1e3a8a; color: white; font-weight: bold; }
        .total-row td { border-color: #1e3a8a; color: white; }
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
                <div style="margin-top: 4px; font-style: italic;">
                    Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi RAB PT Sertifikasi Bermutu Ketenagalistrikan
                    berdasarkan data RAB, transaksi uang masuk, transaksi uang keluar, dan bukti pembayaran yang telah diinput oleh Admin Keuangan.
                </div>
            </div>
        @endif
    </footer>

    <main>
        <div class="report-info">
            <h2>LAPORAN ARUS KAS</h2>
            <p style="font-size: 11px; margin-top: 2px;">Periode: {{ $periodLabel }}</p>
        </div>

        {{-- ============================================ --}}
        {{-- 2. SALDO AWAL + 3. RINCIAN TRANSAKSI --}}
        {{-- ============================================ --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 16%;">No. RAB</th>
                    <th style="width: 24%;">Keterangan</th>
                    <th class="text-right" style="width: 14%;">Uang Masuk (Rp)</th>
                    <th class="text-right" style="width: 14%;">Uang Keluar (Rp)</th>
                    <th class="text-right" style="width: 16%;">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Saldo Awal Row --}}
                <tr class="saldo-row">
                    <td class="text-center">-</td>
                    <td>{{ $cashFlows->count() > 0 ? $cashFlows->first()->transaction_date->format('d/m/Y') : '-' }}</td>
                    <td>-</td>
                    <td>Saldo Awal Periode</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
                </tr>

                @php $runningBalance = $saldoAwal; @endphp
                @foreach($cashFlows as $i => $cf)
                @php
                    $debit = (float) $cf->debit;
                    $credit = (float) $cf->credit;
                    $runningBalance = $runningBalance + $debit - $credit;
                    $rabNumber = $cf->rab ? $cf->rab->rab_number : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $cf->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $rabNumber }}</td>
                    <td>{{ $cf->description }}</td>
                    <td class="text-right {{ $debit > 0 ? 'masuk-text' : '' }}">{{ $debit > 0 ? number_format($debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right {{ $credit > 0 ? 'keluar-text' : '' }}">{{ $credit > 0 ? number_format($credit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                
                {{-- Total Row --}}
                <tr class="total-row">
                    <td colspan="4" class="text-right" style="padding: 8px 6px;">TOTAL</td>
                    <td class="text-right" style="padding: 8px 6px;">{{ number_format($totalUangMasuk, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 8px 6px;">{{ number_format($totalUangKeluar, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 8px 6px;">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ============================================ --}}
        {{-- 4. REKAPITULASI & TANDA TANGAN --}}
        {{-- ============================================ --}}
        <div style="page-break-inside: avoid;">
            <div class="recap-section">
                <div style="margin-bottom: 12px; font-weight: bold; text-align: left; font-size: 11px; line-height: 1.5; color: #1e3a8a;">
                    RINGKASAN MUTASI REKENING<br>
                    PT Sertifikasi Bermutu Ketenagalistrikan<br>
                    <span style="color: #333;">Periode : {{ $periodLabel }}</span><br>
                    <span style="color: #333;">No. Rekening : 8881003328</span><br>
                    <span style="color: #333;">Mata Uang : IDR</span>
                </div>
                <table class="recap-table">
                    <tr>
                        <td colspan="2" class="recap-header">REKAPITULASI</td>
                    </tr>
                    <tr>
                        <th style="width: 55%;">Uraian</th>
                        <th style="width: 45%;" class="text-right">Jumlah (Rp)</th>
                    </tr>
                    <tr>
                        <td>Saldo Awal Periode</td>
                        <td class="text-right font-bold">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Total Uang Masuk</td>
                        <td class="text-right masuk-text">{{ number_format($totalUangMasuk, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Total Uang Keluar</td>
                        <td class="text-right keluar-text">{{ number_format($totalUangKeluar, 0, ',', '.') }}</td>
                    </tr>
                    <tr style="background-color: #f0f4ff; font-weight: bold;">
                        <td style="font-weight: bold;">Saldo Akhir Periode</td>
                        <td class="text-right" style="font-weight: bold; color: #1e3a8a; font-size: 12px;">{{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            {{-- Signature --}}
            <div class="signature-section" style="margin-top: 0;">
                <div class="signature-box">
                    <div class="signature-date">Palembang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    <div class="signature-label">Mengetahui</div>
                    <div class="signature-role">{{ $signerPosition }}</div>
                    <div class="signature-name">{{ $signerName }}</div>
                </div>
            </div>
            
            <div class="clear"></div>
        </div>
    </main>
</body>
</html>
