@extends('layouts.portal')

@section('title', 'Dasbor Dosen & Tridharma - UPH')

@section('content')
<div class="space-y-6">

    <!-- Hero Welcome Banner with Bento Styling & Contextual Action Pill -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <!-- Subtle Pattern Overlay -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    {{ strtoupper(substr($user->full_name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Dosen Pengajar & Pembimbing Akademik
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-mono font-bold tabular-nums">NIDN: {{ $lecturer->nidn ?? '0412057801' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">{{ $user->full_name }}</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-award text-uph-gold mr-1.5"></i> {{ $lecturer->academic_rank ?? 'Lektor Kepala' }}</span>
                        <span><i class="fa-solid fa-graduation-cap text-slate-400 mr-1.5"></i> {{ $lecturer->studyProgram->name_id ?? 'Informatika' }}</span>
                        <span><i class="fa-solid fa-circle-check text-emerald-400 mr-1.5"></i> Status: {{ $lecturer->employment_status ?? 'Tetap' }}</span>
                    </p>
                </div>
            </div>

            <!-- Contextual CTA Buttons & Period Status -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-white/10 text-uph-gold border border-white/20 backdrop-blur-xs font-mono">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Semester Ganjil 2026/2027 &bull; Minggu ke-7</span>
                </span>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <x-digital-id-card-modal :lecturer="$lecturer" :user="$user" />
                    <a href="/portal/lecturer/sessions" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md flex items-center gap-2 hover:scale-[1.02]">
                        <i class="fa-solid fa-qrcode text-sm"></i>
                        <span>Buka Sesi Kelas Hari Ini</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Teaching & Academic Bento KPI Tiles with SVG Circular Progress Rings -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Kelas Aktif -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Kelas Diampu</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-navy dark:text-uph-gold font-mono tabular-nums">{{ $totalClasses }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Seksi</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">
                    Semester Ganjil 2026/2027
                </span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-uph-navy/5 dark:bg-uph-navy/20 text-uph-navy dark:text-uph-gold flex items-center justify-center text-lg border border-uph-navy/10">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <!-- Tile 2: Total Mahasiswa -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Mahasiswa Diajar</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">{{ $totalStudentsEnrolled }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Orang</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">
                    {{ count($lecturer->advisees ?? []) }} Mahasiswa Bimbingan PA
                </span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg border border-cyan-200/50">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Tile 3: Persetujuan KRS Advisee -->
        @php
            $pendingCount = count($pendingKrsAdvisees ?? []);
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Persetujuan KRS PA</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black font-mono tabular-nums {{ $pendingCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $pendingCount }}
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">Menunggu</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold font-mono {{ $pendingCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $pendingCount > 0 ? 'bg-amber-500 animate-ping' : 'bg-emerald-500' }}"></span>
                    {{ $pendingCount > 0 ? 'Perlu approval segera' : 'Semua KRS tuntas' }}
                </span>
            </div>
            <a href="{{ route('krs.advisor') }}" class="h-12 w-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-200/50 hover:scale-105 transition-transform" title="Buka Approval KRS">
                <i class="fa-solid fa-user-check"></i>
            </a>
        </div>

        <!-- Tile 4: OBE Grading Progress with SVG Ring -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Progres Nilai OBE</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        {{ $gradingProgressPercent }}%
                    </h3>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">
                    Tersimpan di SIAKAD
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="{{ min(100, $gradingProgressPercent) }}, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($gradingProgressPercent) }}%
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace Two-Column Bento Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Today's Teaching Sessions & Assigned Classes Grid -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Today's Teaching Sessions -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Jadwal Mengajar</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-chalkboard text-uph-navy dark:text-uph-gold"></i>
                            <span>Sesi Perkuliahan Hari Ini</span>
                        </h2>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-xs font-bold border border-slate-200 dark:border-slate-700">
                        {{ count($todayTeachingClasses ?? []) }} Sesi
                    </span>
                </div>

                @if(isset($todayTeachingClasses) && count($todayTeachingClasses) > 0)
                    <div class="space-y-3">
                        @foreach($todayTeachingClasses as $cls)
                            <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="h-11 w-11 rounded-xl bg-uph-navy text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs ring-1 ring-uph-gold/30">
                                        {{ $cls->section_code ?? 'A' }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-mono font-bold text-uph-navy dark:text-uph-gold">{{ $cls->course->code ?? 'IF101' }}</span>
                                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $cls->course->sks ?? 3 }} SKS</span>
                                        </div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $cls->course->name_id ?? 'Matakuliah' }}</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <span><i class="fa-solid fa-clock text-slate-400 mr-1"></i> {{ substr($cls->start_time ?? '08:00', 0, 5) }} - {{ substr($cls->end_time ?? '11:40', 0, 5) }}</span>
                                            <span><i class="fa-solid fa-door-open text-slate-400 mr-1"></i> {{ $cls->room->name ?? 'Gedung D-401' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                    <a href="/attendance/session/{{ $cls->id ?? '' }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-sm">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Buka QR Presensi</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 dark:text-slate-500 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-800/20">
                        <i class="fa-regular fa-calendar-check text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                        <p class="text-xs font-semibold">Tidak ada jadwal perkuliahan hari ini.</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Kelola materi LMS atau selesaikan input nilai OBE.</p>
                    </div>
                @endif
            </div>

            <!-- Assigned Classes Data Grid with Instant Search -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10"
                 x-data="{
                     search: '',
                     matches(code, name) {
                         return !this.search || code.toLowerCase().includes(this.search.toLowerCase()) || name.toLowerCase().includes(this.search.toLowerCase());
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Mata Kuliah Diampu</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-table-cells text-uph-navy dark:text-uph-gold"></i>
                            <span>Daftar Seluruh Seksi Kelas & Spreadsheet Nilai</span>
                        </h2>
                    </div>

                    <a href="{{ route('grading.index') }}" class="px-4 py-2 rounded-xl bg-uph-navy dark:bg-uph-gold text-white dark:text-slate-950 font-bold text-xs hover:bg-uph-navy-dark transition-colors flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-table-cells"></i>
                        <span>Input Nilai OBE</span>
                    </a>
                </div>

                <!-- Instant Search -->
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Cari kode atau nama kelas..." class="w-full text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uph-navy dark:focus:ring-uph-gold">
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                                <th class="py-3 px-3">Kode & Matakuliah</th>
                                <th class="py-3 px-2 text-center">Kelas</th>
                                <th class="py-3 px-2 text-center">SKS</th>
                                <th class="py-3 px-3">Hari & Waktu</th>
                                <th class="py-3 px-2 text-center">Enrolled</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse(($assignedClasses ?? $classSections ?? []) as $cls)
                                @php
                                    $code = $cls->course->code ?? 'IF101';
                                    $name = $cls->course->name_id ?? 'Matakuliah';
                                @endphp
                                <tr x-show="matches('{{ $code }}', '{{ addslashes($name) }}')" class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $name }}</p>
                                        <span class="font-mono text-[11px] text-slate-400 dark:text-slate-500">{{ $code }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ $cls->section_code ?? 'A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono font-bold text-slate-800 dark:text-slate-200">{{ $cls->course->sks ?? 3 }}</td>
                                    <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                                        <p class="font-medium">{{ $cls->day_of_week ?? 'Senin' }}, {{ substr($cls->start_time ?? '08:00', 0, 5) }}</p>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $cls->room->name ?? 'D-401' }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono font-bold tabular-nums text-slate-900 dark:text-white">
                                        {{ $cls->krs_items_count ?? count($cls->krsItems ?? []) }} mhs
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <a href="/grading/{{ $cls->id }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-uph-navy/10 text-uph-navy dark:bg-uph-gold/15 dark:text-uph-gold font-bold text-xs hover:bg-uph-navy hover:text-white dark:hover:bg-uph-gold dark:hover:text-slate-900 transition-colors">
                                            <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                            <span>Nilai</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        Belum ada seksi kelas yang ditugaskan pada semester ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Quick Actions & Advisee Alerts -->
        <div class="space-y-6">

            <!-- Sanggah Nilai Mahasiswa Alert Tile -->
            <div class="bg-gradient-to-br from-amber-500 to-amber-700 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/20 text-white font-mono border border-white/30">
                        Transparansi Nilai
                    </span>
                    <i class="fa-solid fa-scale-balanced text-white text-lg"></i>
                </div>
                <h3 class="text-base font-bold tracking-tight">Sanggah Nilai Mahasiswa</h3>
                <p class="text-xs text-amber-100 mt-1">Evaluasi transparansi komponen nilai OBE dan tindak lanjuti permohonan sanggahan nilai mahasiswa secara objektif.</p>
                <a href="/portal/lecturer/appeals" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-amber-900 font-bold text-xs hover:bg-amber-50 transition-all shadow">
                    <span>Lihat Pengajuan Sanggah</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Quick Access Portals -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10 space-y-2">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Pusat Layanan Dosen</h3>
                <a href="{{ route('krs.advisor') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-user-check text-amber-500 w-4"></i>
                        <span class="font-semibold">Persetujuan KRS Advisee ({{ $pendingCount }})</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
                <a href="{{ route('lms.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-folder-tree text-uph-navy dark:text-uph-gold w-4"></i>
                        <span class="font-semibold">LMS & Modul 16-Sesi</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
                <a href="{{ route('thesis.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-graduation-cap text-indigo-500 w-4"></i>
                        <span class="font-semibold">Bimbingan Skripsi & Sidang</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
                <a href="{{ route('bkd.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-briefcase text-emerald-500 w-4"></i>
                        <span class="font-semibold">Beban Kerja Dosen (BKD)</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
