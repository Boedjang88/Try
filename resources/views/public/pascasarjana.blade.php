@extends('layouts.public', ['title' => 'Program Pascasarjana (S2 & S3) - UPH'])

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-uph-navy-dark via-uph-navy to-uph-navy-dark text-white py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-uph-gold text-xs font-bold border border-uph-gold/30">
                <i class="fa-solid fa-user-graduate"></i> Pendidikan Tingkat Lanjut (Graduate School)
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-extrabold text-white leading-tight">
                Program Pascasarjana: Magister (S2), Spesialis &amp; Doktoral (S3)
            </h1>
            <p class="text-base text-slate-300 leading-relaxed">
                Tingkatkan kepemimpinan strategis dan keunggulan riset Anda. Dirancang untuk para profesional, akademisi, dan pemimpin industri dengan jadwal perkuliahan blended dan kurikulum terapan mutakhir.
            </p>
            <div class="pt-4 flex flex-wrap gap-4">
                <a href="/pmb/register" class="px-6 py-3 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all">
                    <i class="fa-solid fa-file-signature mr-1.5"></i> Daftar Pascasarjana Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Graduate Programs Grid -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <h2 class="text-3xl font-display font-extrabold text-uph-navy">Program Magister &amp; Doktoral Terakreditasi</h2>
        <p class="text-sm text-slate-500">Pendidikan lanjutan berfokus pada inovasi, publikasi ilmiah terindeks Scopus, dan solusi nyata industri.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($graduatePrograms as $prog)
            <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-uph-gold shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded bg-uph-navy/10 text-uph-navy font-mono font-bold text-xs">
                            {{ $prog->degree_level }} &bull; {{ $prog->code }}
                        </span>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            {{ $prog->accreditation_grade ?? 'A' }}
                        </span>
                    </div>

                    <h3 class="font-display font-extrabold text-lg text-slate-900">
                        {{ $prog->name_id }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        Fakultas: <strong class="text-slate-700">{{ $prog->faculty->name_id ?? 'UPH' }}</strong><br>
                        Gelar Akademik: <strong class="text-uph-navy">{{ $prog->title_abbr ?? 'M.Kom.' }}</strong> &bull; Beban SKS: {{ $prog->minimum_graduation_sks ?? 36 }} SKS
                    </p>
                </div>

                <div class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs mt-4">
                    <a href="/curriculum-preview/{{ $prog->code }}" class="text-uph-navy font-bold hover:underline">
                        Struktur Kurikulum &rarr;
                    </a>
                    <a href="/pmb/register?prodi={{ $prog->id }}" class="px-4 py-1.5 rounded-lg bg-uph-crimson text-white font-bold hover:bg-uph-crimson-hover">
                        Daftar S2/S3
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-12 text-center text-slate-400 bg-white rounded-2xl border">
                Tidak ada program pascasarjana aktif saat ini.
            </div>
        @endforelse
    </div>
</section>
@endsection
