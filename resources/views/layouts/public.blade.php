<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'id') }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Universitas Pelita Harapan' }} | Faith, Knowledge, Godly Character</title>
    <meta name="description" content="Universitas Pelita Harapan (UPH) - Universitas Kristen unggul berstandar global dengan kurikulum Outcome-Based Education (OBE) dan ekosistem akademik terpadu.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Tailwind & Alpine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
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
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        display: ['Montserrat', '"Playfair Display"', 'serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                    boxShadow: {
                        'uph': '0 4px 20px -2px rgba(0, 32, 91, 0.08)',
                        'uph-lg': '0 10px 30px -4px rgba(0, 32, 91, 0.14)',
                        'uph-crimson': '0 4px 20px -2px rgba(200, 16, 46, 0.25)',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .glassmorphism-header {
            background: rgba(10, 37, 64, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans bg-surface-slate text-slate-800 antialiased selection:bg-uph-gold selection:text-uph-navy-dark" x-data="{ mobileMenuOpen: false, acadDropdownOpen: false, pmbDropdownOpen: false }">

    <!-- Top Prestige & Accreditation Banner -->
    <div class="bg-uph-navy text-white text-xs py-2 px-4 border-b border-uph-gold/30">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-uph-gold/20 text-uph-gold-warm font-semibold text-[11px] border border-uph-gold/30">
                    <i class="fa-solid fa-award"></i> Akreditasi UNGGUL BAN-PT
                </span>
                <span class="hidden sm:inline text-slate-300">Pendaftaran Mahasiswa Baru (PMB) T.A. 2026/2027 Telah Dibuka</span>
            </div>
            <div class="flex items-center gap-5 text-[11px] text-slate-200">
                <a href="https://wa.me/628111632838" target="_blank" class="hover:text-uph-gold transition-colors flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp text-emerald-400"></i> Hubungi Konselor: 0811-1632-838
                </a>
                <span class="text-white/20">|</span>
                <a href="/portal/login" class="hover:text-uph-gold font-semibold transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-lock text-[10px] text-uph-gold"></i> Single Sign-On (SSO)
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Sticky Glassmorphic) -->
    <header class="sticky top-0 z-40 glassmorphism-header border-b border-white/10 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <div class="flex items-center gap-4">
                    <a href="/" class="flex items-center gap-3 group">
                        <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-uph-navy to-uph-navy-dark border-2 border-uph-gold flex items-center justify-center text-uph-gold font-serif font-black text-2xl shadow-md group-hover:scale-105 transition-transform">
                            U
                        </div>
                        <div class="flex flex-col">
                            <span class="font-display font-extrabold text-lg tracking-wider text-white group-hover:text-uph-gold transition-colors">
                                UNIVERSITAS PELITA HARAPAN
                            </span>
                            <span class="text-[11px] font-medium tracking-widest text-slate-300 uppercase">
                                Faith &bull; Knowledge &bull; Godly Character
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-200">
                    <a href="/" class="hover:text-uph-gold transition-colors py-2 border-b-2 border-transparent hover:border-uph-gold">
                        Beranda
                    </a>

                    <!-- Program Akademik & Fakultas Mega-Dropdown -->
                    <div class="relative" @mouseenter="acadDropdownOpen = true" @mouseleave="acadDropdownOpen = false">
                        <button class="flex items-center gap-1.5 hover:text-uph-gold transition-colors py-2">
                            <span>Akademik & Fakultas</span>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': acadDropdownOpen }"></i>
                        </button>
                        <div x-show="acadDropdownOpen" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="absolute left-0 w-96 rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 p-4 mt-1 z-50 text-slate-800 border border-slate-100"
                             x-cloak>
                            <div class="space-y-2">
                                <a href="/academic/undergraduate" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                                    <div class="p-2.5 rounded-xl bg-uph-navy/10 text-uph-navy font-bold text-sm shrink-0"><i class="fa-solid fa-graduation-cap"></i></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-xs text-uph-navy">Sarjana Reguler (S1)</p>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold font-mono bg-emerald-100 text-emerald-800">UNGGUL</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">14 Fakultas & Kurikulum Outcome-Based Education (OBE)</p>
                                    </div>
                                </a>
                                <a href="/academic/pascasarjana" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                                    <div class="p-2.5 rounded-xl bg-uph-gold/15 text-uph-gold-warm font-bold text-sm shrink-0"><i class="fa-solid fa-user-graduate"></i></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-xs text-uph-navy">Pascasarjana (S2 & S3)</p>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold font-mono bg-emerald-100 text-emerald-800">INTERNASIONAL</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Magister Manajemen, Magister Hukum, Doktoral</p>
                                    </div>
                                </a>
                                <a href="/academic/kelas-karyawan" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 font-bold text-sm shrink-0"><i class="fa-solid fa-briefcase"></i></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-xs text-uph-navy">Kelas Karyawan & Eksekutif</p>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold font-mono bg-blue-100 text-blue-800">FLEXIBLE</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Blended learning untuk praktisi dan profesional</p>
                                    </div>
                                </a>
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] px-2 text-slate-500">
                                <span>Akreditasi BAN-PT & Internasional</span>
                                <a href="/academic/programs" class="font-bold text-uph-crimson hover:underline">Semua Prodi &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <a href="/admissions/pmb" class="hover:text-uph-gold transition-colors py-2">
                        Penerimaan (PMB)
                    </a>
                    <a href="/academic/curriculum" class="hover:text-uph-gold transition-colors py-2">
                        Kurikulum OBE
                    </a>
                    <a href="/events" class="hover:text-uph-gold transition-colors py-2">
                        Agenda & Berita
                    </a>
                </nav>

                <!-- Action CTAs -->
                <div class="hidden lg:flex items-center gap-3">
                    @auth
                        <a href="/portal" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider shadow transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="fa-solid fa-gauge"></i> Return to My Workspace ({{ auth()->user()->primary_role ?? auth()->user()->role }})
                        </a>
                    @else
                        <a href="/pmb/register" class="px-5 py-2.5 rounded-lg bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-file-signature mr-1.5"></i> Apply Now / PMB Online
                        </a>
                        <a href="/login" class="px-4 py-2.5 rounded-lg border border-uph-gold text-uph-gold hover:bg-uph-gold hover:text-uph-navy-dark font-bold text-xs uppercase tracking-wider transition-all">
                            <i class="fa-solid fa-right-to-bracket mr-1"></i> MyUPH Portal Login
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="lg:hidden flex items-center gap-2">
                    @auth
                        <a href="/portal" class="px-3 py-1.5 rounded-md bg-emerald-600 text-white font-bold text-xs">
                            Workspace
                        </a>
                    @else
                        <a href="/login" class="px-3 py-1.5 rounded-md bg-uph-crimson text-white font-bold text-xs">
                            Portal
                        </a>
                    @endauth
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2.5 rounded-lg text-slate-200 hover:text-white hover:bg-white/10 transition-colors focus:outline-none">
                        <i class="fa-solid fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                        <i class="fa-solid fa-xmark text-xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden bg-uph-navy-dark border-b border-white/10 px-4 pt-3 pb-6 space-y-3"
             x-cloak>
            <a href="/" class="block px-3 py-2 rounded-md font-semibold text-white hover:bg-white/10">Beranda</a>
            <a href="/academic/undergraduate" class="block px-3 py-2 rounded-md font-semibold text-slate-200 hover:bg-white/10">Program Sarjana (S1)</a>
            <a href="/academic/pascasarjana" class="block px-3 py-2 rounded-md font-semibold text-slate-200 hover:bg-white/10">Program Pascasarjana (S2 & S3)</a>
            <a href="/academic/kelas-karyawan" class="block px-3 py-2 rounded-md font-semibold text-slate-200 hover:bg-white/10">Kelas Karyawan</a>
            <a href="/admissions/pmb" class="block px-3 py-2 rounded-md font-semibold text-slate-200 hover:bg-white/10">Penerimaan Mahasiswa Baru</a>
            <a href="/academic/curriculum" class="block px-3 py-2 rounded-md font-semibold text-slate-200 hover:bg-white/10">Kurikulum OBE & CPL</a>
            <a href="/events" class="block px-3 py-2 rounded-md font-semibold text-slate-200 hover:bg-white/10">Agenda & Berita</a>
            <div class="pt-4 border-t border-white/10 space-y-2">
                @auth
                    <a href="/portal" class="w-full block text-center py-2.5 rounded-lg bg-emerald-600 text-white font-bold text-sm">
                        Return to My Workspace ({{ auth()->user()->primary_role ?? auth()->user()->role }})
                    </a>
                @else
                    <a href="/pmb/register" class="w-full block text-center py-2.5 rounded-lg bg-uph-crimson text-white font-bold text-sm">
                        Apply Now / PMB Online
                    </a>
                    <a href="/login" class="w-full block text-center py-2.5 rounded-lg border border-uph-gold text-uph-gold font-bold text-sm">
                        MyUPH Portal Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Workspace Body -->
    <main class="flex-grow">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Authoritative Global Footer -->
    <footer class="bg-uph-navy-dark text-slate-300 border-t-4 border-uph-gold mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <!-- Col 1: University Identity -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded bg-uph-navy border border-uph-gold flex items-center justify-center text-uph-gold font-serif font-black text-xl">
                            U
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-white text-base">UNIVERSITAS PELITA HARAPAN</h3>
                            <p class="text-[10px] text-uph-gold uppercase tracking-widest">Global Campus Network</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Mendidik para pemimpin masa depan yang berakar pada Iman kepada Kristus, berpengetahuan tinggi, dan memiliki karakter ilahi untuk melayani bangsa dan dunia.
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-white/10 text-uph-gold text-xs font-bold border border-uph-gold/30">
                            <i class="fa-solid fa-check-double text-[10px]"></i> BAN-PT: UNGGUL
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-white/10 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                            <i class="fa-solid fa-shield-halved text-[10px]"></i> ISO 9001:2015
                        </span>
                    </div>
                </div>

                <!-- Col 2: Program Studi -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4 border-b border-white/10 pb-2">
                        Fakultas & Sekolah
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li><a href="/academic/undergraduate" class="hover:text-uph-gold transition-colors">Fakultas Ilmu Komputer (Informatika & SI)</a></li>
                        <li><a href="/academic/undergraduate" class="hover:text-uph-gold transition-colors">Fakultas Kedokteran & Ilmu Kesehatan</a></li>
                        <li><a href="/academic/undergraduate" class="hover:text-uph-gold transition-colors">Fakultas Hukum (Business & International Law)</a></li>
                        <li><a href="/academic/undergraduate" class="hover:text-uph-gold transition-colors">School of Business (Akuntansi & Manajemen)</a></li>
                        <li><a href="/academic/undergraduate" class="hover:text-uph-gold transition-colors">School of Design (Arsitektur & DKV)</a></li>
                        <li><a href="/academic/undergraduate" class="hover:text-uph-gold transition-colors">Fakultas Ilmu Pendidikan (Teachers College)</a></li>
                    </ul>
                </div>

                <!-- Col 3: Kampus Network -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4 border-b border-white/10 pb-2">
                        Kampus Kami
                    </h4>
                    <ul class="space-y-3 text-xs text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-uph-gold mt-1"></i>
                            <div>
                                <strong class="text-white">Kampus Utama Lippo Village</strong>
                                <p class="text-slate-400 text-[11px]">Jl. M.H. Thamrin Boulevard 1100, Lippo Village, Tangerang 15811</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-uph-gold mt-1"></i>
                            <div>
                                <strong class="text-white">Kampus Medan</strong>
                                <p class="text-slate-400 text-[11px]">Lippo Plaza Medan Lt. 5-7, Jl. Imam Bonjol No. 6, Medan</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-uph-gold mt-1"></i>
                            <div>
                                <strong class="text-white">Kampus Surabaya</strong>
                                <p class="text-slate-400 text-[11px]">The Samator, Jl. Raya Kedung Baruk No. 26-28, Surabaya</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Layanan & Helpdesk -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4 border-b border-white/10 pb-2">
                        Layanan Terpadu
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-300 mb-5">
                        <li><a href="/portal/login" class="hover:text-uph-gold transition-colors">SIAKAD & KRS Online</a></li>
                        <li><a href="/portal/login" class="hover:text-uph-gold transition-colors">LMS Pembelajaran Daring</a></li>
                        <li><a href="/portal/login" class="hover:text-uph-gold transition-colors">Perpustakaan Digital Johannes Oentoro</a></li>
                        <li><a href="/admissions/pmb" class="hover:text-uph-gold transition-colors">One-Stop Admission Center</a></li>
                    </ul>
                    <div class="flex items-center gap-3">
                        <a href="https://instagram.com/uphimpactslives" target="_blank" class="h-8 w-8 rounded-full bg-white/10 hover:bg-uph-crimson flex items-center justify-center text-white transition-colors">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="https://youtube.com/uphimpactslives" target="_blank" class="h-8 w-8 rounded-full bg-white/10 hover:bg-uph-crimson flex items-center justify-center text-white transition-colors">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                        <a href="https://linkedin.com/school/universitas-pelita-harapan" target="_blank" class="h-8 w-8 rounded-full bg-white/10 hover:bg-uph-crimson flex items-center justify-center text-white transition-colors">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-400 gap-4">
                <p>&copy; {{ date('Y') }} Universitas Pelita Harapan. All Rights Reserved. Enterprise Monolith SIAKAD & ERP Architecture.</p>
                <div class="flex gap-6">
                    <a href="/privacy" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                    <a href="/terms" class="hover:text-white transition-colors">Syarat & Ketentuan</a>
                    <a href="/pddikti-status" class="hover:text-white transition-colors">Sinkronisasi PDDIKTI</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Enterprise Toast Notification Stack -->
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
                    <p class="font-bold uppercase tracking-wider text-[11px]" x-text="t.title || 'Notifikasi'"></p>
                    <p class="text-slate-300 mt-0.5 text-xs" x-text="t.message"></p>
                </div>
            </div>
        </template>
    </div>

</body>
</html>
