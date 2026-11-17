@extends('layouts.portal', ['title' => 'Keamanan Akun & Telemetri Sesi'])

@section('content')
<div class="space-y-6">
    <!-- Executive Header -->
    <div class="bg-gradient-to-r from-uph-navy via-slate-900 to-uph-navy p-6 sm:p-8 rounded-2xl text-white shadow-xl border border-uph-gold/30 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 mb-2">
                    <i class="fa-solid fa-user-shield"></i>
                    Zero-Trust Security Telemetry &bull; Audit Trail
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Telemetri Sesi & Keamanan Akun</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Informasi aktif sesi perangkat, alamat IP terdaftar, hak akses RBAC/ABAC, serta riwayat jejak audit seluruh transaksi yang dilakukan oleh akun ini.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-xl bg-emerald-500/20 text-emerald-400 text-xs font-mono font-bold border border-emerald-500/30">
                    <i class="fa-solid fa-lock mr-1.5"></i>
                    Session Enforced
                </span>
            </div>
        </div>
    </div>

    <!-- Active Session Telemetry Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Client IP -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Alamat IP Aktif</span>
                <i class="fa-solid fa-network-wired text-uph-navy dark:text-uph-gold"></i>
            </div>
            <p class="text-lg font-black font-mono text-slate-900 dark:text-white">{{ $sessionMetadata['ip_address'] }}</p>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-circle text-[8px]"></i> Alamat Terverifikasi
            </p>
        </div>

        <!-- Device & Browser -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Perangkat & Browser</span>
                <i class="fa-solid fa-laptop text-uph-navy dark:text-uph-gold"></i>
            </div>
            <p class="text-base font-bold text-slate-900 dark:text-white truncate">{{ $sessionMetadata['browser'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1 truncate">{{ $sessionMetadata['os'] }}</p>
        </div>

        <!-- Role Scope -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Cakupan Peran (RBAC)</span>
                <i class="fa-solid fa-id-badge text-uph-navy dark:text-uph-gold"></i>
            </div>
            <p class="text-lg font-black font-mono text-uph-navy dark:text-uph-gold">{{ $sessionMetadata['role_label'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Multi-Tenancy Scoped</p>
        </div>

        <!-- Last Active -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Aktivitas Terakhir</span>
                <i class="fa-solid fa-clock text-uph-navy dark:text-uph-gold"></i>
            </div>
            <p class="text-sm font-bold font-mono text-slate-900 dark:text-white">{{ $sessionMetadata['last_active'] }}</p>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">Sesi Terautentikasi</p>
        </div>
    </div>

    <!-- Personal Activity History / Audit Trail -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-uph-gold"></i>
                    Riwayat Transaksi & Audit Trail Personal
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Catatan seluruh tindakan yang dijalankan oleh akun Anda demi transparansi dan akuntabilitas akademik.
                </p>
            </div>
            <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                {{ $auditLogs->count() }} Entri Terakhir
            </span>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800 font-bold uppercase text-[10px] text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-3">Waktu Kejadian</th>
                        <th class="p-3">Aksi / Event</th>
                        <th class="p-3">Entitas Objek</th>
                        <th class="p-3">Alamat IP</th>
                        <th class="p-3 text-right">Integritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-3 font-mono text-[11px] text-slate-500">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="p-3 font-bold font-mono text-uph-navy dark:text-uph-gold">
                                {{ $log->action }}
                            </td>
                            <td class="p-3 text-slate-600 dark:text-slate-400">
                                {{ class_basename($log->auditable_type ?? 'System') }}
                            </td>
                            <td class="p-3 font-mono text-slate-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="p-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    <i class="fa-solid fa-check mr-1"></i> Recorded
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat aktivitas yang tercatat untuk akun ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
