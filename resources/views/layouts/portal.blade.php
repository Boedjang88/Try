<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'id') }}" class="h-full bg-surface-slate dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#00205B">

    <title>{{ $title ?? 'Portal SIAKAD & ERP Terpadu' }} - Universitas Pelita Harapan</title>

    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Early Dark Mode FOUC Prevention -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Tailwind & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'uph-navy': '#00205B',
                        'uph-navy-dark': '#0A2540',
                        'uph-crimson': '#C8102E',
                        'uph-crimson-hover': '#A00C24',
                        'uph-gold': '#D4AF37',
                        'uph-gold-warm': '#FFB800',
                        'surface': '#FFFFFF',
                        'surface-slate': '#F8FAFC',
                        'surface-border': '#E2E8F0',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                        display: ['Montserrat', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    boxShadow: {
                        'uph': '0 4px 20px -2px rgba(0, 32, 91, 0.08)',
                        'uph-lg': '0 10px 30px -4px rgba(0, 32, 91, 0.14)',
                    }
                }
            }
        }
    </script>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        /* Scrollbar aesthetics */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #F1F5F9; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        
        /* Print media enforcement: clean black-on-white */
        @media print {
            body, .dark body { background: #ffffff !important; color: #000000 !important; }
            header, nav, aside, #enterprise-toast-container, #demo-account-switcher, button { display: none !important; }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-surface-slate" 
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: false,
          searchOpen: false,
          searchQuery: '',
          searchIndex: 0,
          searchResults: [
              { title: 'KRS War & Pemilihan Jadwal', url: '/krs', group: 'Akademik', icon: 'fa-calendar-check' },
              { title: 'Presensi QR Code 15 Detik', url: '/attendance/scan', group: 'Perkuliahan', icon: 'fa-qrcode' },
              { title: 'LMS Perkuliahan & Tugas 16-Sesi', url: '/lms', group: 'Perkuliahan', icon: 'fa-book-open' },
              { title: 'Spreadsheet Penilaian Nilai Huruf & OBE', url: '/grading', group: 'Dosen', icon: 'fa-table-cells' },
              { title: 'KHS & Transkrip Akademik (EWS)', url: '/khs', group: 'Akademik', icon: 'fa-file-invoice' },
              { title: 'Evaluasi Dosen Oleh Mahasiswa (EDOM)', url: '/edom', group: 'Akademik', icon: 'fa-clipboard-check' },
              { title: 'Tagihan UKT & Virtual Account', url: '/finance/invoices', group: 'Keuangan', icon: 'fa-credit-card' },
              { title: 'Skripsi & Logbook Bimbingan', url: '/thesis', group: 'Tugas Akhir', icon: 'fa-graduation-cap' },
              { title: 'Verifikasi Yudisium & SKPI Digital', url: '/yudisium', group: 'Kelulusan', icon: 'fa-award' },
              { title: 'Pemetaan Kurikulum OBE, CPL & CPMK', url: '/curriculum/obe', group: 'Kaprodi', icon: 'fa-diagram-project' },
              { title: 'Jadwal Kuliah & Deteksi Bentrok', url: '/timetable', group: 'BAAK', icon: 'fa-clock' },
              { title: 'PMB & Ujian CBT Online', url: '/pmb/cbt', group: 'Admissions', icon: 'fa-laptop-code' },
              { title: 'Sinkronisasi PDDIKTI Neo Feeder', url: '/pddikti/sync', group: 'Admin', icon: 'fa-cloud-arrow-up' },
              { title: 'Dashboard Eksekutif Rektorat (KPI)', url: '/rector/dashboard', group: 'Rektorat', icon: 'fa-chart-pie' }
          ],
          get filteredResults() {
              if (!this.searchQuery) return this.searchResults.slice(0, 8);
              return this.searchResults.filter(item => 
                  item.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                  item.group.toLowerCase().includes(this.searchQuery.toLowerCase())
              );
          },
          toggleDarkMode() {
              if (document.documentElement.classList.contains('dark')) {
                  document.documentElement.classList.remove('dark');
                  localStorage.theme = 'light';
              } else {
                  document.documentElement.classList.add('dark');
                  localStorage.theme = 'dark';
              }
          },
          handleKeyShortcuts(e) {
              if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || e.target.isContentEditable) {
                  return;
              }
              if (e.altKey && (e.key === 'd' || e.key === 'D')) {
                  e.preventDefault();
                  window.location.href = '/portal';
              } else if (e.altKey && (e.key === 't' || e.key === 'T')) {
                  e.preventDefault();
                  this.toggleDarkMode();
              } else if (e.altKey && (e.key === 's' || e.key === 'S')) {
                  e.preventDefault();
                  window.dispatchEvent(new CustomEvent('toggle-demo-control'));
              } else if (e.key === '?' || (e.key === '/' && !e.ctrlKey && !e.altKey)) {
                  e.preventDefault();
                  window.dispatchEvent(new CustomEvent('open-shortcuts'));
              }
          }
      }"
      @keydown.window="handleKeyShortcuts($event)"
      @keydown.window.ctrl.k.prevent="searchOpen = true; $nextTick(() => $refs.searchInput.focus())"
      @keydown.window.escape="searchOpen = false">

    <!-- Global Command Palette Search Modal (Ctrl+K) -->
    <div x-show="searchOpen" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 bg-slate-900/60 backdrop-blur-sm flex justify-center items-start"
         x-cloak>
        <div class="w-full max-w-2xl transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 overflow-hidden divide-y divide-slate-100" 
             @click.away="searchOpen = false">
            <div class="relative flex items-center px-4 py-3 bg-slate-50">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-lg mr-3"></i>
                <input type="text" 
                       x-ref="searchInput"
                       x-model="searchQuery"
                       @keydown.arrow-down.prevent="searchIndex = Math.min(searchIndex + 1, filteredResults.length - 1)"
                       @keydown.arrow-up.prevent="searchIndex = Math.max(searchIndex - 1, 0)"
                       @keydown.enter.prevent="if (filteredResults[searchIndex]) window.location.href = filteredResults[searchIndex].url"
                       class="h-11 w-full bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none" 
                       placeholder="Cari menu, KRS, mahasiswa, jadwal, nilai, tagihan... (Ketik atau gunakan panah)">
                <kbd class="hidden sm:inline-block rounded bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-600">ESC</kbd>
            </div>
            
            <div class="max-h-96 overflow-y-auto p-2 space-y-1">
                <template x-for="(res, index) in filteredResults" :key="index">
                    <a :href="res.url" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-colors"
                       :class="index === searchIndex ? 'bg-uph-navy text-white' : 'hover:bg-slate-100 text-slate-700'"
                       @mouseenter="searchIndex = index">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center text-sm"
                                 :class="index === searchIndex ? 'bg-white/20 text-uph-gold' : 'bg-slate-100 text-uph-navy'">
                                <i class="fa-solid" :class="res.icon"></i>
                            </div>
                            <span class="font-semibold text-sm" x-text="res.title"></span>
                        </div>
                        <span class="text-[11px] uppercase tracking-wider px-2 py-0.5 rounded font-mono font-bold"
                              :class="index === searchIndex ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" 
                              x-text="res.group"></span>
                    </a>
                </template>
                <div x-show="filteredResults.length === 0" class="py-12 text-center text-slate-400 text-xs">
                    <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                    Tidak ada menu atau halaman yang cocok dengan "<span x-text="searchQuery"></span>"
                </div>
            </div>

            <div class="px-4 py-2.5 bg-slate-50 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Navigasi: <kbd class="px-1.5 py-0.5 rounded bg-white border text-slate-600 font-mono text-[10px]">&uarr;</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white border text-slate-600 font-mono text-[10px]">&darr;</kbd> &bull; Pilih: <kbd class="px-1.5 py-0.5 rounded bg-white border text-slate-600 font-mono text-[10px]">&crarr;</kbd></span>
                <span>SIAKAD & LMS Unified Enterprise Monolith</span>
            </div>
        </div>
    </div>

    <!-- Mobile Off-Canvas Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm lg:hidden"
         @click="sidebarOpen = false"
         x-cloak>
    </div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Container (Desktop Collapsible & Mobile Off-Canvas) -->
        <!-- Dynamic Per-Role Scoped Sidebar -->
        <x-portal-sidebar />

        <!-- Right Main Workspace Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Command Bar: Glassmorphic Sticky Header with Dynamic Breadcrumbs -->
            <header class="h-16 shrink-0 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-[0_1px_3px_0_rgba(0,0,0,0.03)] z-30 transition-colors">
                <!-- Left: Mobile Toggle & Page Title / Breadcrumbs -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <button @click="sidebarOpen = true" class="p-2 -ml-2 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden transition-colors" aria-label="Buka Menu Navigasi">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <!-- Breadcrumbs & Contextual Status -->
                    <div class="flex items-center gap-2.5">
                        <nav class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                            <a href="/portal" class="hover:text-uph-navy dark:hover:text-uph-gold transition-colors flex items-center gap-1">
                                <i class="fa-solid fa-house text-[11px]"></i>
                                <span>Portal</span>
                            </a>
                            <i class="fa-solid fa-chevron-right text-[8px] text-slate-300 dark:text-slate-600"></i>
                            <span class="text-slate-900 dark:text-slate-100 font-semibold tracking-tight">{{ $title ?? 'SIAKAD & ERP' }}</span>
                        </nav>

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-uph-navy/5 text-uph-navy dark:bg-uph-gold/10 dark:text-uph-gold border border-uph-navy/15 dark:border-uph-gold/25">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            T.A. 2026/2027 Ganjil &bull; Aktif
                        </span>
                    </div>
                </div>

                <!-- Right: Ctrl+K Quick Search, Notifications, Dark Mode, Shortcuts, User Menu -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Global Search Pill -->
                    <button @click="searchOpen = true" class="hidden sm:flex items-center gap-3 px-3.5 py-1.5 rounded-lg bg-slate-100/80 hover:bg-slate-200/80 dark:bg-slate-800/80 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-slate-700/80 text-slate-500 dark:text-slate-400 text-xs transition-colors">
                        <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                        <span>Cari menu, KRS, mahasiswa...</span>
                        <kbd class="rounded bg-white dark:bg-slate-900 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300 shadow-xs border border-slate-200 dark:border-slate-700 font-mono">Ctrl K</kbd>
                    </button>

                    <!-- Dark Mode Toggle Button (Alt+T) -->
                    <button @click="toggleDarkMode()" 
                            class="p-2 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-uph-gold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" 
                            title="Ganti Mode Gelap / Terang (Alt+T)">
                        <i class="fa-solid fa-moon dark:hidden text-sm"></i>
                        <i class="fa-solid fa-sun hidden dark:inline text-sm text-uph-gold"></i>
                    </button>

                    <!-- Keyboard Shortcuts Guide Button (?) -->
                    <button @click="$dispatch('open-shortcuts')" 
                            class="p-2 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-uph-gold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" 
                            title="Panduan Pintasan Keyboard (?)">
                        <i class="fa-solid fa-keyboard text-sm"></i>
                    </button>

                    <!-- Real-Time Activity Notification Bell -->
                    <x-notification-bell />

                    <!-- Profile Dropdown & Logout -->
                    <div class="relative flex items-center gap-3 pl-3 border-l border-slate-200/80 dark:border-slate-800/80" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 focus:outline-none">
                            <div class="h-8 w-8 rounded-full bg-uph-navy text-uph-gold border border-uph-gold/50 flex items-center justify-center font-bold text-xs shadow-xs ring-1 ring-uph-gold/20">
                                {{ strtoupper(substr(auth()->user()->full_name ?? 'UP', 0, 2)) }}
                            </div>
                            <div class="hidden md:block text-left text-xs">
                                <p class="font-bold text-slate-900 dark:text-white leading-tight">{{ auth()->user()->full_name ?? 'Civitas Akademika' }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">{{ auth()->user()->role ?? 'User' }}</p>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div x-show="userMenuOpen" 
                             @click.away="userMenuOpen = false"
                             class="absolute right-0 top-11 w-48 rounded-xl bg-white dark:bg-slate-900 shadow-2xl ring-1 ring-black/10 dark:ring-white/10 py-1.5 z-50 text-xs border border-slate-200/80 dark:border-slate-800/80"
                             x-cloak>
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                <p class="font-bold text-slate-900 dark:text-white">{{ auth()->user()->full_name ?? 'Civitas Akademika' }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ auth()->user()->email ?? 'civitas@uph.edu' }}</p>
                            </div>
                            <a href="/portal/profile" class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="fa-solid fa-user-circle text-slate-400"></i> Profil Saya
                            </a>
                            <a href="/portal/account/password" class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="fa-solid fa-key text-uph-gold"></i> Ganti Kata Sandi (IAM)
                            </a>
                            <a href="/portal/account/security" class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="fa-solid fa-user-shield text-emerald-500"></i> Keamanan Akun
                            </a>
                            <a href="/portal/audit-logs" class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Riwayat Aktivitas
                            </a>
                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-uph-crimson font-bold hover:bg-red-50 dark:hover:bg-red-950/40 text-left">
                                    <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Content Canvas with Animation & Mobile Bottom Padding -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-surface-slate dark:bg-slate-950 pb-24 lg:pb-8 transition-colors">
                <div class="max-w-7xl mx-auto space-y-6 animate-fade-in">
                    {{-- Global Flash & Validation Error Banner (Stage 17 Self-Healing) --}}
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                                    <i class="fa-solid fa-circle-check text-base"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-100">Operasi Berhasil</h4>
                                    <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 p-1.5 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-rose-100 dark:bg-rose-900/60 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-900 dark:text-rose-100">Perhatian / Kendala</h4>
                                    <p class="text-xs font-medium mt-0.5">{{ session('error') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 p-1.5 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-100">Peringatan Sistem</h4>
                                    <p class="text-xs font-medium mt-0.5">{{ session('warning') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-200 p-1.5 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div x-data="{ show: true }" x-show="show" x-transition class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 shadow-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-rose-900 dark:text-rose-100">
                                    <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                                    <span>Terdapat Kesalahan Input ({{ $errors->count() }})</span>
                                </div>
                                <button @click="show = false" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 p-1">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>
                            <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 dark:text-rose-300">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>

            <!-- Mobile Fixed Bottom Navigation Bar (< 1024px Screens) for 1-Thumb Ergonomics -->
            <nav id="mobile-bottom-nav" 
                 class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/80 dark:border-slate-800/80 px-4 py-2 flex items-center justify-around text-slate-500 dark:text-slate-400 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
                <a href="/portal" 
                   class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center transition-colors {{ request()->is('portal') || request()->is('portal/*/dashboard') ? 'text-uph-navy dark:text-uph-gold font-bold' : 'hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="fa-solid fa-house text-base"></i>
                    <span class="text-[10px] tracking-tight">Beranda</span>
                </a>

                <a href="/krs" 
                   class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center transition-colors {{ request()->is('krs*') ? 'text-uph-navy dark:text-uph-gold font-bold' : 'hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="fa-solid fa-calendar-check text-base"></i>
                    <span class="text-[10px] tracking-tight">Jadwal/KRS</span>
                </a>

                <a href="/attendance/scan" 
                   class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center transition-colors {{ request()->is('attendance*') ? 'text-uph-crimson font-bold' : 'hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="h-9 w-9 -mt-4 rounded-full bg-uph-crimson text-white flex items-center justify-center shadow-lg ring-4 ring-white dark:ring-slate-900">
                        <i class="fa-solid fa-qrcode text-base"></i>
                    </div>
                    <span class="text-[10px] tracking-tight">Presensi</span>
                </a>

                <a href="/portal/profile" 
                   class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center transition-colors {{ request()->is('portal/profile*') || request()->is('portal/account*') ? 'text-uph-navy dark:text-uph-gold font-bold' : 'hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="fa-solid fa-id-card text-base"></i>
                    <span class="text-[10px] tracking-tight">KTM/Akun</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Global Floating Enterprise Toast Notification Stack -->
    <div id="enterprise-toast-container" 
         class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"
         x-data="{ toasts: [] }"
         @toast.window="toasts.push($event.detail); setTimeout(() => { toasts.shift() }, $event.detail.duration || 4000)">
        <template x-for="(t, index) in toasts" :key="index">
            <div class="pointer-events-auto p-4 rounded-xl shadow-2xl border text-xs flex items-start gap-3 transform transition-all duration-300"
                 :class="{
                    'bg-slate-900 text-white border-uph-gold': t.type === 'info',
                    'bg-emerald-950 text-emerald-100 border-emerald-500': t.type === 'success',
                    'bg-red-950 text-red-100 border-uph-crimson': t.type === 'error',
                    'bg-amber-950 text-amber-100 border-amber-500': t.type === 'warning'
                 }">
                <i class="fa-solid mt-0.5 text-sm" :class="{
                    'fa-circle-info text-uph-gold': t.type === 'info',
                    'fa-circle-check text-emerald-400': t.type === 'success',
                    'fa-circle-xmark text-uph-crimson': t.type === 'error',
                    'fa-triangle-exclamation text-amber-400': t.type === 'warning'
                }"></i>
                <div class="flex-1">
                    <p class="font-bold uppercase tracking-wider text-[11px]" x-text="t.title || 'Sistem Terpadu'"></p>
                    <p class="text-slate-300 mt-0.5 text-xs" x-text="t.message"></p>
                </div>
            </div>
        </template>
    </div>

    <!-- Instant 1-Click Demo Account Switcher (Stage 8) -->
    <x-account-switcher />

    <!-- Power-User Keyboard Shortcuts Modal (Stage 13) -->
    <x-keyboard-shortcuts-modal />

</body>
</html>
