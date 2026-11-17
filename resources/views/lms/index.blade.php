@extends('layouts.portal')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded bg-uph-navy text-uph-gold text-[10px] font-bold font-mono">
                    {{ $classSection->course->code ?? 'CS101' }} &bull; Kelas {{ $classSection->section_code ?? 'A' }}
                </span>
                <span class="text-xs text-slate-400">&bull; 16 Sesi Terstruktur</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">
                {{ $classSection->course->name_id ?? 'Pilih Mata Kuliah' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Dosen: <strong class="text-slate-800">{{ $classSection->lecturer->formatted_name ?? ($classSection->lecturer->user->full_name ?? '-') }}</strong> &bull; 
                Ruang: <span class="font-bold text-slate-700">{{ $classSection->room->code ?? 'TBA' }}</span> &bull; 
                SKS: <span class="font-mono font-bold text-uph-navy">{{ $classSection->course->sks ?? 3 }} SKS</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($classSection && $allSections->count() > 1)
            <form method="GET" action="/lms">
                <select name="class_section_id" onchange="this.form.submit()" class="h-10 px-3 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-uph-navy">
                    @foreach($allSections as $sec)
                    <option value="{{ $sec->id }}" {{ $classSection->id === $sec->id ? 'selected' : '' }}>
                        {{ $sec->course->code }} ({{ $sec->section_code }}) - {{ $sec->course->name_id }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>

    <!-- 16-Week Timeline Progress Milestone Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center text-xs">
            <span class="font-bold uppercase tracking-wider text-slate-700">Progres Perkuliahan Semester</span>
            <span class="font-mono font-bold text-uph-navy">16 Sesi Perkuliahan (14 Kuliah + UTS + UAS)</span>
        </div>

        <div class="grid grid-cols-4 gap-2 text-center text-xs">
            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200">
                <span class="font-bold text-uph-navy block text-xs">Sesi 1 - 7</span>
                <span class="text-[10px] text-slate-500">Materi Fundamental</span>
            </div>
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                <span class="font-bold text-amber-800 block text-xs">Sesi 8 (UTS)</span>
                <span class="text-[10px] text-slate-500">Ujian Tengah Semester</span>
            </div>
            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200">
                <span class="font-bold text-uph-navy block text-xs">Sesi 9 - 15</span>
                <span class="text-[10px] text-slate-500">Pendalaman & Proyek</span>
            </div>
            <div class="p-3 rounded-xl bg-red-50 border border-red-200">
                <span class="font-bold text-uph-crimson block text-xs">Sesi 16 (UAS)</span>
                <span class="text-[10px] text-slate-500">Ujian Akhir Semester</span>
            </div>
        </div>
    </div>

    <!-- Sessions Timeline List (16 Sesi) -->
    <div class="space-y-4">
        @if($classSection)
        @foreach($classSection->lectureSessions as $ses)
        @php
            $isExam = in_array($ses->session_type, ['uts', 'uas'], true);
        @endphp
        <div class="bg-white rounded-2xl border {{ $isExam ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200' }} p-6 shadow-sm hover:shadow-uph transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-xl {{ $isExam ? 'bg-uph-crimson text-white' : 'bg-uph-navy text-uph-gold' }} flex flex-col items-center justify-center font-mono shrink-0 shadow-sm">
                    <span class="text-[9px] uppercase font-bold">SESI</span>
                    <span class="text-lg font-black leading-none">{{ $ses->session_number }}</span>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $isExam ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-700' }}">
                            {{ strtoupper($ses->session_type) }}
                        </span>
                        <h3 class="text-base font-display font-bold text-uph-navy">
                            {{ $ses->title }}
                        </h3>
                    </div>
                    <p class="text-xs text-slate-500">
                        Jadwal Rencana: <strong class="text-slate-800">{{ $ses->planned_date ? $ses->planned_date->format('d F Y') : '-' }}</strong> &bull; 
                        Ruang: <span class="font-semibold text-slate-700">{{ $ses->room->code ?? ($classSection->room->code ?? 'Ruang Kelas') }}</span>
                    </p>
                    @if($ses->assignments->count() > 0)
                    <div class="pt-1 flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold">
                            <i class="fa-solid fa-file-arrow-up mr-1"></i> {{ $ses->assignments->count() }} Tugas Kuliah
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                @if($isExam)
                <a href="/portal/student/lms/exam/{{ $classSection->id }}" class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-uph-crimson to-amber-600 hover:from-red-700 hover:to-amber-700 text-white font-bold text-xs shadow-md flex items-center justify-center gap-1.5 transition-all">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Mulai Ujian Online</span>
                </a>
                @endif
                <a href="/attendance/session/{{ $ses->id }}" class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl border border-uph-navy text-uph-navy hover:bg-uph-navy hover:text-white font-bold text-xs transition-colors">
                    <i class="fa-solid fa-qrcode mr-1"></i> Presensi QR
                </a>
                <a href="/lms/session/{{ $ses->id }}" class="flex-1 sm:flex-none text-center px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs shadow transition-colors">
                    Detail Sesi & Tugas &rarr;
                </a>
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>
@endsection
