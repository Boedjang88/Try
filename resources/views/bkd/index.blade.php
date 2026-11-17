@extends('layouts.portal')

@section('title', 'Beban Kinerja Dosen (BKD) - UPH')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    Lembaga Layanan Pendidikan Tinggi (LLDIKTI)
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Sertifikasi Dosen (Serdos) Standard</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Beban Kinerja Dosen (BKD) & Tri Dharma</h1>
            <p class="text-xs text-slate-500 mt-1">
                Rekapitulasi beban kerja pengajaran, penelitian, pengabdian kepada masyarakat, dan kegiatan penunjang akademik.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                <i class="fa-solid fa-calendar-check text-uph-gold"></i>
                Periode {{ $activePeriod->name ?? 'Semester Aktif' }}
            </span>
            <button onclick="window.print()" class="p-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition-colors" title="Cetak Laporan BKD">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>
    </div>

    <!-- Lecturer Profile Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Nama Dosen</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $lecturer->formatted_name ?? ($lecturer->user->full_name ?? '-') }}</span>
                <span class="text-slate-500 font-mono">NIDN: {{ $lecturer->nidn }}</span>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Program Studi & Fakultas</span>
                <span class="font-bold text-slate-800 mt-0.5 block">{{ $lecturer->studyProgram->name_id }}</span>
                <span class="text-slate-500">{{ $lecturer->studyProgram->faculty->name_id ?? 'UPH' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Jabatan Fungsional</span>
                <span class="font-bold text-slate-800 mt-0.5 block">Lektor Kepala (Senior Lecturer)</span>
                <span class="text-slate-500 font-mono">Status: Dosen Tetap</span>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Status Sertifikasi</span>
                <span class="text-emerald-700 font-bold flex items-center gap-1 mt-0.5">
                    <i class="fa-solid fa-certificate text-emerald-600"></i> Dosen Tersertifikasi (Serdos)
                </span>
                <span class="text-slate-400 text-[10px]">No. Registrasi: 198004122005011002</span>
            </div>
        </div>
    </div>

    <!-- BKD Compliance Status Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border {{ $bkdStatus['color'] }}">
                    {{ $bkdStatus['badge'] }}
                </span>
                <h2 class="text-xl sm:text-2xl font-display font-black text-slate-900 mt-2">
                    Total Beban Kerja Tri Dharma: <span class="font-mono text-uph-navy">{{ number_format($totalBkdSks, 2) }} SKS</span>
                </h2>
                <p class="text-xs text-slate-600 mt-1 max-w-2xl leading-relaxed">
                    {{ $bkdStatus['message'] }}
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center shrink-0">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Standar DIKTI</span>
                <div class="text-xl font-black text-slate-800 font-mono mt-0.5">
                    12 - 16 SKS
                </div>
                <span class="text-[10px] text-emerald-600 font-bold block mt-0.5">Rentang Beban Ideal</span>
            </div>
        </div>

        <!-- 4 Dharma SKS Breakdown Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 pt-6 text-xs">
            <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200">
                <span class="text-blue-900 font-bold block uppercase text-[10px] tracking-wider">A. Pendidikan</span>
                <span class="text-2xl font-black text-blue-900 font-mono mt-1 block">
                    {{ number_format($totalPendidikanSks, 2) }} <span class="text-xs font-normal">SKS</span>
                </span>
                <span class="text-[10px] text-blue-700 mt-1 block">Mengajar, skripsi, PA</span>
            </div>

            <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-200">
                <span class="text-purple-900 font-bold block uppercase text-[10px] tracking-wider">B. Penelitian</span>
                <span class="text-2xl font-black text-purple-900 font-mono mt-1 block">
                    {{ number_format($totalPenelitianSks, 2) }} <span class="text-xs font-normal">SKS</span>
                </span>
                <span class="text-[10px] text-purple-700 mt-1 block">Hibah & publikasi ilmiah</span>
            </div>

            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200">
                <span class="text-amber-900 font-bold block uppercase text-[10px] tracking-wider">C. Pengabdian (PkM)</span>
                <span class="text-2xl font-black text-amber-900 font-mono mt-1 block">
                    {{ number_format($totalPkmSks, 2) }} <span class="text-xs font-normal">SKS</span>
                </span>
                <span class="text-[10px] text-amber-700 mt-1 block">Pelatihan & bina masyarakat</span>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200">
                <span class="text-emerald-900 font-bold block uppercase text-[10px] tracking-wider">D. Penunjang</span>
                <span class="text-2xl font-black text-emerald-900 font-mono mt-1 block">
                    {{ number_format($totalPenunjangSks, 2) }} <span class="text-xs font-normal">SKS</span>
                </span>
                <span class="text-[10px] text-emerald-700 mt-1 block">SPMI & organisasi profesi</span>
            </div>
        </div>
    </div>

    <!-- Dharma 1: Pengajaran Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-900">Bidang A: Pendidikan & Pengajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar kelas perkuliahan, bimbingan tugas akhir, dan bimbingan akademik yang diampu.</p>
            </div>
            <span class="font-mono font-bold text-xs text-uph-navy bg-uph-navy/10 px-3 py-1 rounded-lg">
                Subtotal: {{ number_format($totalPendidikanSks, 2) }} SKS
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-6">Kegiatan Pengajaran</th>
                        <th class="py-3 px-4">Kelas & Jadwal</th>
                        <th class="py-3 px-4 text-center">Peserta</th>
                        <th class="py-3 px-4 text-center">Beban SKS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($classSections as $idx => $cs)
                    <tr class="hover:bg-slate-50/75">
                        <td class="py-3 px-4 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-6">
                            <span class="font-mono font-bold text-uph-navy text-xs">{{ $cs->course->code }}</span>
                            <span class="text-slate-900 font-bold block">{{ $cs->course->name_id }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-800">Kelas {{ $cs->section_code }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $cs->day_of_week }}, {{ substr($cs->start_time, 0, 5) }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                            {{ $cs->krsItems->count() }} Mhs
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-uph-navy">
                            {{ $cs->course->sks }}.00
                        </td>
                    </tr>
                    @endforeach

                    <!-- Bimbingan Skripsi Row -->
                    <tr class="bg-slate-50/50">
                        <td class="py-3 px-4 text-center font-mono text-slate-400">-</td>
                        <td class="py-3 px-6">
                            <span class="font-bold text-slate-900">Bimbingan Tugas Akhir / Skripsi Mahasiswa</span>
                            <span class="text-[10px] text-slate-400 block">4 Mahasiswa bimbingan setara 1 SKS BKD</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $thesisAdviseesCount }} Mahasiswa Bimbingan Aktif</td>
                        <td class="py-3 px-4 text-center font-mono text-slate-600">{{ $thesisAdviseesCount }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-uph-navy">{{ number_format($thesisBkdSks, 2) }}</td>
                    </tr>

                    <!-- Pembimbing Akademik Row -->
                    <tr class="bg-slate-50/50">
                        <td class="py-3 px-4 text-center font-mono text-slate-400">-</td>
                        <td class="py-3 px-6">
                            <span class="font-bold text-slate-900">Dosen Pembimbing Akademik (Dosen Wali)</span>
                            <span class="text-[10px] text-slate-400 block">Konseling KRS, studi, dan pembinaan EWS</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $paStudentCount }} Mahasiswa Binaan</td>
                        <td class="py-3 px-4 text-center font-mono text-slate-600">{{ $paStudentCount }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-uph-navy">{{ number_format($paBkdSks, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dharma 2, 3, 4 Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Dharma Penelitian -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm">Bidang B: Penelitian</h4>
                <span class="font-mono font-bold text-xs text-purple-700 bg-purple-50 px-2 py-0.5 rounded">
                    {{ number_format($totalPenelitianSks, 2) }} SKS
                </span>
            </div>
            <div class="space-y-3 text-xs">
                @foreach($researchProjects as $rp)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <strong class="text-slate-800 line-clamp-2">{{ $rp['title'] }}</strong>
                    <div class="flex justify-between text-[11px] text-slate-500 pt-1">
                        <span>{{ $rp['role'] }}</span>
                        <span class="font-mono font-bold text-purple-700">{{ number_format($rp['sks'], 2) }} SKS</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Dharma PkM -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm">Bidang C: Pengabdian (PkM)</h4>
                <span class="font-mono font-bold text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded">
                    {{ number_format($totalPkmSks, 2) }} SKS
                </span>
            </div>
            <div class="space-y-3 text-xs">
                @foreach($pkmProjects as $pk)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <strong class="text-slate-800 line-clamp-2">{{ $pk['title'] }}</strong>
                    <div class="flex justify-between text-[11px] text-slate-500 pt-1">
                        <span>{{ $pk['partner'] }}</span>
                        <span class="font-mono font-bold text-amber-700">{{ number_format($pk['sks'], 2) }} SKS</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Dharma Penunjang -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm">Bidang D: Penunjang</h4>
                <span class="font-mono font-bold text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                    {{ number_format($totalPenunjangSks, 2) }} SKS
                </span>
            </div>
            <div class="space-y-3 text-xs">
                @foreach($penunjangItems as $pj)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <strong class="text-slate-800 line-clamp-2">{{ $pj['activity'] }}</strong>
                    <div class="flex justify-between text-[11px] text-slate-500 pt-1">
                        <span>{{ $pj['level'] }}</span>
                        <span class="font-mono font-bold text-emerald-700">{{ number_format($pj['sks'], 2) }} SKS</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
