@extends('layouts.portal')

@section('title', 'Dasbor Kaprodi & Kurikulum OBE - UPH')

@section('content')
<div class="space-y-6">

    <!-- Header Section with UPH Midnight Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    IF
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Ketua Program Studi (Kaprodi)
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-bold">Akreditasi: <span class="text-emerald-400 font-black">{{ $studyProgram->accreditation_status ?? 'Unggul' }}</span></span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">Program Studi {{ $studyProgram->name_id ?? 'Informatika' }}</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-graduation-cap text-uph-gold mr-1.5"></i> Gelar Lulusan: {{ $studyProgram->degree_title ?? 'S.Kom.' }}</span>
                        <span><i class="fa-solid fa-building text-slate-400 mr-1.5"></i> {{ $studyProgram->faculty->name_id ?? 'Fakultas Ilmu Komputer' }}</span>
                        <span><i class="fa-solid fa-user-tie text-slate-400 mr-1.5"></i> Kaprodi: {{ $user->full_name }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                <a href="{{ route('curriculum.obe', $curriculum->id ?? '') }}" class="px-4 py-2.5 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 text-xs font-bold transition-all flex items-center gap-2 shadow-sm hover:scale-[1.02]">
                    <i class="fa-solid fa-diagram-project"></i>
                    <span>Matriks OBE & CPL</span>
                </a>
                <a href="{{ route('curriculum.index') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors flex items-center gap-2 border border-white/20">
                    <i class="fa-solid fa-book"></i>
                    <span>Katalog Matakuliah</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Study Program Bento KPI Tiles with SVG Progress Rings -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Mahasiswa Aktif -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Mahasiswa Aktif</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-navy dark:text-uph-gold font-mono tabular-nums">{{ $totalStudents }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Mhs</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Pangkalan Data PDDIKTI</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-uph-navy/5 dark:bg-uph-navy/20 text-uph-navy dark:text-uph-gold flex items-center justify-center text-lg border border-uph-navy/10">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <!-- Tile 2: Rata-Rata IPK with SVG Ring -->
        @php
            $gpaVal = (float)($avgGpa ?? 3.45);
            $gpaPct = ($gpaVal / 4.0) * 100;
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Rata-Rata IPK Prodi</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        {{ number_format($gpaVal, 2) }}
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">/ 4.00</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold font-mono text-emerald-700 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Target SPMI Unggul
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="{{ min(100, $gpaPct) }}, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($gpaPct) }}%
                </div>
            </div>
        </div>

        <!-- Tile 3: Dosen Homebase -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Dosen Homebase</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">{{ count($lecturers) }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Dosen</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">100% Memiliki NIDN</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-200/50">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Tile 4: Capaian OBE & CPL -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Capaian OBE & CPL</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono tabular-nums">{{ $totalCpls }} CPL</h3>
                    <span class="text-xs text-slate-400 font-mono">&bull; {{ $totalCpmks }} CPMK</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Kurikulum OBE 2024</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg border border-purple-200/50">
                <i class="fa-solid fa-diagram-project"></i>
            </div>
        </div>
    </div>

    <!-- Two-Column Grid: Grade Lock Monitoring & Yudisium Clearance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Grade Lock Monitoring & Overrides with Alpine Instant Search -->
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10"
                 x-data="{
                     search: '',
                     filterLock: 'all',
                     matches(name, code, lecturer, isLocked) {
                         const matchQuery = !this.search || name.toLowerCase().includes(this.search.toLowerCase()) || code.toLowerCase().includes(this.search.toLowerCase()) || lecturer.toLowerCase().includes(this.search.toLowerCase());
                         const matchLock = this.filterLock === 'all' || (this.filterLock === 'locked' && isLocked) || (this.filterLock === 'open' && !isLocked);
                         return matchQuery && matchLock;
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Otorisasi Nilai</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-lock text-amber-500"></i>
                            <span>Rekap & Penguncian Nilai Matakuliah (Grade Lock)</span>
                        </h2>
                    </div>

                    <!-- Filter Pills -->
                    <div class="flex items-center gap-2">
                        <button @click="filterLock = 'all'" :class="filterLock === 'all' ? 'bg-uph-navy text-white dark:bg-uph-gold dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">Semua</button>
                        <button @click="filterLock = 'locked'" :class="filterLock === 'locked' ? 'bg-uph-navy text-white dark:bg-uph-gold dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">Terkunci</button>
                        <button @click="filterLock = 'open'" :class="filterLock === 'open' ? 'bg-uph-navy text-white dark:bg-uph-gold dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">Terbuka</button>
                    </div>
                </div>

                <!-- Instant Search -->
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Cari matakuliah atau dosen pengampu..." class="w-full text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uph-navy dark:focus:ring-uph-gold">
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                                <th class="py-3 px-3">Matakuliah & Kelas</th>
                                <th class="py-3 px-3">Dosen Pengampu</th>
                                <th class="py-3 px-2 text-center">Peserta</th>
                                <th class="py-3 px-2 text-center">Status Kunci</th>
                                <th class="py-3 px-3 text-right">Otorisasi Kaprodi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($classSections as $sec)
                                @php
                                    $isLocked = $sec->krsItems->where('is_locked', true)->count() > 0;
                                    $cName = $sec->course->name_id ?? 'Matakuliah';
                                    $cCode = $sec->course->code ?? 'IF101';
                                    $dLec = $sec->lecturer->user->full_name ?? 'Dosen Pengampu';
                                @endphp
                                <tr x-show="matches('{{ addslashes($cName) }}', '{{ $cCode }}', '{{ addslashes($dLec) }}', {{ $isLocked ? 'true' : 'false' }})" class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $cName }}</p>
                                        <span class="font-mono text-[11px] text-slate-400 dark:text-slate-500">{{ $cCode }} &bull; Seksi {{ $sec->section_code }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                        {{ $dLec }}
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono font-bold tabular-nums text-slate-900 dark:text-white">
                                        {{ count($sec->krsItems) }}
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        @if($isLocked)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 dark:bg-rose-950/60 text-red-800 dark:text-rose-300 border border-red-300 dark:border-rose-800">
                                                <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                <i class="fa-solid fa-lock-open text-[9px]"></i> Terbuka
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('grading.sheet', $sec->id) }}" class="p-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Lihat Lembar Nilai">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </a>
                                            <form method="POST" action="/grading/{{ $sec->id }}/lock" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg {{ $isLocked ? 'bg-amber-600 hover:bg-amber-700' : 'bg-red-600 hover:bg-red-700' }} text-white font-bold text-[11px] transition-colors shadow-xs">
                                                    {{ $isLocked ? 'Buka Kunci' : 'Kunci Final' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        Tidak ada kelas aktif untuk periode akademik ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- OBE Curriculum Mapping Status -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Peta Capaian</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-diagram-project text-uph-navy dark:text-uph-gold"></i>
                            <span>Capaian Pembelajaran Lulusan (OBE CPL)</span>
                        </h2>
                    </div>
                    <a href="{{ route('curriculum.obe', $curriculum->id ?? '') }}" class="text-xs font-bold text-uph-navy dark:text-uph-gold hover:underline">
                        Buka Matriks Lengkap &rarr;
                    </a>
                </div>

                @if($curriculum && count($curriculum->obeCpls) > 0)
                    <div class="space-y-3">
                        @foreach($curriculum->obeCpls as $cpl)
                            <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-800/40 text-xs flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-uph-navy text-white">{{ $cpl->code }}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $cpl->category }}</span>
                                    </div>
                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ $cpl->description_id }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 font-mono text-[11px] font-bold shrink-0 border border-indigo-200 dark:border-indigo-800">
                                    {{ $cpl->obe_cpmks_count }} CPMK
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-4">Belum ada CPL yang dikonfigurasi.</p>
                @endif
            </div>

        </div>

        <!-- Right Col: Graduation / Yudisium Clearance & Homebase Lecturers -->
        <div class="space-y-6">

            <!-- Yudisium Approval Queue -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-award text-uph-gold"></i>
                        <span>Verifikasi Yudisium Prodi</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                        {{ count($pendingYudisium) }} Antrian
                    </span>
                </div>

                @if(count($pendingYudisium) > 0)
                    <div class="space-y-2.5">
                        @foreach($pendingYudisium as $yud)
                            <div class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-800/40 text-xs flex items-center justify-between gap-2">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $yud->student->user->full_name ?? 'Mahasiswa' }}</p>
                                    <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400">NIM: {{ $yud->student->nim }}</p>
                                </div>
                                <form method="POST" action="/yudisium/{{ $yud->id }}/verify">
                                    @csrf
                                    <input type="hidden" name="checkpoint" value="faculty_cleared">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-xs">
                                        Validasi
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-4 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                        Tidak ada antrian calon wisudawan yang belum diverifikasi.
                    </p>
                @endif
                <a href="{{ route('yudisium.index') }}" class="block text-center mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs font-bold text-uph-navy dark:text-uph-gold hover:underline">
                    Kelola Yudisium & SKPI &rarr;
                </a>
            </div>

            <!-- Homebase Lecturers List -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-users text-indigo-500"></i>
                    <span>Dosen Homebase ({{ count($lecturers) }})</span>
                </h3>

                <div class="space-y-2.5">
                    @foreach($lecturers as $lec)
                        <div class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-800/40 text-xs flex items-center justify-between">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $lec->user->full_name ?? 'Dosen' }}</p>
                                <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400">NIDN: {{ $lec->nidn }} &bull; {{ $lec->academic_rank }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-mono">
                                {{ $lec->employment_status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
