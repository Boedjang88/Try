@extends('layouts.portal')

@section('title', 'Direktori Akun & IAM Lifecycle Manager')

@section('content')
<div class="space-y-6" x-data="{ createUserModalOpen: false, resetPasswordModalOpen: false, targetUser: null }">

    <!-- Hero Header -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-uph-navy via-[#061B40] to-slate-900 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30">
                    Identity &amp; Access Management (IAM)
                </span>
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            </div>
            <h1 class="text-2xl font-display font-black tracking-tight text-white">
                Direktori Akun &amp; Manajemen Siklus Pengguna
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Kelola kredensial resmi, penetapan peran hierarkis, lingkup ABAC Fakultas/Prodi, dan status aktifasi seluruh sivitas akademika.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('portal.iam.privileges') }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider transition-colors border border-white/10 flex items-center gap-1.5">
                <i class="fa-solid fa-table-cells"></i> Matriks Hak Akses
            </a>
            <button @click="createUserModalOpen = true" class="px-4 py-2 rounded-xl bg-uph-gold hover:bg-amber-400 text-slate-950 font-display font-black text-xs uppercase tracking-wider shadow-md transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i> Tambah Akun Baru
            </button>
        </div>
    </div>

    <!-- KPI Bento Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Akun</span>
            <span class="text-2xl font-mono font-black text-slate-900 dark:text-white tabular-nums">{{ $stats['total'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Akun Aktif</span>
            <span class="text-2xl font-mono font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $stats['active'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">Ditangguhkan</span>
            <span class="text-2xl font-mono font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ $stats['suspended'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-uph-navy dark:text-uph-gold block">Mahasiswa</span>
            <span class="text-2xl font-mono font-black text-uph-navy dark:text-uph-gold tabular-nums">{{ $stats['students'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">Dosen / Kaprodi</span>
            <span class="text-2xl font-mono font-black text-indigo-600 dark:text-indigo-400 tabular-nums">{{ $stats['lecturers'] }}</span>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('portal.iam.users') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="sm:col-span-5 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                       placeholder="Cari nama lengkap, email, atau NIM/username...">
            </div>

            <!-- Role Filter -->
            <div class="sm:col-span-3">
                <select name="role" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                    <option value="">Semua Peran Sistem</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                            {{ $r->display_name }} (Level {{ $r->level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="sm:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-uph-navy hover:bg-[#061B40] text-white font-bold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('portal.iam.users') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs text-center" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Enterprise User Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="sticky top-0 z-10 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-xs border-b border-slate-200 dark:border-slate-700 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Pengguna / Sivitas</th>
                        <th class="py-3 px-4">Identifier / Username</th>
                        <th class="py-3 px-4">Peran (RBAC)</th>
                        <th class="py-3 px-4">Lingkup ABAC (Fakultas / Prodi)</th>
                        <th class="py-3 px-4">Status Akun</th>
                        <th class="py-3 px-4 text-right">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                            <!-- Name & Email -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-uph-navy/10 dark:bg-uph-gold/10 text-uph-navy dark:text-uph-gold font-black flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white block leading-tight">{{ $user->full_name }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Identifier -->
                            <td class="py-3 px-4 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                {{ $user->username }}
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                    {{ $user->role === 'superadmin' ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : '' }}
                                    {{ in_array($user->role, ['rector', 'dean']) ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : '' }}
                                    {{ $user->role === 'kaprodi' ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : '' }}
                                    {{ $user->role === 'lecturer' ? 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : '' }}
                                    {{ $user->role === 'student' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : '' }}
                                    {{ in_array($user->role, ['baak', 'bauk']) ? 'bg-cyan-100 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800' : '' }}
                                    {{ $user->role === 'applicant' ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' : '' }}
                                ">
                                    {{ $user->role }}
                                </span>
                            </td>

                            <!-- ABAC Scope -->
                            <td class="py-3 px-4 text-[11px] text-slate-600 dark:text-slate-400">
                                @if($user->faculty || $user->studyProgram)
                                    <div class="space-y-0.5">
                                        @if($user->faculty)
                                            <span class="block font-semibold text-slate-800 dark:text-slate-200">{{ $user->faculty->name_id }}</span>
                                        @endif
                                        @if($user->studyProgram)
                                            <span class="block text-slate-500 dark:text-slate-400">Prodi: {{ $user->studyProgram->name_id }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Global (Tanpa Batasan Scope)</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Ditangguhkan
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Toggle Active / Suspended Form -->
                                    <form method="POST" action="{{ route('portal.iam.users.toggle-status', $user) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktifasi akun ini?');">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition-colors {{ $user->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }}"
                                                title="{{ $user->is_active ? 'Tangguhkan Akun' : 'Aktifkan Akun' }}">
                                            <i class="fa-solid {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                            <span>{{ $user->is_active ? 'Suspend' : 'Aktifkan' }}</span>
                                        </button>
                                    </form>

                                    <!-- Reset Password Button -->
                                    <form method="POST" action="{{ route('portal.iam.users.reset-password', $user) }}" class="inline" onsubmit="return confirm('Reset kata sandi pengguna ini menjadi \'password\'?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[10px] border border-slate-200 dark:border-slate-700 transition-colors" title="Reset Password">
                                            <i class="fa-solid fa-key"></i> Reset
                                        </button>
                                    </form>

                                    <!-- Inspect Privileges -->
                                    <a href="{{ route('portal.iam.privileges', ['user_id' => $user->id]) }}" class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-[10px] border border-purple-200 transition-colors" title="Lihat &amp; Override Hak Akses">
                                        <i class="fa-solid fa-shield-halved"></i> Izin
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada data pengguna yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div x-show="createUserModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-5 shadow-2xl" @click.away="createUserModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-display font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-uph-gold"></i>
                    <span>Registrasi Akun Sivitas Baru (IAM)</span>
                </h3>
                <button @click="createUserModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('portal.iam.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="full_name" required class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30" placeholder="Contoh: Christian Jonathan, M.Kom.">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Resmi UPH</label>
                        <input type="email" name="email" required class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30" placeholder="nama@uph.edu">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Username / NIM / NIDN</label>
                        <input type="text" name="username" required class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30" placeholder="01082200001">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Awal</label>
                        <input type="password" name="password" required minlength="8" value="password" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30" placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Peran Utama (RBAC)</label>
                        <select name="role" required class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Scope Fakultas (ABAC)</label>
                        <select name="faculty_id" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                            <option value="">Tanpa Batasan Fakultas</option>
                            @foreach($faculties as $f)
                                <option value="{{ $f->id }}">{{ $f->name_id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Scope Prodi (ABAC)</label>
                        <select name="study_program_id" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                            <option value="">Tanpa Batasan Prodi</option>
                            @foreach($faculties as $f)
                                @foreach($f->studyPrograms as $sp)
                                    <option value="{{ $sp->id }}">[{{ $f->code }}] {{ $sp->name_id }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="createUserModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-[#061B40] text-white font-bold text-xs uppercase tracking-wider shadow-md">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
