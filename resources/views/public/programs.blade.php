@extends('layouts.public')

@section('content')
<div class="bg-uph-navy-dark text-white py-14 border-b-2 border-uph-gold">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-xs font-bold uppercase tracking-widest text-uph-gold">Pendidikan Tinggi Unggul</span>
        <h1 class="text-3xl sm:text-4xl font-display font-black text-white mt-2">Daftar Fakultas & Program Studi</h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-2xl">
            Pilihlah jalur pendidikan sarjana, pascasarjana, atau kelas profesional dengan kurikulum Outcome-Based Education (OBE) dan akreditasi BAN-PT Unggul.
        </p>

        <!-- Degree Level Filter Bar -->
        <div class="mt-8 flex flex-wrap gap-2 text-xs">
            <a href="/academic/programs?level=all" class="px-4 py-2 rounded-lg font-bold transition-colors {{ $degreeLevel === 'all' ? 'bg-uph-gold text-uph-navy-dark' : 'bg-white/10 text-white hover:bg-white/20' }}">
                Semua Jenjang
            </a>
            <a href="/academic/programs?level=S1" class="px-4 py-2 rounded-lg font-bold transition-colors {{ $degreeLevel === 'S1' ? 'bg-uph-gold text-uph-navy-dark' : 'bg-white/10 text-white hover:bg-white/20' }}">
                Program Sarjana (S1)
            </a>
            <a href="/academic/programs?level=S2" class="px-4 py-2 rounded-lg font-bold transition-colors {{ $degreeLevel === 'S2' ? 'bg-uph-gold text-uph-navy-dark' : 'bg-white/10 text-white hover:bg-white/20' }}">
                Magister (S2)
            </a>
            <a href="/academic/programs?level=Profesi" class="px-4 py-2 rounded-lg font-bold transition-colors {{ $degreeLevel === 'Profesi' ? 'bg-uph-gold text-uph-navy-dark' : 'bg-white/10 text-white hover:bg-white/20' }}">
                Profesi & Spesialis
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-12">
    @foreach($faculties as $faculty)
    @if($faculty->studyPrograms->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 bg-surface-slate border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-uph-crimson">{{ $faculty->code }}</span>
                <h2 class="text-xl font-display font-bold text-uph-navy mt-0.5">{{ $faculty->name_id }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $faculty->description }}</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-uph-navy/10 text-uph-navy font-bold text-xs shrink-0">
                {{ $faculty->studyPrograms->count() }} Program Studi
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($faculty->studyPrograms as $prodi)
            <div class="p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                <div class="space-y-1.5 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-bold uppercase">
                            {{ $prodi->degree_level }}
                        </span>
                        <h3 class="text-base font-display font-bold text-slate-900 truncate">
                            {{ $prodi->name_id }}
                        </h3>
                        <span class="text-xs text-slate-400 font-semibold font-mono">
                            ({{ $prodi->title_abbr }})
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                        <span>Kode PDDIKTI: <strong class="font-mono text-slate-700">{{ $prodi->pddikti_code }}</strong></span>
                        <span>&bull;</span>
                        <span>Masa Studi: <strong>{{ $prodi->standard_study_period_semesters }} Semester ({{ $prodi->minimum_graduation_sks }} SKS)</strong></span>
                        <span>&bull;</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[10px]">
                            <i class="fa-solid fa-award"></i> Akreditasi {{ $prodi->accreditation_grade }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">
                    <a href="/academic/curriculum/preview/{{ $prodi->code }}" class="flex-1 sm:flex-none text-center px-4 py-2 rounded-lg border border-uph-navy text-uph-navy hover:bg-uph-navy hover:text-white font-bold text-xs transition-colors">
                        Kurikulum OBE
                    </a>
                    <a href="/admissions/pmb/register?prodi={{ $prodi->id }}" class="flex-1 sm:flex-none text-center px-4 py-2 rounded-lg bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs transition-colors">
                        Daftar PMB
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection
