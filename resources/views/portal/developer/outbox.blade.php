@extends('layouts.portal')

@section('title', 'Outbound Notification Log & Dispatch Simulator')

@section('content')
<div class="space-y-6" x-data="{ previewModal: false, activeDispatch: null }">

    <!-- Header & Hero -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest">
                    Multi-Channel Telemetry
                </span>
                <span class="text-xs text-slate-400">Outbox &amp; Message Dispatcher</span>
            </div>
            <h1 class="text-2xl font-bold font-serif text-white flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-uph-gold"></i>
                Log Pengiriman Notifikasi Eksternal (Outbox)
            </h1>
            <p class="text-xs text-slate-300">
                Audit trail lengkap seluruh pesan WhatsApp Gateway dan notifikasi email resmi yang terkirim secara otomatis kepada civitas akademika.
            </p>
        </div>

        <div>
            <a href="{{ route('portal.developer.sandbox') }}" 
               class="px-4 py-2.5 rounded-xl bg-white/10 text-white font-bold text-xs hover:bg-white/20 border border-white/20 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-flask text-uph-gold"></i>
                Kembali ke Sandbox API
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900/60 p-4 rounded-xl border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Dispatches</span>
                <i class="fa-solid fa-envelopes-bulk text-uph-gold"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Semua notifikasi terkirim</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-300 font-bold uppercase tracking-wider">WhatsApp Gateway</span>
                <i class="fa-brands fa-whatsapp text-emerald-400 text-lg"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['whatsapp'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Pesan instan WhatsApp terkirim</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-sky-500/30 bg-sky-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-sky-300 font-bold uppercase tracking-wider">Email Resmi UPH</span>
                <i class="fa-solid fa-at text-sky-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['email'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Template HTML resmi terkirim</p>
        </div>
    </div>

    <!-- Dispatch Log Table -->
    <div class="bg-slate-900/80 rounded-2xl border border-white/10 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-3 bg-white/[0.02]">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-list text-uph-gold"></i>
                Riwayat Pengiriman Pesan
            </h2>

            <form method="GET" action="{{ route('portal.developer.outbox') }}" class="flex items-center gap-2">
                <select name="channel" onchange="this.form.submit()" class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Kanal</option>
                    <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                </select>

                <select name="event_type" onchange="this.form.submit()" class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Event</option>
                    <option value="VA_PAID" {{ request('event_type') === 'VA_PAID' ? 'selected' : '' }}>VA Paid</option>
                    <option value="KRS_APPROVED" {{ request('event_type') === 'KRS_APPROVED' ? 'selected' : '' }}>KRS Approved</option>
                    <option value="EXAM_SEAT_ASSIGNED" {{ request('event_type') === 'EXAM_SEAT_ASSIGNED' ? 'selected' : '' }}>Exam Seat Assigned</option>
                    <option value="APPROVAL_REQUIRED" {{ request('event_type') === 'APPROVAL_REQUIRED' ? 'selected' : '' }}>Approval Required</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-white/5 uppercase tracking-wider text-[10px] text-slate-400 font-bold border-b border-white/10">
                    <tr>
                        <th class="py-3 px-4">Dispatch ID</th>
                        <th class="py-3 px-4">Kanal &amp; Event</th>
                        <th class="py-3 px-4">Penerima (Recipient)</th>
                        <th class="py-3 px-4">Judul / Subjek</th>
                        <th class="py-3 px-4">Waktu &amp; Status</th>
                        <th class="py-3 px-4 text-right">Inspeksi Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($dispatches as $d)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-uph-gold">
                                {{ $d->dispatch_id }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    @if($d->channel === 'whatsapp')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                            <i class="fa-brands fa-whatsapp"></i> WA
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 flex items-center gap-1">
                                            <i class="fa-solid fa-envelope"></i> EMAIL
                                        </span>
                                    @endif
                                    <span class="font-mono text-[10px] text-slate-400">{{ $d->event_type }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-white">
                                {{ $d->recipient }}
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-bold text-white text-xs">{{ $d->subject_or_title }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 uppercase">
                                    {{ $d->status }}
                                </span>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $d->created_at->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button" 
                                        @click="activeDispatch = {{ json_encode($d) }}; previewModal = true"
                                        class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-uph-gold hover:text-uph-navy font-bold text-xs transition-colors inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Preview</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-paper-plane text-3xl mb-2 text-slate-600"></i>
                                <p class="font-semibold">Belum ada riwayat pengiriman notifikasi eksternal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispatches->hasPages())
            <div class="p-4 border-t border-white/10">
                {{ $dispatches->links() }}
            </div>
        @endif
    </div>

    <!-- Preview Modal -->
    <div x-show="previewModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-white/20 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl p-6 space-y-4" @click.away="previewModal = false">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-uph-gold" x-text="activeDispatch ? activeDispatch.dispatch_id : ''"></span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                          :class="activeDispatch && activeDispatch.channel === 'whatsapp' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-sky-500/20 text-sky-300'"
                          x-text="activeDispatch ? activeDispatch.channel : ''"></span>
                </div>
                <button @click="previewModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- WhatsApp Preview Rendering -->
            <template x-if="activeDispatch && activeDispatch.channel === 'whatsapp'">
                <div class="bg-[#0b141a] p-6 rounded-2xl border border-white/10 flex justify-center">
                    <div class="max-w-md w-full bg-[#005c4b] text-[#e9edef] rounded-2xl rounded-tl-sm p-4 shadow-lg text-xs leading-relaxed font-sans space-y-2">
                        <pre class="whitespace-pre-wrap font-sans text-xs" x-text="activeDispatch.payload_body"></pre>
                        <div class="flex items-center justify-end gap-1 text-[10px] text-[#8696a0]">
                            <span x-text="new Date(activeDispatch.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                            <i class="fa-solid fa-check-double text-[#53bdeb]"></i>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Email Preview Rendering -->
            <template x-if="activeDispatch && activeDispatch.channel === 'email'">
                <div class="bg-white rounded-xl p-4 text-slate-800 max-h-96 overflow-y-auto border border-white/20">
                    <div x-html="activeDispatch.payload_body"></div>
                </div>
            </template>

            <div class="flex justify-end pt-2 border-t border-white/10">
                <button type="button" @click="previewModal = false" class="px-4 py-2 rounded-xl bg-white/10 text-white text-xs font-bold hover:bg-white/20">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
