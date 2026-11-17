@extends('layouts.portal')

@section('title', 'Matriks Peran & Hak Akses Granular (RBAC + IAM)')

@section('content')
<div class="space-y-6">

    <!-- Hero Header -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-uph-navy via-[#061B40] to-slate-900 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30">
                    RBAC &amp; Privilege Matrix
                </span>
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            </div>
            <h1 class="text-2xl font-display font-black tracking-tight text-white">
                Matriks Hak Akses Granular &amp; Custom Privilege Overrides
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Struktur otorisasi berbasis hak akses granular per peran sistem serta override khusus akun individu sivitas.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('portal.iam.users') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider transition-colors border border-white/10 flex items-center gap-1.5">
                <i class="fa-solid fa-users-gear"></i> Direktori Akun
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Section 1: Per-User Custom Privilege Override Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-purple-600"></i>
                    <span>Override Hak Akses Khusus Individu (User-Level Override)</span>
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Berikan atau cabut izin tertentu pada pengguna spesifik tanpa harus mengubah peran utamanya.
                </p>
            </div>

            <!-- User Selector Form -->
            <form method="GET" action="{{ route('portal.iam.privileges') }}" class="flex items-center gap-2">
                <select name="user_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                    <option value="">-- Pilih Pengguna untuk Di-Inspect / Override --</option>
                    @foreach($allUsers as $u)
                        <option value="{{ $u->id }}" {{ $selectedUser?->id === $u->id ? 'selected' : '' }}>
                            {{ $u->full_name }} ({{ $u->role }} - {{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($selectedUser)
            <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200/60 dark:border-purple-800/40 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-purple-700 dark:text-purple-300 block">Target Override Akun</span>
                        <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $selectedUser->full_name }}</span>
                        <span class="text-slate-500 dark:text-slate-400">({{ $selectedUser->email }}) &bull; Peran: <strong class="uppercase text-uph-navy dark:text-uph-gold">{{ $selectedUser->primary_role }}</strong></span>
                    </div>

                    <!-- Custom Overrides Summary -->
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-800 font-mono text-[11px] font-bold text-purple-700 dark:text-purple-300">
                            {{ $selectedUser->customPermissions->count() }} Custom Overrides Aktif
                        </span>
                    </div>
                </div>

                <!-- Override Action Form -->
                <form action="{{ route('portal.iam.privileges.override') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pt-2 border-t border-purple-200/40">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Permission Slug</label>
                        <select name="permission_slug" required class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30">
                            @foreach($permissions as $mod => $perms)
                                <optgroup label="Modul: {{ $mod }}">
                                    @foreach($perms as $p)
                                        @php
                                            $effective = $selectedUser->hasPrivilege($p->slug);
                                            $hasCustom = $selectedUser->customPermissions->firstWhere('permission_id', $p->id);
                                        @endphp
                                        <option value="{{ $p->slug }}">
                                            {{ $p->slug }} &bull; {{ $p->name }} (Status: {{ $effective ? 'Diizinkan' : 'Terkunci' }}{{ $hasCustom ? ' - OVERRIDDEN' : '' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Aksi Override</label>
                        <select name="action" required class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500/30">
                            <option value="grant">Berikan Izin (Explicit Grant)</option>
                            <option value="revoke">Cabut Izin (Explicit Revoke)</option>
                            <option value="clear">Hapus Override (Kembali ke Default)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-4 flex items-center gap-2">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-sm flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i> Eksekusi Override
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-6 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 text-center text-slate-400 text-xs">
                Pilih pengguna pada dropdown di atas untuk menguji atau menetapkan override hak akses khusus akun.
            </div>
        @endif
    </div>

    <!-- Section 2: Visual 9-Role Privilege Matrix Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-xs space-y-0">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-table-cells text-uph-gold"></i>
                    <span>Matriks Otoritas Standar 9 Peran Sistem</span>
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Tanda centang hijau menunjukkan izin bawaan (default permissions) yang otomatis melekat pada peran tersebut.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="sticky top-0 z-20 bg-slate-100/95 dark:bg-slate-800/95 backdrop-blur-xs border-b border-slate-200 dark:border-slate-700 text-[10px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                        <th class="py-3 px-4 min-w-[240px]">Permission Slug &amp; Nama Izin</th>
                        @foreach($roles as $role)
                            <th class="py-3 px-3 text-center min-w-[85px]">
                                <span class="block truncate" title="{{ $role->display_name }}">{{ $role->name }}</span>
                                <span class="text-[9px] text-slate-400 font-mono block">Lvl {{ $role->level }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($permissions as $moduleName => $modulePermissions)
                        <!-- Module Group Header Row -->
                        <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                            <td colspan="{{ count($roles) + 1 }}" class="py-2 px-4 text-[11px] font-black uppercase tracking-wider text-uph-navy dark:text-uph-gold">
                                <i class="fa-solid fa-folder-tree mr-1.5 opacity-75"></i> Modul {{ $moduleName }}
                            </td>
                        </tr>

                        @foreach($modulePermissions as $perm)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-2.5 px-4">
                                    <span class="font-bold text-slate-900 dark:text-white block leading-tight">{{ $perm->name }}</span>
                                    <span class="font-mono text-[10px] text-slate-400">{{ $perm->slug }}</span>
                                </td>

                                @foreach($roles as $role)
                                    @php
                                        $hasPerm = $role->hasPermission($perm->slug);
                                    @endphp
                                    <td class="py-2.5 px-3 text-center">
                                        @if($role->name === 'superadmin' || $hasPerm)
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-slate-100 dark:bg-slate-800/60 text-slate-300 dark:text-slate-700 text-xs">
                                                <i class="fa-solid fa-minus"></i>
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
