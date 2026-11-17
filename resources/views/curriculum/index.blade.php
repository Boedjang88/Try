@extends('layouts.portal')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-uph-gold">Manajemen Akademik Kaprodi</span>
            <h1 class="text-2xl font-display font-black text-uph-navy mt-1">Kurikulum Outcome-Based Education (OBE)</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola pemetaan Capaian Pembelajaran Lulusan (CPL), CPMK mata kuliah, pohon prasyarat, dan bobot asesmen terstandarisasi.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-check-double text-emerald-500"></i> Standar Akreditasi UNGGUL & OBE
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Curriculum Cards List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($curriculums as $cur)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-uph transition-all p-6 flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded bg-uph-navy/10 text-uph-navy font-mono font-bold text-xs">
                        {{ $cur->code }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if($cur->is_locked)
                        <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-bold">
                            <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                        </span>
                        @else
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                            Aktif Diedit
                        </span>
                        @endif
                    </div>
                </div>

                <h3 class="font-display font-bold text-base text-uph-navy leading-snug">
                    {{ $cur->name }}
                </h3>

                <p class="text-xs text-slate-500">
                    Program Studi: <strong class="text-slate-800">{{ $cur->studyProgram->name_id }} ({{ $cur->studyProgram->degree_level }})</strong>
                </p>

                <!-- SKS Breakdown -->
                <div class="grid grid-cols-3 gap-2 bg-surface-slate p-3 rounded-xl border border-slate-100 text-center text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">TOTAL</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $cur->total_sks }} SKS</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">WAJIB</span>
                        <span class="font-mono font-bold text-uph-navy text-sm">{{ $cur->mandatory_sks }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">PILIHAN</span>
                        <span class="font-mono font-bold text-uph-crimson text-sm">{{ $cur->elective_sks }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                    <span><i class="fa-solid fa-layer-group text-slate-400 mr-1"></i> {{ $cur->cpls->count() }} CPL Terpetakan</span>
                    <span><i class="fa-solid fa-book text-slate-400 mr-1"></i> {{ $cur->courses->count() }} Mata Kuliah</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <a href="/curriculum/obe/{{ $cur->id }}" class="w-full block py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-display font-bold text-xs uppercase tracking-wider text-center shadow transition-colors">
                    <i class="fa-solid fa-diagram-project mr-1.5"></i> Buka Workspace OBE
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-2xl p-12 text-center text-slate-400 border border-dashed">
            <i class="fa-solid fa-diagram-project text-4xl text-slate-300 mb-3 block"></i>
            Belum ada kurikulum yang terdaftar pada program studi Anda.
        </div>
        @endforelse
    </div>
</div>
@endsection
