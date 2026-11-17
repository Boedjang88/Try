@extends('layouts.portal')

@section('title', 'Dasbor Dekanat Fakultas - UPH')

@section('content')
<div class="space-y-6">

    <!-- Header Section with UPH Midnight Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    {{ $faculty->code ?? 'FIK' }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Dekanat Fakultas (ABAC Scoped)
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-bold">Dekan: {{ $user->full_name }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">{{ $faculty->name_id ?? 'Fakultas Ilmu Komputer' }}</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-graduation-cap text-uph-gold mr-1.5"></i> {{ count($prodis) }} Program Studi Aktif</span>
                        <span><i class="fa-solid fa-users text-slate-400 mr-1.5"></i> {{ $facultyStudents }} Mahasiswa Fakultas</span>
                        <span><i class="fa-solid fa-award text-teal-400 mr-1.5"></i> Standar Akreditasi Unggul</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                <a href="{{ route('bkd.index') }}" class="px-4 py-2.5 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 text-xs font-bold transition-all flex items-center gap-2 shadow-sm hover:scale-[1.02]">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Evaluasi BKD Dosen</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Faculty Bento KPIs with SVG Circular Progress Rings -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Mahasiswa Fakultas -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Mahasiswa Fakultas</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-navy dark:text-uph-gold font-mono tabular-nums">{{ $facultyStudents }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Mhs</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Tingkat Retensi: 98.4%</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-uph-navy/5 dark:bg-uph-navy/20 text-uph-navy dark:text-uph-gold flex items-center justify-center text-lg border border-uph-navy/10">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Tile 2: Rata-Rata IPK with SVG Ring -->
        @php
            $fAvg = (float)($facultyAvgIpk ?? 3.52);
            $fAvgPct = ($fAvg / 4.0) * 100;
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Rata-Rata IPK</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        {{ number_format($fAvg, 2) }}
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">/ 4.00</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold font-mono text-emerald-700 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Sangat Baik
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="{{ min(100, $fAvgPct) }}, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($fAvgPct) }}%
                </div>
            </div>
        </div>

        <!-- Tile 3: Dosen Tetap Fakultas -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Dosen Fakultas</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">{{ $facultyLecturers }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Dosen</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">100% Bersertifikasi</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-200/50">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <!-- Tile 4: Program Studi -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Program Studi</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-teal-700 dark:text-teal-400 font-mono tabular-nums">{{ count($prodis) }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Prodi</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Akreditasi Unggul</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg border border-teal-200/50">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
    </div>

    <!-- Study Programs Performance Table in this Faculty -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Program Studi</span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                    <i class="fa-solid fa-graduation-cap text-uph-navy dark:text-uph-gold"></i>
                    <span>Kinerja Program Studi di Bawah Naungan {{ $faculty->name_id }}</span>
                </h2>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <th class="py-3 px-3">Program Studi</th>
                        <th class="py-3 px-2 text-center">Jenjang</th>
                        <th class="py-3 px-2 text-center">Gelar</th>
                        <th class="py-3 px-2 text-center">Status Akreditasi</th>
                        <th class="py-3 px-3 text-right">Aksi Dekanat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                    @foreach($prodis as $prodi)
                        <tr class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3 px-3">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $prodi->name_id }}</p>
                                <span class="font-mono text-[11px] text-slate-400 dark:text-slate-500">{{ $prodi->code }}</span>
                            </td>
                            <td class="py-3 px-2 text-center font-bold text-slate-700 dark:text-slate-300">
                                {{ $prodi->degree_level }}
                            </td>
                            <td class="py-3 px-2 text-center font-mono font-bold text-uph-navy dark:text-uph-gold">
                                {{ $prodi->degree_title }}
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $prodi->accreditation_status ?? 'Unggul' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="/curriculum?study_program_id={{ $prodi->id }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-uph-navy/10 text-uph-navy dark:bg-uph-gold/15 dark:text-uph-gold font-bold text-xs hover:bg-uph-navy hover:text-white dark:hover:bg-uph-gold dark:hover:text-slate-900 transition-colors">
                                    <i class="fa-solid fa-chart-line text-[10px]"></i>
                                    <span>Mutu Prodi</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
