@extends('layouts.portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    timeLeft: 5400, // 90 minutes
    examSubmitted: false,
    selectedAnswers: {},
    timerString() {
        const m = Math.floor(this.timeLeft / 60);
        const s = this.timeLeft % 60;
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    },
    async submitExam() {
        if (!confirm('Apakah Anda yakin ingin menyelesaikan dan mengumpulkan lembar jawaban ujian ini?')) return;
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            const res = await fetch('/portal/student/lms/exam/{{ $classSection->id }}/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    exam_type: '{{ $examType }}',
                    answers: this.selectedAnswers,
                    score: 87.50
                })
            });
            const data = await res.json();
            if (data.success) {
                this.examSubmitted = true;
                alert(data.message);
                window.location.href = '/portal/student/khs';
            } else {
                alert(data.message || 'Gagal mengumpulkan ujian.');
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi server saat mengirim jawaban.');
        }
    },
    init() {
        const timer = setInterval(() => {
            if (this.timeLeft > 0 && !this.examSubmitted) {
                this.timeLeft--;
            } else if (this.timeLeft <= 0 && !this.examSubmitted) {
                clearInterval(timer);
                alert('Waktu ujian telah habis! Lembar jawaban akan dikumpulkan secara otomatis.');
                this.submitExam();
            }
        }, 1000);
    }
}">

    <!-- Examination Banner Header -->
    <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-extrabold uppercase tracking-widest">
                    <i class="fa-solid fa-shield-check"></i> Ujian Terverifikasi &bull; Presensi: {{ number_format($attendancePct, 0) }}% (>= 75%)
                </span>
                <span class="text-xs text-slate-300 font-mono">OBE Assessment Hub</span>
            </div>
            <h1 class="text-2xl font-display font-black text-white">
                {{ strtoupper($examType) }}: {{ $classSection->course->name_id }} ({{ $classSection->course->code }})
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Dosen: {{ $classSection->lecturer->formatted_name ?? ($classSection->lecturer->user->full_name ?? 'Dosen') }} &bull; Sesi Kelas {{ $classSection->section_code }} &bull; {{ $classSection->course->sks }} SKS
            </p>
        </div>

        <!-- Timer Display Card -->
        <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/20 text-center shrink-0">
            <span class="text-[10px] uppercase font-bold text-uph-gold block tracking-wider">Sisa Waktu Pengerjaan</span>
            <span class="text-2xl font-mono font-black text-white" x-text="timerString()">90:00</span>
        </div>
    </div>

    <!-- Student Credentials Strip -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex flex-wrap items-center justify-between text-xs text-slate-600 gap-2">
        <div><strong>Peserta Ujian:</strong> {{ $student->user->full_name }} (NIM: <span class="font-mono text-uph-navy font-bold">{{ $student->nim }}</span>)</div>
        <div><strong>Program Studi:</strong> {{ $student->studyProgram->name_id ?? 'Informatika' }}</div>
        <div><strong>Status Presensi:</strong> <span class="text-emerald-600 font-bold">Lolos Syarat Presensi ({{ number_format($attendancePct, 0) }}%)</span></div>
    </div>

    <!-- Examination Questions Card -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-8">
        <div>
            <h2 class="font-display font-bold text-lg text-slate-900 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Lembar Soal Ujian Standar OBE</span>
                <span class="text-xs text-slate-400 font-normal">5 Soal Capaian Pembelajaran (CPL-01 s/d CPL-05)</span>
            </h2>
        </div>

        <!-- Question 1 -->
        <div class="space-y-3">
            <div class="flex items-start gap-3">
                <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs shrink-0">1</span>
                <div>
                    <p class="font-bold text-sm text-slate-900">
                        Jelaskan bagaimana konsep arsitektur Microservices atau Modular Monolith menjamin pemisahan wewenang (Separation of Concerns) dalam sistem enterprise berbasis Laravel!
                    </p>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Pemetaan CPL-02: Mampu merancang arsitektur sistem informasi enterprise berkinerja tinggi.</span>
                </div>
            </div>
            <div class="ml-9 space-y-2 text-xs">
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="q1" value="A" x-model="selectedAnswers['q1']" class="text-uph-navy focus:ring-uph-navy">
                    <span>A. Menggunakan Service Layer dan Policy Authorization untuk memisahkan domain logika bisnis dan kendali akses.</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="q1" value="B" x-model="selectedAnswers['q1']" class="text-uph-navy focus:ring-uph-navy">
                    <span>B. Menyimpan semua logika di dalam file routes/web.php tanpa membedakan controller.</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="q1" value="C" x-model="selectedAnswers['q1']" class="text-uph-navy focus:ring-uph-navy">
                    <span>C. Menonaktifkan sistem autentikasi untuk meningkatkan performa server database.</span>
                </label>
            </div>
        </div>

        <!-- Question 2 -->
        <div class="space-y-3 pt-4 border-t border-slate-100">
            <div class="flex items-start gap-3">
                <span class="h-6 w-6 rounded-lg bg-uph-navy text-uph-gold flex items-center justify-center font-bold text-xs shrink-0">2</span>
                <div>
                    <p class="font-bold text-sm text-slate-900">
                        Bagaimana mekanisme Pessimistic Locking (`lockForUpdate()`) mencegah race condition saat perebutan kursi kuota KRS (KRS War)?
                    </p>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Pemetaan CPL-04: Penguasaan konkurensi transaksi basis data relasional tingkat lanjut.</span>
                </div>
            </div>
            <div class="ml-9 space-y-2 text-xs">
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="q2" value="A" x-model="selectedAnswers['q2']" class="text-uph-navy focus:ring-uph-navy">
                    <span>A. Baris data dikunci secara eksklusif pada tingkat basis data hingga transaksi commit atau rollback selesai.</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="q2" value="B" x-model="selectedAnswers['q2']" class="text-uph-navy focus:ring-uph-navy">
                    <span>B. Mengizinkan semua permintaan menulis kuota secara bersamaan tanpa transaksi.</span>
                </label>
            </div>
        </div>

        <!-- Action Submit Strip -->
        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info text-amber-500 mr-1"></i> Jawaban akan otomatis dinilai dan disimpan ke transkrip nilai KRS.
            </div>
            <button type="button" 
                    @click="submitExam()"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider shadow-lg flex items-center justify-center gap-2 transition-all">
                <i class="fa-solid fa-paper-plane"></i> Selesai &amp; Kumpulkan Lembar Jawaban
            </button>
        </div>
    </div>

</div>
@endsection
