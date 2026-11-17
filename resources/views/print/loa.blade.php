<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LETTER_OF_ACCEPTANCE_{{ $applicant->registration_number }}_{{ $applicant->user->full_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
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
            line-height: 1.5;
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
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 55pt;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            color: rgba(212, 175, 55, 0.04);
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
            padding-bottom: 12px;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }
        .univ-logo-crest {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #00205B;
            color: #D4AF37;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 34px;
            flex-shrink: 0;
        }
        .univ-meta {
            text-align: center;
            flex-grow: 1;
            padding: 0 15px;
        }
        .univ-meta h1 {
            font-family: 'Playfair Display', serif;
            font-size: 16pt;
            font-weight: 900;
            color: #00205B;
            margin: 0;
        }
        .univ-meta h2 {
            font-size: 10pt;
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
            margin-bottom: 18px;
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
            letter-spacing: 1px;
        }
        .doc-title-block p {
            font-size: 8.5pt;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .recipient-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 9pt;
            position: relative;
            z-index: 1;
        }

        .decision-badge-card {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            border-radius: 10px;
            padding: 14px;
            margin: 14px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        .meta-list {
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 8.5pt;
        }
        .meta-list li {
            padding: 3px 0;
            display: flex;
        }
        .meta-list span.k { width: 180px; color: #64748b; font-weight: 600; }
        .meta-list span.s { width: 10px; color: #94a3b8; }
        .meta-list span.v { font-weight: 700; color: #00205B; }

        .letter-body {
            font-size: 9pt;
            line-height: 1.55;
            color: #334155;
            text-align: justify;
            margin-bottom: 18px;
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
        .font-mono { font-family: 'JetBrains Mono', monospace; }

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
            <i class="fa-solid fa-envelope-open-text" style="color: #D4AF37; font-size: 16px;"></i>
            <span style="font-size: 13px; font-weight: 700;">Official Letter of Acceptance (LoA) - Calon Mahasiswa Baru UPH</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/portal/pmb/dashboard" class="screen-btn btn-white">
                <i class="fa-solid fa-arrow-left"></i> Dasbor PMB
            </a>
            <a href="{{ $verificationUrl }}" target="_blank" class="screen-btn btn-white">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i> Verifikasi QR Asli
            </a>
            <button onclick="window.print()" class="screen-btn btn-gold">
                <i class="fa-solid fa-print"></i> Cetak Dokumen LoA
            </button>
        </div>
    </div>

    <!-- Official Document Container -->
    <div class="page-container">
        <div class="watermark">
            OFFICIAL ADMISSION<br>
            <span style="font-size: 22pt; font-family: 'Plus Jakarta Sans'; font-weight: 700; color: rgba(0,32,91,0.04);">UNIVERSITAS PELITA HARAPAN</span>
        </div>

        <!-- Official Header -->
        <div class="univ-header">
            <div class="univ-logo-crest">U</div>
            <div class="univ-meta">
                <h1>UNIVERSITAS PELITA HARAPAN</h1>
                <h2>DIREKTORAT ADMISI & PENERIMAAN MAHASISWA BARU (PMB)</h2>
                <p>Jl. M.H. Thamrin Boulevard 1100, Lippo Village, Tangerang 15811, Banten - Indonesia</p>
                <p>Telp: +62 21 546 0901 &bull; Email: admissions@uph.edu &bull; Website: join.uph.edu</p>
            </div>
            <div class="seal-qr">
                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                    <rect width="100" height="100" fill="white" />
                    <rect x="5" y="5" width="28" height="28" fill="#00205B" rx="3" />
                    <rect x="11" y="11" width="16" height="16" fill="white" rx="1" />
                    <rect x="15" y="15" width="8" height="8" fill="#D4AF37" />
                    <rect x="67" y="5" width="28" height="28" fill="#00205B" rx="3" />
                    <rect x="73" y="11" width="16" height="16" fill="white" rx="1" />
                    <rect x="77" y="15" width="8" height="8" fill="#D4AF37" />
                    <rect x="5" y="67" width="28" height="28" fill="#00205B" rx="3" />
                    <rect x="11" y="73" width="16" height="16" fill="white" rx="1" />
                    <rect x="15" y="77" width="8" height="8" fill="#D4AF37" />
                    <circle cx="50" cy="50" r="10" fill="#00205B" />
                    <rect x="40" y="12" width="12" height="10" fill="#00205B" />
                    <rect x="42" y="75" width="14" height="12" fill="#00205B" />
                </svg>
            </div>
        </div>

        <!-- Document Title -->
        <div class="doc-title-block">
            <h3>LETTER OF ACCEPTANCE (SURAT PENERIMAAN)</h3>
            <p>Nomor Surat: <strong>UPH/PMB/LOA/{{ date('Y') }}/{{ $applicant->registration_number ?? '202600109' }}</strong></p>
        </div>

        <!-- Recipient Information -->
        <div class="recipient-box">
            <span style="font-size: 8pt; color: #64748b; font-weight: 700; text-transform: uppercase;">Kepada Yth. Calon Mahasiswa Baru:</span>
            <div style="font-size: 11pt; font-weight: 800; color: #00205B; margin-top: 2px;">
                {{ $applicant->user->full_name ?? 'Calon Mahasiswa Unggul' }}
            </div>
            <div style="font-size: 8pt; color: #475569;">
                No. Registrasi: <span class="font-mono font-bold">{{ $applicant->registration_number ?? 'REG-2026-00109' }}</span> &bull; 
                Asal Sekolah / Institusi: <span class="font-bold">SMA Pelita Harapan Lippo Village</span>
            </div>
        </div>

        <!-- Letter Body -->
        <div class="letter-body">
            <p style="margin-top: 0;">
                Dengan penuh rasa syukur dan sukacita, Pimpinan <strong>Universitas Pelita Harapan (UPH)</strong> menyampaikan selamat atas keberhasilan Saudara/i dalam menempuh seluruh rangkaian seleksi Computer-Based Testing (CBT) dan evaluasi portofolio akademik Penerimaan Mahasiswa Baru Tahun Akademik <strong>2026/2027</strong>.
            </p>
            <p>
                Berdasarkan hasil evaluasi Dewan Seleksi Akademik, Saudara/i secara resmi dinyatakan:
            </p>

            <!-- Decision Box -->
            <div class="decision-badge-card">
                <div>
                    <span style="display: inline-block; padding: 4px 12px; background: #059669; color: white; border-radius: 6px; font-weight: 800; font-size: 11pt; letter-spacing: 1px; text-transform: uppercase;">
                        DITERIMA (ACCEPTED)
                    </span>
                    <ul class="meta-list" style="margin-top: 10px;">
                        <li><span class="k">Program Studi</span><span class="s">:</span><span class="v">{{ $applicant->targetStudyProgram->name_id ?? 'Informatika' }} (Strata-1)</span></li>
                        <li><span class="k">Fakultas</span><span class="s">:</span><span class="v">{{ $applicant->targetStudyProgram->faculty->name_id ?? 'Ilmu Komputer' }}</span></li>
                        <li><span class="k">Jalur Pendaftaran</span><span class="s">:</span><span class="v">{{ $applicant->admissionWave->name ?? 'Gelombang 1 - Jalur Prestasi CBT' }}</span></li>
                        <li><span class="k">Skor Ujian CBT</span><span class="s">:</span><span class="v font-mono">{{ number_format($applicant->cbt_score ?? 88.5, 1) }} / 100.0 (Sangat Memuaskan)</span></li>
                        <li><span class="k">Kategori Beasiswa</span><span class="s">:</span><span class="v" style="color: #059669;">{{ $applicant->scholarship_tier ?? 'Tier 1 (Beasiswa Prestasi Penuh 100%)' }}</span></li>
                    </ul>
                </div>
                <div style="text-align: right; border-left: 2px dashed #bfdbfe; padding-left: 16px;">
                    <span style="font-size: 7.5pt; color: #64748b; font-weight: 700; text-transform: uppercase;">NIM Cadangan:</span>
                    <div class="font-mono" style="font-size: 12pt; font-weight: 900; color: #00205B;">
                        {{ $applicant->generated_nim ?? '01082200099' }}
                    </div>
                    <span style="font-size: 7pt; color: #059669; font-weight: 700;">Kampus Lippo Village</span>
                </div>
            </div>

            <p>
                Sebagai calon civitas akademika UPH, Saudara/i diharapkan mengonfirmasi penerimaan ini serta menyelesaikan proses registrasi ulang administratif melalui portal PMB sebelum tanggal <strong>{{ date('d F Y', strtotime('+14 days')) }}</strong>. Informasi mengenai orientasi studi (UPH Festival), jadwal tes kesehatan, dan pengambilan kartu mahasiswa dapat diakses melalui portal resmi.
            </p>
            <p style="margin-bottom: 0;">
                Selamat bergabung menjadi bagian dari komunitas pembelajar UPH. Kiranya Tuhan senantiasa memberkati langkah studi Saudara/i dalam meraih kepemimpinan berkarakter dan berdampak bagi bangsa.
            </p>
        </div>

        <!-- Official Signatures Block -->
        <div class="signatures-grid">
            <div class="verify-seal">
                <div class="seal-qr">
                    <a href="{{ $verificationUrl }}" target="_blank" title="Verifikasi Keaslian Dokumen">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                        <rect width="100" height="100" fill="white" />
                        <rect x="5" y="5" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="11" y="11" width="16" height="16" fill="white" rx="1" />
                        <rect x="15" y="15" width="8" height="8" fill="#D4AF37" />
                        <rect x="67" y="5" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="73" y="11" width="16" height="16" fill="white" rx="1" />
                        <rect x="77" y="15" width="8" height="8" fill="#D4AF37" />
                        <rect x="5" y="67" width="28" height="28" fill="#00205B" rx="3" />
                        <rect x="11" y="73" width="16" height="16" fill="white" rx="1" />
                        <rect x="15" y="77" width="8" height="8" fill="#D4AF37" />
                        <circle cx="50" cy="50" r="10" fill="#00205B" />
                        <rect x="40" y="12" width="12" height="10" fill="#00205B" />
                        <rect x="42" y="75" width="14" height="12" fill="#00205B" />
                    </svg>
                    </a>
                </div>
                <div style="font-size: 6.5pt; color: #475569; line-height: 1.3;">
                    <strong style="color: #00205B; text-transform: uppercase;">Digital Admission Verification</strong><br>
                    Dokumen resmi diterbitkan otomatis oleh Sistem PMB Terpadu Universitas Pelita Harapan.<br>
                    <a href="{{ $verificationUrl }}" target="_blank" style="font-family: monospace; color: #C8102E; font-weight: 700; text-decoration: none; word-break: break-all;">{{ $verificationUrl }}</a>
                </div>
            </div>

            <div style="text-align: center; font-size: 8.5pt;">
                <span>Tangerang, {{ date('d F Y') }}</span><br>
                <strong>Direktur Admisi & Pemasaran Mahasiswa</strong>
                <div style="height: 50px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-family: 'Playfair Display', serif; color: #00205B; font-size: 15pt; font-style: italic; opacity: 0.85;">
                        Dr. Stephen Liando, M.B.A.
                    </span>
                </div>
                <div style="font-weight: 800; color: #00205B; text-decoration: underline;">Dr. Stephen Liando, M.B.A.</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 7.5pt; color: #64748b;">NIDN: 0318047501 &bull; Direktur PMB UPH</div>
            </div>
        </div>
    </div>

</body>
</html>
