@extends('layouts.portal')

@section('title', 'Dasbor BAUK - Biro Administrasi Keuangan UPH')

@section('content')
<div class="space-y-6">

    <!-- Header Section with UPH Midnight Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    BK
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Biro Administrasi Keuangan (BAUK)
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-mono font-bold">Periode: {{ $activePeriod->name ?? '2026/2027 Ganjil' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">Pengelolaan Tagihan & Rekonsiliasi Finansial</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-credit-card text-uph-gold mr-1.5"></i> Multi-Bank Virtual Account Gateway</span>
                        <span><i class="fa-solid fa-lock text-red-400 mr-1.5"></i> Financial Hold Mahasiswa</span>
                        <span><i class="fa-solid fa-file-invoice-dollar text-emerald-400 mr-1.5"></i> Settlement Otomatis Real-Time</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                <a href="{{ route('finance.index') }}" class="px-4 py-2.5 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 text-xs font-bold transition-all flex items-center gap-2 shadow-sm hover:scale-[1.02]">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Kelola Seluruh Tagihan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Financial KPIs with SVG Circular Progress Rings -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Total Billed -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10">
            <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Total Tagihan Terbit</span>
            <div class="flex items-baseline gap-1 mt-1.5">
                <span class="text-xs text-slate-400 font-bold">Rp</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                    {{ number_format($totalBilled / 1000000, 1) }}M
                </h3>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">BPP Pokok & Biaya SKS</span>
        </div>

        <!-- Tile 2: Kas Masuk with SVG Ring -->
        @php
            $rate = (float)($collectionRate ?? 0);
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Kas SPP Masuk</span>
                <div class="flex items-baseline gap-1 mt-1.5">
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Rp</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        {{ number_format($totalCollected / 1000000, 1) }}M
                    </h3>
                </div>
                <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold font-mono text-emerald-700 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $rate }}% Tingkat Pelunasan
                </span>
            </div>

            <!-- SVG Ring -->
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-14 w-14 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="{{ min(100, $rate) }}, 100" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[11px] font-bold font-mono text-slate-700 dark:text-slate-200">
                    {{ round($rate) }}%
                </div>
            </div>
        </div>

        <!-- Tile 3: Tunggakan -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10">
            <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Tunggakan Belum Lunas</span>
            <div class="flex items-baseline gap-1 mt-1.5">
                <span class="text-xs text-rose-600 dark:text-rose-400 font-bold">Rp</span>
                <h3 class="text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400 font-mono tabular-nums">
                    {{ number_format($totalPending / 1000000, 1) }}M
                </h3>
            </div>
            <span class="text-[11px] text-rose-600 dark:text-rose-400 font-bold mt-2 block font-mono">Termasuk Tagihan Overdue</span>
        </div>

        <!-- Tile 4: Mahasiswa Dalam Hold -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">Mahasiswa Hold</span>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-black text-red-700 dark:text-rose-400 font-mono tabular-nums">{{ count($blockedStudents) }}</h3>
                    <span class="text-xs text-slate-400 font-mono">Mhs</span>
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block font-mono">Akses KRS Dinonaktifkan</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-red-50 dark:bg-rose-950/40 text-red-600 dark:text-rose-400 flex items-center justify-center text-lg border border-red-200/50">
                <i class="fa-solid fa-user-lock"></i>
            </div>
        </div>
    </div>

    <!-- Main Workspace Two-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Students in Financial Hold & Overdue Billing -->
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10"
                 x-data="{
                     search: '',
                     matches(name, nim) {
                         return !this.search || name.toLowerCase().includes(this.search.toLowerCase()) || nim.toLowerCase().includes(this.search.toLowerCase());
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-crimson">Financial Hold</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-user-lock text-red-600"></i>
                            <span>Daftar Mahasiswa Berstatus Financial Hold</span>
                        </h2>
                    </div>

                    <a href="/portal/bauk/bulk-data" class="px-3.5 py-1.5 rounded-xl bg-uph-navy text-white text-xs font-bold hover:bg-uph-navy-dark transition-colors flex items-center gap-2 shadow-xs">
                        <i class="fa-solid fa-file-csv text-uph-gold"></i>
                        <span>Ekspor CSV</span>
                    </a>
                </div>

                <!-- Instant Search -->
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Cari nama mahasiswa atau NIM..." class="w-full text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uph-navy dark:focus:ring-uph-gold">
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                                <th class="py-3 px-3">Mahasiswa & NIM</th>
                                <th class="py-3 px-3">Program Studi</th>
                                <th class="py-3 px-3">Tagihan Tertunggak</th>
                                <th class="py-3 px-2 text-center">Status Hold</th>
                                <th class="py-3 px-3 text-right">Aksi BAUK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($blockedStudents as $st)
                                @php
                                    $unpaidInv = $st->invoices->first();
                                    $name = $st->user->full_name ?? 'Mahasiswa';
                                    $nim = $st->nim ?? '';
                                @endphp
                                <tr x-show="matches('{{ addslashes($name) }}', '{{ $nim }}')" class="hover:bg-uph-navy/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $name }}</p>
                                        <span class="font-mono text-[11px] text-slate-400 dark:text-slate-500">NIM: {{ $nim }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                                        {{ $st->studyProgram->name_id ?? 'Informatika' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <p class="font-mono font-bold text-red-700 dark:text-rose-400 tabular-nums">Rp {{ number_format($unpaidInv->amount ?? 16500000, 0, ',', '.') }}</p>
                                        <span class="text-[10px] text-slate-400 font-mono">VA: {{ $unpaidInv->va_number ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono bg-red-100 dark:bg-rose-950/60 text-red-800 dark:text-rose-300 border border-red-300 dark:border-rose-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                            HOLD AKTIF
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        @if($unpaidInv)
                                            <form method="POST" action="/finance/invoice/{{ $unpaidInv->id }}/simulate" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition-colors shadow-xs" title="Simulasikan Pelunasan VA">
                                                    Simulasi Bayar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        Tidak ada mahasiswa yang sedang dalam status financial hold.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Col: Real-Time VA Settlement Feed -->
        <div class="space-y-6">

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-emerald-500"></i>
                    <span>Pelunasan Virtual Account Terkini</span>
                </h3>

                <div class="space-y-3">
                    @forelse($recentTransactions as $tx)
                        <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/20 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $tx->student->user->full_name ?? 'Mahasiswa' }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-mono text-[9px] font-bold bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-100">
                                    <span class="h-1 w-1 rounded-full bg-emerald-600"></span>
                                    LUNAS
                                </span>
                            </div>
                            <div class="mt-1.5 flex items-center justify-between text-[11px] pt-1 border-t border-emerald-200/50 dark:border-emerald-800/40">
                                <span class="font-mono text-slate-500 dark:text-slate-400">VA: {{ $tx->va_number }}</span>
                                <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">Rp {{ number_format($tx->paid_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-4">Belum ada transaksi hari ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Bulk Data Center Card -->
            <div class="bg-gradient-to-br from-uph-navy to-[#0F172A] p-6 rounded-2xl border border-uph-gold/30 text-white shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold font-mono border border-uph-gold/30">
                        Zero Memory CSV
                    </span>
                    <i class="fa-solid fa-server text-uph-gold text-lg"></i>
                </div>
                <h3 class="text-base font-bold tracking-tight">Bulk Data Command Center</h3>
                <p class="text-xs text-slate-300 mt-1">Ekspor tagihan & rekonsiliasi jutaan baris data secara streaming langsung ke file CSV.</p>
                <a href="/portal/bauk/bulk-data" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-uph-gold text-slate-950 font-bold text-xs hover:bg-uph-gold-warm transition-all shadow">
                    <span>Buka Bulk Data BAUK</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
