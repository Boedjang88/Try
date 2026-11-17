@extends('layouts.public', ['title' => 'Agenda, Seminar & Berita - UPH'])

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-uph-navy-dark via-uph-navy to-uph-navy-dark text-white py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-uph-gold text-xs font-bold border border-uph-gold/30">
                <i class="fa-solid fa-calendar-check"></i> Agenda Kampus &amp; Kegiatan Terbuka
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-extrabold text-white leading-tight">
                Agenda Akademik, Seminar Internasional &amp; Info Terkini UPH
            </h1>
            <p class="text-base text-slate-300 leading-relaxed">
                Temukan jadwal kuliah terbuka, simposium riset, open house PMB, dan informasi terkini dari ekosistem akademik Universitas Pelita Harapan.
            </p>
        </div>
    </div>
</section>

<!-- Events Grid Section -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-2xl font-display font-extrabold text-uph-navy">Agenda &amp; Berita Unggulan</h2>
            <p class="text-xs text-slate-500">Terbuka untuk umum, mahasiswa, dan alumni</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full bg-uph-navy/10 text-uph-navy font-bold text-xs">
                T.A. {{ $academicPeriod->name ?? '2026/2027 Ganjil' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($events as $event)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-lg transition-all flex flex-col justify-between group">
                <div>
                    <div class="relative h-48 overflow-hidden bg-slate-100">
                        <img src="{{ $event['image'] }}" alt="{{ $event['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-uph-navy/90 backdrop-blur-md text-uph-gold text-[10px] font-bold uppercase tracking-wider">
                            {{ $event['category'] }}
                        </span>
                    </div>

                    <div class="p-6 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-slate-400 font-mono">
                            <i class="fa-regular fa-calendar text-uph-gold"></i>
                            <span>{{ $event['date'] }}</span>
                        </div>
                        <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-uph-navy transition-colors">
                            {{ $event['title'] }}
                        </h3>
                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed">
                            {{ $event['description'] }}
                        </p>
                        <div class="pt-2 flex items-center gap-2 text-xs text-slate-500">
                            <i class="fa-solid fa-location-dot text-uph-crimson text-sm"></i>
                            <span class="truncate">{{ $event['location'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 pt-0">
                    <a href="/pmb/register" class="w-full py-2.5 rounded-xl border border-uph-navy text-uph-navy hover:bg-uph-navy hover:text-white font-bold text-xs text-center block transition-colors">
                        Reservasi Tempat &amp; Info Lengkap
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
