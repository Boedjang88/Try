@extends('layouts.portal', ['title' => 'AI Academic Advisor & GPA Simulator'])

@section('content')
<div class="space-y-6" x-data="gpaSimulator()">
    <!-- Executive Header -->
    <div class="bg-gradient-to-r from-uph-navy via-slate-900 to-uph-navy p-6 sm:p-8 rounded-2xl text-white shadow-xl border border-uph-gold/30 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 mb-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    AI Academic Copilot &bull; Kemdiktisaintek OBE Standard
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Smart Academic Advisor & Simulator IPK</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Simulasikan proyeksi Indeks Prestasi Semester (IPS) & Kumulatif (IPK), kalkulasi kelulusan predikat pujian (Cum Laude), dan peroleh rekomendasi mata kuliah semester berikutnya secara otomatis.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/portal/student/krs" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check"></i>
                    KRS Aktif
                </a>
                <a href="/portal/student/khs" class="px-4 py-2.5 rounded-xl bg-uph-gold text-uph-navy hover:bg-uph-gold-warm text-xs font-bold shadow-lg shadow-uph-gold/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice"></i>
                    KHS & Transkrip
                </a>
            </div>
        </div>
    </div>

    <!-- Main Simulator Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Interactive Course Grade Slider (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-uph-gold"></i>
                            Simulasi Nilai Huruf Semester Berjalan
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ubah estimasi nilai huruf untuk melihat dampak seketika pada IPK kumulatif.</p>
                    </div>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-uph-navy/10 text-uph-navy dark:bg-uph-navy/40 dark:text-uph-gold">
                        {{ $activePeriod->name ?? '2026/2027 Ganjil' }}
                    </span>
                </div>

                <!-- Course List with Grade Pickers -->
                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($enrolledItems as $item)
                        @php
                            $course = $item->classSection?->course;
                            $sks = $course->sks ?? 3;
                        @endphp
                        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $course->code ?? 'MK001' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $sks }} SKS</span>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white truncate mt-0.5">
                                    {{ $course->name ?? 'Mata Kuliah' }}
                                </h3>
                                <p class="text-xs text-slate-400">Kelas {{ $item->classSection?->name ?? 'A' }} &bull; {{ $item->classSection?->lecturer?->user?->full_name ?? 'Dosen' }}</p>
                            </div>

                            <!-- Grade Select Button Group -->
                            <div class="flex items-center gap-1">
                                <template x-for="grade in ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'D', 'E']" :key="grade">
                                    <button type="button" 
                                            @click="setGrade('{{ $item->id }}', grade)"
                                            class="w-8 h-8 rounded-lg text-xs font-mono font-bold transition-all flex items-center justify-center border"
                                            :class="simulatedGrades['{{ $item->id }}'] === grade 
                                                ? 'bg-uph-navy text-uph-gold border-uph-gold shadow-md scale-105 dark:bg-uph-gold dark:text-uph-navy' 
                                                : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200'"
                                            x-text="grade">
                                    </button>
                                </template>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                            Tidak ada mata kuliah aktif di KRS semester ini. Silakan isi KRS terlebih dahulu.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Automated Study Plan Recommendation for Next Semester -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-uph-crimson"></i>
                            Rekomendasi Paket Studi Semester Berikutnya
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis otomatis prasyarat kurikulum & mata kuliah wajib untuk lulus tepat 8 semester.</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                        {{ $recommendations['total_recommended_sks'] ?? 0 }} / {{ $recommendations['max_allowed_sks'] ?? 24 }} SKS
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($recommendations['recommended_courses'] ?? [] as $rec)
                        <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-uph-navy/30 dark:hover:border-uph-gold/40 transition-colors flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-uph-navy/10 text-uph-navy dark:bg-uph-navy dark:text-uph-gold flex items-center justify-center font-bold text-xs font-mono">
                                    {{ $rec['semester_level'] ?? 1 }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300">{{ $rec['code'] }}</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $rec['is_mandatory'] ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : 'bg-slate-200 text-slate-600' }}">
                                            {{ $rec['priority'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white mt-0.5">{{ $rec['name'] }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold font-mono px-2.5 py-1 rounded bg-white dark:bg-slate-900 border text-slate-700 dark:text-slate-300">
                                {{ $rec['sks'] }} SKS
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            Semua mata kuliah wajib kurikulum telah terpenuhi atau sedang diambil.
                        </div>
                    @endforelse
                </div>

                @if(!empty($recommendations['locked_courses']))
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Mata Kuliah Belum Terbuka (Terkendala Prasyarat):</h4>
                        <div class="space-y-1.5">
                            @foreach($recommendations['locked_courses'] as $locked)
                                <div class="text-xs text-slate-500 flex items-center justify-between">
                                    <span><i class="fa-solid fa-lock text-slate-400 mr-1.5"></i> {{ $locked['code'] }} - {{ $locked['name'] }}</span>
                                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">Prasyarat: {{ implode(', ', $locked['missing_prerequisites']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Real-Time Projected IPK & Honors Card (1 Col) -->
        <div class="space-y-6">
            <!-- Projected Score Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Hasil Simulasi Real-Time</h3>
                
                <!-- Big IPK Display -->
                <div class="mt-4 text-center p-6 rounded-2xl bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Proyeksi IPK Kumulatif</p>
                    <div class="text-5xl font-black font-mono tracking-tight text-uph-navy dark:text-uph-gold mt-1" x-text="simData.projected_ipk.toFixed(2)">
                        {{ number_format($baseline['projected_ipk'], 2) }}
                    </div>
                    <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                         :class="{
                            'bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950 dark:text-amber-200': simData.predicate_badge_color === 'gold' || simData.predicate_badge_color === 'amber',
                            'bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950 dark:text-emerald-200': simData.predicate_badge_color === 'emerald',
                            'bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-950 dark:text-blue-200': simData.predicate_badge_color === 'blue',
                            'bg-indigo-100 text-indigo-800 border border-indigo-300 dark:bg-indigo-950 dark:text-indigo-200': simData.predicate_badge_color === 'indigo',
                            'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200': simData.predicate_badge_color === 'slate'
                         }">
                        <i class="fa-solid fa-medal"></i>
                        <span x-text="simData.graduation_predicate">{{ $baseline['graduation_predicate'] }}</span>
                    </div>
                </div>

                <!-- IPS & SKS Stats -->
                <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700">
                        <p class="text-[11px] text-slate-400">Proyeksi IPS</p>
                        <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-0.5" x-text="simData.projected_ips.toFixed(2)">
                            {{ number_format($baseline['projected_ips'], 2) }}
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700">
                        <p class="text-[11px] text-slate-400">SKS Semester Ini</p>
                        <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-0.5" x-text="simData.semester_sks">
                            {{ $baseline['semester_sks'] }}
                        </p>
                    </div>
                </div>

                <!-- SKS Graduation Progress Bar -->
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                        <span class="text-slate-600 dark:text-slate-300">Progress Kelulusan SKS</span>
                        <span class="font-mono text-uph-navy dark:text-uph-gold font-bold">
                            <span x-text="simData.projected_sks_completed">{{ $baseline['projected_sks_completed'] }}</span> / 144 SKS
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-uph-navy to-uph-gold h-2.5 rounded-full transition-all duration-500"
                             :style="'width: ' + simData.completion_percentage + '%'"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1 text-right">
                        Sisa <span class="font-bold text-slate-700 dark:text-slate-200" x-text="simData.sks_remaining">{{ $baseline['sks_remaining'] }}</span> SKS lagi untuk wisuda
                    </p>
                </div>
            </div>

            <!-- Honors / Cum Laude Eligibility Rubric Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-award text-uph-gold"></i>
                    Syarat Predikat Pujian (Cum Laude)
                </h3>
                
                <div class="mt-3 space-y-2.5 text-xs">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid mt-0.5" :class="simData.projected_ipk >= 3.51 ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-slate-300'"></i>
                        <span :class="simData.projected_ipk >= 3.51 ? 'text-slate-800 dark:text-slate-200 font-semibold' : 'text-slate-400'">
                            IPK Kumulatif &ge; 3.51 (Min. Cum Laude)
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fa-solid mt-0.5" :class="simData.honors_eligible ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-uph-crimson'"></i>
                        <span :class="simData.honors_eligible ? 'text-slate-800 dark:text-slate-200 font-semibold' : 'text-slate-500'">
                            Tidak ada nilai D atau E di seluruh riwayat studi
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fa-solid mt-0.5" :class="simData.current_semester <= 8 ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-uph-crimson'"></i>
                        <span :class="simData.current_semester <= 8 ? 'text-slate-800 dark:text-slate-200 font-semibold' : 'text-slate-500'">
                            Masa studi maksimal 8 semester reguler
                        </span>
                    </div>
                </div>

                <template x-if="simData.honors_disqualification_reasons.length > 0">
                    <div class="mt-4 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-800 dark:text-red-300">
                        <p class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Catatan Ketidaklayakan Predikat:
                        </p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-[11px]">
                            <template x-for="r in simData.honors_disqualification_reasons" :key="r">
                                <li x-text="r"></li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function gpaSimulator() {
    return {
        simulatedGrades: {},
        simData: @json($baseline),
        init() {
            @foreach($enrolledItems as $item)
                this.simulatedGrades['{{ $item->id }}'] = '{{ $item->letter_grade ?? "A" }}';
            @endforeach
        },
        setGrade(krsItemId, grade) {
            this.simulatedGrades[krsItemId] = grade;
            this.syncSimulation();
        },
        async syncSimulation() {
            try {
                const res = await fetch('/portal/student/smart-advisor/simulate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        simulated_grades: this.simulatedGrades
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.simData = data.data;
                }
            } catch (err) {
                console.error('Simulation error:', err);
            }
        }
    }
}
</script>
@endsection
