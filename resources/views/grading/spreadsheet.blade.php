@extends('layouts.portal')

@section('content')
<div id="grade-spreadsheet-app" 
     data-section-id="{{ $classSection->id }}"
     data-is-locked="{{ $isLocked ? 'true' : 'false' }}"
     data-weight-presence="{{ $course->weight_presence }}"
     data-weight-task="{{ $course->weight_task }}"
     data-weight-uts="{{ $course->weight_uts }}"
     data-weight-uas="{{ $course->weight_uas }}"
     class="space-y-6">

    <!-- Top Command Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/grading" class="text-xs text-uph-navy font-bold hover:underline">
                    &larr; Daftar Kelas Kuliah
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="font-mono text-xs font-bold text-uph-navy">{{ $course->code }}</span>
                <span class="text-xs text-slate-400">&bull; Kelas {{ $classSection->section_code }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">{{ $course->name_id }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Dosen: <strong class="text-slate-800">{{ $classSection->lecturer->formatted_name ?? ($classSection->lecturer->user->full_name ?? '-') }}</strong> &bull; 
                Total Peserta: <span class="font-mono font-bold text-slate-800">{{ $classSection->krsItems->count() }} Mahasiswa</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="/grading/{{ $classSection->id }}/export" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-file-csv text-emerald-600"></i> Ekspor CSV
            </a>

            @if(!$isLocked)
            <button type="button" id="spreadsheet-save-btn" class="px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Nilai
            </button>
            <button type="button" id="spreadsheet-lock-btn" class="px-5 py-2.5 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-colors flex items-center gap-2">
                <i class="fa-solid fa-lock"></i> Kunci Nilai Resmi
            </button>
            @else
            <span class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs border border-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-lock text-slate-400"></i> Nilai Terkunci Permanen
            </span>
            @endif
        </div>
    </div>

    <!-- OBE Weight Formula Pill Banner -->
    <div class="bg-surface-slate rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row justify-between items-center text-xs gap-3">
        <div class="flex items-center gap-3">
            <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Bobot Asesmen OBE:</span>
            <span class="px-2.5 py-1 rounded bg-white border border-slate-200 font-mono">Presensi: <strong>{{ $course->weight_presence }}%</strong></span>
            <span class="px-2.5 py-1 rounded bg-white border border-slate-200 font-mono">Tugas: <strong>{{ $course->weight_task }}%</strong></span>
            <span class="px-2.5 py-1 rounded bg-white border border-slate-200 font-mono">UTS: <strong>{{ $course->weight_uts }}%</strong></span>
            <span class="px-2.5 py-1 rounded bg-white border border-slate-200 font-mono">UAS: <strong>{{ $course->weight_uas }}%</strong></span>
        </div>
        <span class="text-slate-400 text-[11px]">
            *Gunakan tombol panah keyboard (&uarr; &darr; &larr; &rarr;) untuk navigasi antar sel seperti Excel.
        </span>
    </div>

    <!-- Spreadsheet Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-3 px-3 text-center border-r w-12">No</th>
                        <th class="py-3 px-4 border-r w-32">NIM</th>
                        <th class="py-3 px-4 border-r min-w-[200px]">Nama Mahasiswa</th>
                        <th class="py-3 px-3 text-center border-r w-24">Presensi ({{ $course->weight_presence }}%)</th>
                        <th class="py-3 px-3 text-center border-r w-24">Tugas ({{ $course->weight_task }}%)</th>
                        <th class="py-3 px-3 text-center border-r w-24">UTS ({{ $course->weight_uts }}%)</th>
                        <th class="py-3 px-3 text-center border-r w-24">UAS ({{ $course->weight_uas }}%)</th>
                        <th class="py-3 px-3 text-center border-r w-28 bg-blue-50/50">Nilai Akhir</th>
                        <th class="py-3 px-3 text-center border-r w-24 bg-blue-50/50">Grade</th>
                        <th class="py-3 px-3 text-center w-20 bg-blue-50/50">Bobot</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 tabular-nums">
                    @foreach($classSection->krsItems as $rowIndex => $item)
                    @php
                        $student = $item->krsSubmission->student;
                        $final = $item->final_numeric_score ?? 0;
                        $gradeInfo = \App\Models\KrsItem::convertToGradePoint((float)$final);
                    @endphp
                    <tr class="krs-grade-row hover:bg-slate-50 transition-colors" data-krs-item-id="{{ $item->id }}">
                        <td class="py-2.5 px-3 text-center border-r text-slate-400 font-mono">{{ $rowIndex + 1 }}</td>
                        <td class="py-2.5 px-4 border-r font-mono font-bold text-uph-navy">{{ $student->nim }}</td>
                        <td class="py-2.5 px-4 border-r font-semibold text-slate-900 truncate">{{ $student->user->full_name }}</td>

                        <!-- Editable Score Cells -->
                        <td class="p-1 border-r text-center">
                            <input type="number" step="0.5" min="0" max="100" 
                                   value="{{ $item->score_presence }}"
                                   data-row="{{ $rowIndex }}" data-col="1"
                                   {{ $isLocked ? 'disabled' : '' }}
                                   class="grade-cell-input input-score-pres w-full h-8 text-center text-xs font-mono font-bold bg-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-uph-navy rounded">
                        </td>
                        <td class="p-1 border-r text-center">
                            <input type="number" step="0.5" min="0" max="100" 
                                   value="{{ $item->score_task }}"
                                   data-row="{{ $rowIndex }}" data-col="2"
                                   {{ $isLocked ? 'disabled' : '' }}
                                   class="grade-cell-input input-score-task w-full h-8 text-center text-xs font-mono font-bold bg-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-uph-navy rounded">
                        </td>
                        <td class="p-1 border-r text-center">
                            <input type="number" step="0.5" min="0" max="100" 
                                   value="{{ $item->score_uts }}"
                                   data-row="{{ $rowIndex }}" data-col="3"
                                   {{ $isLocked ? 'disabled' : '' }}
                                   class="grade-cell-input input-score-uts w-full h-8 text-center text-xs font-mono font-bold bg-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-uph-navy rounded">
                        </td>
                        <td class="p-1 border-r text-center">
                            <input type="number" step="0.5" min="0" max="100" 
                                   value="{{ $item->score_uas }}"
                                   data-row="{{ $rowIndex }}" data-col="4"
                                   {{ $isLocked ? 'disabled' : '' }}
                                   class="grade-cell-input input-score-uas w-full h-8 text-center text-xs font-mono font-bold bg-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-uph-navy rounded">
                        </td>

                        <!-- Calculated Displays -->
                        <td class="py-2.5 px-3 text-center border-r bg-blue-50/30 font-mono font-black text-sm text-uph-navy">
                            <span class="display-final-score">{{ number_format($final, 2) }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-center border-r bg-blue-50/30">
                            <span class="display-letter-badge px-2 py-0.5 rounded font-mono font-bold text-xs {{ $final >= 70 ? 'bg-blue-100 text-blue-800' : ($final >= 55 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-uph-crimson') }}">
                                {{ $item->letter_grade ?? $gradeInfo['letter'] }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center bg-blue-50/30 font-mono font-bold text-xs text-slate-700">
                            <span class="display-grade-point">{{ number_format($item->grade_point ?? $gradeInfo['point'], 2) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/resources/js/modules/grade-spreadsheet.js"></script>
@endsection
