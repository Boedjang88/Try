@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{
    diffModalOpen: false,
    selectedLog: null,
    showDiff(log) {
        this.selectedLog = log;
        this.diffModalOpen = true;
    }
}">

    <!-- Executive Header Banner -->
    <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded bg-uph-gold/20 text-uph-gold border border-uph-gold/30 text-[10px] font-extrabold uppercase tracking-widest">
                    <i class="fa-solid fa-fingerprint"></i> Immutable Ledger Explorer
                </span>
                <span class="text-xs text-slate-300 font-mono">&bull; Compliance &amp; SPMI Audit Trail</span>
            </div>
            <h1 class="text-2xl font-display font-black text-white">Global Audit Trail Explorer</h1>
            <p class="text-xs text-slate-300 mt-1">
                Catatan mutasi data sensitif, override dispensasi akademik, verifikasi finansial BAUK, dan aktivitas otorisasi pengguna.
            </p>
        </div>

        <div class="text-right">
            <span class="text-[11px] text-slate-400 block">Total Aktivitas Tercatat</span>
            <span class="text-2xl font-mono font-black text-uph-gold">{{ $auditLogs->total() }} Log</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('portal.executive.audit-logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Cari Kata Kunci / Aktor</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Action, nama, email, alasan..." class="w-full p-2.5 border rounded-xl text-xs outline-none focus:ring-1 focus:ring-uph-navy">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Aksi (Action Type)</label>
                <select name="action_type" class="w-full p-2.5 border rounded-xl text-xs outline-none focus:ring-1 focus:ring-uph-navy">
                    <option value="">Semua Jenis Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ request('action_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full p-2.5 border rounded-xl text-xs outline-none focus:ring-1 focus:ring-uph-navy">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs shadow flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('portal.executive.audit-logs') }}" class="px-4 py-2.5 border rounded-xl text-slate-600 font-bold text-xs hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Waktu (WIB)</th>
                        <th class="py-3 px-4">Aktor / User</th>
                        <th class="py-3 px-4">Tindakan (Action)</th>
                        <th class="py-3 px-4">Alasan &amp; Deskripsi Mutasi</th>
                        <th class="py-3 px-4">IP &amp; Agent</th>
                        <th class="py-3 px-4 text-center">Data Diff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i:s') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $log->user->full_name ?? 'System/Automated' }}</div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log->user->email ?? '-' }} &bull; {{ $log->user->role ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-wider
                                    {{ str_contains($log->action, 'APPROVED') || str_contains($log->action, 'VERIFIED') ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ str_contains($log->action, 'LOCKED') ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ str_contains($log->action, 'UNBLOCK') || str_contains($log->action, 'OVERRIDE') ? 'bg-cyan-100 text-cyan-800' : '' }}
                                    {{ str_contains($log->action, 'REVISION') || str_contains($log->action, 'REJECT') ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ !str_contains($log->action, 'APPROVED') && !str_contains($log->action, 'LOCKED') && !str_contains($log->action, 'UNBLOCK') && !str_contains($log->action, 'REJECT') ? 'bg-slate-100 text-slate-800' : '' }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $log->reason }}">
                                {{ $log->reason ?: '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[10px] text-slate-400 whitespace-nowrap">
                                <div>{{ $log->ip_address ?: '127.0.0.1' }}</div>
                                <span class="truncate block max-w-[120px]" title="{{ $log->user_agent }}">{{ substr($log->user_agent ?? 'Console', 0, 18) }}...</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($log->old_values || $log->new_values)
                                    <button type="button" 
                                            @click="showDiff({{ json_encode($log) }})"
                                            class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-code-compare"></i> Diff
                                    </button>
                                @else
                                    <span class="text-slate-300 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-check text-2xl mb-1 block"></i>
                                Tidak ada log audit yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

    <!-- JSON Diff Viewer Modal -->
    <div x-show="diffModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200" @click.away="diffModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-code-compare text-uph-gold text-lg"></i>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900" x-text="selectedLog?.action">Audit Log Diff</h3>
                        <p class="text-[11px] text-slate-400 font-mono" x-text="selectedLog?.created_at"></p>
                    </div>
                </div>
                <button type="button" @click="diffModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-bold text-rose-600 uppercase block mb-1">Old Values (Sebelum)</span>
                    <pre class="bg-rose-50/60 border border-rose-200/80 rounded-xl p-3 text-[11px] font-mono text-rose-900 max-h-60 overflow-auto whitespace-pre-wrap" x-text="JSON.stringify(selectedLog?.old_values, null, 2) || '(Kosong / Creation)'"></pre>
                </div>
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase block mb-1">New Values (Sesudah)</span>
                    <pre class="bg-emerald-50/60 border border-emerald-200/80 rounded-xl p-3 text-[11px] font-mono text-emerald-900 max-h-60 overflow-auto whitespace-pre-wrap" x-text="JSON.stringify(selectedLog?.new_values, null, 2) || '(Kosong / Deletion)'"></pre>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t text-right">
                <button type="button" @click="diffModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
