@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{
    unlockModalOpen: false,
    lockModalOpen: false,
    activeSectionId: null,
    activeSectionCode: '',
    activeCourseName: '',
    auditReason: '',
    errorMessage: '',
    isSubmitting: false,

    openUnlockModal(id, code, name) {
        this.activeSectionId = id;
        this.activeSectionCode = code;
        this.activeCourseName = name;
        this.auditReason = '';
        this.errorMessage = '';
        this.unlockModalOpen = true;
    },

    async submitUnlock() {
        if (!this.auditReason || this.auditReason.trim().length < 8) {
            this.errorMessage = 'Alasan override wajib diisi minimal 8 karakter demi kepatuhan audit BAAK/Lembaga Akreditasi.';
            return;
        }

        this.isSubmitting = true;
        this.errorMessage = '';
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');

        try {
            const res = await fetch(`/portal/kaprodi/grade-locks/${this.activeSectionId}/unlock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ audit_reason: this.auditReason.trim() })
            });

            const data = await res.json();
            if (data.success) {
                this.unlockModalOpen = false;
                if (window.EnterpriseToast) {
                    window.EnterpriseToast.success(data.message, 'Override Berhasil');
                }
                setTimeout(() => window.location.reload(), 800);
            } else {
                this.errorMessage = data.message || 'Gagal membuka kunci nilai.';
            }
        } catch (e) {
            this.errorMessage = 'Terjadi kesalahan jaringan.';
        } finally {
            this.isSubmitting = false;
        }
    },

    async toggleLock(sectionId, action) {
        if (!confirm(`Konfirmasi: Kunci nilai matakuliah ini secara resmi?`)) return;

        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            const res = await fetch(`/portal/kaprodi/grade-locks/${sectionId}/lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
            const data = await res.json();
            if (data.success) {
                if (window.EnterpriseToast) window.EnterpriseToast.success(data.message, 'Terkunci');
                setTimeout(() => window.location.reload(), 800);
            }
        } catch (e) {
            alert('Gagal mengunci nilai.');
        }
    }
}">

    <!-- Breadcrumb & Header Banner -->
    <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded bg-uph-gold/20 text-uph-gold border border-uph-gold/30 text-[10px] font-extrabold uppercase tracking-widest">
                    Otoritas Ketua Program Studi (Kaprodi)
                </span>
                <span class="text-xs text-slate-300 font-mono">&bull; Periode: {{ $activePeriod->name ?? '2026/2027 Ganjil' }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-white">Manajemen Kunci Nilai & Override Mutu</h1>
            <p class="text-xs text-slate-300 mt-1">
                Program Studi: <strong class="text-uph-gold">{{ $studyProgram->name_id ?? 'Informatika' }}</strong> &bull; 
                Fakultas: <span class="text-white">{{ $studyProgram->faculty->name_id ?? 'Ilmu Komputer' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/portal/kaprodi/dashboard" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors flex items-center gap-2 border border-white/10">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dasbor
            </a>
            <a href="/curriculum" class="px-4 py-2 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-uph-navy-dark font-extrabold text-xs shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-diagram-project"></i> Matriks CPL / CPMK
            </a>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Seksi Matakuliah</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-mono font-black text-uph-navy">{{ $classSections->count() }}</span>
                <span class="text-[11px] font-bold text-slate-500">Kelas Dibuka</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nilai Terkunci Resmi</span>
            <div class="flex items-baseline justify-between mt-2">
                @php
                    $lockedCount = $classSections->filter(fn($s) => $s->krsItems->where('is_locked', true)->count() > 0)->count();
                @endphp
                <span class="text-2xl font-mono font-black text-emerald-600">{{ $lockedCount }}</span>
                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Ledger Beku</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Draft / Belum Dikunci</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-mono font-black text-amber-500">{{ $classSections->count() - $lockedCount }}</span>
                <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Dalam Penilaian</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Mahasiswa Dinilai</span>
            <div class="flex items-baseline justify-between mt-2">
                @php
                    $totalStudents = $classSections->sum(fn($s) => $s->krsItems->count());
                @endphp
                <span class="text-2xl font-mono font-black text-uph-crimson">{{ $totalStudents }}</span>
                <span class="text-[11px] font-bold text-slate-500">Peserta Kuliah</span>
            </div>
        </div>
    </div>

    <!-- Main Class Sections Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 bg-surface-slate border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Daftar Status Penguncian Nilai Seksi Kuliah</h2>
                <p class="text-xs text-slate-500">Kunci nilai mencegah modifikasi sepihak. Pembukaan kunci (override) diaudit secara permanen.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-3 px-4">Kode & Matakuliah</th>
                        <th class="py-3 px-3 text-center">Seksi</th>
                        <th class="py-3 px-3 text-center">SKS</th>
                        <th class="py-3 px-4">Dosen Pengampu</th>
                        <th class="py-3 px-3 text-center">Peserta</th>
                        <th class="py-3 px-3 text-center">Status Ledger</th>
                        <th class="py-3 px-4 text-center">Aksi Otoritas Kaprodi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classSections as $section)
                    @php
                        $isLocked = $section->krsItems->where('is_locked', true)->count() > 0;
                        $enrolledCount = $section->krsItems->count();
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $section->course->name_id }}</span>
                            <span class="font-mono text-uph-navy font-bold text-[11px]">{{ $section->course->code }}</span>
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700">
                            {{ $section->section_code }}
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-800">
                            {{ $section->course->sks }}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-800 block truncate max-w-xs">
                                {{ $section->lecturer->formatted_name ?? ($section->lecturer->user->full_name ?? 'Tim Dosen') }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">NIDN: {{ $section->lecturer->nidn ?? '-' }}</span>
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700">
                            {{ $enrolledCount }} / {{ $section->max_quota }}
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            @if($isLocked)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Terkunci (Locked)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-lock-open text-[9px]"></i> Terbuka (Editable)
                                </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="/grading/{{ $section->id }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1" title="Lihat Spreadsheet">
                                    <i class="fa-solid fa-table-cells"></i> Rekap
                                </a>

                                @if($isLocked)
                                <button type="button" 
                                        @click="openUnlockModal('{{ $section->id }}', '{{ $section->section_code }}', '{{ addslashes($section->course->name_id) }}')"
                                        class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-key"></i> Buka Kunci
                                </button>
                                @else
                                <button type="button" 
                                        @click="toggleLock('{{ $section->id }}', 'lock')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-lock"></i> Kunci Nilai
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            Tidak ada kelas perkuliahan ditemukan pada program studi ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Audit Trail Section -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-bold text-sm text-slate-800 uppercase tracking-wider">Log Audit Override Nilai (BAAK & SPMI Compliance)</h3>
                <p class="text-xs text-slate-500">Rekam jejak tidak dapat dihapus untuk setiap pembukaan kunci nilai.</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-600 font-mono text-[10px] font-bold">10 Aktivitas Terakhir</span>
        </div>

        <div class="space-y-2">
            @forelse($auditLogs as $log)
            <div class="flex items-start justify-between p-3 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 h-6 w-6 rounded-full bg-uph-navy/10 text-uph-navy flex items-center justify-center text-xs shrink-0 font-bold">
                        <i class="fa-solid fa-shield-halved text-[10px]"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800">{{ $log->description }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            Oleh: <strong class="text-slate-600">{{ $log->user->full_name ?? 'Sistem' }}</strong> &bull; IP: <span class="font-mono">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                        </p>
                    </div>
                </div>
                <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
            </div>
            @empty
            <p class="text-xs text-slate-400 italic">Belum ada riwayat override nilai pada periode ini.</p>
            @endforelse
        </div>
    </div>

    <!-- Unlock Override Modal (Mandatory Audit Reason) -->
    <div x-show="unlockModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="unlockModalOpen = false">
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2 text-amber-600 font-display font-black text-base">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Otorisasi Pembukaan Kunci Nilai</span>
                </div>
                <button type="button" @click="unlockModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 leading-relaxed">
                Anda akan membuka kunci nilai kelas <strong x-text="activeCourseName"></strong> (Seksi <span x-text="activeSectionCode"></span>).
                Dosen pengampu akan diizinkan mengubah nilai komponen mahasiswa kembali.
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">
                    Alasan Pembukaan Kunci (Wajib Diisi - Audit Trail) <span class="text-uph-crimson">*</span>
                </label>
                <textarea x-model="auditReason" rows="4" 
                          placeholder="Tuliskan alasan resmi pembukaan kunci (min. 8 karakter), contoh: Koreksi nilai UAS mahasiswa Christian Jonathan berdasarkan persetujuan Dekanat per 15 Nov 2026..." 
                          class="w-full p-3 text-xs border rounded-xl focus:ring-2 focus:ring-uph-gold focus:border-uph-navy outline-none"></textarea>
                <div class="flex justify-between items-center text-[10px] text-slate-400">
                    <span>Minimal 8 karakter</span>
                    <span :class="auditReason.trim().length >= 8 ? 'text-emerald-600 font-bold' : 'text-slate-400'" x-text="`${auditReason.trim().length} karakter`"></span>
                </div>
            </div>

            <template x-if="errorMessage">
                <div class="p-3 rounded-lg bg-red-100 text-red-800 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span x-text="errorMessage"></span>
                </div>
            </template>

            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" @click="unlockModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" 
                        @click="submitUnlock()" 
                        :disabled="isSubmitting || auditReason.trim().length < 8"
                        class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="!isSubmitting"><i class="fa-solid fa-key mr-1"></i> Buka Kunci Nilai Resmi</span>
                    <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
