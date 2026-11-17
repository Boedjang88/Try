@extends('layouts.public')

@section('content')
<div class="bg-uph-navy text-white py-14 border-b-2 border-uph-gold">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <a href="/academic/programs" class="text-xs text-uph-gold hover:underline">
                        &larr; Kembali ke Daftar Program Studi
                    </a>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-xs uppercase tracking-wider text-slate-300 font-bold">{{ $studyProgram->faculty->name_id }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-display font-black text-white">
                    Kurikulum OBE: {{ $studyProgram->name_id }} ({{ $studyProgram->degree_level }})
                </h1>
                <p class="text-xs sm:text-sm text-slate-300">
                    Kode Kurikulum: <span class="font-mono text-uph-gold font-bold">{{ $curriculum->code ?? 'KUR-2024-OBE' }}</span> &bull; 
                    Total Beban Studi: <span class="font-bold text-white">{{ $curriculum->total_sks ?? 144 }} SKS</span> &bull;
                    Gelar: <span class="font-bold text-white">{{ $studyProgram->title_abbr }}</span>
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <a href="/admissions/pmb/register?prodi={{ $studyProgram->id }}" class="px-6 py-3 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-display font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all">
                    Daftar di Program Ini
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" x-data="{ activeTab: 'cpl' }">
    <!-- Tab Controls -->
    <div class="flex border-b border-slate-200 gap-4 text-xs font-bold uppercase tracking-wider">
        <button @click="activeTab = 'cpl'" 
                :class="activeTab === 'cpl' ? 'border-b-2 border-uph-navy text-uph-navy' : 'text-slate-400 hover:text-slate-600'"
                class="pb-3 px-2 transition-colors">
            Capaian Pembelajaran (CPL)
        </button>
        <button @click="activeTab = 'courses'" 
                :class="activeTab === 'courses' ? 'border-b-2 border-uph-navy text-uph-navy' : 'text-slate-400 hover:text-slate-600'"
                class="pb-3 px-2 transition-colors">
            Distribusi Mata Kuliah (Semester 1 - 8)
        </button>
    </div>

    <!-- Tab 1: CPL Matrix -->
    <div x-show="activeTab === 'cpl'" class="mt-8 space-y-6">
        @if($curriculum && $curriculum->cpls->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($curriculum->cpls as $cpl)
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:border-uph-gold transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="px-2.5 py-1 rounded bg-uph-navy text-uph-gold font-mono font-bold text-xs">
                        {{ $cpl->code }}
                    </span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        {{ $cpl->category }}
                    </span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">
                    {{ $cpl->description_id }}
                </p>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-xl p-12 text-center text-slate-400 text-xs border border-dashed">
            <i class="fa-solid fa-diagram-project text-3xl mb-3 text-slate-300 block"></i>
            Data CPL Kurikulum sedang disinkronkan oleh Kaprodi melalui sistem OBE.
        </div>
        @endif
    </div>

    <!-- Tab 2: Courses Per Semester -->
    <div x-show="activeTab === 'courses'" class="mt-8 space-y-8" x-cloak>
        @if(!empty($coursesBySemester))
        @foreach($coursesBySemester as $sem => $courses)
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="bg-surface-slate px-6 py-3 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-display font-bold text-sm text-uph-navy">
                    Semester {{ $sem }}
                </h3>
                <span class="text-xs font-mono font-bold text-slate-600">
                    Total: {{ collect($courses)->sum('sks') }} SKS
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Mata Kuliah</th>
                            <th class="py-3 px-4 text-center">SKS (T/P)</th>
                            <th class="py-3 px-4">Prasyarat</th>
                            <th class="py-3 px-4 text-center">Tipe</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($courses as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono font-bold text-uph-navy">{{ $c->code }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $c->name_id }}</span>
                                <span class="text-[11px] text-slate-400 italic">{{ $c->name_en }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-semibold">
                                {{ $c->sks }} ({{ $c->sks_theory }}/{{ $c->sks_practice }})
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                @if($c->prerequisites->count() > 0)
                                    @foreach($c->prerequisites as $p)
                                    <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[10px] border">
                                        {{ $p->code }} (Min. {{ $p->pivot->minimum_grade ?? 'D' }})
                                    </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $c->is_mandatory ? 'bg-uph-navy/10 text-uph-navy' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $c->is_mandatory ? 'Wajib' : 'Pilihan' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        @else
        <div class="bg-white rounded-xl p-12 text-center text-slate-400 text-xs border border-dashed">
            <i class="fa-solid fa-book-open text-3xl mb-3 text-slate-300 block"></i>
            Mata kuliah kurikulum ini sedang ditinjau oleh Gugus Penjaminan Mutu (GPM).
        </div>
        @endif
    </div>
</div>
@endsection
