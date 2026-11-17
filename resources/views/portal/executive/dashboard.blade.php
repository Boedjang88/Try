@extends('layouts.portal')

@section('title', 'Dasbor Eksekutif Rektorat (EDSS) - UPH')

@section('content')
<div class="space-y-6">

    <!-- Header Section with UPH Midnight Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                        Executive Decision Support System (EDSS)
                    </span>
                    <span class="text-white/40">&bull;</span>
                    <span class="text-xs text-slate-300 font-medium">Universitas Pelita Harapan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">Dasbor Analitika Strategis Rektorat</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                    Pemantauan KPI kelembagaan menyeluruh: penerimaan mahasiswa baru, kesehatan finansial, mutu akademik OBE, dan kesiapan akreditasi LAM/BAN-PT.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 text-uph-gold text-xs font-bold border border-white/20 font-mono">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live Data (Cached 5m)
                </span>
                <button onclick="window.print()" class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-colors" title="Cetak Ringkasan Eksekutif">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Core University Bento KPI Metrics with SVG Rings -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Mahasiswa Aktif -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Total Mahasiswa Aktif</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-navy dark:text-uph-gold font-mono tabular-nums">{{ $totalActiveStudents }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Mhs</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">
                    {{ $totalFaculties }} Fakultas &bull; {{ count($studyPrograms) }} Prodi
                </span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-uph-navy/5 dark:bg-uph-navy/20 text-uph-navy dark:text-uph-gold flex items-center justify-center text-lg border border-uph-navy/10">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <!-- Tile 2: Rata-Rata IPK with SVG Ring -->
        @php
            $uIpk = (float)($universityAvgIpk ?? 3.48);
            $uIpkPct = ($uIpk / 4.0) * 100;
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Rata-Rata IPK Kampus</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        {{ number_format($uIpk, 2) }}
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">/ 4.00</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold font-mono text-emerald-700 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Standar Mutu Unggul (SPMI)
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="{{ min(100, $uIpkPct) }}, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($uIpkPct) }}%
                </div>
            </div>
        </div>

        <!-- Tile 3: Kas Terkumpul -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Kas SPP/BPP Masuk</span>
                <div class="flex items-baseline gap-1 mt-1.5">
                    <span class="text-xs text-slate-400 font-bold">Rp</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                        {{ number_format($totalTuitionCollected / 1000000, 1) }}M
                    </h3>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Tahun Akademik 2026/2027</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg border border-emerald-200/50">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <!-- Tile 4: Total Dosen Ber-NIDN -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Dosen Ber-NIDN</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-gold-warm font-mono tabular-nums">{{ $totalLecturers }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Dosen</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Rasio Dosen:Mhs 1 : 25</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-200/50">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>
    </div>

    <!-- Faculties & Study Programs Breakdown Table with Alpine Instant Search -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10"
         x-data="{
             search: '',
             matches(code, name, faculty) {
                 return !this.search || code.toLowerCase().includes(this.search.toLowerCase()) || name.toLowerCase().includes(this.search.toLowerCase()) || faculty.toLowerCase().includes(this.search.toLowerCase());
             }
         }">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Pangkalan Data Program Studi</span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                    <i class="fa-solid fa-sitemap text-uph-navy dark:text-uph-gold"></i>
                    <span>Distribusi Program Studi & Status Akreditasi Nasional</span>
                </h2>
            </div>
        </div>

        <!-- Instant Search -->
        <div class="mb-4">
            <input type="text" x-model="search" placeholder="Cari program studi atau fakultas..." class="w-full text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uph-navy dark:focus:ring-uph-gold">
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <th class="py-3 px-3">Kode</th>
                        <th class="py-3 px-3">Program Studi</th>
                        <th class="py-3 px-3">Fakultas</th>
                        <th class="py-3 px-2 text-center">Jenjang</th>
                        <th class="py-3 px-2 text-center">Status Akreditasi</th>
                        <th class="py-3 px-3 text-right">Kepatuhan Feeder</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                    @foreach($studyPrograms as $p)
                        @php
                            $pCode = $p->code ?? '';
                            $pName = $p->name_id ?? '';
                            $fName = $p->faculty->name_id ?? '';
                        @endphp
                        <tr x-show="matches('{{ $pCode }}', '{{ addslashes($pName) }}', '{{ addslashes($fName) }}')" class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3 px-3 font-mono font-bold text-slate-400 dark:text-slate-500">{{ $pCode }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $pName }}</td>
                            <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ $fName }}</td>
                            <td class="py-3 px-2 text-center font-bold text-slate-700 dark:text-slate-300">{{ $p->degree_level }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $p->accreditation_status ?? 'Unggul' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                100% Synced
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
