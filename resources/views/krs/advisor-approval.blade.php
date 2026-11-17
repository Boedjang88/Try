@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{ rejectModalOpen: false, activeSubmissionId: '', rejectNote: '', isProcessing: false }">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-uph-gold">Persetujuan Dosen Pembimbing Akademik (Dosen PA)</span>
            <h1 class="text-2xl font-display font-black text-uph-navy mt-1">Review & Validasi KRS Mahasiswa Bimbingan</h1>
            <p class="text-xs text-slate-500 mt-1">
                Dosen PA: <strong class="text-slate-800">{{ auth()->user()->full_name }}</strong> &bull; 
                Periode Akademik: <strong class="text-uph-navy">{{ $activePeriod->name }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-1.5 rounded-xl bg-uph-navy/10 text-uph-navy font-bold text-xs">
                Total Mahasiswa Bimbingan: {{ $adviseeSubmissions->count() }}
            </span>
        </div>
    </div>

    <!-- Advisee KRS Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 bg-surface-slate border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Daftar Pengajuan Rencana Studi</h2>
            <span class="text-xs text-slate-400">Verifikasi kelayakan SKS berdasarkan IPS mahasiswa</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa (NIM)</th>
                        <th class="py-3 px-3 text-center">Semester</th>
                        <th class="py-3 px-3 text-center">IPS Lalu</th>
                        <th class="py-3 px-3 text-center">IPK Kumulatif</th>
                        <th class="py-3 px-3 text-center">SKS Diajukan</th>
                        <th class="py-3 px-4">Daftar Mata Kuliah</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($adviseeSubmissions as $sub)
                    <tr class="hover:bg-slate-50" id="sub-row-{{ $sub->id }}">
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $sub->student->user->full_name }}</span>
                            <span class="font-mono text-uph-navy font-bold text-[11px]">{{ $sub->student->nim }}</span>
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700">
                            Sem. {{ $sub->student->current_semester }}
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-800">
                            {{ $sub->student->last_semester_gpa_ips }}
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono font-bold text-uph-navy">
                            {{ $sub->student->current_gpa_ipk }}
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            <span class="px-2 py-0.5 rounded font-mono font-bold text-xs {{ $sub->total_sks > $sub->student->max_sks_allowed ? 'bg-red-100 text-uph-crimson' : 'bg-slate-100 text-slate-800' }}">
                                {{ $sub->total_sks }} / {{ $sub->student->max_sks_allowed }} SKS
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <div class="space-y-1 max-w-xs">
                                @foreach($sub->items as $item)
                                <div class="text-[11px] text-slate-700 truncate">
                                    <strong class="font-mono text-uph-navy">{{ $item->classSection->course->code }}</strong> - {{ $item->classSection->course->name_id }} ({{ $item->classSection->section_code }})
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            <span id="status-badge-{{ $sub->id }}" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $sub->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($sub->status === 'submitted' ? 'bg-blue-100 text-blue-800' : ($sub->status === 'rejected' ? 'bg-red-100 text-uph-crimson' : 'bg-slate-100 text-slate-600')) }}">
                                {{ $sub->status }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($sub->status === 'submitted' || $sub->status === 'rejected')
                                <button type="button" 
                                        @click="approveKrs('{{ $sub->id }}')" 
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition-colors">
                                    <i class="fa-solid fa-check mr-1"></i> Setujui
                                </button>
                                <button type="button" 
                                        @click="activeSubmissionId = '{{ $sub->id }}'; rejectModalOpen = true" 
                                        class="px-3 py-1.5 rounded-lg border border-uph-crimson text-uph-crimson hover:bg-red-50 font-bold text-xs transition-colors">
                                    <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                </button>
                                @else
                                <a href="/krs/print/{{ $sub->id }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                                    <i class="fa-solid fa-file-pdf mr-1"></i> Lihat Dokumen
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            Tidak ada pengajuan KRS mahasiswa bimbingan yang menunggu peninjauan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div x-show="rejectModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="rejectModalOpen = false">
            <h3 class="font-display font-bold text-base text-uph-crimson">Tolak Pengajuan KRS Mahasiswa</h3>
            <p class="text-xs text-slate-500">Berikan catatan bimbingan atau instruksi mata kuliah yang harus diganti oleh mahasiswa.</p>
            <div class="space-y-3">
                <textarea x-model="rejectNote" rows="3" placeholder="Contoh: Harap kurangi 1 mata kuliah karena batas SKS terlampaui..." class="w-full p-3 text-xs border rounded-lg focus:ring-1 focus:ring-uph-crimson"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border rounded-lg text-slate-600 font-bold text-xs">Batal</button>
                <button type="button" @click="submitRejection()" class="px-5 py-2 bg-uph-crimson text-white rounded-lg font-bold text-xs shadow">Kirim Penolakan</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function approveKrs(submissionId) {
        if (!confirm('Setujui KRS mahasiswa ini? Mahasiswa akan dapat mencetak dokumen resmi KRS.')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`/api/krs/approve/${submissionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await res.json();
            if (data.success) {
                const badge = document.getElementById(`status-badge-${submissionId}`);
                if (badge) {
                    badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800';
                    badge.textContent = 'approved';
                }
                if (window.EnterpriseToast) window.EnterpriseToast.success(data.message, 'KRS Disetujui');
            }
        } catch (e) {
            if (window.EnterpriseToast) window.EnterpriseToast.error('Gagal menyetujui KRS.');
        }
    }

    async function submitRejection() {
        const app = Alpine.$data(document.querySelector('[x-data]'));
        if (!app.rejectNote.trim()) {
            alert('Silakan tuliskan catatan penolakan.');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`/api/krs/reject/${app.activeSubmissionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ rejection_note: app.rejectNote })
            });
            const data = await res.json();
            if (data.success) {
                app.rejectModalOpen = false;
                const badge = document.getElementById(`status-badge-${app.activeSubmissionId}`);
                if (badge) {
                    badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-100 text-uph-crimson';
                    badge.textContent = 'rejected';
                }
                if (window.EnterpriseToast) window.EnterpriseToast.info(data.message, 'KRS Dikembalikan');
            }
        } catch (e) {
            if (window.EnterpriseToast) window.EnterpriseToast.error('Gagal mengirim penolakan.');
        }
    }
</script>
@endsection
