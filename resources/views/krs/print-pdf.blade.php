<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>KRS_{{ $krsSubmission->student->nim }}_{{ $krsSubmission->academicPeriod->code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-text h1 {
            font-size: 14pt;
            margin: 0;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header-text h2 {
            font-size: 11pt;
            margin: 2px 0;
            font-weight: normal;
        }
        .header-text p {
            font-size: 8pt;
            margin: 0;
            color: #333;
        }
        .doc-title {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .doc-title h3 {
            font-size: 13pt;
            margin: 0;
            font-weight: bold;
            text-decoration: underline;
        }
        .doc-title span {
            font-size: 10pt;
            font-weight: bold;
        }
        .student-info {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        .student-info td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        .table-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .qr-section {
            border: 1px dashed #666;
            padding: 8px 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8.5pt;
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
            font-size: 9.5pt;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
            width: 33.3%;
            padding-bottom: 60px;
        }
        .print-btn-bar {
            background: #00205B;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .print-btn-bar button {
            background: #D4AF37;
            color: #00205B;
            border: none;
            padding: 8px 20px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
        }
        @media print {
            .print-btn-bar { display: none; }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button onclick="window.print()">
            &#128424; Cetak Dokumen KRS (Print / Save as PDF)
        </button>
    </div>

    <!-- Letterhead -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <div style="font-size: 32pt; font-weight: bold; border: 2px solid #000; width: 50px; height: 50px; line-height: 50px; margin: auto;">
                    U
                </div>
            </td>
            <td class="header-text">
                <h1>UNIVERSITAS PELITA HARAPAN</h1>
                <h2>BIRO ADMINISTRASI AKADEMIK (BAAK)</h2>
                <p>Jl. M.H. Thamrin Boulevard 1100, Lippo Village, Tangerang 15811 &bull; Telp: (021) 5460901 &bull; www.uph.edu</p>
            </td>
        </tr>
    </table>

    <div class="doc-title">
        <h3>KARTU RENCANA STUDI (KRS)</h3>
        <span>TAHUN AKADEMIK: {{ strtoupper($krsSubmission->academicPeriod->name) }}</span>
    </div>

    <!-- Student Metadata -->
    <table class="student-info">
        <tr>
            <td style="width: 18%;"><strong>NIM</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 30%;" class="font-mono font-bold">{{ $krsSubmission->student->nim }}</td>
            <td style="width: 20%;"><strong>Fakultas</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 28%;">{{ $krsSubmission->student->studyProgram->faculty->name_id }}</td>
        </tr>
        <tr>
            <td><strong>Nama Mahasiswa</strong></td>
            <td>:</td>
            <td class="font-bold">{{ strtoupper($krsSubmission->student->user->full_name) }}</td>
            <td><strong>Program Studi</strong></td>
            <td>:</td>
            <td>{{ $krsSubmission->student->studyProgram->name_id }} ({{ $krsSubmission->student->studyProgram->degree_level }})</td>
        </tr>
        <tr>
            <td><strong>Semester / SKS Max</strong></td>
            <td>:</td>
            <td>Semester {{ $krsSubmission->student->current_semester }} / Max {{ $krsSubmission->student->max_sks_allowed }} SKS</td>
            <td><strong>Dosen PA</strong></td>
            <td>:</td>
            <td>{{ $krsSubmission->student->academicAdvisor->formatted_name ?? ($krsSubmission->approvedByLecturer->user->full_name ?? '-') }}</td>
        </tr>
        <tr>
            <td><strong>Status KRS</strong></td>
            <td>:</td>
            <td class="font-bold">{{ strtoupper($krsSubmission->status) }} (Disetujui Resmi)</td>
            <td><strong>Tanggal Persetujuan</strong></td>
            <td>:</td>
            <td>{{ $krsSubmission->approved_at ? $krsSubmission->approved_at->format('d F Y H:i') : date('d F Y') }} WIB</td>
        </tr>
    </table>

    <!-- Course Table -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 12%;">KODE MK</th>
                <th style="width: 32%;">NAMA MATA KULIAH</th>
                <th style="width: 6%;">SKS</th>
                <th style="width: 6%;">KELAS</th>
                <th style="width: 18%;">JADWAL KULIAH</th>
                <th style="width: 10%;">RUANG</th>
                <th style="width: 12%;">PARAF</th>
            </tr>
        </thead>
        <tbody>
            @foreach($krsSubmission->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center font-mono font-bold">{{ $item->classSection->course->code }}</td>
                <td>{{ $item->classSection->course->name_id }}</td>
                <td class="text-center font-mono font-bold">{{ $item->classSection->course->sks }}</td>
                <td class="text-center font-mono">{{ $item->classSection->section_code }}</td>
                <td>{{ $item->classSection->day_name }}, {{ substr($item->classSection->start_time, 0, 5) }}-{{ substr($item->classSection->end_time, 0, 5) }}</td>
                <td class="text-center font-mono">{{ $item->classSection->room->code ?? 'TBA' }}</td>
                <td></td>
            </tr>
            @endforeach
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL SKS TERAMBIL :</td>
                <td class="text-center font-mono">{{ $krsSubmission->total_sks }}</td>
                <td colspan="4">Beban Maksimal Diizinkan: {{ $krsSubmission->student->max_sks_allowed }} SKS</td>
            </tr>
        </tbody>
    </table>

    <!-- QR & Security Verification Banner -->
    <div class="qr-section">
        <div>
            <strong>KODE VERIFIKASI DIGITAL (SECURITY TOKEN):</strong><br>
            <span class="font-mono" style="font-size: 10pt; color: #00205B;">{{ $krsSubmission->qr_token ?? 'QRS-KRS-OFFICIAL-VALIDATED' }}</span><br>
            <span style="font-size: 7.5pt; color: #555;">Dokumen ini telah diverifikasi dan disahkan secara elektronik melalui Sistem Informasi Akademik Terpadu (SIAKAD) UPH.</span>
        </div>
        <div style="border: 2px solid #000; padding: 6px; font-weight: bold; text-align: center; font-size: 8pt; background: #fafafa;">
            [QR VERIFIED]<br>SIAKAD UPH
        </div>
    </div>

    <!-- Signatures Tri-partite -->
    <table class="signatures">
        <tr>
            <td>
                Tangerang, {{ date('d F Y') }}<br>
                Mahasiswa Yang Bersangkutan,<br><br><br><br><br>
                <strong><u>{{ strtoupper($krsSubmission->student->user->full_name) }}</u></strong><br>
                NIM: {{ $krsSubmission->student->nim }}
            </td>
            <td>
                Menyetujui,<br>
                Dosen Pembimbing Akademik,<br><br><br><br><br>
                <strong><u>{{ $krsSubmission->student->academicAdvisor->formatted_name ?? ($krsSubmission->approvedByLecturer->user->full_name ?? 'Dosen PA') }}</u></strong><br>
                NIDN/NIP: {{ $krsSubmission->student->academicAdvisor->nidn ?? '1988012019' }}
            </td>
            <td>
                Mengetahui,<br>
                Ketua Program Studi,<br><br><br><br><br>
                <strong><u>{{ $krsSubmission->student->studyProgram->headOfProgram->full_name ?? 'Kaprodi' }}</u></strong><br>
                Program Studi {{ $krsSubmission->student->studyProgram->name_id }}
            </td>
        </tr>
    </table>

</body>
</html>
