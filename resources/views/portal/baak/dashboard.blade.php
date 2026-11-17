@extends('layouts.portal')

@section('title', 'Dasbor BAAK - Biro Administrasi Akademik UPH')

@section('content')
<div class="space-y-6">

    <!-- Header Section with UPH Midnight Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    BK
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Biro Administrasi Akademik (BAAK)
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-mono font-bold">Periode: {{ $activePeriod->name ?? '2026/2027 Ganjil' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">Manajemen Tata Kelola Akademik Terpadu</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-calendar text-uph-gold mr-1.5"></i> Kalender Master Universitas</span>
                        <span><i class="fa-solid fa-cloud-arrow-up text-cyan-400 mr-1.5"></i> PDDIKTI Neo Feeder Gateway</span>
                        <span><i class="fa-solid fa-award text-purple-400 mr-1.5"></i> Penomoran Ijazah Nasional (PIN)</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                <a href="{{ route('pddikti.index') }}" class="px-4 py-2.5 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 text-xs font-bold transition-all flex items-center gap-2 shadow-sm hover:scale-[1.02]">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Sinkronisasi PDDIKTI</span>
                </a>
                <a href="{{ route('yudisium.index') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors flex items-center gap-2 border border-white/20">
                    <i class="fa-solid fa-scroll"></i>
                    <span>Yudisium & SKPI</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core BAAK Bento KPIs with SVG Circular Progress Rings -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Mahasiswa Aktif -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Mahasiswa Terdaftar</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-navy dark:text-uph-gold font-mono tabular-nums">{{ $totalActiveStudents }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Mhs</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold font-mono text-emerald-600 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Validasi PDDIKTI 100%
                </span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-uph-navy/5 dark:bg-uph-navy/20 text-uph-navy dark:text-uph-gold flex items-center justify-center text-lg border border-uph-navy/10">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Tile 2: Seksi Kelas Berjalan -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Seksi Kelas</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-purple-700 dark:text-purple-400 font-mono tabular-nums">{{ $totalSections }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Kelas</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">0 Clash Ruang & Jadwal</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg border border-purple-200/50">
                <i class="fa-solid fa-chalkboard"></i>
            </div>
        </div>

        <!-- Tile 3: Ruang Kuliah & Lab -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Ruang Kuliah & Lab</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">{{ $totalRooms }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Ruangan</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Kampus Lippo Village</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg border border-cyan-200/50">
                <i class="fa-solid fa-door-open"></i>
            </div>
        </div>

        <!-- Tile 4: Verifikasi Yudisium BAAK -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Verifikasi PIN Ijazah</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono tabular-nums">{{ count($pendingClearances) }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Berkas</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Menunggu Penomoran PIN</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-200/50">
                <i class="fa-solid fa-scroll"></i>
            </div>
        </div>
    </div>

    <!-- Main Workspace Two-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Master Academic Calendar & PDDIKTI Feeder Logs -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Academic Calendar Timeline Stepper -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Kalender Akademik</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-calendar-check text-uph-navy dark:text-uph-gold"></i>
                            <span>Jadwal Batas Kalender Master ({{ $activePeriod->name ?? '2026/2027 Ganjil' }})</span>
                        </h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Masa KRS War</span>
                        <p class="font-bold text-slate-900 dark:text-white mt-1">
                            {{ $activePeriod->krs_start_date ? $activePeriod->krs_start_date->format('d M Y') : '10 Agu 2026' }} - 
                            {{ $activePeriod->krs_end_date ? $activePeriod->krs_end_date->format('d M Y') : '25 Agu 2026' }}
                        </p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 mt-2 font-mono">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Sedang Dibuka
                        </span>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Perkuliahan 16-Minggu</span>
                        <p class="font-bold text-slate-900 dark:text-white mt-1">
                            {{ $activePeriod->lecture_start_date ? $activePeriod->lecture_start_date->format('d M Y') : '01 Sep 2026' }} - 
                            {{ $activePeriod->lecture_end_date ? $activePeriod->lecture_end_date->format('d M Y') : '20 Des 2026' }}
                        </p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 mt-2 font-mono">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            Sesi 1 s.d. 16
                        </span>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Batas Kunci Nilai</span>
                        <p class="font-bold text-slate-900 dark:text-white mt-1">
                            {{ $activePeriod->grading_deadline_date ? $activePeriod->grading_deadline_date->format('d M Y') : '10 Jan 2027' }}
                        </p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 mt-2 font-mono">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Hard Deadline Dosen
                        </span>
                    </div>
                </div>
            </div>

            <!-- PDDIKTI Neo Feeder Sync Activity Table with Alpine Instant Search -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10"
                 x-data="{
                     search: '',
                     matches(entity, batch) {
                         return !this.search || entity.toLowerCase().includes(this.search.toLowerCase()) || batch.toLowerCase().includes(this.search.toLowerCase());
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-cyan-600 dark:text-cyan-400">Sinkronisasi Nasional</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-cloud-arrow-up text-cyan-500"></i>
                            <span>Status Sinkronisasi PDDIKTI Neo Feeder</span>
                        </h2>
                    </div>
                    <form method="POST" action="/pddikti/sync/Mahasiswa">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Sinkronisasi Cepat</span>
                        </button>
                    </form>
                </div>

                <!-- Instant Search -->
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Cari entitas atau batch reference..." class="w-full text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uph-navy dark:focus:ring-uph-gold">
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                                <th class="py-3 px-3">Entitas Feeder</th>
                                <th class="py-3 px-3">Batch Reference</th>
                                <th class="py-3 px-2 text-center">Terkirim</th>
                                <th class="py-3 px-2 text-center">Gagal</th>
                                <th class="py-3 px-2 text-center">Status</th>
                                <th class="py-3 px-3 text-right">Waktu Sync</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($pddiktiLogs as $log)
                                @php
                                    $eType = $log->entity_type ?? 'Feeder';
                                    $bRef = $log->batch_reference ?? '-';
                                @endphp
                                <tr x-show="matches('{{ $eType }}', '{{ $bRef }}')" class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $eType }}</td>
                                    <td class="py-3 px-3 font-mono text-[11px] text-slate-500 dark:text-slate-400">{{ $bRef }}</td>
                                    <td class="py-3 px-2 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $log->success_count }}</td>
                                    <td class="py-3 px-2 text-center font-mono font-bold text-rose-600 dark:text-rose-400 tabular-nums">{{ $log->failed_count }}</td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right text-slate-400 dark:text-slate-500 text-[11px]">
                                        {{ $log->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        Belum ada catatan aktivitas Feeder.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Col: Yudisium Clearance & Bulk Data Card -->
        <div class="space-y-6">

            <!-- Yudisium Verification Queue -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-scroll text-purple-600 dark:text-purple-400"></i>
                    <span>Antrian Yudisium & Verifikasi PIN</span>
                </h3>

                @if(count($pendingClearances) > 0)
                    <div class="space-y-3">
                        @foreach($pendingClearances as $clr)
                            <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 text-xs space-y-2">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $clr->student->user->full_name ?? 'Mahasiswa' }}</p>
                                    <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400">NIM: {{ $clr->student->nim }} &bull; {{ $clr->student->studyProgram->name_id ?? 'IF' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-1 text-[10px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-700/50">
                                    <span>Keuangan: <strong class="text-emerald-600 dark:text-emerald-400">OK</strong></span>
                                    <span>Perpustakaan: <strong class="text-emerald-600 dark:text-emerald-400">OK</strong></span>
                                </div>
                                <form method="POST" action="/yudisium/{{ $clr->id }}/verify">
                                    @csrf
                                    <input type="hidden" name="checkpoint" value="national_pin">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs transition-colors shadow-xs">
                                        Terbitkan Nomor PIN
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                        Seluruh berkas yudisium mahasiswa telah diproses tuntas.
                    </p>
                @endif
            </div>

            <!-- Bulk Data Center Card -->
            <div class="bg-gradient-to-br from-uph-navy to-[#0F172A] p-6 rounded-2xl border border-uph-gold/30 text-white shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold font-mono border border-uph-gold/30">
                        Zero Memory CSV
                    </span>
                    <i class="fa-solid fa-server text-uph-gold text-lg"></i>
                </div>
                <h3 class="text-base font-bold tracking-tight">Bulk Data Command Center</h3>
                <p class="text-xs text-slate-300 mt-1">Ekspor data AKM mahasiswa, jadwal kuliah, dan template penilaian OBE tanpa memory bloat.</p>
                <a href="/portal/baak/bulk-data" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-uph-gold text-slate-950 font-bold text-xs hover:bg-uph-gold-warm transition-all shadow">
                    <span>Buka Bulk Data BAAK</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
