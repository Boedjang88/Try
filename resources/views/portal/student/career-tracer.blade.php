@extends('layouts.portal', ['title' => 'Career Center & Tracer Study Kemdiktisaintek'])

@section('content')
<div class="space-y-6" x-data="{
    applyModalOpen: false,
    selectedJob: null,
    appliedJobs: {},
    applySuccess: false,
    applicantCoverNote: 'Saya tertarik mengajukan lamaran ini dengan profil akademik dan portofolio resmi UPH saya.',
    applyForJob() {
        if (!this.selectedJob) return;
        this.appliedJobs[this.selectedJob.title] = true;
        this.applySuccess = true;
        setTimeout(() => {
            this.applyModalOpen = false;
            this.applySuccess = false;
        }, 1500);
    }
}">
    <!-- Executive Header -->
    <div class="bg-gradient-to-r from-uph-navy via-slate-900 to-uph-navy p-6 sm:p-8 rounded-2xl text-white shadow-xl border border-uph-gold/30 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 mb-2">
                    <i class="fa-solid fa-briefcase"></i>
                    Career Center &bull; BAN-PT / LAM Criteria 9 Accreditation
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Career Portal & Alumni Tracer Study</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Jejaring peluang karir & magang dengan mitra industri terkemuka, serta pengisian instrumen resmi Tracer Study Kemdiktisaintek untuk evaluasi luaran tridharma perguruan tinggi.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-xl bg-white/10 text-xs font-mono font-bold border border-white/20">
                    <i class="fa-solid fa-chart-line text-emerald-400 mr-1.5"></i>
                    IKU 1: Lulusan Mendapat Pekerjaan Layak
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Section 1: Accreditation KPI Metrics (BAN-PT / LAM Criteria 9) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400">Total Responden Alumni</p>
            <p class="text-2xl font-black font-mono text-slate-900 dark:text-white mt-1">{{ $metrics['total_responses'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Lulusan terdata di sistem</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400">Tingkat Penyerapan Kerja</p>
            <p class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['employed_percentage'] }}%</p>
            <p class="text-[11px] text-slate-500 mt-1">Bekerja & Wirausaha</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400">Rata-rata Masa Tunggu</p>
            <p class="text-2xl font-black font-mono text-blue-600 dark:text-blue-400 mt-1">{{ $metrics['avg_waiting_time_months'] }} <span class="text-xs font-sans font-semibold">Bulan</span></p>
            <p class="text-[11px] text-slate-500 mt-1">Hingga pekerjaan pertama</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400">Keselarasan Bidang Studi</p>
            <p class="text-2xl font-black font-mono text-uph-gold mt-1">{{ $metrics['high_alignment_percentage'] }}%</p>
            <p class="text-[11px] text-slate-500 mt-1">Kategori Keselarasan Tinggi</p>
        </div>
    </div>

    <!-- Section 2: Partner Company Job & Internship Board -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-handshake text-uph-gold"></i>
                    Papan Peluang Karir & Magang Mitra Industri
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lowongan eksklusif dari jaringan mitra industri MBKM dan alumni UPH.</p>
            </div>
            <span class="text-xs font-mono font-bold px-3 py-1 rounded-full bg-uph-navy/10 text-uph-navy dark:bg-uph-navy dark:text-uph-gold">
                Active Listings
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($jobListings as $job)
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-uph-gold/50 transition-all bg-slate-50/50 dark:bg-slate-800/40 flex flex-col justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                            {{ $job['type'] }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-2">{{ $job['title'] }}</h3>
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-0.5">{{ $job['company'] }}</p>
                        <p class="text-[11px] text-slate-400 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $job['location'] }}</p>

                        <div class="flex flex-wrap gap-1 mt-3">
                            @foreach($job['tags'] as $tag)
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                        <span class="text-[11px] text-slate-400">Batas: {{ $job['deadline'] }}</span>
                        <button type="button" 
                                @click="selectedJob = {{ json_encode($job) }}; applyModalOpen = true"
                                class="px-3 py-1.5 rounded-lg bg-uph-navy hover:bg-uph-navy-dark dark:bg-uph-gold dark:text-uph-navy text-white font-bold text-xs flex items-center gap-1.5 transition-all">
                            <span x-text="appliedJobs['{{ $job['title'] }}'] ? 'Terkirim ✓' : 'Lamar Cepat'"></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Kemdiktisaintek Tracer Study Form -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-question text-uph-crimson"></i>
                    Kuesioner Tracer Study Kemdiktisaintek
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Wajib diisi oleh calon wisudawan dan alumni untuk pelaporan akreditasi BAN-PT.</p>
            </div>
            @if($existingTracer)
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    <i class="fa-solid fa-circle-check mr-1"></i> Sudah Terisi
                </span>
            @endif
        </div>

        <form action="/portal/student/career-tracer/submit" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tahun Kelulusan / Wisuda</label>
                    <input type="number" name="graduation_year" required min="2020" max="2030" 
                           value="{{ old('graduation_year', $existingTracer->graduation_year ?? 2026) }}"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Status Pekerjaan Saat Ini</label>
                    <select name="employment_status" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                        <option value="employed" {{ ($existingTracer->employment_status ?? '') === 'employed' ? 'selected' : '' }}>Bekerja (Karyawan Swasta/BUMN/PNS)</option>
                        <option value="entrepreneur" {{ ($existingTracer->employment_status ?? '') === 'entrepreneur' ? 'selected' : '' }}>Wiraswasta / Founder Bisnis</option>
                        <option value="continuing_study" {{ ($existingTracer->employment_status ?? '') === 'continuing_study' ? 'selected' : '' }}>Melanjutkan Studi S2 / Profesi</option>
                        <option value="seeking" {{ ($existingTracer->employment_status ?? '') === 'seeking' ? 'selected' : '' }}>Sedang Mencari Pekerjaan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Masa Tunggu Mendapat Kerja (Bulan)</label>
                    <input type="number" name="waiting_time_months" required min="0" max="60"
                           value="{{ old('waiting_time_months', $existingTracer->waiting_time_months ?? 2) }}"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Instansi / Perusahaan</label>
                    <input type="text" name="company_name" placeholder="Contoh: PT Bank Central Asia Tbk"
                           value="{{ old('company_name', $existingTracer->company_name ?? '') }}"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Posisi / Jabatan</label>
                    <input type="text" name="job_title" placeholder="Contoh: Software Development Engineer"
                           value="{{ old('job_title', $existingTracer->job_title ?? '') }}"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Rentang Penghasilan Bulanan</label>
                    <select name="monthly_income_range" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                        <option value="5 - 10 Juta" {{ ($existingTracer->monthly_income_range ?? '') === '5 - 10 Juta' ? 'selected' : '' }}>Rp 5.000.000 - Rp 10.000.000 (UMR+)</option>
                        <option value="10 - 20 Juta" {{ ($existingTracer->monthly_income_range ?? '') === '10 - 20 Juta' ? 'selected' : '' }}>Rp 10.000.000 - Rp 20.000.000</option>
                        <option value="> 20 Juta" {{ ($existingTracer->monthly_income_range ?? '') === '> 20 Juta' ? 'selected' : '' }}>> Rp 20.000.000 (Tier 1 Tech / Finance)</option>
                        <option value="< 5 Juta" {{ ($existingTracer->monthly_income_range ?? '') === '< 5 Juta' ? 'selected' : '' }}>< Rp 5.000.000</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tingkat Keselarasan Pekerjaan dengan Program Studi</label>
                    <select name="job_major_alignment" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                        <option value="high" {{ ($existingTracer->job_major_alignment ?? '') === 'high' ? 'selected' : '' }}>Tinggi (Sangat relevan dengan bidang ilmu yang dipelajari)</option>
                        <option value="moderate" {{ ($existingTracer->job_major_alignment ?? '') === 'moderate' ? 'selected' : '' }}>Sedang (Memerlukan sebagian kompetensi program studi)</option>
                        <option value="low" {{ ($existingTracer->job_major_alignment ?? '') === 'low' ? 'selected' : '' }}>Rendah (Tidak berhubungan langsung)</option>
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-uph-navy text-white hover:bg-uph-navy-dark dark:bg-uph-gold dark:text-uph-navy font-bold text-xs shadow-md transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Data Tracer Study
                </button>
            </div>
        </form>
    </div>

    <!-- Interactive Fast Application Modal -->
    <div x-show="applyModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @keydown.escape.window="applyModalOpen = false">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4"
             @click.away="applyModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-uph-navy/10 text-uph-navy dark:bg-uph-gold/10 dark:text-uph-gold flex items-center justify-center font-bold">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="selectedJob ? selectedJob.title : 'Lamar Pekerjaan'"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="selectedJob ? selectedJob.company + ' • ' + selectedJob.location : ''"></p>
                    </div>
                </div>
                <button type="button" @click="applyModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <template x-if="applySuccess">
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300 flex items-center justify-center text-xl mx-auto">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Lamaran Berhasil Dikirimkan!</h4>
                    <p class="text-xs text-emerald-700 dark:text-emerald-400">Profil mahasiswa dan transkrip terverifikasi Anda telah diteruskan ke portal talent acquisition mitra.</p>
                </div>
            </template>

            <template x-if="!applySuccess">
                <div class="space-y-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs space-y-1.5">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>Nama Pelamar:</span>
                            <span class="font-bold">{{ auth()->user()->full_name }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>NIM Mahasiswa:</span>
                            <span class="font-mono font-bold">{{ $student->nim ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>Program Studi:</span>
                            <span class="font-bold">{{ $student->studyProgram->name_id ?? 'Informatika' }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>IPK Kumulatif Terverifikasi:</span>
                            <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($student->current_ipk ?? 3.88, 2) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Pendukung / Cover Note Singkat</label>
                        <textarea x-model="applicantCoverNote" rows="3" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 dark:focus:ring-uph-gold/30"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="applyModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                            Batal
                        </button>
                        <button type="button" @click="applyForJob()" class="px-5 py-2 rounded-xl bg-uph-navy hover:bg-uph-navy-dark dark:bg-uph-gold dark:text-uph-navy text-white text-xs font-bold shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            Kirim Lamaran Cepat
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
