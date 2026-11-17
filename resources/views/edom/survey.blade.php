@extends('layouts.portal')

@section('title', 'Evaluasi Dosen Oleh Mahasiswa (EDOM) - UPH')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    answers: {},
    totalQuestions: {{ count($questions) }},
    get answeredCount() {
        return Object.keys(this.answers).length;
    },
    get isComplete() {
        return this.answeredCount >= this.totalQuestions;
    },
    selectAnswer(qIndex, val) {
        this.answers[qIndex] = val;
    }
}">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="/khs" class="inline-flex items-center gap-2 text-xs font-bold text-uph-navy hover:text-uph-navy-dark transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Kartu Hasil Studi (KHS)
        </a>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
            <i class="fa-solid fa-shield-halved text-amber-600"></i> Wajib Diselesaikan untuk Membuka Nilai
        </span>
    </div>

    <!-- Course & Lecturer Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-100 pb-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-uph-navy text-white flex items-center justify-center text-2xl font-bold shadow-md shrink-0">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                        Kuesioner Evaluasi Dosen (EDOM)
                    </span>
                    <h1 class="text-xl sm:text-2xl font-display font-black text-slate-900 mt-1">
                        {{ $krsItem->classSection->course->name_id }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span class="font-mono font-bold text-uph-navy">{{ $krsItem->classSection->course->code }}</span> &bull; 
                        Kelas: <strong>{{ $krsItem->classSection->section_code }}</strong> &bull; 
                        {{ $krsItem->classSection->course->sks }} SKS &bull; 
                        Periode: <strong>{{ $krsItem->classSection->academicPeriod->name ?? 'Semester Aktif' }}</strong>
                    </p>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs">
                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Dosen Pengampu</span>
                <span class="font-bold text-slate-800 text-sm block mt-0.5">
                    {{ $krsItem->classSection->lecturer->formatted_name ?? ($krsItem->classSection->lecturer->user->full_name ?? 'Dosen Pengampu') }}
                </span>
                <span class="text-slate-500 font-mono text-[11px]">
                    NIDN: {{ $krsItem->classSection->lecturer->nidn ?? '-' }}
                </span>
            </div>
        </div>

        <!-- Anonymity Notice -->
        <div class="mt-6 flex items-start gap-3 bg-blue-50/70 border border-blue-200 rounded-xl p-4 text-xs text-blue-900">
            <i class="fa-solid fa-user-secret text-blue-600 text-base mt-0.5 shrink-0"></i>
            <div>
                <strong class="font-bold">Jaminan Kerahasiaan & Anonimitas:</strong>
                <p class="text-blue-800/90 mt-0.5 leading-relaxed">
                    Kuesioner ini dievaluasi secara agregat dan sepenuhnya anonim oleh Badan Penjaminan Mutu Internal (SPMI). Identitas Anda tidak akan pernah dipublikasikan kepada dosen bersangkutan. Berikanlah penilaian yang objektif, jujur, dan bertanggung jawab.
                </p>
            </div>
        </div>

        <!-- Progress Tracker Bar -->
        <div class="mt-6 pt-6 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="font-bold text-slate-700">Progres Pengisian Kuesioner:</span>
                <span class="font-mono font-bold text-uph-navy">
                    <span x-text="answeredCount">0</span> / <span x-text="totalQuestions">10</span> Butir Soal Terjawab
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="bg-uph-crimson h-2.5 rounded-full transition-all duration-300"
                     :style="'width: ' + ((answeredCount / totalQuestions) * 100) + '%'"></div>
            </div>
        </div>
    </div>

    <!-- Survey Form -->
    <form action="/edom/{{ $krsItem->id }}" method="POST" class="space-y-6">
        @csrf

        <!-- Question Cards -->
        @foreach($questions as $index => $q)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 transition-all"
             :class="{ 'border-uph-navy/40 shadow-md ring-1 ring-uph-navy/20': answers[{{ $index }}] !== undefined }">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600">
                    {{ $q['dimension'] }}
                </span>
                <span class="font-mono text-xs font-black text-slate-400">
                    #{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <p class="text-sm sm:text-base font-bold text-slate-800 mt-2 leading-relaxed">
                {{ $q['question'] }}
            </p>

            <!-- 5-Point Likert Scale Buttons -->
            <div class="mt-5 grid grid-cols-5 gap-2 sm:gap-3">
                @php
                    $scaleOptions = [
                        1 => ['label' => 'Sangat Tidak Setuju', 'short' => 'STS', 'color' => 'peer-checked:bg-rose-600 peer-checked:text-white'],
                        2 => ['label' => 'Tidak Setuju', 'short' => 'TS', 'color' => 'peer-checked:bg-amber-500 peer-checked:text-white'],
                        3 => ['label' => 'Cukup / Netral', 'short' => 'N', 'color' => 'peer-checked:bg-slate-500 peer-checked:text-white'],
                        4 => ['label' => 'Setuju', 'short' => 'S', 'color' => 'peer-checked:bg-blue-600 peer-checked:text-white'],
                        5 => ['label' => 'Sangat Setuju', 'short' => 'SS', 'color' => 'peer-checked:bg-emerald-600 peer-checked:text-white'],
                    ];
                @endphp

                @foreach($scaleOptions as $val => $opt)
                <label class="cursor-pointer select-none">
                    <input type="radio" 
                           name="answers[{{ $index }}]" 
                           value="{{ $val }}" 
                           class="peer sr-only"
                           @change="selectAnswer({{ $index }}, {{ $val }})"
                           required>
                    <div class="p-3 text-center rounded-xl border-2 border-slate-200 bg-slate-50/50 hover:bg-slate-100 hover:border-slate-300 transition-all {{ $opt['color'] }} peer-checked:border-transparent peer-checked:shadow-sm">
                        <span class="block font-black text-sm sm:text-base">{{ $val }}</span>
                        <span class="block text-[10px] sm:text-[11px] font-medium leading-tight mt-1 line-clamp-2">
                            <span class="hidden sm:inline">{{ $opt['label'] }}</span>
                            <span class="sm:hidden">{{ $opt['short'] }}</span>
                        </span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Qualitative Feedback Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Umpan Balik Kualitatif (Opsional)</h3>
            <p class="text-xs text-slate-500">Berikan saran atau apresiasi deskriptif untuk pengembangan mutu perkuliahan yang lebih baik.</p>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-thumbs-up text-emerald-500 mr-1"></i> Aspek Positif / Kelebihan Pengajaran Dosen
                    </label>
                    <textarea name="feedback_positive" rows="3" 
                              class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                              placeholder="Contoh: Dosen sangat komunikatif, memberikan studi kasus riil industri terkini, dan materi slide sangat komprehensif..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i> Saran Konstruktif untuk Perbaikan ke Depan
                    </label>
                    <textarea name="feedback_constructive" rows="3" 
                              class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                              placeholder="Contoh: Diharapkan materi latihan koding di LMS dapat diperbanyak sebelum ujian berlangsung..."></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 sticky bottom-4">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-uph-navy"></i>
                <span x-show="!isComplete" class="text-rose-600 font-bold">
                    Lengkapi seluruh 10 butir pertanyaan sebelum mengirimkan formulir.
                </span>
                <span x-show="isComplete" class="text-emerald-600 font-bold" x-cloak>
                    Seluruh butir soal telah terjawab. Siap diserahkan!
                </span>
            </div>

            <button type="submit" 
                    :disabled="!isComplete"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md transition-all flex items-center justify-center gap-2"
                    :class="isComplete ? 'bg-uph-crimson hover:bg-uph-crimson-hover shadow-uph-crimson cursor-pointer' : 'bg-slate-300 cursor-not-allowed'">
                <i class="fa-solid fa-paper-plane"></i> Kirim Evaluasi & Buka Nilai KHS
            </button>
        </div>
    </form>
</div>
@endsection
