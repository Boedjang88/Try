@extends('layouts.portal')

@section('title', 'Transkrip Akademik Kumulatif - UPH')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar (Hidden during print) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/khs{{ request('student_id') ? '?student_id=' . request('student_id') : '' }}" class="text-xs font-bold text-uph-navy hover:underline">
                    &larr; Kembali ke KHS Semester
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Biro Administrasi Akademik & Kemahasiswaan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Transkrip Akademik Kumulatif</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Rekapitulasi resmi seluruh matakuliah yang telah lulus dan perhitungan IPK berstandar nasional PDDIKTI.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Dokumen Resmi (PDF)
            </button>
        </div>
    </div>

    <!-- Official Document Canvas -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-12 print:p-0 print:border-none print:shadow-none print:rounded-none max-w-5xl mx-auto text-slate-900">
        
        <!-- Institutional Header / Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-6 mb-8 text-center relative">
            <div class="flex items-center justify-center gap-4 mb-2">
                <div class="w-14 h-14 rounded-full bg-uph-navy text-uph-gold flex items-center justify-center text-2xl font-black font-display shrink-0 border-2 border-uph-gold">
                    UPH
                </div>
                <div class="text-left">
                    <h2 class="text-xl sm:text-2xl font-display font-black text-uph-navy tracking-tight uppercase">
                        UNIVERSITAS PELITA HARAPAN
                    </h2>
                    <p class="text-xs font-bold tracking-widest text-slate-600 uppercase">
                        BIRO ADMINISTRASI AKADEMIK & SISTEM PENJAMINAN MUTU INTERNAL
                    </p>
                    <p class="text-[11px] text-slate-400">
                        MH Thamrin Boulevard 1100, Lippo Village, Tangerang 15811 &bull; siakad.uph.edu
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200">
                <h3 class="text-base sm:text-lg font-bold font-display uppercase tracking-wider text-slate-900">
                    TRANSKRIP AKADEMIK SEMENTARA
                </h3>
                <span class="text-xs font-mono text-slate-500">
                    OFFICIAL CUMULATIVE ACADEMIC TRANSCRIPT
                </span>
            </div>
        </div>

        <!-- Student Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-y-3 gap-x-6 text-xs border border-slate-200 rounded-xl p-4 mb-8 bg-slate-50/50 print:bg-transparent">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Mahasiswa</span>
                <span class="font-bold text-slate-900 text-sm block mt-0.5">{{ $student->user->full_name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Induk Mahasiswa (NIM)</span>
                <span class="font-mono font-bold text-slate-900 text-sm block mt-0.5">{{ $student->nim }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Program Studi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $student->studyProgram->name_id }}</span>
                <span class="text-slate-500 text-[11px]">Jenjang Strata-1 (S1)</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Fakultas</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $student->studyProgram->faculty->name_id ?? 'Fakultas Ilmu Komputer' }}</span>
                <span class="text-slate-500 text-[11px]">Angkatan: {{ $student->enrollment_year }}</span>
            </div>
        </div>

        <!-- Progress Towards 144 SKS (Graduation Bar) -->
        <div class="mb-8 p-5 rounded-xl border border-slate-200 bg-slate-50 print:hidden">
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="font-bold text-slate-700">Progres Capaian Kelulusan Sarjana (S1):</span>
                <span class="font-mono font-bold text-uph-navy">
                    {{ $totalSksLulus }} / {{ $targetSks }} SKS ({{ $sksProgressPercent }}%)
                </span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-uph-navy to-uph-crimson h-3 rounded-full transition-all duration-500" 
                     style="width: {{ $sksProgressPercent }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                <span>Kurikulum Berbasis Capaian (OBE Standard)</span>
                <span>Sisa Beban Studi: <strong class="text-slate-800">{{ max(0, $targetSks - $totalSksLulus) }} SKS</strong></span>
            </div>
        </div>

        <!-- Transcript Courses Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-900 bg-slate-100 print:bg-transparent text-slate-900 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3 text-center w-10">No</th>
                        <th class="py-3 px-3 w-28">Kode</th>
                        <th class="py-3 px-4">Nama Matakuliah / Course Name</th>
                        <th class="py-3 px-3 text-center w-14">SKS</th>
                        <th class="py-3 px-3 text-center w-16">Nilai</th>
                        <th class="py-3 px-3 text-center w-16">Bobot</th>
                        <th class="py-3 px-3 text-center w-16">Mutu</th>
                        <th class="py-3 px-4 text-center w-36">Periode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                    @forelse($transcriptCourses as $idx => $c)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $c['code'] }}</td>
                        <td class="py-2.5 px-4">
                            <span class="font-bold text-slate-900 block">{{ $c['name_id'] }}</span>
                            <span class="text-[10px] text-slate-400 italic">{{ $c['name_en'] }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold">{{ $c['sks'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $c['letter_grade'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono">{{ number_format($c['grade_point'], 2) }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-bold">{{ number_format($c['quality_point'], 2) }}</td>
                        <td class="py-2.5 px-4 text-center text-slate-500 text-[11px]">{{ $c['academic_period'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 italic">
                            Belum ada riwayat matakuliah yang telah lulus dalam transkrip.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-y-2 border-slate-900 font-bold bg-slate-50 print:bg-transparent">
                    <tr>
                        <td colspan="3" class="py-3 px-4 uppercase text-slate-900">Total Kumulatif:</td>
                        <td class="py-3 px-3 text-center font-mono text-slate-900">{{ $totalSksLulus }}</td>
                        <td colspan="2" class="py-3 px-3 text-right text-slate-600">Total Mutu:</td>
                        <td class="py-3 px-3 text-center font-mono text-uph-navy font-black">{{ number_format($totalMutuPoints, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Academic Summary Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border border-slate-200 rounded-xl p-5 mb-10 bg-slate-50/50 print:bg-transparent text-xs">
            <div>
                <span class="text-slate-500 uppercase font-bold text-[10px] block">Indeks Prestasi Kumulatif (IPK)</span>
                <span class="text-2xl font-black text-uph-navy font-mono mt-0.5 block">
                    {{ number_format($ipk, 2) }} <span class="text-xs text-slate-400">/ 4.00</span>
                </span>
            </div>
            <div>
                <span class="text-slate-500 uppercase font-bold text-[10px] block">Total SKS Diperoleh</span>
                <span class="text-2xl font-black text-slate-900 font-mono mt-0.5 block">
                    {{ $totalSksLulus }} <span class="text-xs text-slate-400">SKS</span>
                </span>
            </div>
            <div>
                <span class="text-slate-500 uppercase font-bold text-[10px] block">Predikat Kelulusan Sementara</span>
                <span class="text-base font-bold text-emerald-700 mt-1 block">
                    {{ $predicate }}
                </span>
            </div>
        </div>

        <!-- Official Legal Signatures & Digital Verification Stamp -->
        <div class="grid grid-cols-2 gap-8 text-xs pt-6 border-t border-slate-200">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-2xl shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <span class="font-bold text-slate-800 block text-xs">Verifikasi Keaslian Dokumen:</span>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">
                        Dokumen ini diterbitkan secara elektronik oleh Sistem Informasi Akademik UPH dan sah tanpa tanda tangan basah berdasarkan UU ITE No. 11/2008.
                    </p>
                    <span class="font-mono text-[10px] text-slate-400 mt-1 block">
                        SHA256: {{ hash('sha256', $student->nim . '|' . $totalSksLulus . '|' . $ipk) }}
                    </span>
                </div>
            </div>

            <div class="text-right">
                <p class="text-slate-500">Tangerang, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-800 mt-0.5">Universitas Pelita Harapan</p>
                <p class="text-slate-600 text-[11px]">Kepala Biro Administrasi Akademik (BAAK)</p>
                <div class="h-16 flex items-end justify-end">
                    <span class="text-[11px] text-slate-400 italic">[Ditandatangani Secara Elektronik]</span>
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1 inline-block min-w-[200px]">
                    Dr. Ir. Johanes Tan, M.Sc., Ph.D.
                </p>
                <p class="text-[10px] text-slate-400 font-mono">NIP: 197804122005011002</p>
            </div>
        </div>

    </div>
</div>
@endsection
