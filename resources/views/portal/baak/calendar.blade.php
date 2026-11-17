@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'timetable',
    editModalOpen: false,
    rolloverModalOpen: false,
    selectedPeriod: {
        id: '{{ $activePeriod->id ?? '' }}',
        name: '{{ addslashes($activePeriod->name ?? '') }}',
        start_date: '{{ $activePeriod->start_date ? $activePeriod->start_date->toDateString() : '' }}',
        end_date: '{{ $activePeriod->end_date ? $activePeriod->end_date->toDateString() : '' }}',
        krs_start_date: '{{ $activePeriod->krs_start_date ? $activePeriod->krs_start_date->format('Y-m-d\TH:i') : '' }}',
        krs_end_date: '{{ $activePeriod->krs_end_date ? $activePeriod->krs_end_date->format('Y-m-d\TH:i') : '' }}',
        is_krs_active: {{ ($activePeriod->is_krs_active ?? true) ? 'true' : 'false' }},
        is_grading_active: {{ ($activePeriod->is_grading_active ?? true) ? 'true' : 'false' }}
    },
    isSaving: false,

    async savePeriodWindow() {
        this.isSaving = true;
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            const res = await fetch(`/portal/baak/calendar/period/${this.selectedPeriod.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(this.selectedPeriod)
            });
            const data = await res.json();
            if (data.success) {
                this.editModalOpen = false;
                if (window.EnterpriseToast) window.EnterpriseToast.success(data.message, 'Kalender Diperbarui');
                setTimeout(() => window.location.reload(), 800);
            }
        } catch (e) {
            alert('Gagal memperbarui jendela kalender akademik.');
        } finally {
            this.isSaving = false;
        }
    }
}">

    <!-- Top Command Banner -->
    <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-extrabold uppercase tracking-widest">
                    Biro Administrasi Akademik (BAAK)
                </span>
                <span class="text-xs text-slate-300 font-mono">&bull; Master Calendar & Timetable</span>
            </div>
            <h1 class="text-2xl font-display font-black text-white">Kalender Akademik & Penjadwalan Ruang</h1>
            <p class="text-xs text-slate-300 mt-1">
                Atur jendela pengisian KRS, perkuliahan 16-sesi, ujian UTS/UAS, dan resolusi bentrok ruang kuliah.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="rolloverModalOpen = true" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i> Wizard Rollover Semester &amp; Billing
            </button>
            <button type="button" @click="editModalOpen = true" class="px-4 py-2 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-uph-navy-dark font-extrabold text-xs shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-calendar-days"></i> Edit Jendela Periode Aktif
            </button>
        </div>
    </div>

    <!-- Active Window Alert Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Periode Perkuliahan</span>
                <span class="text-sm font-bold text-slate-800 block mt-0.5">{{ $activePeriod->name ?? '2026/2027 Ganjil' }}</span>
                <span class="text-[11px] text-slate-500 font-mono">
                    {{ $activePeriod->start_date ? $activePeriod->start_date->format('d M Y') : '-' }} s.d. {{ $activePeriod->end_date ? $activePeriod->end_date->format('d M Y') : '-' }}
                </span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4">
            <div class="h-10 w-10 rounded-xl {{ ($activePeriod->is_krs_active ?? true) ? 'bg-blue-50 text-uph-navy' : 'bg-red-50 text-uph-crimson' }} flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Jendela KRS War Online</span>
                <span class="text-sm font-bold {{ ($activePeriod->is_krs_active ?? true) ? 'text-emerald-600' : 'text-uph-crimson' }} block mt-0.5">
                    {{ ($activePeriod->is_krs_active ?? true) ? 'Sedang Dibuka (Active)' : 'Ditutup (Closed)' }}
                </span>
                <span class="text-[11px] text-slate-500 font-mono">
                    {{ $activePeriod->krs_start_date ? $activePeriod->krs_start_date->format('d M H:i') : '-' }} - {{ $activePeriod->krs_end_date ? $activePeriod->krs_end_date->format('d M H:i') : '-' }}
                </span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4">
            <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Kapasitas Ruang</span>
                <span class="text-sm font-bold text-slate-800 block mt-0.5">{{ $rooms->count() }} Ruangan Kuliah & Lab</span>
                <span class="text-[11px] text-slate-500 font-mono">Gedung UPH Lippo Village Building A, B, C</span>
            </div>
        </div>
    </div>

    <!-- Timetable Visual Weekly Inspector -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 bg-surface-slate border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Matriks Jadwal Perkuliahan Mingguan (Zero Conflict)</h2>
                <p class="text-xs text-slate-500">Peta jadwal kelas seluruh program studi per hari dan alokasi ruang kuliah.</p>
            </div>
        </div>

        @php
            $dayNames = [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu'
            ];
        @endphp

        <div class="p-6 space-y-6">
            @foreach($dayNames as $dayNum => $dayName)
            @php
                $daySections = $sectionsByDay->get($dayNum, collect());
            @endphp
            <div class="border rounded-xl overflow-hidden">
                <div class="bg-slate-100 px-4 py-2.5 font-bold text-xs text-slate-800 flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-uph-navy"></i>
                        {{ $dayName }}
                    </span>
                    <span class="px-2 py-0.5 rounded bg-white text-slate-600 font-mono text-[10px] font-bold">
                        {{ $daySections->count() }} Kelas Terjadwal
                    </span>
                </div>

                <div class="p-4 bg-white grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($daySections as $sec)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-blue-50/40 hover:border-blue-200 transition-all text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-uph-navy text-xs">{{ $sec->course->code }} ({{ $sec->section_code }})</span>
                            <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                Ruang {{ $sec->room->code ?? 'UPH' }}
                            </span>
                        </div>
                        <p class="font-bold text-slate-800 truncate">{{ $sec->course->name_id }}</p>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-200">
                            <span class="font-mono font-semibold">
                                <i class="fa-regular fa-clock mr-1 text-slate-400"></i>{{ substr($sec->start_time ?? '08:00', 0, 5) }} - {{ substr($sec->end_time ?? '10:30', 0, 5) }}
                            </span>
                            <span class="font-medium truncate max-w-[120px]">
                                {{ $sec->lecturer->formatted_name ?? ($sec->lecturer->user->full_name ?? '-') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic py-2">Tidak ada jadwal perkuliahan pada hari {{ $dayName }}.</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Edit Academic Period Window Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2 text-uph-navy font-display font-black text-base">
                    <i class="fa-solid fa-calendar-days text-uph-gold"></i>
                    <span>Pengaturan Jendela Kalender Akademik</span>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Periode Akademik</label>
                    <input type="text" x-model="selectedPeriod.name" class="w-full p-2.5 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Semester</label>
                        <input type="date" x-model="selectedPeriod.start_date" class="w-full p-2.5 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Akhir Semester</label>
                        <input type="date" x-model="selectedPeriod.end_date" class="w-full p-2.5 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buka KRS War</label>
                        <input type="datetime-local" x-model="selectedPeriod.krs_start_date" class="w-full p-2.5 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tutup KRS War</label>
                        <input type="datetime-local" x-model="selectedPeriod.krs_end_date" class="w-full p-2.5 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy">
                    </div>
                </div>

                <div class="pt-2 space-y-2 border-t">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                        <input type="checkbox" x-model="selectedPeriod.is_krs_active" class="rounded text-uph-navy focus:ring-uph-navy">
                        <span>Aktifkan Pengisian KRS Mahasiswa (KRS War Open)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                        <input type="checkbox" x-model="selectedPeriod.is_grading_active" class="rounded text-uph-navy focus:ring-uph-navy">
                        <span>Aktifkan Input & Penguncian Nilai Dosen</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" @click="editModalOpen = false" class="px-4 py-2 border rounded-xl text-slate-600 font-bold text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" 
                        @click="savePeriodWindow()" 
                        :disabled="isSaving"
                        class="px-5 py-2 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-uph-gold font-bold text-xs shadow flex items-center gap-2">
                    <span x-show="!isSaving"><i class="fa-solid fa-save mr-1"></i> Simpan Kalender</span>
                    <span x-show="isSaving"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Wizard Rollover Semester Modal -->
    <div x-show="rolloverModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200" @click.away="rolloverModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Wizard Rollover Semester &amp; Mass Billing</h3>
                        <p class="text-xs text-slate-500">Transisi otomatis AKM, semester mahasiswa &amp; penerbitan tagihan</p>
                    </div>
                </div>
                <button type="button" @click="rolloverModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('portal.baak.calendar.rollover') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <i class="fa-solid fa-triangle-exclamation"></i> Perhatian Tindakan Massal (Irreversible)
                    </div>
                    <p>Proses ini akan mengarsipkan aktivitas kuliah (AKM) periode berjalan, menaikkan semester seluruh mahasiswa aktif (+1), mereset status finansial, dan menerbitkan tagihan semester baru secara massal.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">Periode Akademik Aktif Saat Ini (Sumber)</label>
                    <input type="hidden" name="from_period_id" value="{{ $activePeriod->id ?? '' }}">
                    <input type="text" readonly value="{{ $activePeriod->name ?? 'Tidak ada periode aktif' }} ({{ $activePeriod->code ?? '' }})" class="w-full p-2.5 bg-slate-100 border rounded-xl text-xs font-semibold text-slate-700 outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">Pilih Periode Akademik Baru yang Akan Diaktifkan (Target)</label>
                    <select name="to_period_id" required class="w-full p-2.5 border rounded-xl text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-uph-navy">
                        <option value="">-- Pilih Periode Target --</option>
                        @foreach($allPeriods as $p)
                            @if(!$p->is_active)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }}) &bull; {{ $p->semester_type }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button type="button" @click="rolloverModalOpen = false" class="px-4 py-2 border rounded-xl text-slate-600 font-bold text-xs hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow flex items-center gap-2">
                        <i class="fa-solid fa-bolt"></i> Eksekusi Transisi Semester Massal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
