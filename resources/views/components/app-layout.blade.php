<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-surface-slate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Unified University ERP & SIAKAD' }} - Universitas Pelita Harapan</title>
    <link rel="icon" href="data:,">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/font-awesome.min.css">

    <!-- Scripts and Styles via Vite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'uph-navy': '#00205B',
                        'uph-navy-dark': '#061B40',
                        'uph-crimson': '#C8102E',
                        'uph-gold': '#D4AF37',
                        'uph-gold-warm': '#FFB800',
                        'surface-slate': '#F8FAFC',
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800" x-data="{ sidebarOpen: false, searchOpen: false }">

    <!-- Global Command Search (Ctrl+K) -->
    <div x-show="searchOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 bg-slate-900/60 backdrop-blur-sm"
         @keydown.window.escape="searchOpen = false"
         @keydown.window.ctrl.k.prevent="searchOpen = true"
         style="display: none;">
        <div class="mx-auto max-w-xl transform rounded-xl bg-white p-4 shadow-2xl ring-1 ring-black/5" @click.away="searchOpen = false">
            <div class="relative">
                <i class="fa fa-search absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" class="h-11 w-full pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy" placeholder="Cari menu, KRS, mahasiswa, mata kuliah, jadwal... (Ctrl+K)">
            </div>
            <div class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-400 flex justify-between">
                <span>Navigasi cepat menggunakan tombol panah</span>
                <span>ESC untuk menutup</span>
            </div>
        </div>
    </div>

    <!-- Top Command Bar -->
    <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b-2 border-uph-gold bg-uph-navy-dark px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-white lg:hidden" @click="sidebarOpen = true">
            <i class="fa fa-bars text-xl"></i>
        </button>

        <div class="flex items-center gap-3">
            <a href="/">
                <img class="h-9 w-auto" src="/assets/uph/img/logo-uph-white.png" alt="UPH" onerror="this.src='/assets/uph/img/logo-uph-vertical.png'">
            </a>
            <div class="hidden sm:block pl-3 border-l border-white/20">
                <span class="text-xs font-bold text-white tracking-wider uppercase">SIAKAD & LMS TERPADU</span>
                <span class="block text-[10px] text-uph-gold-warm">Universitas Pelita Harapan</span>
            </div>
        </div>

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6 justify-end items-center">
            <button @click="searchOpen = true" class="hidden md:flex items-center gap-2 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-300 hover:bg-white/20 border border-white/10">
                <i class="fa fa-search"></i>
                <span>Cari Cepat...</span>
                <kbd class="rounded bg-black/30 px-1.5 py-0.5 text-[10px] font-semibold text-slate-300">Ctrl K</kbd>
            </button>

            <!-- Notification Bell -->
            <button class="relative p-2 text-slate-300 hover:text-white">
                <i class="fa fa-bell text-lg"></i>
                <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-uph-crimson"></span>
            </button>

            <!-- User Dropdown Menu -->
            <div class="flex items-center gap-3 pl-3 border-l border-white/20">
                <div class="h-9 w-9 rounded-full bg-uph-navy text-uph-gold border border-uph-gold flex items-center justify-center font-bold text-sm">
                    {{ substr(auth()->user()->full_name ?? 'UPH', 0, 2) }}
                </div>
                <div class="hidden sm:block text-left text-xs">
                    <p class="font-bold text-white">{{ auth()->user()->full_name ?? 'Civitas Akademika' }}</p>
                    <p class="text-slate-300 text-[11px]">{{ auth()->user()->roles->first()->name ?? 'Portal Terpadu' }}</p>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar Navigation -->
        <aside class="hidden lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-slate-200 bg-white min-h-[calc(100vh-4rem)]">
            {{ $sidebar ?? '' }}
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 p-6 lg:p-8 bg-surface-slate overflow-x-hidden">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
