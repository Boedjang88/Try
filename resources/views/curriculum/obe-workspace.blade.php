@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'cpl', cplModalOpen: false, cpmkModalOpen: false, prereqModalOpen: false, selectedCourseId: '' }">
    <!-- Workspace Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/curriculum/obe" class="text-xs text-uph-navy font-bold hover:underline">
                    &larr; Daftar Kurikulum
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs font-mono font-bold text-uph-gold uppercase">{{ $curriculum->code }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">{{ $curriculum->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Program Studi: <strong class="text-slate-800">{{ $curriculum->studyProgram->name_id }} ({{ $curriculum->studyProgram->degree_level }})</strong> &bull; 
                Total SKS: <span class="font-mono font-bold text-uph-navy">{{ $curriculum->total_sks }} SKS</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(!$curriculum->is_locked)
            <form method="POST" action="/curriculum/obe/{{ $curriculum->id }}/lock" onsubmit="return confirm('Kunci kurikulum ini? Setelah dikunci, pemetaan CPL dan bobot tidak dapat diubah kembali.')">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-lock mr-1.5"></i> Kunci Kurikulum
                </button>
            </form>
            @else
            <span class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs border border-slate-200">
                <i class="fa-solid fa-lock text-slate-500 mr-1.5"></i> Kurikulum Terkunci (Locked)
            </span>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800 font-semibold flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-uph-crimson text-base"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Workspace Tabs Navigation -->
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-2 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wider">
        <button @click="activeTab = 'cpl'" 
                :class="activeTab === 'cpl' ? 'bg-uph-navy text-uph-gold shadow' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-lg transition-colors">
            1. Capaian Pembelajaran (CPL)
        </button>
        <button @click="activeTab = 'weights'" 
                :class="activeTab === 'weights' ? 'bg-uph-navy text-uph-gold shadow' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-lg transition-colors">
            2. Bobot Penilaian OBE Mata Kuliah
        </button>
        <button @click="activeTab = 'cpmk'" 
                :class="activeTab === 'cpmk' ? 'bg-uph-navy text-uph-gold shadow' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-lg transition-colors">
            3. Pemetaan CPMK ke CPL
        </button>
        <button @click="activeTab = 'prereq'" 
                :class="activeTab === 'prereq' ? 'bg-uph-navy text-uph-gold shadow' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-lg transition-colors">
            4. Hirarki Prasyarat Mata Kuliah
        </button>
        <button @click="activeTab = 'attainment'" 
                :class="activeTab === 'attainment' ? 'bg-uph-navy text-uph-gold shadow' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-lg transition-colors">
            5. Radar Ketercapaian CPL (Attainment)
        </button>
    </div>

    <!-- TAB 1: CPL Matrix -->
    <div x-show="activeTab === 'cpl'" class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Daftar Capaian Pembelajaran Lulusan (CPL)</h2>
            @if(!$curriculum->is_locked)
            <button @click="cplModalOpen = true" class="px-4 py-2 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow">
                <i class="fa-solid fa-plus mr-1"></i> Tambah CPL Baru
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($curriculum->cpls as $cpl)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded bg-uph-navy text-uph-gold font-mono font-bold text-xs">
                        {{ $cpl->code }}
                    </span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        {{ $cpl->category }}
                    </span>
                </div>
                <p class="text-xs text-slate-800 leading-relaxed font-medium">
                    {{ $cpl->description_id }}
                </p>
                @if($cpl->description_en)
                <p class="text-[11px] text-slate-400 italic">
                    {{ $cpl->description_en }}
                </p>
                @endif
                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400 flex justify-between">
                    <span>Urutan: {{ $cpl->order_sequence }}</span>
                    <span>Terkait dengan {{ $cpl->cpmks->count() }} CPMK</span>
                </div>
            </div>
            @empty
            <div class="col-span-2 bg-white rounded-2xl p-12 text-center text-slate-400 border border-dashed">
                Belum ada CPL yang dibuat. Klik tombol "Tambah CPL Baru" di atas.
            </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: Dynamic Course Assessment Weights Configuration -->
    <div x-show="activeTab === 'weights'" class="space-y-6" x-cloak>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-6 bg-surface-slate border-b border-slate-200 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Konfigurasi Bobot Penilaian OBE</h2>
                    <p class="text-xs text-slate-500">Bobot harus berjumlah tepat 100.00% (Presensi + Tugas + UTS + UAS).</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-uph-navy/10 text-uph-navy font-bold text-xs">
                    {{ $curriculum->courses->count() }} Mata Kuliah
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="py-3 px-4">Kode & Mata Kuliah</th>
                            <th class="py-3 px-4 text-center">SKS</th>
                            <th class="py-3 px-4 text-center">Presensi (%)</th>
                            <th class="py-3 px-4 text-center">Tugas (%)</th>
                            <th class="py-3 px-4 text-center">UTS (%)</th>
                            <th class="py-3 px-4 text-center">UAS (%)</th>
                            <th class="py-3 px-4 text-center">Passing Grade</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($curriculum->courses as $course)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-uph-navy">{{ $course->code }}</span>
                                <span class="font-bold text-slate-800 block">{{ $course->name_id }}</span>
                                <span class="text-[10px] text-slate-400">Sem. {{ $course->semester_level }} &bull; {{ $course->is_mandatory ? 'Wajib' : 'Pilihan' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold">{{ $course->sks }}</td>
                            
                            <form method="POST" action="/curriculum/courses/{{ $course->id }}/weights">
                                @csrf
                                <td class="py-3 px-2 text-center">
                                    <input type="number" step="0.5" min="0" max="100" name="weight_presence" value="{{ $course->weight_presence }}" class="w-16 h-8 text-center text-xs font-mono font-bold border rounded focus:ring-1 focus:ring-uph-navy" {{ $curriculum->is_locked ? 'disabled' : '' }}>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <input type="number" step="0.5" min="0" max="100" name="weight_task" value="{{ $course->weight_task }}" class="w-16 h-8 text-center text-xs font-mono font-bold border rounded focus:ring-1 focus:ring-uph-navy" {{ $curriculum->is_locked ? 'disabled' : '' }}>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <input type="number" step="0.5" min="0" max="100" name="weight_uts" value="{{ $course->weight_uts }}" class="w-16 h-8 text-center text-xs font-mono font-bold border rounded focus:ring-1 focus:ring-uph-navy" {{ $curriculum->is_locked ? 'disabled' : '' }}>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <input type="number" step="0.5" min="0" max="100" name="weight_uas" value="{{ $course->weight_uas }}" class="w-16 h-8 text-center text-xs font-mono font-bold border rounded focus:ring-1 focus:ring-uph-navy" {{ $curriculum->is_locked ? 'disabled' : '' }}>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <select name="passing_grade_letter" class="h-8 px-2 text-xs font-mono font-bold border rounded" {{ $curriculum->is_locked ? 'disabled' : '' }}>
                                        <option value="C" {{ $course->passing_grade_letter === 'C' ? 'selected' : '' }}>C (2.00)</option>
                                        <option value="C+" {{ $course->passing_grade_letter === 'C+' ? 'selected' : '' }}>C+ (2.30)</option>
                                        <option value="B" {{ $course->passing_grade_letter === 'B' ? 'selected' : '' }}>B (3.00)</option>
                                        <option value="D" {{ $course->passing_grade_letter === 'D' ? 'selected' : '' }}>D (1.00)</option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if(!$curriculum->is_locked)
                                    <button type="submit" class="px-3 py-1.5 rounded bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-[11px] shadow">
                                        Simpan
                                    </button>
                                    @endif
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: CPMK to CPL Alignment -->
    <div x-show="activeTab === 'cpmk'" class="space-y-6" x-cloak>
        <div class="flex justify-between items-center">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Pemetaan Capaian Pembelajaran Mata Kuliah (CPMK)</h2>
            @if(!$curriculum->is_locked)
            <button @click="cpmkModalOpen = true" class="px-4 py-2 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow">
                <i class="fa-solid fa-plus mr-1"></i> Petakan CPMK Baru
            </button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="py-3 px-4">Mata Kuliah</th>
                            <th class="py-3 px-4">Kode CPMK</th>
                            <th class="py-3 px-4">CPL Terkait</th>
                            <th class="py-3 px-4">Deskripsi Kemampuan Akhir</th>
                            <th class="py-3 px-4 text-center">Bobot</th>
                            <th class="py-3 px-4">Metrik Penilaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($curriculum->courses as $course)
                            @foreach($course->cpmks as $cpmk)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-bold text-uph-navy">{{ $course->code }} - {{ $course->name_id }}</td>
                                <td class="py-3 px-4 font-mono font-bold">{{ $cpmk->code }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded bg-uph-navy/10 text-uph-navy font-mono font-bold text-[10px]">
                                        {{ $cpmk->cpl->code ?? 'CPL' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700">{{ $cpmk->description }}</td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-uph-crimson">{{ $cpmk->weight_percentage }}%</td>
                                <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">{{ $cpmk->assessment_metric ?? 'Tugas & Rubrik' }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: Prerequisite Tree & Structure -->
    <div x-show="activeTab === 'prereq'" class="space-y-6" x-cloak>
        <div class="flex justify-between items-center">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Struktur Prasyarat Mata Kuliah</h2>
            @if(!$curriculum->is_locked)
            <button @click="prereqModalOpen = true" class="px-4 py-2 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Prasyarat Mata Kuliah
            </button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="py-3 px-4">Mata Kuliah Utama</th>
                            <th class="py-3 px-4 text-center">Semester</th>
                            <th class="py-3 px-4">Mata Kuliah Prasyarat</th>
                            <th class="py-3 px-4 text-center">Nilai Minimum Prasyarat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($curriculum->courses as $course)
                            @if($course->prerequisites->count() > 0)
                                @foreach($course->prerequisites as $prereq)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-bold text-uph-navy">{{ $course->code }} - {{ $course->name_id }}</td>
                                    <td class="py-3 px-4 text-center font-mono">{{ $course->semester_level }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $prereq->code }} - {{ $prereq->name_id }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-mono font-bold text-[11px]">
                                            Min. {{ $prereq->pivot->minimum_grade }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: OBE CPL Attainment Radar & Analytics -->
    <div x-show="activeTab === 'attainment'" class="space-y-6" x-cloak>
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Analisis Ketercapaian Lulusan (CPL Attainment Matrix)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pemetaan capaian riil mahasiswa dari Nilai Kelas Kuliah &rarr; CPMK &rarr; CPL (Standar Akreditasi Internasional &amp; BAN-PT)</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Ambang Batas Kelulusan: &ge; 70%
                </span>
            </div>
        </div>

        @if(!empty($cplAttainments))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($cplAttainments as $item)
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-sm text-uph-navy bg-uph-navy/10 px-2.5 py-1 rounded-lg">{{ $item['code'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $item['meets_target'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                {{ $item['meets_target'] ? 'MEMENUHI TARGET' : 'PERLU EVALUASI' }}
                            </span>
                        </div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase">{{ $item['category'] }}</div>
                        <p class="text-xs text-slate-600 line-clamp-3" title="{{ $item['description'] }}">{{ $item['description'] }}</p>
                        
                        <div class="pt-2 border-t border-slate-100 space-y-1.5">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-500">Ketercapaian Riil:</span>
                                <span class="font-mono {{ $item['meets_target'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $item['score'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $item['meets_target'] ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ min(100, $item['score']) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal 1: Add CPL -->
    <div x-show="cplModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="cplModalOpen = false">
            <h3 class="font-display font-bold text-base text-uph-navy">Tambah Capaian Pembelajaran Lulusan (CPL)</h3>
            <form method="POST" action="/curriculum/obe/{{ $curriculum->id }}/cpl" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kode CPL</label>
                    <input type="text" name="code" required placeholder="Contoh: CPL-01 atau CPL-KU1" class="w-full h-10 px-3 border rounded-lg">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori Capaian</label>
                    <select name="category" required class="w-full h-10 px-3 border rounded-lg bg-white">
                        <option value="Sikap">Sikap (Attitude)</option>
                        <option value="Keterampilan Umum">Keterampilan Umum (General Skills)</option>
                        <option value="Pengetahuan">Pengetahuan (Knowledge)</option>
                        <option value="Keterampilan Khusus">Keterampilan Khusus (Specialized Skills)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Capaian (Bahasa Indonesia)</label>
                    <textarea name="description_id" required rows="3" class="w-full p-3 border rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Capaian (English - Optional)</label>
                    <textarea name="description_en" rows="2" class="w-full p-3 border rounded-lg"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="cplModalOpen = false" class="px-4 py-2 border rounded-lg text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-uph-navy text-white rounded-lg font-bold">Simpan CPL</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Map CPMK -->
    <div x-show="cpmkModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="cpmkModalOpen = false">
            <h3 class="font-display font-bold text-base text-uph-navy">Petakan CPMK ke Mata Kuliah</h3>
            <form method="POST" :action="'/curriculum/courses/' + selectedCourseId + '/cpmk'" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Mata Kuliah</label>
                    <select x-model="selectedCourseId" required class="w-full h-10 px-3 border rounded-lg bg-white">
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($curriculum->courses as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name_id }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Hubungkan ke CPL</label>
                    <select name="obe_cpl_id" required class="w-full h-10 px-3 border rounded-lg bg-white">
                        <option value="">-- Pilih CPL --</option>
                        @foreach($curriculum->cpls as $cpl)
                            <option value="{{ $cpl->id }}">{{ $cpl->code }} ({{ $cpl->category }}): {{ substr($cpl->description_id, 0, 50) }}...</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kode CPMK</label>
                    <input type="text" name="code" required placeholder="Contoh: CPMK-01" class="w-full h-10 px-3 border rounded-lg">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi CPMK</label>
                    <textarea name="description" required rows="2" class="w-full p-3 border rounded-lg"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bobot (%)</label>
                        <input type="number" step="0.5" min="1" max="100" name="weight_percentage" required value="25" class="w-full h-10 px-3 border rounded-lg font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Metrik Asesmen</label>
                        <input type="text" name="assessment_metric" placeholder="Tugas / Praktikum" class="w-full h-10 px-3 border rounded-lg">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="cpmkModalOpen = false" class="px-4 py-2 border rounded-lg text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-uph-navy text-white rounded-lg font-bold" :disabled="!selectedCourseId">Simpan CPMK</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Add Prerequisite -->
    <div x-show="prereqModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="prereqModalOpen = false">
            <h3 class="font-display font-bold text-base text-uph-navy">Tambah Prasyarat Mata Kuliah</h3>
            <form method="POST" :action="'/curriculum/courses/' + selectedCourseId + '/prerequisites'" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mata Kuliah Utama</label>
                    <select x-model="selectedCourseId" required class="w-full h-10 px-3 border rounded-lg bg-white">
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($curriculum->courses as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name_id }} (Sem. {{ $c->semester_level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mata Kuliah yang Menjadi Prasyarat</label>
                    <select name="prerequisite_course_id" required class="w-full h-10 px-3 border rounded-lg bg-white">
                        <option value="">-- Pilih Mata Kuliah Prasyarat --</option>
                        @foreach($curriculum->courses as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name_id }} (Sem. {{ $c->semester_level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nilai Huruf Minimum Lulus</label>
                    <select name="minimum_grade" required class="w-full h-10 px-3 border rounded-lg bg-white font-mono font-bold">
                        <option value="D">D (1.00)</option>
                        <option value="C" selected>C (2.00)</option>
                        <option value="C+">C+ (2.30)</option>
                        <option value="B">B (3.00)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="prereqModalOpen = false" class="px-4 py-2 border rounded-lg text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-uph-navy text-white rounded-lg font-bold" :disabled="!selectedCourseId">Simpan Prasyarat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
