<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKPI_{{ $clearance->student->nim }}_{{ $clearance->national_pin_number ?? 'PIN-UPH' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
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
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
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
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 40pt;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            color: rgba(0, 32, 91, 0.03);
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
            border-bottom: 2px solid #00205B;
            padding-bottom: 8px;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }
        .univ-logo-crest {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            background: #00205B;
            color: #D4AF37;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 30px;
            flex-shrink: 0;
        }
        .univ-meta {
            text-align: center;
            flex-grow: 1;
            padding: 0 12px;
        }
        .univ-meta h1 {
            font-family: 'Playfair Display', serif;
            font-size: 14pt;
            font-weight: 900;
            color: #00205B;
            margin: 0;
        }
        .univ-meta h2 {
            font-size: 8.5pt;
            font-weight: 700;
            color: #C8102E;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .univ-meta p { font-size: 7pt; color: #64748b; margin: 0; }

        .doc-title-block {
            text-align: center;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }
        .doc-title-block h3 {
            font-family: 'Playfair Display', serif;
            font-size: 11pt;
            font-weight: 900;
            color: #00205B;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title-block p {
            font-size: 7.5pt;
            color: #64748b;
            font-style: italic;
            margin: 1px 0 0 0;
        }

        .section-header {
            background: #00205B;
            color: white;
            padding: 3px 8px;
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 8px 0 4px 0;
            border-radius: 3px;
        }
        .section-header span {
            font-size: 7pt;
            font-weight: normal;
            font-style: italic;
            color: #cbd5e1;
        }

        .meta-table {
            width: 100%;
            font-size: 7.5pt;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .meta-table td { padding: 2px 4px; vertical-align: top; }
        .meta-label { color: #64748b; font-weight: 600; width: 140px; }
        .meta-sep { width: 8px; color: #94a3b8; }
        .meta-val { color: #0f172a; font-weight: 700; }
        .en-italic { font-style: italic; color: #64748b; font-weight: normal; }

        .cpl-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 6px;
            font-size: 7.5pt;
            background: #fafafa;
        }
        .cpl-title { font-weight: 800; color: #00205B; font-size: 8pt; margin-bottom: 2px; }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
            font-size: 8pt;
        }
        .verify-seal {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .seal-qr { width: 50px; height: 50px; flex-shrink: 0; }
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
            <i class="fa-solid fa-certificate" style="color: #D4AF37; font-size: 16px;"></i>
            <span style="font-size: 13px; font-weight: 700;">Surat Keterangan Pendamping Ijazah (SKPI / Diploma Supplement) - Bilingual</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/portal/student/yudisium" class="screen-btn btn-white">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Yudisium
            </a>
            <a href="{{ $verificationUrl }}" target="_blank" class="screen-btn btn-white">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i> Verifikasi QR Asli
            </a>
            <button onclick="window.print()" class="screen-btn btn-gold">
                <i class="fa-solid fa-print"></i> Cetak SKPI Resmi
            </button>
        </div>
    </div>

    <!-- Official Page Container -->
    <div class="page-container">
        <div class="watermark">
            SURAT KETERANGAN PENDAMPING IJAZAH<br>
            <span style="font-size: 20pt; font-family: 'Plus Jakarta Sans';">DIPLOMA SUPPLEMENT &bull; UPH</span>
        </div>

        <!-- Official Header -->
        <div class="univ-header">
            <div class="univ-logo-crest">U</div>
            <div class="univ-meta">
                <h1>UNIVERSITAS PELITA HARAPAN</h1>
                <h2>SURAT KETERANGAN PENDAMPING IJAZAH (DIPLOMA SUPPLEMENT)</h2>
                <p>Permendikbudristek No. 81 Tahun 2014 &bull; Kerangka Kualifikasi Nasional Indonesia (KKNI Level 6)</p>
                <p>Website: www.uph.edu &bull; Email: registrar@uph.edu</p>
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

        <div class="doc-title-block">
            <h3>SURAT KETERANGAN PENDAMPING IJAZAH</h3>
            <p>DIPLOMA SUPPLEMENT &bull; Nomor SKPI: <strong class="font-mono">{{ $clearance->national_pin_number ?? 'PIN-UPH/2026/IF/01082200001' }}</strong></p>
        </div>

        <!-- 1. IDENTITAS PEMILIK SKPI -->
        <div class="section-header">1. Informasi Pemegang SKPI <span>/ Information Identifying the Qualification Holder</span></div>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Nama Lengkap <span class="en-italic">/ Full Name</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $clearance->student->user->full_name ?? 'Christian Jonathan' }}</td>
                <td class="meta-label">Gelar Yang Diberikan <span class="en-italic">/ Title Awarded</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">Sarjana Komputer (S.Kom.) <span class="en-italic">/ Bachelor of Computer Science</span></td>
            </tr>
            <tr>
                <td class="meta-label">Nomor Induk (NIM) <span class="en-italic">/ Student ID</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val font-mono">{{ $clearance->student->nim ?? '01082200001' }}</td>
                <td class="meta-label">Nomor Ijazah Nasional (PIN)</td>
                <td class="meta-sep">:</td>
                <td class="meta-val font-mono" style="color: #00205B;">{{ $clearance->national_pin_number ?? 'PIN-UPH/2026/IF/01082200001' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Tempat & Tgl Lahir <span class="en-italic">/ Date of Birth</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">Jakarta, 12 Agustus 2004</td>
                <td class="meta-label">Tanggal Kelulusan <span class="en-italic">/ Graduation Date</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $clearance->graduation_date ? $clearance->graduation_date->format('d F Y') : date('d F Y') }}</td>
            </tr>
        </table>

        <!-- 2. IDENTITAS INSTITUSI -->
        <div class="section-header">2. Informasi Institusi Pendidikan <span>/ Information Identifying the Awarding Institution</span></div>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Institusi <span class="en-italic">/ Higher Education</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">Universitas Pelita Harapan (UPH)</td>
                <td class="meta-label">Akreditasi Institusi <span class="en-italic">/ Accreditation</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">UNGGUL (BAN-PT No. 129/SK/BAN-PT/Akred/PT/III/2021)</td>
            </tr>
            <tr>
                <td class="meta-label">Program Studi <span class="en-italic">/ Study Program</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">{{ $clearance->student->studyProgram->name_id ?? 'Informatika' }} <span class="en-italic">/ Computer Science</span></td>
                <td class="meta-label">Jenjang KKNI <span class="en-italic">/ National Qualification</span></td>
                <td class="meta-sep">:</td>
                <td class="meta-val">Jenjang 6 (Level 6 KKNI / Bachelor Degree)</td>
            </tr>
        </table>

        <!-- 3. CAPAIAN PEMBELAJARAN LULUSAN (OBE CPL) -->
        <div class="section-header">3. Capaian Pembelajaran Lulusan (CPL) <span>/ Learning Outcomes (KKNI Level 6)</span></div>
        
        <div class="cpl-box">
            <div class="cpl-title">A. Kemampuan Kerja Khusus / Professional Work Capabilities</div>
            <p style="margin: 0;">
                Mampu merancang, mengimplementasikan, dan mengevaluasi solusi perangkat lunak berbasis cloud, arsitektur microservices, dan sistem kecerdasan artifisial terdistribusi dengan menerapkan prinsip rekayasa perangkat lunak modern serta standar keamanan siber internasional.
            </p>
            <p style="margin: 2px 0 0 0; color: #64748b; font-style: italic;">
                Able to design, implement, and evaluate cloud-native enterprise software solutions, microservices architectures, and distributed AI systems applying rigorous modern software engineering principles and international cyber security standards.
            </p>
        </div>

        <div class="cpl-box">
            <div class="cpl-title">B. Penguasaan Pengetahuan Ilmiah / Knowledge Domain Mastery</div>
            <p style="margin: 0;">
                Menguasai konsep teoritis ilmu komputasi, algoritma tingkat lanjut, struktur data, matematika diskrit, rekayasa data skala besar (Big Data Analytics), dan manajemen basis data relasional serta non-relasional berkinerja tinggi.
            </p>
            <p style="margin: 2px 0 0 0; color: #64748b; font-style: italic;">
                Mastering theoretical computer science principles, advanced algorithms, discrete mathematics, large-scale data engineering, and high-performance database management systems.
            </p>
        </div>

        <div class="cpl-box">
            <div class="cpl-title">C. Sikap & Kepemimpinan Etis / Attitudes & Ethical Leadership</div>
            <p style="margin: 0;">
                Menunjukkan integritas moral berlandaskan iman Kristiani, profesionalisme akademik, etika profesi komputasi, dan tanggung jawab sosial dalam memimpin transformasi digital yang berkelanjutan bagi kemaslahatan masyarakat.
            </p>
            <p style="margin: 2px 0 0 0; color: #64748b; font-style: italic;">
                Demonstrating moral integrity, Christian worldview leadership, professional computing ethics, and social responsibility in spearheading sustainable digital transformation.
            </p>
        </div>

        <!-- 4. REKAM PRESTASI KO-KURIKULER & SERTIFIKASI -->
        <div class="section-header">4. Prestasi & Sertifikasi Kompetensi <span>/ Certifications & Extracurricular Accomplishments</span></div>
        <table class="meta-table" style="margin-bottom: 4px;">
            <tr>
                <td style="width: 25px;" class="font-mono">1.</td>
                <td><strong>AWS Certified Solutions Architect – Associate</strong> &bull; Amazon Web Services (Validation ID: AWS-091823)</td>
            </tr>
            <tr>
                <td class="font-mono">2.</td>
                <td><strong>Juara 1 National Collegiate Hackathon 2025</strong> &bull; Kategori Artificial Intelligence & FinTech Platform</td>
            </tr>
            <tr>
                <td class="font-mono">3.</td>
                <td><strong>Publikasi Ilmiah Terindeks Scopus Q3</strong>: <em>"Optimizing Enterprise Monolith Concurrency with Redis Cluster"</em> (IEEE Access)</td>
            </tr>
        </table>

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
                <div style="font-size: 6pt; color: #475569; line-height: 1.25;">
                    <strong style="color: #00205B; text-transform: uppercase;">Pangkalan Data Pendidikan Tinggi (PDDIKTI)</strong><br>
                    Dokumen SKPI ini sah terverifikasi secara nasional dengan nomor PIN Ijazah terdaftar.<br>
                    <a href="{{ $verificationUrl }}" target="_blank" style="font-family: monospace; color: #00205B; font-weight: 700; text-decoration: none; word-break: break-all;">{{ $verificationUrl }}</a>
                </div>
            </div>

            <div style="text-align: center;">
                <span>Tangerang, {{ date('d F Y') }}</span><br>
                <strong>Dekan Fakultas Ilmu Komputer</strong>
                <div style="height: 40px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-family: 'Playfair Display', serif; color: #00205B; font-size: 13pt; font-style: italic; opacity: 0.85;">
                        Prof. Dr. Ir. Samuel H. Gunawan, M.Sc.
                    </span>
                </div>
                <div style="font-weight: 800; color: #00205B; text-decoration: underline;">Prof. Dr. Ir. Samuel H. Gunawan, M.Sc.</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 7pt; color: #64748b;">NIP: 197405121999031002</div>
            </div>
        </div>
    </div>

</body>
</html>
