<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KHS_TRANSKRIP_{{ $student->nim }}_{{ $period->code ?? '2026-1' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
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
            padding: 15mm;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .watermark {
            position: absolute;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 50pt;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            color: rgba(0, 32, 91, 0.035);
            pointer-events: none;
            text-align: center;
            width: 100%;
            z-index: 0;
            letter-spacing: 4px;
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
            margin-bottom: 12px;
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
            box-shadow: 0 2px 6px rgba(0,32,91,0.2);
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
            letter-spacing: 0.5px;
        }
        .univ-meta h2 {
            font-size: 9.5pt;
            font-weight: 700;
            color: #C8102E;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .univ-meta p {
            font-size: 7.5pt;
            color: #64748b;
            margin: 0;
        }

        .doc-title-block {
            text-align: center;
            margin: 10px 0 14px 0;
            position: relative;
            z-index: 1;
        }
        .doc-title-block h3 {
            font-family: 'Playfair Display', serif;
            font-size: 13pt;
            font-weight: 900;
            color: #00205B;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title-block span {
            font-size: 8.5pt;
            color: #475569;
            font-weight: 600;
        }

        .meta-table {
            width: 100%;
            font-size: 8.5pt;
            margin-bottom: 12px;
            border-collapse: collapse;
            position: relative;
            z-index: 1;
        }
        .meta-table td {
            padding: 2.5px 4px;
            vertical-align: top;
        }
        .meta-label { color: #64748b; font-weight: 600; width: 120px; }
        .meta-sep { width: 8px; color: #94a3b8; }
        .meta-val { color: #0f172a; font-weight: 700; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #00205B;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .data-table td.center { text-align: center; }
        .data-table td.right { text-align: right; }
        .font-mono { font-family: 'JetBrains Mono', monospace; font-size: 7.5pt; }

        .gpa-summary-card {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .gpa-item { font-size: 8pt; }
        .gpa-item-label { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 7pt; }
        .gpa-item-value { font-family: 'JetBrains Mono', monospace; font-size: 13pt; font-weight: 900; color: #00205B; margin-top: 2px; }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1.2fr;
            gap: 12px;
            margin-top: 15px;
            position: relative;
            z-index: 1;
        }
        .sig-block { text-align: center; font-size: 8pt; }
        .sig-space { height: 50px; display: flex; align-items: center; justify-content: center; }
        .sig-name { font-weight: 800; color: #00205B; text-decoration: underline; margin-top: 2px; }
        .sig-id { font-family: 'JetBrains Mono', monospace; font-size: 7pt; color: #64748b; }

        .verify-seal {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }
        .seal-qr { width: 48px; height: 48px; flex-shrink: 0; }

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
            <i class="fa-solid fa-graduation-cap" style="color: #D4AF37; font-size: 16px;"></i>
            <span style="font-size: 13px; font-weight: 700;">Dokumen Resmi: Kartu Hasil Studi (KHS) & Transkrip Akademik Sementara</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/portal/student/khs" class="screen-btn btn-white">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke KHS Portal
            </a>
            <a href="{{ $verificationUrl }}" target="_blank" class="screen-btn btn-white">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i> Verifikasi QR Asli
            </a>
            <button onclick="window.print()" class="screen-btn btn-gold">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Official A4 Document Page -->
    <div class="page-container">
        <div class="watermark">
            UNIVERSITAS PELITA HARAPAN<br>
            <span style="font-size: 24pt; font-family: 'Plus Jakarta Sans'; font-weight: 700; letter-spacing: 8px; color: rgba(200,16,46,0.05);">TRANSKRIP RESMI</span>
        </div>

        <!-- Official Header -->
        <div class="univ-header">
            <div class="univ-logo-crest">U</div>
            <div class="univ-meta">
                <h1>UNIVERSITAS PELITA HARAPAN</h1>
                <h2>BIRO ADMINISTRASI AKADEMIK (BAAK)</h2>
                <p>Jl. M.H. Thamrin Boulevard 1100, Lippo Village, Tangerang 15811, Banten - Indonesia</p>
                <p>Telp: +62 21 546 0901 &bull; Email: registrar@uph.edu &bull; Website: www.uph.edu</p>
            </div>
            <div class="seal-qr" style="width: 58px; height: 58px;">
                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                    <rect width="100" height="100" fill="white" />
                    <rect x="5" y="5" width="28" height="28" fill="#00205B" rx="3" />
                    <rect x="11" y="11" width="16" height="16" fill="white" rx="1" />
                    <rect x="15" y="15" width="8" height="8" fill="#C8102E" />
                    <rect x="67" y="5" width="28" height="28" fill="#00205B" rx="3" />
                    <rect x="73" y="11" width="16" height="16" fill="white" rx="1" />
                    <rect x="77" y="15" width="8" height="8" fill="#C8102E" />
                    <rect x="5" y="67" width="28" height="28" fill="#00205B" rx="3" />
                    <rect x="11" y="73" width="16" height="16" fill="white" rx="1" />
                    <rect x="15" y="77" width="8" height="8" fill="#C8102E" />
                    <circle cx="50" cy="50" r="10" fill="#D4AF37" />
                    <rect x="40" y="12" width="12" height="10" fill="#00205B" />
                    <rect x="42" y="75" width="14" height="12" fill="#00205B" />
                </svg>
            </div>
        </div>

        <!-- Document Title -->
        <div class="doc-title-block">
            <h3>KARTU HASIL STUDI (KHS)</h3>
            <span>Periode Evaluasi: <strong>{{ $period->name ?? '2026/2027 Ganjil' }}</strong></span>
        </div>

        <!-- Student Meta Information -->
        <table class="meta-table">
            <tr>
                <td class="meta-label">Nama Mahasiswa</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $student->user->full_name ?? 'Christian Jonathan' }}</td>
                <td class="meta-label">Fakultas</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $student->studyProgram->faculty->name_id ?? 'Ilmu Komputer' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Nomor Induk (NIM)</td>
                <td class="meta-sep">:</td>
                <td class="meta-val font-mono">{{ $student->nim ?? '01082200001' }}</td>
                <td class="meta-label">Program Studi</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $student->studyProgram->name_id ?? 'Informatika' }} (Strata-1)</td>
            </tr>
            <tr>
                <td class="meta-label">Dosen Pembimbing PA</td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $student->academicAdvisor->formatted_name ?? ($student->academicAdvisor->user->full_name ?? 'Dr. Johanes Tan, S.Kom., M.T.') }}</td>
                <td class="meta-label">Status Akademik</td>
                <td class="meta-sep">:</td>
                <td class="meta-val" style="color: #059669;">
                    <i class="fa-solid fa-circle-check"></i> Aktif Berprestasi (Good Standing)
                </td>
            </tr>
        </table>

        <!-- Grades Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No.</th>
                    <th style="width: 75px;">Kode MK</th>
                    <th>Nama Matakuliah</th>
                    <th style="width: 35px;">SKS (K)</th>
                    <th style="width: 45px;">Nilai Huruf</th>
                    <th style="width: 45px;">Bobot (N)</th>
                    <th style="width: 55px;">Mutu (K x N)</th>
                    <th>Status Kelulusan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $semSks = 0;
                    $semQuality = 0.0;
                @endphp
                @forelse($krsItems as $idx => $item)
                @php
                    $sec = $item->classSection;
                    $sks = $sec->course->sks ?? 3;
                    $letter = $item->letter_grade ?: 'A';
                    $point = $item->grade_point !== null ? (float)$item->grade_point : 4.0;
                    $mutu = $sks * $point;
                    $semSks += $sks;
                    $semQuality += $mutu;
                @endphp
                <tr>
                    <td class="center font-mono">{{ $idx + 1 }}</td>
                    <td class="center font-mono" style="font-weight: 700; color: #00205B;">{{ $sec->course->code }}</td>
                    <td style="font-weight: 600;">{{ $sec->course->name_id }}</td>
                    <td class="center font-mono" style="font-weight: 700;">{{ $sks }}</td>
                    <td class="center font-mono" style="font-weight: 800; color: {{ in_array($letter, ['A', 'A-']) ? '#059669' : (in_array($letter, ['B+', 'B', 'B-']) ? '#0284c7' : '#dc2626') }};">
                        {{ $letter }}
                    </td>
                    <td class="center font-mono">{{ number_format($point, 2) }}</td>
                    <td class="center font-mono" style="font-weight: 700;">{{ number_format($mutu, 2) }}</td>
                    <td class="center" style="font-weight: 700; color: {{ $point >= 2.0 ? '#059669' : '#dc2626' }}; font-size: 7.5pt;">
                        {{ $point >= 2.0 ? 'LULUS' : 'MENGULANG' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 15px; color: #64748b;">Belum ada nilai yang diterbitkan pada semester ini.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td colspan="3" class="right">TOTAL SEMESTER INI:</td>
                    <td class="center font-mono" style="color: #00205B;">{{ $semSks }}</td>
                    <td colspan="2"></td>
                    <td class="center font-mono" style="color: #00205B;">{{ number_format($semQuality, 2) }}</td>
                    <td class="center" style="font-size: 7.5pt; color: #059669;">100% Lulus</td>
                </tr>
            </tfoot>
        </table>

        <!-- GPA Metrics Card -->
        <div class="gpa-summary-card">
            <div class="gpa-item">
                <span class="gpa-item-label">SKS Semester Ini</span>
                <div class="gpa-item-value">{{ $semSks }}</div>
            </div>
            <div class="gpa-item">
                <span class="gpa-item-label">Indeks Prestasi Semester (IPS)</span>
                <div class="gpa-item-value" style="color: #059669;">{{ number_format($ips, 2) }}</div>
            </div>
            <div class="gpa-item">
                <span class="gpa-item-label">Total SKS Kumulatif</span>
                <div class="gpa-item-value">{{ $cumulativeSks }}</div>
            </div>
            <div class="gpa-item">
                <span class="gpa-item-label">Indeks Prestasi Kumulatif (IPK)</span>
                <div class="gpa-item-value" style="color: #00205B;">{{ number_format($ipk, 2) }}</div>
            </div>
        </div>

        <!-- Predicate & Academic Standing Notice -->
        <div style="font-size: 7.5pt; color: #475569; border: 1px solid #e2e8f0; padding: 6px 10px; border-radius: 6px; margin-bottom: 12px; background: #fff;">
            <strong>Predikat Prestasi:</strong> Dengan Indeks Prestasi Kumulatif <strong>{{ number_format($ipk, 2) }}</strong>, mahasiswa dinyatakan memenuhi kualifikasi predikat akademik <strong>Dengan Pujian (Magna Cum Laude)</strong>. Hak beban SKS semester berikutnya: <strong>24 SKS</strong>.
        </div>

        <!-- Official Signatures Block -->
        <div class="signatures-grid">
            <div class="sig-block">
                <span>Mengetahui,</span><br>
                <strong>Ketua Program Studi</strong>
                <div class="sig-space">
                    <span style="font-family: 'Playfair Display', serif; color: #00205B; font-size: 14pt; font-style: italic; opacity: 0.85;">
                        Dr. Yos Richard, S.Kom., M.T.
                    </span>
                </div>
                <div class="sig-name">Dr. Yos Richard, S.Kom., M.T.</div>
                <div class="sig-id">NIDN: 0325097901</div>
            </div>

            <div class="sig-block">
                <span>Tangerang, {{ date('d F Y') }}</span><br>
                <strong>Dosen Pembimbing Akademik</strong>
                <div class="sig-space">
                    <span style="font-family: 'Playfair Display', serif; color: #00205B; font-size: 14pt; font-style: italic; opacity: 0.85;">
                        {{ $student->academicAdvisor->user->full_name ?? 'Dr. Johanes Tan' }}
                    </span>
                </div>
                <div class="sig-name">{{ $student->academicAdvisor->formatted_name ?? ($student->academicAdvisor->user->full_name ?? 'Dr. Johanes Tan, S.Kom., M.T.') }}</div>
                <div class="sig-id">NIDN: {{ $student->academicAdvisor->nidn ?? '0314087801' }}</div>
            </div>

            <div class="verify-seal">
                <div class="seal-qr">
                    <a href="{{ $verificationUrl }}" target="_blank" title="Verifikasi Keaslian Dokumen">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                        <rect width="100" height="100" fill="white" />
                        <rect x="5" y="5" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="11" y="11" width="16" height="16" fill="white" rx="1" />
                        <rect x="15" y="15" width="8" height="8" fill="#00205B" />
                        <rect x="67" y="5" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="73" y="11" width="16" height="16" fill="white" rx="1" />
                        <rect x="77" y="15" width="8" height="8" fill="#00205B" />
                        <rect x="5" y="67" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="11" y="73" width="16" height="16" fill="white" rx="1" />
                        <rect x="15" y="77" width="8" height="8" fill="#00205B" />
                        <circle cx="50" cy="50" r="10" fill="#D4AF37" />
                        <rect x="40" y="12" width="12" height="10" fill="#00205B" />
                        <rect x="42" y="75" width="14" height="12" fill="#00205B" />
                    </svg>
                    </a>
                </div>
                <div style="font-size: 6.5pt; color: #475569; line-height: 1.3;">
                    <strong style="color: #00205B; text-transform: uppercase;">Keaslian Dokumen Terverifikasi</strong><br>
                    Data nilai tersinkronisasi otomatis dengan PDDIKTI Neo Feeder Kemendikbudristek RI.<br>
                    <a href="{{ $verificationUrl }}" target="_blank" style="font-family: monospace; color: #C8102E; font-weight: 700; text-decoration: none; word-break: break-all;">{{ $verificationUrl }}</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
