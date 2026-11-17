@extends('layouts.portal')

@section('title', 'Tiket Helpdesk ' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">

    <!-- Header & Ticket Info Bar -->
    <div class="bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.helpdesk.index') }}" class="text-xs text-uph-gold hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Antrean
                    </a>
                    <span class="text-slate-600">&bull;</span>
                    <span class="font-mono text-xs text-slate-300 font-bold">{{ $ticket->ticket_number }}</span>
                </div>
                <h1 class="text-xl font-bold font-serif text-white">
                    {{ $ticket->subject }}
                </h1>
            </div>

            <!-- Status Controls -->
            <div class="flex items-center gap-2">
                @if($isStaff)
                    <form method="POST" action="{{ route('portal.helpdesk.status', $ticket) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="status" onchange="this.form.submit()" 
                                class="bg-slate-800 text-xs text-slate-200 border border-white/20 rounded-lg px-3 py-1.5 focus:border-uph-gold">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Status: Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Status: In Progress</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Status: Resolved (Tuntas)</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Status: Closed</option>
                        </select>
                    </form>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 uppercase">
                        {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 pt-2 border-t border-white/10">
            <span><i class="fa-solid fa-building mr-1 text-slate-500"></i>Biro: <strong class="text-white">{{ strtoupper($ticket->department) }}</strong></span>
            <span><i class="fa-solid fa-tag mr-1 text-slate-500"></i>Kategori: <strong class="text-white">{{ $ticket->category }}</strong></span>
            <span><i class="fa-solid fa-user mr-1 text-slate-500"></i>Pemohon: <strong class="text-white">{{ $ticket->user->full_name ?? 'Civitas' }}</strong></span>
            <span><i class="fa-solid fa-stopwatch mr-1 text-slate-500"></i>Target SLA: 
                <strong class="{{ $ticket->isSlaOverdue() ? 'text-rose-400 font-bold' : 'text-emerald-400' }}">
                    {{ $ticket->sla_due_at->format('d M Y, H:i') }} ({{ $ticket->sla_due_at->diffForHumans() }})
                </strong>
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Conversation Timeline Thread -->
    <div class="bg-slate-900/80 rounded-2xl border border-white/10 p-6 space-y-4 shadow-xl">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-white/10 pb-3">
            <i class="fa-solid fa-comments text-uph-gold"></i>
            Riwayat Tanggapan &amp; Catatan Biro
        </h2>

        <div class="space-y-4">
            @foreach($ticket->replies as $reply)
                @php
                    $isStaffReply = in_array($reply->user->primary_role ?? $reply->user->role, ['superadmin', 'baak', 'bauk']);
                @endphp
                <div class="p-4 rounded-xl border {{ $reply->is_internal_note ? 'bg-amber-500/10 border-amber-500/30' : ($isStaffReply ? 'bg-uph-navy/40 border-uph-gold/30' : 'bg-white/5 border-white/10') }}">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-white">{{ $reply->user->full_name ?? 'User' }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-white/10 text-slate-300 uppercase">
                                {{ strtoupper($reply->user->primary_role ?? 'USER') }}
                            </span>
                            @if($reply->is_internal_note)
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <i class="fa-solid fa-lock mr-1"></i>CATATAN INTERNAL STAF
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    <p class="text-xs text-slate-200 leading-relaxed whitespace-pre-wrap">{{ $reply->message }}</p>
                </div>
            @endforeach
        </div>

        <!-- Reply Input Box -->
        <form method="POST" action="{{ route('portal.helpdesk.reply', $ticket) }}" class="pt-4 border-t border-white/10 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Kirim Balasan / Tindak Lanjut</label>
                <textarea name="message" rows="3" required placeholder="Tuliskan balasan, panduan solusi, atau pertanyaan lanjutan..." class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold"></textarea>
            </div>

            <div class="flex items-center justify-between gap-3">
                @if($isStaff)
                    <label class="flex items-center gap-2 text-xs text-amber-300 cursor-pointer">
                        <input type="checkbox" name="is_internal_note" value="1" class="rounded bg-slate-800 border-white/20 text-uph-gold">
                        <span>Catatan internal biro (hanya terlihat oleh sesama staf)</span>
                    </label>
                @else
                    <div></div>
                @endif

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-uph-gold text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    Kirim Balasan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
