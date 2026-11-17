@extends('layouts.public', ['title' => 'Kelas Karyawan & Eksekutif - UPH'])

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-uph-navy-dark via-uph-navy to-uph-navy-dark text-white py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                <i class="fa-solid fa-briefcase"></i> Program Fleksibel untuk Profesional &amp; Karyawan
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-extrabold text-white leading-tight">
                Kelas Karyawan: Raih Gelar Sarjana &amp; Magister Tanpa Mengorbankan Karier
            </h1>
            <p class="text-base text-slate-300 leading-relaxed">
                Jadwal kuliah fleksibel (malam hari &amp; weekend), metode pembelajaran blended learning (70% daring, 30% tatap muka), serta skema cicilan SPP bulanan terjangkau bagi para pekerja profesional.
            </p>
            <div class="pt-4 flex flex-wrap gap-4">
                <a href="/pmb/register" class="px-6 py-3 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all">
                    <i class="fa-solid fa-file-signature mr-1.5"></i> Konsultasi &amp; Daftar Kelas Karyawan
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Advantages of Kelas Karyawan -->
<section class="py-12 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h3 class="font-display font-bold text-base text-slate-900">Jadwal Fleksibel</h3>
                <p class="text-xs text-slate-500 mt-1">Perkuliahan diselenggarakan pada malam hari (18:30 - 21:00 WIB) atau full day hari Sabtu.</p>
            </div>
        </div>
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-xl bg-blue-50 text-uph-navy flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-laptop-file"></i>
            </div>
            <div>
                <h3 class="font-display font-bold text-base text-slate-900">Blended LMS Learning</h3>
                <p class="text-xs text-slate-500 mt-1">Materi rekaman, tugas, dan diskusi interaktif dapat diakses 24/7 melalui sistem LMS UPH.</p>
            </div>
        </div>
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <h3 class="font-display font-bold text-base text-slate-900">Cicilan SPP Bulanan</h3>
                <p class="text-xs text-slate-500 mt-1">Skema pembiayaan bertahap tanpa bunga melalui Virtual Account multi-bank nasional.</p>
            </div>
        </div>
    </div>
</section>

<!-- Available Executive Programs Grid -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <h2 class="text-3xl font-display font-extrabold text-uph-navy">Pilihan Program Studi Kelas Karyawan</h2>
        <p class="text-sm text-slate-500">Ijazah dan transkrip resmi bernilai setara penuh dengan program reguler, terdaftar sah di PDDIKTI.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($executivePrograms as $prodi)
            <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-uph-gold shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 font-mono font-bold text-xs">
                            Kelas Malam / Weekend
                        </span>
                        <span class="text-xs font-bold text-uph-gold font-mono">{{ $prodi->degree_level }}</span>
                    </div>

                    <h3 class="font-display font-extrabold text-lg text-slate-900">
                        {{ $prodi->name_id }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        Fakultas: <strong class="text-slate-700">{{ $prodi->faculty->name_id ?? 'UPH' }}</strong><br>
                        Gelar: <strong class="text-uph-navy">{{ $prodi->title_abbr ?? 'S.Kom.' }}</strong> &bull; Estimasi Lulus: 3.5 - 4 Tahun
                    </p>
                </div>

                <div class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs mt-4">
                    <a href="/curriculum-preview/{{ $prodi->code }}" class="text-uph-navy font-bold hover:underline">
                        Lihat Silabus &rarr;
                    </a>
                    <a href="/pmb/register?prodi={{ $prodi->id }}" class="px-4 py-1.5 rounded-lg bg-uph-crimson text-white font-bold hover:bg-uph-crimson-hover">
                        Daftar Karyawan
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
