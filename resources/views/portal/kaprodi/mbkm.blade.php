@extends('layouts.portal')

@section('title', 'Kaprodi - Konversi SKS Program MBKM')

@section('content')
<div class="space-y-6" x-data="{
    openModal: false,
    selectedProgram: null,
    conversions: [
        { course_id: '', equivalent_sks: 3, converted_numeric_score: 85 }
    ],
    addCourseRow() {
        if (this.conversions.length < 7) {
            this.conversions.push({ course_id: '', equivalent_sks: 3, converted_numeric_score: 85 });
        }
    },
    removeCourseRow(index) {
        if (this.conversions.length > 1) {
            this.conversions.splice(index, 1);
        }
    },
    getTotalSks() {
        return this.conversions.reduce((sum, item) => sum + (parseInt(item.equivalent_sks) || 0), 0);
    },
    openApprovalModal(program) {
        this.selectedProgram = program;
        this.openModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/10 text-indigo-400 border border-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </span>
                <h1 class="text-2xl font-bold text-white tracking-wide">Workspace Kaprodi: Konversi SKS MBKM</h1>
            </div>
            <p class="text-sm text-slate-400 mt-1">Pemetaan ekuivalensi mata kuliah kurikulum terhadap aktivitas MBKM mahasiswa (Maksimal 20 SKS).</p>
        </div>
    </div>

    <!-- Programs Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa</th>
                        <th class="py-3 px-4">Program & Mitra</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Total SKS</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($programs as $prog)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $prog->student->user->full_name }}</div>
                                <div class="text-xs font-mono text-amber-400">{{ $prog->student->nim }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-500/10 text-indigo-300 border border-indigo-500/30">
                                    {{ str_replace('_', ' ', $prog->program_type) }}
                                </span>
                                <div class="font-semibold text-white mt-1">{{ $prog->partner_name }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-400">
                                {{ $prog->start_date->format('d/m/Y') }} – {{ $prog->end_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($prog->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Disetujui
                                    </span>
                                @elseif ($prog->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        Menunggu Review
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-white">
                                {{ $prog->total_converted_sks }} SKS
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($prog->status === 'pending')
                                    <button type="button" 
                                            @click="openApprovalModal({{ json_encode($prog) }})"
                                            class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs shadow-md transition-all">
                                        Petakan 20 SKS & Setujui
                                    </button>
                                @else
                                    <span class="text-xs text-slate-500">Telah Diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500 text-sm">
                                Tidak ada pengajuan program MBKM yang memerlukan verifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $programs->links() }}
        </div>
    </div>

    <!-- Conversion & Approval Modal -->
    <div x-show="openModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">
        <div @click.away="openModal = false" class="relative w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-3xl p-6 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Pemetaan Konversi Ekuivalensi Mata Kuliah (Maks 20 SKS)
                </h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="'/portal/kaprodi/mbkm/' + (selectedProgram ? selectedProgram.id : '') + '/approve'" method="POST" class="space-y-4 mt-4 overflow-y-auto flex-1 pr-2">
                @csrf

                <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400">Total Akumulasi Konversi:</span>
                        <div class="text-xl font-black text-amber-400">
                            <span x-text="getTotalSks()"></span> / 20 SKS
                        </div>
                    </div>
                    <button type="button" @click="addCourseRow()" :disabled="getTotalSks() >= 20" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-colors disabled:opacity-40">
                        + Tambah Mata Kuliah Ekuivalen
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in conversions" :key="index">
                        <div class="p-3 rounded-xl bg-slate-950/40 border border-slate-800 flex items-center gap-3">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Mata Kuliah Kurikulum</label>
                                <select :name="'conversions[' + index + '][course_id]'" x-model="item.course_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white">
                                    <option value="">-- Pilih Mata Kuliah Ekuivalen --</option>
                                    @foreach ($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }} ({{ $c->sks }} SKS)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-20">
                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">SKS</label>
                                <input type="number" :name="'conversions[' + index + '][equivalent_sks]'" x-model.number="item.equivalent_sks" min="1" max="6" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-center text-white">
                            </div>

                            <div class="w-24">
                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Nilai Angka</label>
                                <input type="number" :name="'conversions[' + index + '][converted_numeric_score]'" x-model.number="item.converted_numeric_score" min="0" max="100" step="0.5" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-center text-emerald-400 font-bold">
                            </div>

                            <button type="button" @click="removeCourseRow(index)" class="pt-5 text-rose-400 hover:text-rose-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Verifikasi Kaprodi</label>
                    <textarea name="approval_notes" rows="2" placeholder="Catatan konversi SKS kurikulum..." class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-xs text-white"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700">
                        Batal
                    </button>
                    <button type="submit" :disabled="getTotalSks() > 20 || getTotalSks() === 0" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-slate-950 font-bold text-xs shadow-lg hover:from-emerald-400 hover:to-teal-500 transition-all disabled:opacity-40">
                        Simpan Konversi & Sahkan Transkrip
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
