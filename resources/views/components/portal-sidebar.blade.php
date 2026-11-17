@props(['collapsed' => false])

@php
    $user = auth()->user();
    $role = $user ? ($user->primary_role ?? $user->role ?? 'student') : 'student';
    $currentPath = request()->path();
@endphp

<!-- Sidebar Container -->
<aside :class="{
           'translate-x-0': sidebarOpen,
           '-translate-x-full': !sidebarOpen,
           'lg:w-64': !sidebarCollapsed,
           'lg:w-20': sidebarCollapsed
       }"
       class="fixed inset-y-0 left-0 z-50 flex flex-col bg-uph-navy-dark text-white border-r border-white/10 transition-all duration-300 ease-in-out lg:static lg:translate-x-0">
    
    <!-- Sidebar Header & University Crest -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-white/10 bg-uph-navy">
        <a href="/portal" class="flex items-center gap-3 overflow-hidden">
            <div class="h-10 w-10 shrink-0 rounded-lg bg-white/10 border border-uph-gold flex items-center justify-center text-uph-gold font-serif font-black text-xl shadow">
                U
            </div>
            <div class="flex flex-col truncate" x-show="!sidebarCollapsed">
                <span class="font-display font-bold text-sm tracking-wide text-white">SIAKAD UPH</span>
                <span class="text-[10px] text-uph-gold uppercase tracking-widest truncate">Workspace Terisolasi</span>
            </div>
        </a>

        <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex p-1.5 rounded text-slate-400 hover:text-white hover:bg-white/10 transition-colors" title="Toggle Sidebar">
            <i class="fa-solid" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
        </button>
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- Active Account & Scope Badge -->
    <div class="p-3 border-b border-white/10 bg-white/5" x-show="!sidebarCollapsed">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 shrink-0 rounded-full bg-gradient-to-tr from-uph-navy to-uph-crimson border-2 border-uph-gold flex items-center justify-center text-white font-bold text-sm shadow">
                {{ strtoupper(substr($user->full_name ?? 'AD', 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ $user->full_name ?? 'Civitas Akademika' }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-uph-gold/20 text-uph-gold-warm border border-uph-gold/30 uppercase tracking-wider">
                        {{ strtoupper($role) }}
                    </span>
                    <span class="text-[10px] text-slate-400 truncate">
                        @if($role === 'student' && $user->student)
                            NIM {{ $user->student->nim }}
                        @elseif($role === 'lecturer' && $user->lecturer)
                            NIDN {{ $user->lecturer->nidn }}
                        @elseif($user->studyProgram)
                            {{ $user->studyProgram->name_id }}
                        @elseif($user->faculty)
                            {{ $user->faculty->code }}
                        @else
                            UPH Lippo Village
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Strictly Scoped Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-1 text-xs">
        
        @switch($role)
            {{-- ========================================== --}}
            {{-- 1. MAHASISWA (STUDENT) WORKSPACE MENU       --}}
            {{-- ========================================== --}}
            @case('student')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Akademik Mahasiswa
                </div>

                <a href="/portal/student/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/student/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-gauge-high text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Dasbor Mahasiswa</span>
                </a>

                <a href="/portal/student/krs" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*krs*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-calendar-check text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">KRS War & Rencana Studi</span>
                </a>

                <a href="/portal/student/exam-card" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*exam-card*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-chair text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Kartu Ujian & Nomor Kursi</span>
                </a>

                <a href="/portal/student/attendance" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*attendance*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-qrcode text-sm w-5 text-center text-emerald-400"></i>
                    <span x-show="!sidebarCollapsed">Presensi QR Mandiri</span>
                </a>

                <a href="/portal/student/lms" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*lms*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-book-open text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">LMS Kuliah & Tugas</span>
                </a>

                <a href="/portal/student/khs" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*khs*') || request()->is('*edom*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-file-invoice text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">KHS, Transkrip & EWS</span>
                </a>

                <div class="px-2 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Keuangan & Tugas Akhir
                </div>

                <a href="/portal/student/finance" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*finance*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-credit-card text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Tagihan UKT & VA</span>
                </a>

                <a href="/portal/student/thesis" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*thesis*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-graduation-cap text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Skripsi & Log Bimbingan</span>
                </a>

                <a href="/portal/student/yudisium" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*yudisium*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-award text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Yudisium & SKPI</span>
                </a>

                <a href="/portal/student/mbkm" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*mbkm*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-briefcase text-sm w-5 text-center text-indigo-400"></i>
                    <span x-show="!sidebarCollapsed">Program MBKM (20 SKS)</span>
                </a>

                <div class="px-2 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Smart Campus & Layanan
                </div>

                <a href="/portal/student/smart-advisor" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*smart-advisor*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm w-5 text-center text-uph-gold"></i>
                    <span x-show="!sidebarCollapsed">AI Academic Copilot</span>
                </a>

                <a href="/portal/student/campus-life" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*campus-life*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-book-bookmark text-sm w-5 text-center text-emerald-400"></i>
                    <span x-show="!sidebarCollapsed">E-Library & Smart Lab</span>
                </a>

                <a href="/portal/student/career-tracer" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*career-tracer*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-graduation-cap text-sm w-5 text-center text-cyan-400"></i>
                    <span x-show="!sidebarCollapsed">Karir & Tracer Study</span>
                </a>

                <a href="/portal/helpdesk" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*helpdesk*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-headset text-sm w-5 text-center text-sky-400"></i>
                    <span x-show="!sidebarCollapsed">Helpdesk & Layanan SLA</span>
                </a>
                @break

            {{-- ========================================== --}}
            {{-- 2. DOSEN (LECTURER) WORKSPACE MENU          --}}
            {{-- ========================================== --}}
            @case('lecturer')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Tridharma & Pengajaran
                </div>

                <a href="/portal/lecturer/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/lecturer/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-chalkboard-user text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Dasbor Dosen</span>
                </a>

                <a href="/portal/lecturer/sessions" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/lecturer/sessions*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-qrcode text-sm w-5 text-center text-emerald-400"></i>
                    <span x-show="!sidebarCollapsed">Presensi QR & BAP 16-Sesi</span>
                </a>

                <a href="/portal/lecturer/grading" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*grading*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-table-cells text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Input Nilai & Spreadsheet</span>
                </a>

                <a href="/portal/lecturer/advisees" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*advisees*') || request()->is('*krs/advisor*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-user-check text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Persetujuan KRS Advisee</span>
                </a>

                <a href="/portal/lecturer/lms" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*lms*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-folder-tree text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Materi & Tugas 16-Sesi</span>
                </a>

                <a href="/portal/lecturer/thesis" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*thesis*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-user-graduate text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Bimbingan Skripsi & Ujian</span>
                </a>

                <a href="/portal/lecturer/bkd" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*bkd*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-briefcase text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Beban Kerja Dosen (BKD)</span>
                </a>

                <a href="/portal/lecturer/appeals" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*appeals*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-scale-balanced text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Sanggah Nilai Mahasiswa</span>
                </a>
                @break

            {{-- ========================================== --}}
            {{-- 3. KAPRODI (HEAD OF STUDY PROGRAM) MENU    --}}
            {{-- ========================================== --}}
            @case('kaprodi')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Manajemen Program Studi
                </div>

                <a href="/portal/kaprodi/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/kaprodi/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-sitemap text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Dasbor Kaprodi</span>
                </a>

                <a href="/portal/kaprodi/curriculum" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*curriculum*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-diagram-project text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Kurikulum OBE, CPL & CPMK</span>
                </a>

                <a href="/portal/kaprodi/mbkm" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*mbkm*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-graduation-cap text-sm w-5 text-center text-indigo-400"></i>
                    <span x-show="!sidebarCollapsed">Konversi SKS MBKM</span>
                </a>

                <a href="/portal/kaprodi/grade-locks" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*grade-locks*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-lock-open text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Override & Kunci Nilai</span>
                </a>

                <a href="/portal/kaprodi/yudisium" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*yudisium*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-user-check text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Kelulusan & SKPI Prodi</span>
                </a>
                @break

            {{-- ========================================== --}}
            {{-- 4. DEKAN (DEAN) WORKSPACE MENU              --}}
            {{-- ========================================== --}}
            @case('dean')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Kepemimpinan Fakultas
                </div>

                <a href="/portal/dean/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/dean/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-landmark text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Dasbor Dekanat</span>
                </a>

                <a href="/portal/executive/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/executive/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-chart-pie text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Analitika Mutu Fakultas</span>
                </a>

                <a href="/portal/dean/bkd" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*bkd*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-users-viewfinder text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Kinerja Dosen Fakultas</span>
                </a>
                @break

            {{-- ========================================== --}}
            {{-- 5. BAAK (AKADEMIK) WORKSPACE MENU          --}}
            {{-- ========================================== --}}
            @case('baak')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Biro Administrasi Akademik
                </div>

                <a href="/portal/baak/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/baak/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-building-columns text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Dasbor BAAK</span>
                </a>

                <a href="/portal/baak/calendar" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*calendar*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-calendar-days text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Kalender Akademik & Ruang</span>
                </a>

                <a href="/portal/baak/pddikti" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*pddikti*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-cloud-arrow-up text-sm w-5 text-center text-cyan-400"></i>
                    <span x-show="!sidebarCollapsed">PDDIKTI Neo Feeder</span>
                </a>

                <a href="/portal/baak/yudisium" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*yudisium*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-scroll text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Verifikasi Ijazah & SKPI</span>
                </a>

                <a href="/portal/baak/bulk-data" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*bulk-data*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-server text-sm w-5 text-center text-emerald-400"></i>
                    <span x-show="!sidebarCollapsed">Bulk Data & Export CSV</span>
                </a>

                <a href="/portal/iam/users" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*iam*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-users-gear text-sm w-5 text-center text-purple-400"></i>
                    <span x-show="!sidebarCollapsed">Direktori Akun (IAM)</span>
                </a>

                <a href="/portal/baak/rooms-occupancy" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*rooms-occupancy*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-chair text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Okupansi & Kursi Ujian</span>
                </a>

                <a href="/portal/governance/approvals" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*governance/approvals*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-signature text-sm w-5 text-center text-uph-gold"></i>
                    <span x-show="!sidebarCollapsed">Otorisasi Dual-Control</span>
                </a>

                <a href="/portal/helpdesk" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*helpdesk*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-headset text-sm w-5 text-center text-sky-400"></i>
                    <span x-show="!sidebarCollapsed">Helpdesk & Layanan SLA</span>
                </a>

                <a href="/portal/developer/sandbox" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*developer*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-code text-sm w-5 text-center text-purple-400"></i>
                    <span x-show="!sidebarCollapsed">Developer API Sandbox</span>
                </a>
                @break

            {{-- ========================================== --}}
            {{-- 6. BAUK (KEUANGAN) WORKSPACE MENU          --}}
            {{-- ========================================== --}}
            @case('bauk')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Biro Administrasi Keuangan
                </div>

                <a href="/portal/bauk/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/bauk/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-coins text-sm w-5 text-center text-uph-gold"></i>
                    <span x-show="!sidebarCollapsed">Dasbor Keuangan BAUK</span>
                </a>

                <a href="/portal/bauk/billing" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*billing*') || request()->is('*finance*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-receipt text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Billing & Rekonsiliasi VA</span>
                </a>

                <a href="/portal/bauk/bulk-data" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*bulk-data*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-server text-sm w-5 text-center text-emerald-400"></i>
                    <span x-show="!sidebarCollapsed">Bulk Data & Rekonsiliasi</span>
                </a>

                <a href="/portal/governance/approvals" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*governance/approvals*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-signature text-sm w-5 text-center text-uph-gold"></i>
                    <span x-show="!sidebarCollapsed">Otorisasi Dual-Control</span>
                </a>

                <a href="/portal/helpdesk" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*helpdesk*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-headset text-sm w-5 text-center text-sky-400"></i>
                    <span x-show="!sidebarCollapsed">Helpdesk & Layanan SLA</span>
                </a>

                <a href="/portal/developer/sandbox" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*developer/sandbox*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-code text-sm w-5 text-center text-purple-400"></i>
                    <span x-show="!sidebarCollapsed">Developer API Sandbox</span>
                </a>

                <a href="/portal/developer/architecture" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*developer/architecture*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-sitemap text-sm w-5 text-center text-teal-400"></i>
                    <span x-show="!sidebarCollapsed">Cetak Biru Arsitektur</span>
                </a>
                @break

            {{-- ========================================== --}}
            {{-- 7. REKTOR / EKSEKUTIF / SUPERADMIN MENU     --}}
            {{-- ========================================== --}}
            @case('rector')
            @case('superadmin')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Pimpinan Universitas
                </div>

                <a href="/portal/executive/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/executive/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-chart-line text-sm w-5 text-center text-uph-gold"></i>
                    <span x-show="!sidebarCollapsed">Dasbor Eksekutif Rektorat</span>
                </a>

                <a href="/rector/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*rector/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-tachograph-digital text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">EDSS Strategic KPI</span>
                </a>

                <a href="/portal/baak/pddikti" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*pddikti*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-cloud-arrow-up text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Status PDDIKTI Feeder</span>
                </a>

                <a href="/portal/developer/architecture" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*developer/architecture*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-sitemap text-sm w-5 text-center text-teal-400"></i>
                    <span x-show="!sidebarCollapsed">Master Arsitektur &amp; Alur</span>
                </a>

                @if(auth()->user()->role === 'superadmin')
                    <div class="px-2 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-purple-400" x-show="!sidebarCollapsed">
                        Identity &amp; Access (IAM)
                    </div>

                    <a href="/portal/iam/users" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*iam/users*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-users-gear text-sm w-5 text-center text-purple-400"></i>
                        <span x-show="!sidebarCollapsed">Manajemen Akun Sivitas</span>
                    </a>

                    <a href="/portal/iam/privileges" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*iam/privileges*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-shield-halved text-sm w-5 text-center text-purple-400"></i>
                        <span x-show="!sidebarCollapsed">Matriks Hak Akses &amp; RBAC</span>
                    </a>

                    <div class="px-2 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-amber-400" x-show="!sidebarCollapsed">
                        Tata Kelola &amp; Integrasi API
                    </div>

                    <a href="/portal/governance/approvals" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*governance/approvals*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-signature text-sm w-5 text-center text-uph-gold"></i>
                        <span x-show="!sidebarCollapsed">Otorisasi Dual-Control</span>
                    </a>

                    <a href="/portal/helpdesk" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*helpdesk*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-headset text-sm w-5 text-center text-sky-400"></i>
                        <span x-show="!sidebarCollapsed">Helpdesk &amp; SLA Queue</span>
                    </a>

                    <a href="/portal/baak/rooms-occupancy" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*rooms-occupancy*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-chair text-sm w-5 text-center text-amber-400"></i>
                        <span x-show="!sidebarCollapsed">Okupansi &amp; Kursi Ujian</span>
                    </a>

                    <a href="/portal/developer/sandbox" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*developer/sandbox*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-code text-sm w-5 text-center text-purple-400"></i>
                        <span x-show="!sidebarCollapsed">Webhook &amp; API Sandbox</span>
                    </a>

                    <a href="/portal/developer/outbox" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*developer/outbox*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                        <i class="fa-solid fa-paper-plane text-sm w-5 text-center text-emerald-400"></i>
                        <span x-show="!sidebarCollapsed">Outbound Notification Log</span>
                    </a>
                @endif
                @break

            {{-- ========================================== --}}
            {{-- 8. CALON MAHASISWA (APPLICANT) MENU        --}}
            {{-- ========================================== --}}
            @case('applicant')
                <div class="px-2 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400" x-show="!sidebarCollapsed">
                    Penerimaan Mahasiswa Baru
                </div>

                <a href="/portal/pmb/dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('portal/pmb/dashboard*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-id-card text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Status Pendaftaran PMB</span>
                </a>

                <a href="/portal/pmb/register" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*register*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-file-pen text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Data Formulir & Berkas</span>
                </a>

                <a href="/portal/pmb/cbt" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 transition-colors {{ request()->is('*cbt*') ? 'bg-uph-navy text-uph-gold border-l-4 border-uph-gold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-laptop-code text-sm w-5 text-center text-amber-400"></i>
                    <span x-show="!sidebarCollapsed">Ujian Seleksi CBT</span>
                </a>
                @break

            @default
                <a href="/portal" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold hover:bg-white/10 text-slate-300">
                    <i class="fa-solid fa-house text-sm w-5 text-center"></i>
                    <span x-show="!sidebarCollapsed">Beranda</span>
                </a>
        @endswitch

    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-white/10 bg-black/20 text-[11px] text-slate-400 flex items-center justify-between" x-show="!sidebarCollapsed">
        <span>UPH Enterprise v9.0</span>
        <form method="POST" action="/logout" class="inline">
            @csrf
            <button type="submit" class="text-uph-crimson hover:text-white font-bold transition-colors" title="Keluar">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </form>
    </div>
</aside>
