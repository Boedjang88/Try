@extends('layouts.portal')

@section('title', 'Dual-Control Maker-Checker Approval Gate')

@section('content')
<div class="space-y-6" x-data="{ newModalOpen: false, approveModalOpen: false, rejectModalOpen: false, activeRequest: null, activeReqCode: '' }">

    <!-- Header & Hero -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-widest">
                    Bank-Grade Enterprise Governance
                </span>
                <span class="text-xs text-slate-400">Zero-Trust Dual-Control Engine</span>
            </div>
            <h1 class="text-2xl font-bold font-serif text-white flex items-center gap-2">
                <i class="fa-solid fa-signature text-uph-gold"></i>
                Maker-Checker Dual Approval Inbox
            </h1>
            <p class="text-xs text-slate-300">
                Pintu otorisasi dua pihak independen (Maker menginisiasi &rarr; Checker menyetujui) untuk mutasi akademik dan finansial berisiko tinggi.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="newModalOpen = true" 
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-uph-gold to-amber-500 text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg flex items-center gap-2 transition-all">
                <i class="fa-solid fa-plus-circle"></i>
                Ajukan Otorisasi Baru
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
    @if(session('error') || session('warning'))
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>
            <span>{{ session('error') ?? session('warning') }}</span>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900/60 p-4 rounded-xl border border-amber-500/30 bg-amber-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-amber-300 font-bold uppercase tracking-wider">Menunggu Checker</span>
                <i class="fa-solid fa-hourglass-half text-amber-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['pending'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Memerlukan verifikasi Checker independen</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-300 font-bold uppercase tracking-wider">Telah Disetujui & Diterapkan</span>
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['approved'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Mutasi data tereksekusi pada database ledger</p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-rose-500/30 bg-rose-500/5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-rose-300 font-bold uppercase tracking-wider">Ditolak (Rejected)</span>
                <i class="fa-solid fa-circle-xmark text-rose-400"></i>
            </div>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['rejected'] ?? 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Dibatalkan oleh Checker otoritas berwenang</p>
        </div>
    </div>

    <!-- Filters & Request Directory -->
    <div class="bg-slate-900/80 rounded-2xl border border-white/10 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-3 bg-white/[0.02]">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-uph-gold"></i>
                Daftar Tiket Otorisasi Dual-Control
            </h2>

            <form method="GET" action="{{ route('portal.governance.approvals') }}" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" 
                        class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                </select>

                <select name="workflow_type" onchange="this.form.submit()" 
                        class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Alur Kerja</option>
                    <option value="grade_change_after_lock" {{ request('workflow_type') === 'grade_change_after_lock' ? 'selected' : '' }}>Koreksi Nilai Pasca-Kunci</option>
                    <option value="tuition_waiver_or_discount" {{ request('workflow_type') === 'tuition_waiver_or_discount' ? 'selected' : '' }}>Diskon / Keringanan UKT</option>
                    <option value="krs_late_override" {{ request('workflow_type') === 'krs_late_override' ? 'selected' : '' }}>Dispensasi KRS Terlambat</option>
                    <option value="account_privilege_elevation" {{ request('workflow_type') === 'account_privilege_elevation' ? 'selected' : '' }}>Elevasi Hak Akses</option>
                </select>
            </form>
        </div>

        <div class="divide-y divide-white/5">
            @forelse($requests as $req)
                @php
                    $isMaker = ($req->maker_user_id === $user->id);
                    $canCheck = $req->canBeCheckedBy($user);
                    $diff = $req->payload_diff ?? [];
                @endphp
                <div class="p-5 hover:bg-white/[0.02] transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        
                        <!-- Left Details -->
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-uph-gold bg-uph-gold/10 px-2 py-0.5 rounded border border-uph-gold/30">
                                    {{ $req->request_code }}
                                </span>

                                @if($req->status === 'pending')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        <i class="fa-solid fa-clock mr-1"></i>MENUNGGU PERSETUJUAN
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        <i class="fa-solid fa-check-circle mr-1"></i>DISETUJUI & DIEKSEKUSI
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        <i class="fa-solid fa-ban mr-1"></i>DITOLAK
                                    </span>
                                @endif

                                <span class="text-xs text-slate-400">
                                    Tipe Alur: <strong class="text-slate-200">{{ strtoupper(str_replace('_', ' ', $req->workflow_type)) }}</strong>
                                </span>
                            </div>

                            <p class="text-xs text-slate-300 leading-relaxed">
                                <strong class="text-slate-400">Alasan Maker:</strong> {{ $req->maker_reason }}
                            </p>

                            <!-- Side-by-Side Diff Box -->
                            @if(!empty($diff))
                                <div class="bg-black/30 p-3 rounded-xl border border-white/5 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                    <div class="bg-rose-500/10 p-2.5 rounded-lg border border-rose-500/20">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-rose-400 mb-1">
                                            <i class="fa-solid fa-clock-rotate-left mr-1"></i>Kondisi Sebelum (Existing)
                                        </p>
                                        <pre class="text-[11px] font-mono text-slate-300 overflow-x-auto whitespace-pre-wrap">{{ json_encode($diff['before'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                    <div class="bg-emerald-500/10 p-2.5 rounded-lg border border-emerald-500/20">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 mb-1">
                                            <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>Usulan Perubahan (Proposed Mutation)
                                        </p>
                                        <pre class="text-[11px] font-mono text-slate-300 overflow-x-auto whitespace-pre-wrap">{{ json_encode($diff['after'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-400 pt-1">
                                <span><i class="fa-solid fa-user-pen mr-1 text-slate-500"></i>Maker: <strong class="text-slate-300">{{ $req->maker->full_name ?? 'Civitas' }}</strong> ({{ $req->maker->primary_role ?? 'staff' }})</span>
                                <span><i class="fa-solid fa-user-shield mr-1 text-slate-500"></i>Checker Wajib: <span class="text-amber-300 font-semibold">{{ strtoupper($req->checker_role_required) }}</span></span>
                                <span><i class="fa-regular fa-calendar mr-1 text-slate-500"></i>{{ $req->created_at->format('d M Y, H:i') }}</span>
                                @if($req->resolved_at)
                                    <span class="text-emerald-400"><i class="fa-solid fa-check-double mr-1"></i>Diproses: {{ $req->resolved_at->format('d M Y, H:i') }} oleh {{ $req->checker->full_name ?? 'Checker' }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            @if($req->status === 'pending')
                                @if($canCheck)
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('portal.governance.approvals.approve', $req) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui dan mengeksekusi mutasi ini secara atomik?')">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow flex items-center gap-1.5 transition-colors">
                                                <i class="fa-solid fa-check"></i>
                                                Setujui &amp; Terapkan
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('portal.governance.approvals.reject', $req) }}" onsubmit="return confirm('Tolak permohonan otorisasi ini?')">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-white font-bold text-xs shadow flex items-center gap-1.5 transition-colors">
                                                <i class="fa-solid fa-xmark"></i>
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @elseif($isMaker)
                                    <span class="text-[11px] text-amber-300/80 italic bg-amber-500/10 px-2.5 py-1 rounded border border-amber-500/20">
                                        <i class="fa-solid fa-lock mr-1"></i>Menunggu Otorisasi Checker (Dual-Control)
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-500">
                                        Perlu Otoritas {{ strtoupper($req->checker_role_required) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-xs text-slate-400 font-mono">
                                    Catatan: {{ $req->checker_notes ?? 'Selesai.' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400">
                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-600"></i>
                    <p class="text-sm font-semibold">Tidak ada tiket permohonan otorisasi dual-control.</p>
                </div>
            @endforelse
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-white/10">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form: Buat Permohonan Otorisasi Baru -->
    <div x-show="newModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-white/20 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl space-y-4 p-6" @click.away="newModalOpen = false">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-signature text-uph-gold"></i>
                    Inisiasi Permohonan Dual-Control (Maker)
                </h3>
                <button @click="newModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('portal.governance.approvals.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Jenis Alur Kerja (Workflow)</label>
                    <select name="workflow_type" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                        <option value="grade_change_after_lock">Koreksi Nilai Pasca-Kunci (Pasca UAS)</option>
                        <option value="tuition_waiver_or_discount">Diskon / Beasiswa Keringanan UKT > 50%</option>
                        <option value="krs_late_override">Dispensasi KRS Terlambat (Late Enrollment)</option>
                        <option value="account_privilege_elevation">Elevasi Hak Akses Sementara</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Peran Otoritas Checker Wajib</label>
                    <select name="checker_role_required" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                        <option value="kaprodi">Ketua Program Studi (Kaprodi)</option>
                        <option value="dean">Dekan Fakultas</option>
                        <option value="bauk">Kepala Biro Administrasi Keuangan (BAUK)</option>
                        <option value="superadmin">Super Administrator</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Target Model Type</label>
                        <input type="text" name="target_model_type" value="App\Models\KrsItem" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Target Model ID</label>
                        <input type="text" name="target_model_id" placeholder="UUID KrsItem / Invoice" required class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Alasan Pengajuan Otorisasi (Maker Reason)</label>
                    <textarea name="maker_reason" rows="3" required placeholder="Jelaskan dasar permohonan mutasi ini (misal: koreksi nilai ujian susulan, rekomendasi beasiswa yayasan, dsb)..." class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <button type="button" @click="newModalOpen = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-uph-gold text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg">
                        Kirim Permohonan ke Checker
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
