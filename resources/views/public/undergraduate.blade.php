@extends('layouts.public', ['title' => 'Program Sarjana (S1) - UPH'])

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-uph-navy-dark via-uph-navy to-uph-navy-dark text-white py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-uph-gold text-xs font-bold border border-uph-gold/30">
                <i class="fa-solid fa-graduation-cap"></i> Program Sarjana Unggulan (S1)
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-extrabold text-white leading-tight">
                Pendidikan Sarjana Berstandar Global Berbasis Outcome-Based Education (OBE)
            </h1>
            <p class="text-base text-slate-300 leading-relaxed">
                Pilih program studi sarjana di Universitas Pelita Harapan. Dapatkan pengalaman belajar komprehensif, sertifikasi internasional, kemitraan industri global, dan pembinaan karakter kristiani berintegritas tinggi.
            </p>
            <div class="pt-4 flex flex-wrap gap-4">
                <a href="/pmb/register" class="px-6 py-3 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all">
                    <i class="fa-solid fa-file-signature mr-1.5"></i> Daftar Sarjana S1 Sekarang
                </a>
                <a href="/programs" class="px-6 py-3 rounded-xl border border-uph-gold text-uph-gold hover:bg-uph-gold hover:text-uph-navy-dark font-bold text-xs uppercase tracking-wider transition-all">
                    <i class="fa-solid fa-list mr-1.5"></i> Seluruh Program Studi
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Live Faculties & Study Programs Grid -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <h2 class="text-3xl font-display font-extrabold text-uph-navy">Fakultas &amp; Program Studi Sarjana (S1)</h2>
        <p class="text-sm text-slate-500">Seluruh program studi telah terakreditasi BAN-PT dengan capaian kurikulum OBE standar internasional.</p>
    </div>

    <div class="space-y-10">
        @foreach($faculties as $faculty)
            @if($faculty->studyPrograms->count() > 0)
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-uph-navy/10 text-uph-navy flex items-center justify-center font-bold text-lg">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div>
                                <h3 class="font-display font-extrabold text-lg text-uph-navy">{{ $faculty->name_id }}</h3>
                                <p class="text-xs text-slate-400 font-mono">{{ $faculty->code }} &bull; {{ $faculty->studyPrograms->count() }} Program Studi S1</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 self-start sm:self-auto">
                            Akreditasi Institusi: UNGGUL
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($faculty->studyPrograms as $prodi)
                            <div class="p-5 rounded-xl border border-slate-100 hover:border-uph-gold hover:shadow-md transition-all group bg-slate-50/50 hover:bg-white flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-uph-navy/10 text-uph-navy uppercase">{{ $prodi->code }}</span>
                                        <span class="text-[11px] font-bold text-uph-gold">{{ $prodi->degree_level }}</span>
                                    </div>
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover:text-uph-navy transition-colors">
                                        {{ $prodi->name_id }}
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        Gelar: <strong class="text-slate-700">{{ $prodi->title_abbr ?? 'S.Kom.' }}</strong> &bull; {{ $prodi->minimum_graduation_sks ?? 144 }} SKS
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs mt-3">
                                    <a href="/curriculum-preview/{{ $prodi->code }}" class="text-uph-navy font-bold hover:underline inline-flex items-center gap-1">
                                        Lihat Kurikulum &rarr;
                                    </a>
                                    <a href="/pmb/register?prodi={{ $prodi->id }}" class="px-3 py-1 rounded bg-uph-crimson text-white font-bold text-[11px] hover:bg-uph-crimson-hover">
                                        Daftar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>
@endsection
