@extends('layouts.portal')

@section('title', 'Penilaian Akademik (OBE Grading) - UPH')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    SIAKAD & LMS TERPADU
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Outcome-Based Education (OBE) Standard</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Lembar Penilaian Dosen & Buku Nilai</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola penilaian mahasiswa, perhitungan bobot CPMK/CPL otomatis, dan kunci lembar nilai resmi (Grade Freeze).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                <i class="fa-solid fa-calendar-check text-uph-gold"></i>
                Semester Berjalan
            </span>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    @php
        $totalClasses = $classSections->count();
        $totalStudents = $classSections->sum(fn($cs) => $cs->krsItems->count());
        $lockedClasses = $classSections->filter(fn($cs) => $cs->krsItems->where('is_locked', true)->count() > 0)->count();
        $pendingClasses = $totalClasses - $lockedClasses;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-uph-navy/10 text-uph-navy flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Kelas Diampu</p>
                <h3 class="text-2xl font-display font-black text-slate-900 mt-0.5">{{ $totalClasses }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Peserta KRS</p>
                <h3 class="text-2xl font-display font-black text-slate-900 mt-0.5">{{ $totalStudents }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-pen-ruler"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perlu Finalisasi</p>
                <h3 class="text-2xl font-display font-black text-slate-900 mt-0.5">{{ $pendingClasses }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nilai Terkunci Resmi</p>
                <h3 class="text-2xl font-display font-black text-slate-900 mt-0.5">{{ $lockedClasses }}</h3>
            </div>
        </div>
    </div>

    <!-- Class Section Roster Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Kelas Perkuliahan Anda</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pilih kelas untuk membuka modul spreadsheet penilaian inline atau ekspor rekapitulasi.</p>
            </div>
        </div>

        @if($classSections->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Tidak ada kelas kuliah yang ditemukan</h3>
            <p class="text-xs text-slate-400 mt-1">Anda belum ditugaskan mengajar kelas pada periode akademik aktif ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-6">Matakuliah & Seksi</th>
                        <th class="py-4 px-6">SKS / Jadwal / Ruang</th>
                        <th class="py-4 px-6 text-center">Peserta</th>
                        <th class="py-4 px-6">Bobot OBE</th>
                        <th class="py-4 px-6 text-center">Status Ledger</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($classSections as $section)
                        @php
                            $isSectionLocked = $section->krsItems->where('is_locked', true)->count() > 0;
                            $gradedCount = $section->krsItems->whereNotNull('final_numeric_score')->count();
                            $studentCount = $section->krsItems->count();
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-uph-navy bg-uph-navy/5 px-2 py-0.5 rounded text-[11px]">
                                        {{ $section->course->code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-700 border border-slate-200">
                                        Kelas {{ $section->section_code }}
                                    </span>
                                </div>
                                <div class="font-bold text-slate-900 text-sm mt-1">
                                    {{ $section->course->name_id }}
                                </div>
                                <div class="text-[11px] text-slate-400 italic">
                                    {{ $section->course->name_en }}
                                </div>
                            </td>

                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-700">
                                    {{ $section->course->sks }} SKS
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-slate-400"></i>
                                    {{ $section->day_of_week }}, {{ substr($section->start_time, 0, 5) }} - {{ substr($section->end_time, 0, 5) }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-slate-400"></i>
                                    {{ $section->room->name ?? 'Ruang TBA' }}
                                </div>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <span class="font-mono font-bold text-sm text-slate-800">
                                    {{ $studentCount }}
                                </span>
                                <span class="text-slate-400 text-[10px] block">
                                    Mhs ({{ $gradedCount }} dinilai)
                                </span>
                            </td>

                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1 text-[10px] font-mono">
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600">Pres: {{ (int)$section->course->weight_presence }}%</span>
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600">Tgs: {{ (int)$section->course->weight_task }}%</span>
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600">UTS: {{ (int)$section->course->weight_uts }}%</span>
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600">UAS: {{ (int)$section->course->weight_uas }}%</span>
                                </div>
                            </td>

                            <td class="py-4 px-6 text-center">
                                @if($isSectionLocked)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Terkunci
                                    </span>
                                @elseif($gradedCount == $studentCount && $studentCount > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-check text-[10px]"></i> Lengkap
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-pen-clip text-[10px]"></i> Draft Nilai
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/grading/{{ $section->id }}" 
                                       class="px-3.5 py-2 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs transition-colors inline-flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-table-cells"></i> Lembar Nilai
                                    </a>
                                    <a href="/grading/{{ $section->id }}/export" 
                                       title="Unduh Rekap CSV"
                                       class="p-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition-colors inline-flex items-center justify-center">
                                        <i class="fa-solid fa-file-csv text-emerald-600"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
