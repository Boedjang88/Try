@extends('layouts.portal')

@section('title', 'Dasbor Akademik Mahasiswa - UPH')

@section('content')
<div class="space-y-6">

    <!-- Financial Block / Hold Alert Banner (If Applicable) -->
    @if($isFinanciallyBlocked)
        <div class="rounded-2xl bg-gradient-to-r from-red-600 to-rose-700 p-5 text-white shadow-xl border border-red-500/40 animate-pulse">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0 text-white text-2xl shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-white text-red-700 font-mono">
                                AKUN TERBLOKIR • FINANCIAL HOLD
                            </span>
                            <span class="text-xs text-red-100 font-semibold">Biro Administrasi Keuangan (BAUK)</span>
                        </div>
                        <h2 class="text-lg font-black mt-1">Tunggakan Biaya Kuliah Terdeteksi</h2>
                        <p class="text-xs text-red-100 mt-0.5 max-w-2xl">
                            Akun akademik Anda memiliki tagihan semester berjalan yang melewati tanggal jatuh tempo. Pengisian KRS, pencetakan KHS, dan verifikasi kehadiran ditangguhkan hingga pembayaran Virtual Account diselesaikan.
                        </p>
                    </div>
                </div>

                @if($pendingInvoice)
                    <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-white/20 gap-2">
                        <span class="text-[10px] text-red-200">Total Tagihan:</span>
                        <span class="text-lg font-mono font-black text-white tabular-nums">Rp {{ number_format($pendingInvoice->amount, 0, ',', '.') }}</span>
                        <a href="{{ route('finance.index') }}" class="px-4 py-2 rounded-xl bg-white text-red-700 font-bold text-xs hover:bg-red-50 transition-colors shadow">
                            Bayar Virtual Account <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Hero Welcome Banner with Bento Styling & Contextual Action Pill -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <!-- Subtle Architectural Background Pattern Overlay -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    {{ strtoupper(substr($user->full_name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Mahasiswa Aktif S1
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-mono font-bold tabular-nums">NIM: {{ $student->nim ?? '01082200001' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">{{ $user->full_name }}</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-graduation-cap text-uph-gold mr-1.5"></i> {{ $student->studyProgram->name_id ?? 'Informatika' }} ({{ $student->studyProgram->degree_title ?? 'S.Kom.' }})</span>
                        <span><i class="fa-solid fa-building text-slate-400 mr-1.5"></i> {{ $student->studyProgram->faculty->name_id ?? 'Fakultas Ilmu Komputer' }}</span>
                        <span><i class="fa-solid fa-user-tie text-slate-400 mr-1.5"></i> PA: {{ $student->academicAdvisor->user->full_name ?? 'Dr. Ir. Johanes Tan' }}</span>
                    </p>
                </div>
            </div>

            <!-- Contextual Action Buttons & Period Pill -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-white/10 text-uph-gold border border-white/20 backdrop-blur-xs font-mono">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>T.A. 2026/2027 Ganjil &bull; Minggu ke-7</span>
                </span>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <x-digital-id-card-modal :student="$student" :user="$user" />
                    <a href="/attendance/scan" class="px-4 py-2 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white text-xs font-bold transition-all shadow-md flex items-center gap-2 hover:scale-[1.02]">
                        <i class="fa-solid fa-qrcode text-sm"></i>
                        <span>Scan Presensi</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Stage KRS & Academic Workflow Stepper -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-navy dark:text-uph-gold">
                Alur Siklus Semester Berjalan
            </span>
            <span class="text-xs text-slate-500 font-mono">Tahap 4 dari 5 Aktif</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-4 relative">
            <!-- Step 1: Keuangan -->
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50">
                <div class="h-6 w-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-emerald-900 dark:text-emerald-200 truncate">1. VA Lunas</p>
                    <p class="text-[9px] text-emerald-700 dark:text-emerald-400 truncate">Bebas Tunggakan</p>
                </div>
            </div>

            <!-- Step 2: Pengisian KRS -->
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50">
                <div class="h-6 w-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-emerald-900 dark:text-emerald-200 truncate">2. Isi KRS</p>
                    <p class="text-[9px] text-emerald-700 dark:text-emerald-400 truncate">Paket SKS Terpilih</p>
                </div>
            </div>

            <!-- Step 3: Approval PA -->
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50">
                <div class="h-6 w-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-emerald-900 dark:text-emerald-200 truncate">3. Persetujuan PA</p>
                    <p class="text-[9px] text-emerald-700 dark:text-emerald-400 truncate">Tervalidasi Dosen</p>
                </div>
            </div>

            <!-- Step 4: Kuliah & Presensi (Active) -->
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-uph-navy/10 dark:bg-uph-gold/15 border-2 border-uph-navy dark:border-uph-gold ring-2 ring-uph-gold/20">
                <div class="h-6 w-6 rounded-full bg-uph-navy dark:bg-uph-gold text-white dark:text-slate-900 flex items-center justify-center text-[10px] font-bold shrink-0 animate-pulse">
                    <i class="fa-solid fa-play"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-uph-navy dark:text-uph-gold truncate">4. Perkuliahan</p>
                    <p class="text-[9px] text-slate-500 dark:text-slate-400 truncate">Presensi Sesi 1-16</p>
                </div>
            </div>

            <!-- Step 5: UAS & KHS (Upcoming) -->
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 opacity-60">
                <div class="h-6 w-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 flex items-center justify-center text-[10px] font-bold shrink-0">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-600 dark:text-slate-400 truncate">5. KHS & Nilai</p>
                    <p class="text-[9px] text-slate-400 truncate">Evaluasi Semester</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Asymmetric Bento-Grid KPI Cards with SVG Circular Progress Rings -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: IPK Kumulatif (Progress Ring) -->
        @php
            $currentIpk = (float)($student->cumulative_gpa_ipk ?? 3.65);
            $ipkPercent = ($currentIpk / 4.0) * 100;
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">IPK Kumulatif</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                        {{ number_format($currentIpk, 2) }}
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">/ 4.00</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-mono">
                    <i class="fa-solid fa-arrow-trend-up text-[9px]"></i> +0.15 semester ini
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#D4AF37" stroke-width="3.5" stroke-dasharray="{{ min(100, $ipkPercent) }}, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($ipkPercent) }}%
                </div>
            </div>
        </div>

        <!-- Card 2: SKS Lulus (Progress Ring) -->
        @php
            $sksDone = (int)($student->total_sks_completed ?? 48);
            $sksPercent = ($sksDone / 144) * 100;
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Progres SKS Lulus</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-uph-navy dark:text-uph-gold font-mono tabular-nums">
                        {{ $sksDone }}
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">/ 144 SKS</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">
                    Sisa: {{ max(0, 144 - $sksDone) }} SKS lagi
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#00205B" stroke-width="3.5" stroke-dasharray="{{ min(100, $sksPercent) }}, 100" stroke-linecap="round" class="dark:stroke-cyan-400" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($sksPercent) }}%
                </div>
            </div>
        </div>

        <!-- Card 3: Presensi Kehadiran Kuliah (Progress Ring) -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Tingkat Presensi</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        94%
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">/ min. 75%</span>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Layak Mengikuti UAS
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="94, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    94%
                </div>
            </div>
        </div>

        <!-- Card 4: Sistem Peringatan Dini (EWS Status) -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Sistem EWS Akademik</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="h-3 w-3 rounded-full {{ ($ews['status'] ?? 'NORMAL') === 'NORMAL' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500 animate-ping' }}"></span>
                    <h3 class="text-xl font-display font-black {{ ($ews['status'] ?? 'NORMAL') === 'NORMAL' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-rose-400' }}">
                        {{ $ews['status'] ?? 'NORMAL' }}
                    </h3>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate">
                    {{ empty($ews['warnings']) ? 'Akademik berjalan sesuai standar' : implode(', ', $ews['warnings']) }}
                </p>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Semester:</span>
                <span class="font-bold font-mono text-slate-700 dark:text-slate-300">Smt {{ $student->current_semester ?? 3 }} (Maks 24 SKS)</span>
            </div>
        </div>
    </div>

    <!-- Main Workspace Two-Column Bento Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Today's Schedule & Enrolled Courses Table with Instant Alpine Filters -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Today's Schedule Card -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Jadwal Kuliah</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-calendar-day text-uph-navy dark:text-uph-gold"></i>
                            <span>Kuliah Hari Ini ({{ $currentDayName }})</span>
                        </h2>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-xs font-bold border border-slate-200 dark:border-slate-700">
                        {{ count($todayClasses) }} Matakuliah
                    </span>
                </div>

                @if(count($todayClasses) > 0)
                    <div class="space-y-3">
                        @foreach($todayClasses as $item)
                            <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="h-11 w-11 rounded-xl bg-uph-navy text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs ring-1 ring-uph-gold/30">
                                        {{ $item->classSection->section_code ?? 'A' }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-mono font-bold text-uph-navy dark:text-uph-gold">{{ $item->classSection->course->code ?? 'IF101' }}</span>
                                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $item->classSection->course->sks ?? 3 }} SKS</span>
                                        </div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $item->classSection->course->name_id ?? 'Pemrograman Web' }}</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <span><i class="fa-solid fa-clock text-slate-400 mr-1"></i> {{ substr($item->classSection->start_time ?? '08:00', 0, 5) }} - {{ substr($item->classSection->end_time ?? '11:40', 0, 5) }}</span>
                                            <span><i class="fa-solid fa-door-open text-slate-400 mr-1"></i> {{ $item->classSection->room->name ?? 'Gedung D-401' }}</span>
                                            <span><i class="fa-solid fa-user text-slate-400 mr-1"></i> {{ $item->classSection->lecturer->user->full_name ?? 'Dosen Pengampu' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                    <a href="/attendance/scan/{{ $item->classSection->id ?? '' }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-sm">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Scan Presensi</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 dark:text-slate-500 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-800/20">
                        <i class="fa-regular fa-calendar-check text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                        <p class="text-xs font-semibold">Tidak ada jadwal kuliah tatap muka untuk hari {{ $currentDayName }}.</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Gunakan waktu luang untuk menyelesaikan modul dan kuis di LMS.</p>
                    </div>
                @endif
            </div>

            <!-- Visual Weekly Time-Grid Timetable Component -->
            <x-weekly-timetable-grid :items="$enrolledClasses" />

            <!-- Enrolled KRS Courses Table with Instant Alpine Search & Filter -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10"
                 x-data="{
                     search: '',
                     filterType: 'all',
                     matches(code, name, type) {
                         const matchQuery = !this.search || code.toLowerCase().includes(this.search.toLowerCase()) || name.toLowerCase().includes(this.search.toLowerCase());
                         const matchType = this.filterType === 'all' || type === this.filterType;
                         return matchQuery && matchType;
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Kartu Rencana Studi</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-book-bookmark text-uph-navy dark:text-uph-gold"></i>
                            <span>Daftar Matakuliah Diambil (KRS Semester Ini)</span>
                        </h2>
                    </div>

                    <!-- Instant Filter Pills -->
                    <div class="flex items-center gap-2">
                        <button @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-uph-navy text-white dark:bg-uph-gold dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">Semua</button>
                        <button @click="filterType = 'wajib'" :class="filterType === 'wajib' ? 'bg-uph-navy text-white dark:bg-uph-gold dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">Wajib Prodi</button>
                        <button @click="filterType = 'pilihan'" :class="filterType === 'pilihan' ? 'bg-uph-navy text-white dark:bg-uph-gold dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">Pilihan</button>
                        <a href="{{ route('portal.student.exam-card') }}" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 hover:bg-uph-gold/30 transition-colors inline-flex items-center gap-1">
                            <i class="fa-solid fa-chair text-[10px]"></i>
                            <span>Kursi Ujian</span>
                        </a>
                    </div>
                </div>

                <!-- Instant Search Input -->
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Cari nama matakuliah atau kode (misal: IF101)..." class="w-full text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uph-navy dark:focus:ring-uph-gold">
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                                <th class="py-3 px-3">Kode & Matakuliah</th>
                                <th class="py-3 px-2 text-center">SKS</th>
                                <th class="py-3 px-2 text-center">Kelas</th>
                                <th class="py-3 px-3">Jadwal & Ruang</th>
                                <th class="py-3 px-2 text-center">Presensi</th>
                                <th class="py-3 px-2 text-center">Kursi Ujian</th>
                                <th class="py-3 px-2 text-center">Nilai Sementara</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($enrolledClasses as $krsItem)
                                @php
                                    $courseCode = $krsItem->classSection->course->code ?? 'IF101';
                                    $courseName = $krsItem->classSection->course->name_id ?? 'Matakuliah';
                                    $courseType = str_contains(strtolower($krsItem->classSection->course->type ?? 'wajib'), 'pilihan') ? 'pilihan' : 'wajib';
                                @endphp
                                <tr x-show="matches('{{ $courseCode }}', '{{ addslashes($courseName) }}', '{{ $courseType }}')" class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $courseName }}</p>
                                        <span class="font-mono text-[11px] text-slate-400 dark:text-slate-500">{{ $courseCode }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ $krsItem->classSection->course->sks ?? 3 }}</td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ $krsItem->classSection->section_code ?? 'A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                                        <p class="font-medium">{{ $krsItem->classSection->day_of_week ?? 'Senin' }}, {{ substr($krsItem->classSection->start_time ?? '08:00', 0, 5) }}</p>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $krsItem->classSection->room->name ?? 'D-401' }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono tabular-nums">
                                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ number_format($krsItem->score_presence ?? 95, 0) }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono">
                                        @if($krsItem->exam_seat_number)
                                            <span class="px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/30 text-uph-gold font-bold text-[10px]">
                                                {{ $krsItem->exam_seat_number }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono tabular-nums">
                                        @if($krsItem->letter_grade)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-bold border border-emerald-300 dark:border-emerald-700">
                                                {{ $krsItem->letter_grade }} ({{ number_format($krsItem->final_numeric_score, 1) }})
                                            </span>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">Berjalan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        Belum ada matakuliah yang terdaftar pada semester ini. Silakan akses modul KRS War.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Financial Status, Smart Advisor & Quick Portals -->
        <div class="space-y-6">

            <!-- Financial Summary Bento Card -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-uph-gold"></i>
                        <span>Tagihan Biaya Kuliah</span>
                    </span>
                    <a href="{{ route('finance.index') }}" class="text-xs text-uph-navy dark:text-uph-gold font-bold hover:underline">Detail</a>
                </h3>

                @if($student->invoices && count($student->invoices) > 0)
                    <div class="space-y-3">
                        @foreach($student->invoices as $inv)
                            <div class="p-3.5 rounded-xl border {{ $inv->payment_status === 'paid' ? 'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800' : 'bg-red-50/60 dark:bg-red-950/30 border-red-200 dark:border-red-800' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-bold text-xs {{ $inv->payment_status === 'paid' ? 'text-emerald-900 dark:text-emerald-200' : 'text-red-900 dark:text-rose-200' }}">
                                            {{ $inv->title }}
                                        </p>
                                        <p class="font-mono text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $inv->invoice_number }}</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono {{ $inv->payment_status === 'paid' ? 'bg-emerald-200 dark:bg-emerald-800 text-emerald-900 dark:text-emerald-100' : 'bg-red-200 dark:bg-rose-900 text-red-900 dark:text-rose-100' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $inv->payment_status === 'paid' ? 'bg-emerald-600' : 'bg-red-600' }}"></span>
                                        {{ $inv->payment_status }}
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center justify-between pt-2 border-t border-slate-200/50 dark:border-slate-800/50 text-xs">
                                    <span class="text-slate-500 dark:text-slate-400">Nominal:</span>
                                    <span class="font-mono font-bold text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($inv->amount, 0, ',', '.') }}</span>
                                </div>
                                @if($inv->payment_status === 'pending')
                                    <div class="mt-2">
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">BCA Virtual Account:</span>
                                        <div class="flex items-center justify-between bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-red-300 dark:border-rose-700 mt-1">
                                            <span class="font-mono font-bold text-red-700 dark:text-rose-400 text-xs tabular-nums">{{ $inv->va_number }}</span>
                                            <button onclick="navigator.clipboard.writeText('{{ $inv->va_number }}')" class="text-[10px] text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" title="Salin VA">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-4">Tidak ada riwayat tagihan aktif.</p>
                @endif
            </div>

            <!-- Smart Advisor Feature Teaser Tile -->
            <div class="bg-gradient-to-br from-uph-navy to-[#0F172A] p-6 rounded-2xl border border-uph-gold/30 text-white shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold font-mono border border-uph-gold/30">
                        AI Copilot
                    </span>
                    <i class="fa-solid fa-wand-magic-sparkles text-uph-gold text-lg"></i>
                </div>
                <h3 class="text-base font-bold tracking-tight">Smart Academic Advisor</h3>
                <p class="text-xs text-slate-300 mt-1">Simulasikan target nilai IPK, kelayakan predikat Cum Laude, dan rekomendasi matakuliah otomatis untuk lulus tepat 8 semester.</p>
                <a href="/portal/student/smart-advisor" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-uph-gold text-slate-950 font-bold text-xs hover:bg-uph-gold-warm transition-all shadow">
                    <span>Buka Simulator IPK</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Quick Access Portals -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10 space-y-2">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Layanan Terpadu Kampus</h3>
                <a href="{{ route('lms.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-book-open text-uph-navy dark:text-uph-gold w-4"></i>
                        <span class="font-semibold">LMS Perkuliahan & Tugas</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
                <a href="/portal/student/campus-life" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-book-bookmark text-emerald-500 w-4"></i>
                        <span class="font-semibold">E-Library & Peminjaman Lab</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
                <a href="/portal/student/career-tracer" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-graduation-cap text-cyan-500 w-4"></i>
                        <span class="font-semibold">Pusat Karir & Tracer Study</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
                <a href="{{ route('yudisium.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs text-slate-700 dark:text-slate-300">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-award text-uph-gold w-4"></i>
                        <span class="font-semibold">Kelulusan & SKPI Digital</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
