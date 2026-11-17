@extends('layouts.public')

@section('content')
<!-- Hero Section with UPH Authority & Prestige -->
<section class="relative bg-gradient-to-b from-uph-navy-dark via-uph-navy to-uph-navy-dark text-white overflow-hidden py-24 sm:py-32">
    <!-- Subtle architectural background pattern -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 text-uph-gold text-xs font-bold border border-uph-gold/30">
                    <span class="h-2 w-2 rounded-full bg-uph-gold animate-ping"></span>
                    Penerimaan Mahasiswa Baru (PMB) T.A. 2026/2027
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-extrabold tracking-tight text-white leading-tight">
                    Membentuk Pemimpin Masa Depan Berkarakter Ilahi
                </h1>

                <p class="text-base sm:text-lg text-slate-300 font-normal max-w-2xl leading-relaxed">
                    Universitas Pelita Harapan menghadirkan ekosistem pendidikan tinggi berstandar global dengan kurikulum <span class="text-uph-gold font-semibold">Outcome-Based Education (OBE)</span>, membekali mahasiswa dengan keunggulan akademik, integritas moral, dan kesiapan berkarier di panggung dunia.
                </p>

                <!-- Action CTAs -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="/admissions/pmb/register" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-display font-bold text-sm tracking-wide uppercase shadow-uph-crimson transition-all transform hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-signature"></i> Daftar PMB Sekarang
                    </a>
                    <a href="/academic/programs" class="w-full sm:w-auto px-7 py-4 rounded-xl border-2 border-uph-gold/80 hover:bg-uph-gold hover:text-uph-navy-dark text-uph-gold font-display font-bold text-sm tracking-wide uppercase transition-all text-center flex items-center justify-center gap-2">
                        <i class="fa-solid fa-graduation-cap"></i> Jelajahi Program Studi
                    </a>
                </div>

                <!-- Accreditation & Verification Pill -->
                <div class="pt-6 border-t border-white/10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-uph-gold text-sm"></i>
                        <span>Terakreditasi <strong class="text-white">UNGGUL</strong> BAN-PT</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-uph-gold text-sm"></i>
                        <span>Sistem Penjaminan Mutu Internal (SPMI)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-uph-gold text-sm"></i>
                        <span>Tersinkronisasi PDDIKTI Neo Feeder</span>
                    </div>
                </div>
            </div>

            <!-- Right Hero Card / Interactive Tuition & Scholarship Calculator Widget -->
            <div class="lg:col-span-5"
                 x-data="{
                     prodi: 'Informatika',
                     prodiBaseBpp: {
                         'Informatika': 22500000,
                         'Sistem Informasi': 21000000,
                         'Manajemen': 24000000,
                         'Ilmu Hukum': 23000000,
                         'Desain Komunikasi Visual': 26000000,
                         'Kedokteran': 48000000
                     },
                     score: 88,
                     get baseBpp() {
                         return this.prodiBaseBpp[this.prodi] || 22500000;
                     },
                     get discountPct() {
                         if (this.score >= 90) return 50;
                         if (this.score >= 80) return 25;
                         return 0;
                     },
                     get discountAmount() {
                         return (this.baseBpp * this.discountPct) / 100;
                     },
                     get netTuition() {
                         return this.baseBpp - this.discountAmount;
                     },
                     get tierLabel() {
                         if (this.score >= 90) return 'Tier 1 • Potongan 50%';
                         if (this.score >= 80) return 'Tier 2 • Potongan 25%';
                         return 'Jalur Reguler Tanpa Beasiswa';
                     },
                     formatRupiah(val) {
                         return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                     }
                 }">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 sm:p-7 border border-white/20 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3">
                        <div>
                            <span class="text-[10px] uppercase tracking-widest text-uph-gold font-bold font-mono">Kalkulator Beasiswa 2026</span>
                            <h3 class="text-lg font-display font-bold text-white">Simulasi Biaya & Beasiswa PMB</h3>
                        </div>
                        <div class="h-9 w-9 rounded-xl bg-uph-gold/20 flex items-center justify-center text-uph-gold font-bold text-base">
                            <i class="fa-solid fa-calculator"></i>
                        </div>
                    </div>

                    <!-- Program Studi Selection -->
                    <div class="space-y-1.5 text-xs">
                        <label class="text-slate-300 font-semibold text-[11px]">Pilih Program Studi:</label>
                        <select x-model="prodi" class="w-full text-xs px-3 py-2 rounded-xl bg-slate-900/80 border border-white/20 text-white focus:outline-none focus:ring-1 focus:ring-uph-gold">
                            <option value="Informatika">S1 Informatika (FIK) - Unggul</option>
                            <option value="Sistem Informasi">S1 Sistem Informasi (FIK)</option>
                            <option value="Manajemen">S1 Manajemen Bisnis (FEB)</option>
                            <option value="Ilmu Hukum">S1 Ilmu Hukum (FH)</option>
                            <option value="Desain Komunikasi Visual">S1 Desain Komunikasi Visual (FSD)</option>
                            <option value="Kedokteran">S1 Kedokteran (FK)</option>
                        </select>
                    </div>

                    <!-- Score Slider -->
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-300 font-semibold">Prediksi Rata-Rata Rapor / Skor CBT:</span>
                            <span class="font-mono font-bold text-uph-gold text-sm" x-text="score"></span>
                        </div>
                        <input type="range" min="70" max="100" step="1" x-model.number="score" class="w-full accent-[#D4AF37] h-1.5 bg-slate-700 rounded-lg cursor-pointer">
                        <div class="flex justify-between text-[9px] text-slate-400 font-mono">
                            <span>Min 70</span>
                            <span>80 (Tier 2: 25%)</span>
                            <span>90 (Tier 1: 50%)</span>
                            <span>100</span>
                        </div>
                    </div>

                    <!-- Live Dynamic Calculation Card -->
                    <div class="p-3.5 rounded-xl bg-black/30 border border-white/10 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-[11px] text-slate-300">
                            <span>Estimasi BPP Pokok:</span>
                            <span class="font-mono text-white" x-text="formatRupiah(baseBpp)"></span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-emerald-400 font-semibold" x-text="tierLabel"></span>
                            <span class="font-mono font-bold text-emerald-400" x-text="'-' + formatRupiah(discountAmount)"></span>
                        </div>
                        <div class="pt-2 border-t border-white/10 flex items-center justify-between">
                            <span class="text-slate-200 font-bold">Biaya Bersih / Semester:</span>
                            <span class="font-mono font-black text-uph-gold text-base" x-text="formatRupiah(netTuition)"></span>
                        </div>
                    </div>

                    <!-- Direct PMB Registration CTA -->
                    <a href="/admissions/pmb/register" class="w-full block py-3 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 font-display font-bold text-center text-xs uppercase tracking-wider transition-all shadow-md hover:scale-[1.01]">
                        <i class="fa-solid fa-file-signature mr-1.5"></i> Daftar PMB & Kunci Beasiswa Ini
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Grid -->
<section class="relative -mt-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-20">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($stats as $st)
        <div class="bg-white rounded-xl p-6 shadow-uph border border-surface-border flex items-center gap-4 hover:shadow-uph-lg transition-shadow">
            <div class="h-12 w-12 rounded-xl bg-uph-navy/10 text-uph-navy flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid {{ $st['icon'] }}"></i>
            </div>
            <div>
                <p class="text-2xl font-display font-black text-uph-navy tabular-nums">{{ $st['value'] }}</p>
                <p class="text-xs font-bold text-slate-800">{{ $st['label'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $st['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Outcome-Based Education (OBE) & Curriculum Excellence -->
<section class="py-20 bg-surface-slate">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="text-xs font-bold uppercase tracking-widest text-uph-crimson">Standar Akademik Berkelanjutan</span>
            <h2 class="text-3xl sm:text-4xl font-display font-black text-uph-navy">
                Kurikulum Outcome-Based Education (OBE)
            </h2>
            <p class="text-sm text-slate-600 leading-relaxed">
                Di UPH, setiap mata kuliah dirancang secara terstruktur mulai dari Capaian Pembelajaran Lulusan (CPL), Capaian Pembelajaran Mata Kuliah (CPMK), hingga rubrik asesmen transparan untuk memastikan keselarasan dengan kebutuhan industri 4.0.
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-4 hover:border-uph-gold transition-colors">
                <div class="h-12 w-12 rounded-xl bg-uph-navy text-uph-gold flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <h3 class="text-lg font-display font-bold text-uph-navy">1. Capaian Pembelajaran (CPL)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pemetaan terstandarisasi kompetensi Sikap, Pengetahuan, Keterampilan Umum, dan Keterampilan Khusus yang diakui BAN-PT dan lembaga akreditasi internasional.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-4 hover:border-uph-gold transition-colors">
                <div class="h-12 w-12 rounded-xl bg-uph-crimson text-white flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h3 class="text-lg font-display font-bold text-uph-navy">2. Hirarki Prasyarat & SKS</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Struktur kurikulum 144 SKS dengan pohon prasyarat ketat, memastikan mahasiswa menguasai konsep fundamental sebelum mengambil mata kuliah lanjutan atau tugas akhir.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-4 hover:border-uph-gold transition-colors">
                <div class="h-12 w-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <h3 class="text-lg font-display font-bold text-uph-navy">3. Rubrik Penilaian Terukur</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Bobot asesmen dinamis terbagi atas Presensi (10%), Tugas (20%), UTS (30%), dan UAS (40%) yang terkonversi otomatis ke skala nilai huruf A-E dan IPK 4.00.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Study Programs Showcase -->
<section class="py-20 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-uph-gold">Pilihan Program Studi</span>
                <h2 class="text-3xl font-display font-black text-uph-navy mt-1">Fakultas & Jurusan Unggulan</h2>
            </div>
            <a href="/academic/programs" class="text-xs font-bold text-uph-crimson hover:text-uph-crimson-hover uppercase tracking-wider flex items-center gap-1.5">
                Lihat Semua 40+ Program Studi <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($faculties->take(6) as $faculty)
            <div class="rounded-2xl border border-slate-200 bg-surface-slate p-6 hover:shadow-uph transition-all flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded bg-uph-navy/10 text-uph-navy text-[11px] font-extrabold uppercase">
                            {{ $faculty->code }}
                        </span>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ $faculty->studyPrograms->count() }} Program Studi
                        </span>
                    </div>

                    <h3 class="font-display font-bold text-lg text-uph-navy">
                        {{ $faculty->name_id }}
                    </h3>

                    <p class="text-xs text-slate-500 line-clamp-2">
                        {{ $faculty->description ?? 'Mempersiapkan lulusan unggul berstandar global dengan kurikulum berbasis capaian pembelajaran.' }}
                    </p>

                    <div class="pt-3 border-t border-slate-200/60 space-y-1.5">
                        @foreach($faculty->studyPrograms->take(3) as $prodi)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700 truncate">{{ $prodi->name_id }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold shrink-0">
                                {{ $prodi->accreditation_grade }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-slate-200 flex items-center justify-between">
                    <a href="/academic/curriculum/preview/{{ $faculty->studyPrograms->first()->code ?? '' }}" class="text-xs font-bold text-uph-navy hover:text-uph-crimson transition-colors">
                        Preview Kurikulum OBE &rarr;
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Alumni Testimonials -->
<section class="py-20 bg-uph-navy text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-widest text-uph-gold">Testimoni Alumni</span>
            <h2 class="text-3xl font-display font-black text-white mt-1">Dampak Lulusan UPH di Tingkat Global</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($testimonials as $testi)
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/10 space-y-4">
                <i class="fa-solid fa-quote-left text-3xl text-uph-gold"></i>
                <p class="text-sm text-slate-200 italic leading-relaxed">
                    "{{ $testi['quote'] }}"
                </p>
                <div class="pt-4 border-t border-white/10">
                    <h4 class="font-display font-bold text-white text-base">{{ $testi['name'] }}</h4>
                    <p class="text-xs text-uph-gold font-medium">{{ $testi['title'] }}</p>
                    <p class="text-[11px] text-slate-400">{{ $testi['prodi'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
