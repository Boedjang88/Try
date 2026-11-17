@extends('layouts.public')

@section('title', 'Masuk Portal Akademik & ERP - Universitas Pelita Harapan')

@section('content')
<div class="min-h-[90vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-950 transition-colors">
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-12 rounded-3xl overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        
        <!-- Left Column: Prestigious UPH Navy & Gold Architectural Visual -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#00205B] via-[#061B40] to-[#040E20] p-8 sm:p-12 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Architectural Background Pattern -->
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="arch-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#D4AF37" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#arch-grid)" />
                </svg>
            </div>

            <!-- Top Brand -->
            <div class="relative z-10 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-uph-gold/40 flex items-center justify-center font-black font-display text-xl text-uph-gold shadow-lg">
                        UPH
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-uph-gold block">
                            Universitas Pelita Harapan
                        </span>
                        <h1 class="text-sm font-bold tracking-tight text-white font-display">
                            SIAKAD &amp; ERP Enterprise
                        </h1>
                    </div>
                </div>

                <div class="pt-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-uph-gold/15 text-uph-gold border border-uph-gold/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Zero-Trust Unified Identity Gate
                    </span>
                    <h2 class="mt-4 text-2xl sm:text-3xl font-display font-black text-white leading-tight">
                        Pintu Gerbang Akademik &amp; Tata Kelola Kampus
                    </h2>
                    <p class="mt-3 text-xs text-slate-300 leading-relaxed">
                        Akses aman terpusat untuk Mahasiswa, Dosen, Pimpinan Fakultas, Rektorat, dan Staf Administrasi Akademik.
                    </p>
                </div>
            </div>

            <!-- Operational Highlights -->
            <div class="relative z-10 pt-8 border-t border-white/10 space-y-3 text-xs">
                <div class="flex items-center gap-3 text-slate-300">
                    <i class="fa-solid fa-id-card-clip text-uph-gold text-sm w-4"></i>
                    <span>Login via <strong>Email Resmi</strong>, <strong>NIM</strong>, atau <strong>NIDN/NIP</strong></span>
                </div>
                <div class="flex items-center gap-3 text-slate-300">
                    <i class="fa-solid fa-fingerprint text-uph-gold text-sm w-4"></i>
                    <span>Otentikasi Multi-Peran dengan Perlindungan Session Hijacking</span>
                </div>
                <div class="flex items-center gap-3 text-slate-300">
                    <i class="fa-solid fa-lock text-uph-gold text-sm w-4"></i>
                    <span>Tersertifikasi Audit Keamanan &amp; Standar PDDIKTI Feeder</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Authentication Form & Quick-Fill Demo Card -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between" 
             x-data="{ 
                 identifier: '{{ old('identifier', old('email')) }}', 
                 password: '',
                 quickFillOpen: true,
                 forgotPasswordModal: false,
                 quickFill(id, pwd) {
                     this.identifier = id;
                     this.password = pwd;
                     this.$nextTick(() => {
                         this.$refs.loginForm.submit();
                     });
                 }
             }">

            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-display font-black text-slate-900 dark:text-white tracking-tight">
                            Masuk ke Akun Anda
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Gunakan kredensial resmi UPH untuk membuka workspace persona Anda.
                        </p>
                    </div>
                    <span class="h-2 w-2 rounded-full bg-emerald-500" title="Sistem Aktif"></span>
                </div>

                <!-- Flash & Error Alerts -->
                @if(session('info'))
                    <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-xs text-rose-800 dark:text-rose-200 space-y-1">
                        <div class="flex items-center gap-2 font-bold text-rose-600 dark:text-rose-400">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>Autentikasi Gagal:</span>
                        </div>
                        <p class="pl-5 text-[11px] leading-relaxed">{{ $errors->first() }}</p>
                    </div>
                @endif

                <!-- Official Login Form -->
                <form action="/login" method="POST" class="space-y-4" x-ref="loginForm">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Email Resmi UPH / NIM / NIDN
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-solid fa-user-shield text-xs"></i>
                            </span>
                            <input type="text" name="identifier" required x-model="identifier"
                                   class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 dark:focus:ring-uph-gold/30 focus:border-uph-navy dark:focus:border-uph-gold transition-colors"
                                   placeholder="nama@uph.edu atau NIM 01082200001 atau NIDN">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Kata Sandi
                            </label>
                            <button type="button" 
                                    @click="forgotPasswordModal = true" 
                                    class="text-[11px] text-uph-navy dark:text-uph-gold font-bold hover:underline focus:outline-none">
                                Lupa Kata Sandi?
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input type="password" name="password" required x-model="password"
                                   class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 dark:focus:ring-uph-gold/30 focus:border-uph-navy dark:focus:border-uph-gold transition-colors"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 select-none cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-uph-navy focus:ring-uph-navy">
                            <span class="text-slate-600 dark:text-slate-400">Ingat sesi di perangkat ini</span>
                        </label>
                        <span class="text-[11px] text-slate-400">SSL Encrypted (TLS 1.3)</span>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-xl bg-uph-navy hover:bg-[#061B40] dark:bg-uph-gold dark:text-slate-950 dark:hover:bg-amber-400 text-white font-display font-black text-xs uppercase tracking-wider shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Resmi
                    </button>
                </form>

                <!-- Collapsible Quick Fill Demo Credentials Card -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <button type="button" @click="quickFillOpen = !quickFillOpen" class="flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                            <i class="fa-solid fa-bolt text-uph-gold"></i>
                            <span>Akses Cepat Demo Akun Resmi (1-Click Auto-Submit)</span>
                            <i class="fa-solid fa-chevron-down text-[9px] transition-transform" :class="quickFillOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <span class="text-[10px] text-slate-400 font-mono">Pass: password</span>
                    </div>

                    <div x-show="quickFillOpen" x-collapse class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[10px] pt-1">
                        <button type="button" @click="quickFill('superadmin@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>Superadmin</span>
                            <span class="text-[9px] text-purple-500 font-mono">IAM</span>
                        </button>
                        <button type="button" @click="quickFill('rector@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>Rektor</span>
                            <span class="text-[9px] text-amber-500 font-mono">Exec</span>
                        </button>
                        <button type="button" @click="quickFill('dean.fik@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>Dekan (FIK)</span>
                            <span class="text-[9px] text-teal-500 font-mono">Dekan</span>
                        </button>
                        <button type="button" @click="quickFill('kaprodi.if@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>Kaprodi (IF)</span>
                            <span class="text-[9px] text-indigo-500 font-mono">Prodi</span>
                        </button>
                        <button type="button" @click="quickFill('lecturer1@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>Dosen / PA</span>
                            <span class="text-[9px] text-blue-500 font-mono">Dosen</span>
                        </button>
                        <button type="button" @click="quickFill('01082200001', 'password')" class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 text-left font-semibold border border-emerald-200 dark:border-emerald-800 transition-colors flex items-center justify-between" title="Login menggunakan NIM">
                            <span>Mhs (via NIM)</span>
                            <span class="text-[9px] text-emerald-600 font-mono">0108...</span>
                        </button>
                        <button type="button" @click="quickFill('student1@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>Mhs (Aktif)</span>
                            <span class="text-[9px] text-emerald-500 font-mono">Smt 3</span>
                        </button>
                        <button type="button" @click="quickFill('baak@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>BAAK</span>
                            <span class="text-[9px] text-purple-500 font-mono">Akad</span>
                        </button>
                        <button type="button" @click="quickFill('bauk@uph.edu', 'password')" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-left font-semibold border border-slate-200/80 dark:border-slate-700/80 transition-colors flex items-center justify-between">
                            <span>BAUK</span>
                            <span class="text-[9px] text-cyan-500 font-mono">Keu</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Footnote -->
            <div class="pt-6 text-center text-xs text-slate-400">
                <a href="/" class="hover:text-uph-navy dark:hover:text-uph-gold transition-colors inline-flex items-center gap-1 font-semibold">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Beranda Publik UPH</span>
                </a>
            </div>

        </div>

    </div>

    <!-- Self-Service & IT Helpdesk Password Recovery Modal -->
    <div x-show="forgotPasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
         @keydown.escape.window="forgotPasswordModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4"
             @click.away="forgotPasswordModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-uph-gold/15 text-uph-gold flex items-center justify-center text-lg">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pemulihan Kata Sandi Akun</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Prosedur Keamanan SSO Universitas Pelita Harapan</p>
                    </div>
                </div>
                <button type="button" @click="forgotPasswordModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-1.5">
                    <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-graduate text-uph-navy dark:text-uph-gold"></i>
                        <span>Mahasiswa (S1, S2, Profesi)</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Silakan hubungi Loket BAAK Gedung B atau ajukan tiket bantuan mandiri dengan melampirkan foto Kartu Tanda Mahasiswa (KTM) aktif.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-1.5">
                    <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-chalkboard-user text-uph-navy dark:text-uph-gold"></i>
                        <span>Dosen, Kaprodi & Tendik</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Hubungi IT Directorate Helpdesk (Ext: 1100 / WhatsApp: +62 811 888 0901) atau Administrator IAM Fakultas untuk reset credential OTP.
                    </p>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
                <span class="text-[10px] text-slate-400 font-mono">Demo default: password</span>
                <button type="button" @click="forgotPasswordModal = false" class="px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-[#061B40] dark:bg-uph-gold dark:text-slate-950 text-white font-bold text-xs">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
