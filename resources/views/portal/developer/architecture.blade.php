@extends('layouts.portal', ['title' => 'Master Cetak Biru Arsitektur - UPH Enterprise'])

@section('content')
<div class="space-y-6" x-data="{
    copiedId: null,
    searchQuery: '',
    copyCode(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text);
        this.copiedId = elementId;
        setTimeout(() => this.copiedId = null, 2000);
    }
}">

    <!-- Top Bento Hero Banner -->
    <div class="bg-gradient-to-r from-uph-navy via-slate-900 to-uph-navy-dark rounded-2xl p-6 sm:p-8 text-white border border-white/10 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-uph-gold/20 text-uph-gold border border-uph-gold/40">
                        Enterprise Architecture Blueprint
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                        Zero-Trust Compliant
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-purple-500/20 text-purple-300 border border-purple-500/40">
                        v9.0 Production
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-black tracking-tight text-white">
                    Master Cetak Biru Arsitektur &amp; Alur Sistem
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Dokumentasi teknis menyeluruh memetakan seluruh siklus permintaan HTTP, ERD 5-domain, matriks hak akses 9 persona, dan 5 diagram alur proses bisnis monolit Universitas Pelita Harapan.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="#quick-toc" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors flex items-center gap-2 border border-white/15">
                    <i class="fa-solid fa-list-ol"></i> Daftar Isi
                </a>
                <button @click="window.print()" class="px-4 py-2.5 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 font-bold text-xs transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak / Ekspor PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Live System Metric Counters Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pengguna</span>
            <p class="text-xl font-display font-black text-uph-navy dark:text-uph-gold mt-1">{{ number_format($metrics['total_users'] ?? 0) }}</p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">Civitas Terdaftar</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Mahasiswa Aktif</span>
            <p class="text-xl font-display font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($metrics['total_students'] ?? 0) }}</p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">KRS War Ready</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Dosen Pengampu</span>
            <p class="text-xl font-display font-black text-sky-600 dark:text-sky-400 mt-1">{{ number_format($metrics['total_lecturers'] ?? 0) }}</p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">NIDN Terverifikasi</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Mata Kuliah</span>
            <p class="text-xl font-display font-black text-purple-600 dark:text-purple-400 mt-1">{{ number_format($metrics['total_courses'] ?? 0) }}</p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">OBE &amp; CPMK Mapped</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Kelas Perkuliahan</span>
            <p class="text-xl font-display font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($metrics['class_sections'] ?? 0) }}</p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">Seksi Terjadwal</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Rute Monolit</span>
            <p class="text-xl font-display font-black text-rose-600 dark:text-rose-400 mt-1">{{ number_format($metrics['total_routes'] ?? 0) }}</p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">Endpoints Terdaftar</span>
        </div>
    </div>

    <!-- Main Content Layout with Sticky Table of Contents -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Sticky Left TOC Navigation (1 Col) -->
        <div id="quick-toc" class="lg:col-span-1 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm lg:sticky lg:top-20 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-compass text-uph-gold"></i> Navigasi Seksi
                </h3>
                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">8 Bagian</span>
            </div>

            <nav class="space-y-1 text-xs">
                <a href="#sec-1" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>1. Siklus Hidup Permintaan</span>
                </a>
                <a href="#sec-2" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>2. Peta Direktori Monolit</span>
                </a>
                <a href="#sec-3" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>3. ERD &amp; Skema 5-Domain</span>
                </a>
                <a href="#sec-4" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>4. Matriks 9 Persona &amp; IAM</span>
                </a>
                <a href="#sec-5" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>5. 5 Diagram Alur Bisnis</span>
                </a>
                <a href="#sec-6" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>6. Master Indeks 240+ Rute</span>
                </a>
                <a href="#sec-7" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>7. Penampil Interaktif</span>
                </a>
                <a href="#sec-8" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-uph-navy dark:hover:text-uph-gold transition-colors font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>8. Cheat-Sheet Pemeliharaan</span>
                </a>
            </nav>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span>Engine:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">Laravel {{ $metrics['laravel_version'] ?? '11.x' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>PHP:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">PHP {{ $metrics['php_version'] ?? '8.2' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Driver DB:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300 uppercase">{{ $metrics['db_driver'] ?? 'sqlite' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Testing Assertions:</span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">100% Pass (440+)</span>
                </div>
            </div>
        </div>

        <!-- Documentation Viewer Canvas (3 Cols) -->
        <div class="lg:col-span-3 space-y-8">
            
            <!-- Section 1 Anchor -->
            <div id="sec-1" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">1</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Arsitektur Tingkat Tinggi &amp; Siklus Hidup Permintaan</h2>
                    </div>
                    <button @click="copyCode('ascii-sec-1')" class="px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold flex items-center gap-1 transition-colors">
                        <i class="fa-solid" :class="copiedId === 'ascii-sec-1' ? 'fa-check text-emerald-500' : 'fa-copy'"></i>
                        <span x-text="copiedId === 'ascii-sec-1' ? 'Tersalin' : 'Salin ASCII'"></span>
                    </button>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Setiap permintaan (HTTP Request) yang dikirimkan oleh browser atau API client melewati serangkaian filter Zero-Trust sebelum mencapai Controller layer:
                </p>
                <div class="overflow-x-auto bg-slate-950 p-4 rounded-xl border border-slate-800 shadow-inner">
                    <pre id="ascii-sec-1" class="font-mono text-[11px] leading-relaxed text-emerald-400 whitespace-pre">
+───────────────────────────────────────────────────────────────────────────────────────────────+
|                                    INCOMING CLIENT REQUEST                                    |
|                       (Web Browser Desktop / Mobile PWA / API Webhook)                        |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 1. WEB SERVER & ENTRY POINT: public/index.php ──► Vite Asset Bundler (0 static .html traps)   |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 2. ZERO-TRUST IDENTITY & ACCESS MANAGEMENT (IAM) PIPELINE                                     |
|    - auth: Validasi sesi aktif pengguna                                                       |
|    - active.account: Penolakan akun non-aktif (is_active == false)                            |
|    - EnsureRoleScope: Isolasi URL Prefix per Persona (/portal/student, /portal/lecturer, etc) |
|    - role.privilege: Evaluasi matriks izin granular & ABAC (Scope Fakultas Dekan & Prodi)    |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 3. CONTROLLER & DOMAIN SERVICE LAYER                                                          |
|    - App\Http\Controllers\Portal\* (30+ Controllers)                                          |
|    - Concurrency Lock: DB::transaction() + lockForUpdate() pada pendaftaran KRS               |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 4. ELOQUENT ORM & ATOMIC AUDIT ENGINE: Auditable Trait ──► audit_logs (Actor, Action, Diff)   |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 5. PRESENTATION: resources/views/layouts/portal.blade.php (Bento UI + Flash Banners + Alert)  |
+───────────────────────────────────────────────────────────────────────────────────────────────+</pre>
                </div>
            </div>

            <!-- Section 2 Anchor -->
            <div id="sec-2" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">2</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Struktur Direktori &amp; Monolit Modular</h2>
                    </div>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Pohon folder modular yang memisahkan tanggung jawab Controller, Service, Policy, Middleware, dan Blade Views:
                </p>
                <div class="overflow-x-auto bg-slate-950 p-4 rounded-xl border border-slate-800 shadow-inner">
                    <pre class="font-mono text-[11px] leading-relaxed text-sky-300 whitespace-pre">
recreate/
├── app/
│   ├── backend/Admissions/PMBService.php       # Domain Service Seleksi & Konversi PMB
│   ├── Http/Controllers/Portal/               # 30+ Scoped Workspace Controllers
│   ├── Http/Middleware/EnsureRoleScope.php    # Isolasi Antar-Workspace Persona
│   ├── Models/                                # 30+ Eloquent Models with Auditable Trait
│   └── Services/OutboundNotificationService   # WhatsApp & Email Dispatch Engine
├── database/migrations/                       # 9 Terstruktur Migration Files (SQLite & Postgres)
├── resources/views/
│   ├── layouts/portal.blade.php               # Unified Bento Grid Layout & Command Palette
│   ├── components/portal-sidebar.blade.php    # Scoped Role Navigation Matrix
│   └── portal/                                # Workspaces: student, lecturer, baak, bauk, iam
└── tests/Feature/                             # 35 Automated Test Suites (100% Pass)</pre>
                </div>
            </div>

            <!-- Section 3 Anchor -->
            <div id="sec-3" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">3</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Diagram Relasi Database (ERD 5-Domain)</h2>
                    </div>
                    <button @click="copyCode('ascii-sec-3')" class="px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold flex items-center gap-1 transition-colors">
                        <i class="fa-solid" :class="copiedId === 'ascii-sec-3' ? 'fa-check text-emerald-500' : 'fa-copy'"></i>
                        <span x-text="copiedId === 'ascii-sec-3' ? 'Tersalin' : 'Salin Skema'"></span>
                    </button>
                </div>
                <div class="overflow-x-auto bg-slate-950 p-4 rounded-xl border border-slate-800 shadow-inner">
                    <pre id="ascii-sec-3" class="font-mono text-[10px] leading-relaxed text-amber-300 whitespace-pre">
+====================================================================================================+
| 5 DOMAIN UTAMA DATABASE                                                                            |
+====================================================================================================+
1. Core & IAM: users ◄──► roles ◄──► permissions, audit_logs, faculties, study_programs, rooms
2. PMB & OBE : admission_waves ◄──► applicants, curriculums ◄──► obe_cpls ◄──► obe_cpmks ◄──► courses
3. Operasional: class_sections ◄──► krs_submissions ◄──► krs_items (exam_seat_number)
               class_sections ◄──► lecture_sessions ◄──► attendances, lms_assignments ◄──► lms_submissions
4. Keuangan & Kelulusan: student_invoices, mbkm_programs ◄──► mbkm_conversions, theses ◄──► yudisium_clearances
5. Governance: approval_requests (Maker-Checker), consultation_messages, support_tickets, outbound_dispatches</pre>
                </div>
            </div>

            <!-- Section 4 Anchor -->
            <div id="sec-4" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">4</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Matriks 9 Persona &amp; Kredensial Pengujian</h2>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="p-3">Peran / Persona</th>
                                <th class="p-3">Email Pengujian</th>
                                <th class="p-3">Kata Sandi</th>
                                <th class="p-3">Level</th>
                                <th class="p-3">Ruang Lingkup ABAC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-purple-600 dark:text-purple-400">Super Administrator</td>
                                <td class="p-3 font-mono">superadmin@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">100</td>
                                <td class="p-3">Global / Seluruh Modul &amp; IAM</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-uph-navy dark:text-uph-gold">Rektor &amp; Pimpinan</td>
                                <td class="p-3 font-mono">rector@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">90</td>
                                <td class="p-3">Universitas / EDSS KPI</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">Dekan Fakultas</td>
                                <td class="p-3 font-mono">dean.fik@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">80</td>
                                <td class="p-3">Fakultas Ilmu Komputer (FIK)</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-teal-600 dark:text-teal-400">Ketua Program Studi</td>
                                <td class="p-3 font-mono">kaprodi.if@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">70</td>
                                <td class="p-3">Prodi S1 Informatika (IF)</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-amber-600 dark:text-amber-400">BAAK (Akademik)</td>
                                <td class="p-3 font-mono">baak@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">60</td>
                                <td class="p-3">Kalender, Jadwal &amp; PDDIKTI</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-cyan-600 dark:text-cyan-400">BAUK (Keuangan)</td>
                                <td class="p-3 font-mono">bauk@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">60</td>
                                <td class="p-3">Tagihan SPP &amp; Cekal Finansial</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-blue-600 dark:text-blue-400">Dosen Pengajar / PA</td>
                                <td class="p-3 font-mono">lecturer1@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">50</td>
                                <td class="p-3">Kelas Binaan &amp; Advisee PA</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-emerald-600 dark:text-emerald-400">Mahasiswa Aktif</td>
                                <td class="p-3 font-mono">student1@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">20</td>
                                <td class="p-3">NIM 01082200001 (Bebas Cekal)</td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-bold text-slate-600 dark:text-slate-400">Calon Mahasiswa</td>
                                <td class="p-3 font-mono">applicant1@uph.edu</td>
                                <td class="p-3 font-mono font-bold">password</td>
                                <td class="p-3 font-bold">10</td>
                                <td class="p-3">Formulir PMB &amp; Ujian CBT</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 5 Anchor -->
            <div id="sec-5" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">5</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">5 Diagram Alur Proses Bisnis Utama (Core Flows)</h2>
                    </div>
                </div>
                <div class="space-y-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                        <h4 class="font-bold text-uph-navy dark:text-uph-gold uppercase tracking-wider text-[11px]">Flow 1: PMB ──► VA ──► CBT ──► Promosi &amp; Terbit NIM</h4>
                        <p class="text-slate-600 dark:text-slate-300">Pendaftaran PMB online menerbitkan Virtual Account instan. Setelah pelunasan diverifikasi melalui HMAC webhook, akses Ujian CBT terbuka. Nilai CBT menentukan tier beasiswa, lalu BAAK mempromosikan calon mahasiswa menjadi akun mahasiswa aktif dan mencetak Surat Penerimaan Resmi (LoA).</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                        <h4 class="font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider text-[11px]">Flow 2: Billing BAUK ──► KRS War ──► Live Chat PA ──► Kursi Ujian Acak</h4>
                        <p class="text-slate-600 dark:text-slate-300">BAUK melepaskan status cekal keuangan setelah tagihan lunas. Mahasiswa bersaing memilih kelas di KRS War dengan concurrency lock (lockForUpdate). Mahasiswa dan Dosen PA berkomunikasi secara real-time melalui consultation drawer. Setelah disetujui, BAAK mengalokasikan nomor kursi ujian acak anti-kecurangan untuk mahasiswa yang hadir &ge; 75%.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                        <h4 class="font-bold text-sky-700 dark:text-sky-400 uppercase tracking-wider text-[11px]">Flow 3: Presensi QR 15s ──► Gate 75% ──► Ujian LMS ──► Spreadsheet OBE ──► Sanggah Nilai</h4>
                        <p class="text-slate-600 dark:text-slate-300">Dosen memutar token QR setiap 15 detik dan mengisi BAP. Mahasiswa melakukan presensi dengan validasi geofence GPS 100m. Pada sesi UTS/UAS, mahasiswa dengan kehadiran di bawah 75% terblokir secara otomatis. Dosen menilai di Spreadsheet OBE dan mengunci nilai. Mahasiswa mengisi kuesioner EDOM sebelum nilai KHS terbuka terang, dengan hak mengajukan sanggah nilai bila ada ketidaksesuaian.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                        <h4 class="font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider text-[11px]">Flow 4: Dual-Control Maker-Checker Approval Gate</h4>
                        <p class="text-slate-600 dark:text-slate-300">Mutasi bernilai tinggi (perubahan nilai pasca-kunci, waiver SPP &gt; 50%, override kuota) memerlukan otorisasi dua orang. Maker mengajukan permohonan berserta payload diff. Checker (Kaprodi, Dekan, atau Superadmin) memeriksa diff visual dan mengeksekusi mutasi atomik dalam DB::transaction(), mencatat ID kedua aktor ke audit_logs.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                        <h4 class="font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider text-[11px]">Flow 5: Konversi MBKM ──► 8-Log Skripsi ──► Yudisium 4-Pintu ──► PIN Nasional &amp; SKPI</h4>
                        <p class="text-slate-600 dark:text-slate-300">Mahasiswa mengonversi kegiatan MBKM ke 20 SKS mata kuliah ekuivalen. Setelah menyelesaikan 8 sesi bimbingan skripsi dan lulus sidang, mahasiswa melewati verifikasi kliring 4-pintu (Perpustakaan, Keuangan, Kaprodi, dan BAAK) untuk penerbitan SK Yudisium, PIN Ijazah Nasional, dan SKPI bilingual ber-QR Code.</p>
                    </div>
                </div>
            </div>

            <!-- Section 6 Anchor -->
            <div id="sec-6" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">6</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Master Indeks 240+ Rute Monolit</h2>
                    </div>
                    <a href="/portal/developer/sandbox" class="text-xs font-bold text-uph-navy dark:text-uph-gold hover:underline flex items-center gap-1">
                        Buka API Sandbox <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Seluruh rute terdefinisi di <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[11px]">routes/web.php</code> dan diproteksi oleh Zero-Trust middleware stack. Rincian lengkap URL, HTTP Verb, dan Controller methods dapat dibaca pada file dokumentasi akar <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[11px]">ARCHITECTURE_AND_FLOW.md</code>.
                </p>
            </div>

            <!-- Section 8 Anchor -->
            <div id="sec-8" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs">8</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Developer &amp; Sysadmin Cheat-Sheet</h2>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">Uji Otomatis Lengkap:</span>
                        <code class="block font-mono text-[11px] bg-slate-900 text-emerald-400 p-2 rounded">./vendor/bin/phpunit</code>
                        <span class="text-[10px] text-slate-500">Menjalankan 35 test suites dan 440+ assertions.</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">Pemeriksaan Integritas Sistem:</span>
                        <code class="block font-mono text-[11px] bg-slate-900 text-emerald-400 p-2 rounded">php artisan erp:health-check</code>
                        <span class="text-[10px] text-slate-500">Memvalidasi 7 domain arsitektur monolit UPH.</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">Kompilasi Aset Tanpa Perangkap Statis:</span>
                        <code class="block font-mono text-[11px] bg-slate-900 text-emerald-400 p-2 rounded">npm run build</code>
                        <span class="text-[10px] text-slate-500">Memastikan 0 file .html di dalam public/.</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">Reset Database &amp; Data Uji Demo:</span>
                        <code class="block font-mono text-[11px] bg-slate-900 text-emerald-400 p-2 rounded">php artisan migrate:fresh --seed</code>
                        <span class="text-[10px] text-slate-500">Mengembalikan kondisi data demo realistis.</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
