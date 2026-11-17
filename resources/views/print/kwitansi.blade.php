<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KWITANSI_LUNAS_{{ $invoice->invoice_number }}_{{ $invoice->student->nim }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 18mm;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 65pt;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            color: rgba(5, 150, 105, 0.04);
            pointer-events: none;
            text-align: center;
            width: 100%;
            z-index: 0;
            letter-spacing: 6px;
        }
        .screen-action-bar {
            width: 210mm;
            margin: 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #00205B;
            padding: 12px 20px;
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,32,91,0.25);
        }
        .screen-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-gold { background: #D4AF37; color: #00205B; }
        .btn-gold:hover { background: #C5A028; }
        .btn-white { background: rgba(255,255,255,0.15); color: white; }
        .btn-white:hover { background: rgba(255,255,255,0.25); }

        .univ-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #00205B;
            padding-bottom: 10px;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .univ-logo-crest {
            width: 58px;
            height: 58px;
            border-radius: 10px;
            background: #00205B;
            color: #D4AF37;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 32px;
            flex-shrink: 0;
        }
        .univ-meta {
            text-align: center;
            flex-grow: 1;
            padding: 0 15px;
        }
        .univ-meta h1 {
            font-family: 'Playfair Display', serif;
            font-size: 15pt;
            font-weight: 900;
            color: #00205B;
            margin: 0;
        }
        .univ-meta h2 {
            font-size: 9.5pt;
            font-weight: 700;
            color: #059669;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .univ-meta p {
            font-size: 7.5pt;
            color: #64748b;
            margin: 0;
        }

        .receipt-stamp {
            display: inline-block;
            padding: 4px 14px;
            border: 2px solid #059669;
            border-radius: 6px;
            color: #059669;
            font-weight: 900;
            font-size: 14pt;
            letter-spacing: 2px;
            text-transform: uppercase;
            transform: rotate(-5deg);
        }

        .doc-title-block {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 16px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .doc-title-block h3 {
            font-family: 'Playfair Display', serif;
            font-size: 14pt;
            font-weight: 900;
            color: #00205B;
            margin: 0;
            text-transform: uppercase;
        }

        .meta-table {
            width: 100%;
            font-size: 9pt;
            margin-bottom: 16px;
            border-collapse: collapse;
            position: relative;
            z-index: 1;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .meta-label { color: #64748b; font-weight: 600; width: 140px; }
        .meta-sep { width: 8px; color: #94a3b8; }
        .meta-val { color: #0f172a; font-weight: 700; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #00205B;
            font-weight: 800;
            text-transform: uppercase;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        .total-banner {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }
        .verify-seal {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .seal-qr { width: 56px; height: 56px; flex-shrink: 0; }

        @media print {
            body { background: #fff; }
            .screen-action-bar { display: none !important; }
            .page-container { box-shadow: none; padding: 0; width: 100%; min-height: auto; }
        }
    </style>
</head>
<body>

    <!-- Screen Action Bar -->
    <div class="screen-action-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-receipt" style="color: #D4AF37; font-size: 16px;"></i>
            <span style="font-size: 13px; font-weight: 700;">Kwitansi Resmi Bukti Lunas Pembayaran UKT / Virtual Account</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/portal/student/finance" class="screen-btn btn-white">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Tagihan
            </a>
            <a href="{{ $verificationUrl }}" target="_blank" class="screen-btn btn-white">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i> Verifikasi QR Asli
            </a>
            <button onclick="window.print()" class="screen-btn btn-gold">
                <i class="fa-solid fa-print"></i> Cetak Kwitansi PDF
            </button>
        </div>
    </div>

    <!-- Official Page Container -->
    <div class="page-container">
        <div class="watermark">
            LUNAS &bull; PAID<br>
            <span style="font-size: 26pt; font-family: 'Plus Jakarta Sans'; font-weight: 700; color: rgba(0,32,91,0.04);">UPH FINANCIAL CLEARANCE</span>
        </div>

        <!-- Official Header -->
        <div class="univ-header">
            <div class="univ-logo-crest">U</div>
            <div class="univ-meta">
                <h1>UNIVERSITAS PELITA HARAPAN</h1>
                <h2>BIRO ADMINISTRASI KEUANGAN (BAUK)</h2>
                <p>Gedung D Lantai 1, Lippo Village, Tangerang 15811, Banten - Indonesia</p>
                <p>Telp: +62 21 546 0901 ext. 1205 &bull; Email: billing.bauk@uph.edu</p>
            </div>
            <div style="text-align: right;">
                <div class="receipt-stamp">LUNAS</div>
            </div>
        </div>

        <!-- Document Title & Invoice Numbers -->
        <div class="doc-title-block">
            <div>
                <h3>KWITANSI PEMBAYARAN RESMI</h3>
                <span style="font-size: 8.5pt; color: #64748b;">No. Kwitansi: <strong class="font-mono text-uph-navy">KWT/{{ date('Ymd') }}/{{ substr(md5($invoice->id), 0, 8) }}</strong></span>
            </div>
            <div style="text-align: right; font-size: 8.5pt;">
                <span>Tanggal Lunas: <strong class="font-mono">{{ $invoice->paid_at ? $invoice->paid_at->format('d F Y, H:i') : date('d F Y, H:i') }} WIB</strong></span><br>
                <span>No. Invoice: <strong class="font-mono">{{ $invoice->invoice_number }}</strong></span>
            </div>
        </div>

        <!-- Student Meta Information -->
        <table class="meta-table">
            <tr>
                <td class="meta-label">Telah Diterima Dari</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $invoice->student->user->full_name ?? 'Jessica Aurelia Tan' }}</td>
                <td class="meta-label">Program Studi</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $invoice->student->studyProgram->name_id ?? 'Informatika' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Nomor Induk (NIM)</td>
                <td class="meta-sep">:</td>
                <td class="meta-val font-mono">{{ $invoice->student->nim ?? '01082200002' }}</td>
                <td class="meta-label">Fakultas</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $invoice->student->studyProgram->faculty->name_id ?? 'Ilmu Komputer' }}</td>
            </tr>
            <tr>
                <td class="meta-label">No. Virtual Account</td>
                <td class="meta-sep">:</td>
                <td class="meta-val font-mono" style="color: #00205B;">{{ $invoice->va_number ?? '899901082200002' }} (BCA VA)</td>
                <td class="meta-label">Periode Akademik</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $invoice->academicPeriod->name ?? '2026/2027 Ganjil' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Metode Pembayaran</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $invoice->payment_method ?? 'Bank Virtual Account Real-Time Settlement' }}</td>
                <td class="meta-label">Status Finansial</td>
                <td class="meta-sep">:</td>
                <td class="meta-val" style="color: #059669;">
                    <i class="fa-solid fa-circle-check"></i> Bebas Keuangan / KRS Unlocked
                </td>
            </tr>
        </table>

        <!-- Itemized Invoice Breakdown Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px; text-align: center;">No.</th>
                    <th>Rincian Biaya Akademik</th>
                    <th style="width: 130px; text-align: center;">Komponen</th>
                    <th style="width: 140px; text-align: right;">Jumlah (IDR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $amt = (float)$invoice->amount;
                    $bpp = round($amt * 0.55);
                    $sksFee = round($amt * 0.35);
                    $labFee = $amt - ($bpp + $sksFee);
                @endphp
                <tr>
                    <td style="text-align: center;" class="font-mono">1</td>
                    <td>
                        <strong style="color: #00205B;">Biaya Pokok Pendidikan (BPP Pokok)</strong><br>
                        <span style="font-size: 7.5pt; color: #64748b;">Biaya fasilitas operasional kampus & lisensi perangkat lunak akademik</span>
                    </td>
                    <td style="text-align: center; font-size: 7.5pt;">Tetap / Semester</td>
                    <td style="text-align: right;" class="font-mono">Rp {{ number_format($bpp, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: center;" class="font-mono">2</td>
                    <td>
                        <strong style="color: #00205B;">Biaya Beban SKS Mata Kuliah</strong><br>
                        <span style="font-size: 7.5pt; color: #64748b;">Paket SKS terdaftar semester reguler (20 SKS x Rp 350.000)</span>
                    </td>
                    <td style="text-align: center; font-size: 7.5pt;">Variabel SKS</td>
                    <td style="text-align: right;" class="font-mono">Rp {{ number_format($sksFee, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: center;" class="font-mono">3</td>
                    <td>
                        <strong style="color: #00205B;">Praktikum Laboratorium Komputasi & Jaringan</strong><br>
                        <span style="font-size: 7.5pt; color: #64748b;">Pemakaian cloud resource, server lab, dan infrastruktur HPC</span>
                    </td>
                    <td style="text-align: center; font-size: 7.5pt;">Lab & Praktikum</td>
                    <td style="text-align: right;" class="font-mono">Rp {{ number_format($labFee, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Total Box -->
        <div class="total-banner">
            <div>
                <span style="font-size: 8pt; color: #166534; font-weight: 700; text-transform: uppercase;">Total Pembayaran Terverifikasi:</span>
                <p style="font-size: 9pt; color: #15803d; font-style: italic; margin: 2px 0 0 0;">
                    Terbilang: # {{ ucwords(terbilang($amt)) }} Rupiah #
                </p>
            </div>
            <div style="text-align: right;">
                <span class="font-mono" style="font-size: 16pt; font-weight: 900; color: #15803d;">
                    Rp {{ number_format($amt, 0, ',', '.') }}
                </span>
            </div>
        </div>

        @php
            function terbilang($n) {
                $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
                if ($n < 12) return " " . $angka[$n];
                elseif ($n < 20) return terbilang($n - 10) . " belas";
                elseif ($n < 100) return terbilang((int)($n / 10)) . " puluh" . terbilang($n % 10);
                elseif ($n < 200) return " seratus" . terbilang($n - 100);
                elseif ($n < 1000) return terbilang((int)($n / 100)) . " ratus" . terbilang($n % 100);
                elseif ($n < 2000) return " seribu" . terbilang($n - 1000);
                elseif ($n < 1000000) return terbilang((int)($n / 1000)) . " ribu" . terbilang($n % 1000);
                elseif ($n < 1000000000) return terbilang((int)($n / 1000000)) . " juta" . terbilang($n % 1000000);
                return "";
            }
        @endphp

        <!-- Legal Notes & Signatures -->
        <div class="signatures-grid">
            <div class="verify-seal">
                <div class="seal-qr">
                    <a href="{{ $verificationUrl }}" target="_blank" title="Verifikasi Keaslian Dokumen">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                        <rect width="100" height="100" fill="white" />
                        <rect x="5" y="5" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="11" y="11" width="16" height="16" fill="white" rx="1" />
                        <rect x="15" y="15" width="8" height="8" fill="#059669" />
                        <rect x="67" y="5" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="73" y="11" width="16" height="16" fill="white" rx="1" />
                        <rect x="77" y="15" width="8" height="8" fill="#059669" />
                        <rect x="5" y="67" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="11" y="73" width="16" height="16" fill="white" rx="1" />
                        <rect x="15" y="77" width="8" height="8" fill="#059669" />
                        <circle cx="50" cy="50" r="10" fill="#D4AF37" />
                        <rect x="40" y="12" width="12" height="10" fill="#00205B" />
                        <rect x="42" y="75" width="14" height="12" fill="#00205B" />
                    </svg>
                    </a>
                </div>
                <div style="font-size: 7pt; color: #475569; line-height: 1.3;">
                    <strong style="color: #00205B; text-transform: uppercase;">Verifikasi Elektronik BAUK UPH</strong><br>
                    Pembayaran ini telah teregistrasi di Core Banking Settlement System dan sistem SIAKAD.<br>
                    <a href="{{ $verificationUrl }}" target="_blank" style="font-family: monospace; color: #059669; font-weight: 700; text-decoration: none; word-break: break-all;">{{ $verificationUrl }}</a>
                </div>
            </div>

            <div style="text-align: center; font-size: 8.5pt;">
                <span>Tangerang, {{ date('d F Y') }}</span><br>
                <strong>Biro Administrasi Keuangan (BAUK)</strong>
                <div style="height: 50px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-family: 'Playfair Display', serif; color: #00205B; font-size: 15pt; font-style: italic; opacity: 0.85;">
                        Maria Fransiska, S.E., M.M.
                    </span>
                </div>
                <div style="font-weight: 800; color: #00205B; text-decoration: underline;">Maria Fransiska, S.E., M.M.</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 7.5pt; color: #64748b;">NIP: 2012088501 &bull; Kepala Bagian Kasir & Penagihan</div>
            </div>
        </div>
    </div>

</body>
</html>
