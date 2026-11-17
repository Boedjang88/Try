@extends('layouts.portal')

@section('title', 'Campus SLA Helpdesk & Ticketing Center')

@section('content')
<div class="space-y-6" x-data="{ newTicketModal: false }">

    <!-- Header & Hero -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 uppercase tracking-widest">
                    Service Level Agreement (SLA)
                </span>
                <span class="text-xs text-slate-400">Campus Helpdesk &amp; Rapid Support</span>
            </div>
            <h1 class="text-2xl font-bold font-serif text-white flex items-center gap-2">
                <i class="fa-solid fa-headset text-uph-gold"></i>
                Layanan Bantuan &amp; Helpdesk SLA Kampus
            </h1>
            <p class="text-xs text-slate-300">
                Pusat pengaduan resmi terpadu untuk kendala keuangan (BAUK), administrasi perkuliahan (BAAK), dan sistem IT dengan target waktu penyelesaian terukur.
            </p>
        </div>

        <div>
            <button @click="newTicketModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-uph-gold to-amber-500 text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg flex items-center gap-2 transition-all">
                <i class="fa-solid fa-ticket"></i>
                Buka Tiket Bantuan Baru
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-slate-900/60 p-4 rounded-xl border border-sky-500/30 bg-sky-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-sky-300 font-bold uppercase tracking-wider">Tiket Terbuka</span>
                <i class="fa-solid fa-folder-open text-sky-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['open'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Menunggu penanganan biro</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-amber-500/30 bg-amber-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-amber-300 font-bold uppercase tracking-wider">Sedang Diproses</span>
                <i class="fa-solid fa-spinner text-amber-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['in_progress'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Dalam investigasi staf biro</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-300 font-bold uppercase tracking-wider">Tuntas / Selesai</span>
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['resolved'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Solusi telah dikirimkan</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-rose-500/30 bg-rose-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-rose-300 font-bold uppercase tracking-wider">Prioritas Urgent</span>
                <i class="fa-solid fa-fire text-rose-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['urgent'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">SLA maksimal 12 jam</p>
        </div>
    </div>

    <!-- Ticket Directory Table -->
    <div class="bg-slate-900/80 rounded-2xl border border-white/10 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-3 bg-white/[0.02]">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-inbox text-uph-gold"></i>
                Antrean Tiket Helpdesk
            </h2>

            <form method="GET" action="{{ route('portal.helpdesk.index') }}" class="flex items-center gap-2">
                <select name="department" onchange="this.form.submit()" 
                        class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Departemen</option>
                    <option value="bauk" {{ request('department') === 'bauk' ? 'selected' : '' }}>Keuangan &amp; VA (BAUK)</option>
                    <option value="baak" {{ request('department') === 'baak' ? 'selected' : '' }}>Akademik &amp; KRS (BAAK)</option>
                    <option value="it_support" {{ request('department') === 'it_support' ? 'selected' : '' }}>Sistem IT &amp; LMS</option>
                </select>

                <select name="status" onchange="this.form.submit()" 
                        class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>

                <select name="priority" onchange="this.form.submit()" 
                        class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Prioritas</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent (12 Jam)</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High (24 Jam)</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium (48 Jam)</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low (72 Jam)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-white/5 uppercase tracking-wider text-[10px] text-slate-400 font-bold border-b border-white/10">
                    <tr>
                        <th class="py-3 px-4">No. Tiket &amp; Subjek</th>
                        <th class="py-3 px-4">Biro / Kategori</th>
                        <th class="py-3 px-4">Pemohon</th>
                        <th class="py-3 px-4">Prioritas</th>
                        <th class="py-3 px-4">Status &amp; SLA</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($tickets as $t)
                        @php
                            $isOverdue = $t->isSlaOverdue();
                            $pClass = match($t->priority) {
                                'urgent' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                'high' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'medium' => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
                                default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                            };
                            $sClass = match($t->status) {
                                'resolved' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                'in_progress' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'closed' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                default => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
                            };
                        @endphp
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4">
                                <div class="space-y-0.5">
                                    <span class="font-mono text-[11px] font-bold text-uph-gold">{{ $t->ticket_number }}</span>
                                    <p class="font-bold text-white text-xs hover:text-uph-gold transition-colors">
                                        <a href="{{ route('portal.helpdesk.show', $t) }}">{{ $t->subject }}</a>
                                    </p>
                                    <span class="text-[10px] text-slate-400">{{ $t->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 uppercase tracking-wider text-slate-200">
                                    {{ strtoupper($t->department) }}
                                </span>
                                <p class="text-[11px] text-slate-400 mt-1">{{ $t->category }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-white">{{ $t->user->full_name ?? 'Civitas' }}</p>
                                <span class="text-[10px] text-slate-400">{{ $t->user->primary_role ?? 'student' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $pClass }}">
                                    {{ strtoupper($t->priority) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $sClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $t->status)) }}
                                    </span>
                                    <div>
                                        @if($t->status === 'resolved')
                                            <span class="text-[10px] text-emerald-400 font-semibold">Terselesaikan</span>
                                        @elseif($isOverdue)
                                            <span class="text-[10px] text-rose-400 font-bold animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>SLA TERLEWAT
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400">
                                                Target: {{ $t->sla_due_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('portal.helpdesk.show', $t) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-uph-gold hover:text-uph-navy font-bold text-xs transition-colors inline-flex items-center gap-1.5">
                                    <span>Buka</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-ticket text-3xl mb-2 text-slate-600"></i>
                                <p class="font-semibold">Belum ada tiket bantuan pada kategori ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-white/10">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form: Buka Tiket Baru -->
    <div x-show="newTicketModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-white/20 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl space-y-4 p-6" @click.away="newTicketModal = false">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-ticket text-uph-gold"></i>
                    Buka Tiket Bantuan Helpdesk
                </h3>
                <button @click="newTicketModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('portal.helpdesk.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Tujuan Biro / Dept</label>
                        <select name="department" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                            <option value="bauk">BAUK (Keuangan &amp; Tagihan VA)</option>
                            <option value="baak">BAAK (Akademik &amp; KRS)</option>
                            <option value="it_support">IT Support &amp; LMS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Prioritas (Target SLA)</label>
                        <select name="priority" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                            <option value="medium">Medium (Target 48 Jam)</option>
                            <option value="urgent">Urgent (Target 12 Jam)</option>
                            <option value="high">High (Target 24 Jam)</option>
                            <option value="low">Low (Target 72 Jam)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Kategori Masalah</label>
                    <input type="text" name="category" placeholder="Contoh: Pembayaran VA Belum Masuk / Cekal Keuangan" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Judul / Subjek Tiket</label>
                    <input type="text" name="subject" placeholder="Ringkasan kendala secara spesifik..." required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Uraian Detail Kendala</label>
                    <textarea name="message" rows="4" required placeholder="Tuliskan nomor referensi pembayaran, kode matakuliah, atau pesan error yang muncul..." class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <button type="button" @click="newTicketModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-uph-gold text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg">
                        Daftarkan Tiket ke Biro
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
