@extends('layouts.portal')

@section('title', 'Keamanan Akun & Hak Akses (IAM)')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-uph-navy via-[#061B40] to-slate-900 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30">
                    IAM Account Security &bull; Role {{ strtoupper($user->primary_role) }}
                </span>
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            </div>
            <h1 class="text-2xl font-display font-black tracking-tight text-white">
                Keamanan Akun &amp; Matriks Hak Akses
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Kelola kata sandi akun Anda dan periksa daftar izin operasional aktif Anda di sistem SIAKAD.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="/portal" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider transition-colors border border-white/10">
                &larr; Beranda
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Change Password Card -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 space-y-5 shadow-xs">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-uph-navy/10 dark:bg-uph-gold/10 text-uph-navy dark:text-uph-gold flex items-center justify-center text-lg">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Ganti Kata Sandi</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Pastikan gunakan kombinasi yang kuat dan unik</p>
                </div>
            </div>

            @if(session('success'))
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan:
                    </div>
                    <ul class="list-disc pl-5 text-[11px]">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('portal.account.password.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kata Sandi Saat Ini
                    </label>
                    <input type="password" name="current_password" required
                           class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kata Sandi Baru (Minimal 8 Karakter)
                    </label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-uph-navy hover:bg-[#061B40] text-white font-bold text-xs uppercase tracking-wider shadow-md transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Kata Sandi
                </button>
            </form>
        </div>

        <!-- Right: Active Account Profile & Effective Privileges -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Account ABAC Identity Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-address-card text-uph-gold"></i>
                        <span>Identitas Akun &amp; Lingkup ABAC</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        Status: Akun Aktif
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Nama Lengkap</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $user->full_name }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Email Resmi</span>
                        <span class="font-bold text-slate-900 dark:text-white truncate block">{{ $user->email }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Peran Utama (RBAC)</span>
                        <span class="font-bold text-uph-navy dark:text-uph-gold uppercase">{{ $user->primary_role }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Scope Fakultas</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $user->faculty->name_id ?? 'Global (Semua)' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Scope Program Studi</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $user->studyProgram->name_id ?? 'Global (Semua)' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Level Otoritas IAM</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">Level {{ $user->roleModel->level ?? 20 }}</span>
                    </div>
                </div>
            </div>

            <!-- Active Permissions Checklist Matrix -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-emerald-500"></i>
                            <span>Daftar Hak Akses Operasional Anda</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Evaluasi otomatis dari Peran Sistem dan Custom Overrides khusus akun Anda
                        </p>
                    </div>
                </div>

                <div class="max-h-80 overflow-y-auto space-y-2 pr-1 text-xs">
                    @foreach($permissions as $perm)
                        @php
                            $hasAccess = $user->hasPrivilege($perm->slug);
                        @endphp
                        <div class="p-2.5 rounded-xl border flex items-center justify-between {{ $hasAccess ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/60 dark:border-emerald-800/40 text-slate-800 dark:text-slate-200' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200/40 dark:border-slate-800 text-slate-400 dark:text-slate-600' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="h-5 w-5 rounded-md flex items-center justify-center text-[10px] {{ $hasAccess ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-400' }}">
                                    <i class="fa-solid {{ $hasAccess ? 'fa-check' : 'fa-xmark' }}"></i>
                                </span>
                                <div>
                                    <span class="font-semibold block leading-tight">{{ $perm->name }}</span>
                                    <span class="font-mono text-[10px] opacity-75">{{ $perm->slug }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $hasAccess ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                                {{ $hasAccess ? 'Diizinkan' : 'Terkunci' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
