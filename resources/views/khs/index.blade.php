@extends('layouts.portal')

@section('title', 'Kartu Hasil Studi (KHS) - UPH')

@section('content')
<div class="space-y-6">
    <!-- Top Header & Period Selector -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    SIAKAD & Evaluasi Studi
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Universitas Pelita Harapan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Kartu Hasil Studi (KHS)</h1>
            <p class="text-xs text-slate-500 mt-1">
                Transkrip evaluasi capaian pembelajaran matakuliah per semester dan catatan akademik resmi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Period Selector Form -->
            <form method="GET" action="/khs" class="flex items-center gap-2">
                @if(request('student_id'))
                    <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                @endif
                <label for="period-select" class="text-xs font-bold text-slate-600 hidden sm:inline">Periode:</label>
                <select id="period-select" name="academic_period_id" onchange="this.form.submit()" 
                        class="text-xs font-bold text-slate-700 bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/20">
                    @foreach($academicPeriods as $period)
                        <option value="{{ $period->id }}" {{ $selectedPeriod && $selectedPeriod->id === $period->id ? 'selected' : '' }}>
                            {{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="/khs/transcript{{ request('student_id') ? '?student_id=' . request('student_id') : '' }}" 
               class="px-4 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-graduation-cap"></i> Transkrip Kumulatif
            </a>

            <button onclick="window.print()" 
                    class="p-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition-colors"
                    title="Cetak KHS">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>
    </div>

    <!-- Student Bio Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Nama Mahasiswa</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ $student->user->full_name }}</span>
                <span class="text-slate-500 font-mono">NIM: {{ $student->nim }}</span>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Program Studi & Fakultas</span>
                <span class="font-bold text-slate-800 mt-0.5 block">{{ $student->studyProgram->name_id }}</span>
                <span class="text-slate-500">{{ $student->studyProgram->faculty->name_id ?? 'UPH Main Campus' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Semester & Kurikulum</span>
                <span class="font-bold text-slate-800 mt-0.5 block">Semester {{ $student->current_semester }}</span>
                <span class="text-slate-500">Angkatan {{ $student->enrollment_year }} &bull; Kurikulum OBE</span>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Dosen Pembimbing Akademik (PA)</span>
                <span class="font-bold text-slate-800 mt-0.5 block">{{ $student->academicAdvisor->user->full_name ?? 'Dr. Dosen Wali, M.Kom.' }}</span>
                <span class="text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                    <i class="fa-solid fa-circle-check text-[10px]"></i> Registrasi Finansial Lunas
                </span>
            </div>
        </div>
    </div>

    <!-- Early Warning System (EWS) Alert Banner -->
    @if($ewsStatus['level'] === 'danger')
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white">
                        {{ $ewsStatus['badge'] }}
                    </span>
                    <h3 class="font-bold text-rose-900 text-sm">{{ $ewsStatus['title'] }}</h3>
                </div>
                <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                    {{ $ewsStatus['message'] }}
                </p>
            </div>
        </div>
    @elseif($ewsStatus['level'] === 'warning')
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white">
                        {{ $ewsStatus['badge'] }}
                    </span>
                    <h3 class="font-bold text-amber-900 text-sm">{{ $ewsStatus['title'] }}</h3>
                </div>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    {{ $ewsStatus['message'] }}
                </p>
            </div>
        </div>
    @elseif($ewsStatus['level'] === 'success')
        <div class="bg-gradient-to-r from-uph-navy/5 via-uph-gold/10 to-uph-navy/5 border border-uph-gold/30 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-uph-gold text-slate-900 flex items-center justify-center text-lg shrink-0 shadow-sm">
                <i class="fa-solid fa-award"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-uph-navy text-white">
                        {{ $ewsStatus['badge'] }}
                    </span>
                    <h3 class="font-bold text-uph-navy text-sm">{{ $ewsStatus['title'] }}</h3>
                </div>
                <p class="text-xs text-slate-700 mt-1 leading-relaxed">
                    {{ $ewsStatus['message'] }}
                </p>
            </div>
        </div>
    @endif

    <!-- EDOM Survey Gate Warning Banner -->
    @if(!$allEdomCompleted)
        <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wide">
                        Evaluasi Dosen Oleh Mahasiswa (EDOM) Belum Lengkap ({{ $pendingEdomCount }} Tertunda)
                    </h4>
                    <p class="text-xs text-amber-800/90 mt-0.5 leading-relaxed">
                        Sesuai standar Sistem Penjaminan Mutu Internal (SPMI), nilai akhir dan nilai huruf matakuliah dikaburkan sampai Anda mengisi kuesioner evaluasi pembelajaran dosen.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Academic GPA Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Indeks Prestasi Semester (IPS)</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-uph-navy font-mono">{{ number_format($ips, 2) }}</span>
                <span class="text-xs text-slate-400">/ 4.00</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                Berdasarkan {{ $totalGradedSks }} SKS dinilai
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Indeks Prestasi Kumulatif (IPK)</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-uph-navy font-mono">{{ number_format($ipk, 2) }}</span>
                <span class="text-xs text-slate-400">/ 4.00</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                Total {{ $cumulativeSks }} SKS diselesaikan
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Beban Studi Semester</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-slate-900 font-mono">{{ $totalSemesterSks }}</span>
                <span class="text-xs text-slate-400">SKS</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                {{ $krsItems->count() }} Matakuliah terdaftar
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Batas Beban SKS Semester Depan</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-emerald-600 font-mono">{{ $maxSksNext }}</span>
                <span class="text-xs text-slate-400">SKS Maks</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                Hak kuota KRS semester mendatang
            </span>
        </div>
    </div>

    <!-- KHS Course List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Rincian Nilai Capaian Matakuliah</h2>
                <p class="text-xs text-slate-500 mt-0.5">Periode Akademik: {{ $selectedPeriod->name ?? 'Semester Aktif' }}</p>
            </div>
            <div class="text-xs font-mono text-slate-500">
                Total Bobot Mutu: <strong class="text-slate-800">{{ number_format($totalQualityPoints, 2) }}</strong>
            </div>
        </div>

        @if($krsItems->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Tidak ada data KRS pada periode ini</h3>
            <p class="text-xs text-slate-400 mt-1">Mahasiswa tidak memiliki rencana studi yang disetujui pada periode semester terpilih.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-4 text-center w-12">No</th>
                        <th class="py-4 px-6">Kode & Matakuliah</th>
                        <th class="py-4 px-4 text-center">SKS</th>
                        <th class="py-4 px-4 text-center">Presensi</th>
                        <th class="py-4 px-4 text-center">Tugas</th>
                        <th class="py-4 px-4 text-center">UTS</th>
                        <th class="py-4 px-4 text-center">UAS</th>
                        <th class="py-4 px-4 text-center">Nilai Akhir</th>
                        <th class="py-4 px-4 text-center">Huruf</th>
                        <th class="py-4 px-4 text-center">Bobot</th>
                        <th class="py-4 px-4 text-center">Mutu</th>
                        <th class="py-4 px-6 text-center">Status EDOM</th>
                        <th class="py-4 px-4 text-center">Transparansi OBE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($krsItems as $idx => $item)
                        @php
                            $c = $item->classSection->course;
                            $sks = $c->sks;
                            $hasGrade = $item->final_numeric_score !== null;
                            $edomDone = $item->edom_completed;
                            $mutu = ($hasGrade && $edomDone) ? round($sks * (float)$item->grade_point, 2) : 0.00;
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-4 px-4 text-center text-slate-400 font-mono">
                                {{ $idx + 1 }}
                            </td>

                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-uph-navy bg-uph-navy/5 px-2 py-0.5 rounded text-[11px]">
                                        {{ $c->code }}
                                    </span>
                                    <span class="text-slate-400 text-[10px]">Kelas {{ $item->classSection->section_code }}</span>
                                </div>
                                <div class="font-bold text-slate-900 text-xs mt-1">
                                    {{ $c->name_id }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $item->classSection->lecturer->formatted_name ?? ($item->classSection->lecturer->user->full_name ?? '-') }}
                                </div>
                            </td>

                            <td class="py-4 px-4 text-center font-bold text-slate-700">
                                {{ $sks }}
                            </td>

                            <!-- Grade Breakdown Columns (Masked if EDOM pending) -->
                            <td class="py-4 px-4 text-center font-mono">
                                @if(!$hasGrade)
                                    <span class="text-slate-300">-</span>
                                @elseif(!$edomDone)
                                    <span class="blur-[4px] select-none text-slate-400">85.0</span>
                                @else
                                    {{ $item->score_presence ?? '0' }}
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center font-mono">
                                @if(!$hasGrade)
                                    <span class="text-slate-300">-</span>
                                @elseif(!$edomDone)
                                    <span class="blur-[4px] select-none text-slate-400">82.0</span>
                                @else
                                    {{ $item->score_task ?? '0' }}
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center font-mono">
                                @if(!$hasGrade)
                                    <span class="text-slate-300">-</span>
                                @elseif(!$edomDone)
                                    <span class="blur-[4px] select-none text-slate-400">88.0</span>
                                @else
                                    {{ $item->score_uts ?? '0' }}
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center font-mono">
                                @if(!$hasGrade)
                                    <span class="text-slate-300">-</span>
                                @elseif(!$edomDone)
                                    <span class="blur-[4px] select-none text-slate-400">90.0</span>
                                @else
                                    {{ $item->score_uas ?? '0' }}
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center font-mono font-bold">
                                @if(!$hasGrade)
                                    <span class="text-slate-400 italic text-[11px]">Belum Ada</span>
                                @elseif(!$edomDone)
                                    <span class="blur-[5px] select-none text-slate-400 font-bold">87.50</span>
                                @else
                                    <span class="text-slate-900">{{ number_format((float)$item->final_numeric_score, 2) }}</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center">
                                @if(!$hasGrade)
                                    <span class="text-slate-300">-</span>
                                @elseif(!$edomDone)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-lock text-[9px]"></i> Kunci
                                    </span>
                                @else
                                    @php
                                        $letterColor = match($item->letter_grade) {
                                            'A', 'A-' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'B+', 'B', 'B-' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'C+', 'C' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded font-mono font-bold text-xs border {{ $letterColor }}">
                                        {{ $item->letter_grade }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center font-mono">
                                @if(!$hasGrade || !$edomDone)
                                    <span class="text-slate-300">-</span>
                                @else
                                    {{ number_format((float)$item->grade_point, 2) }}
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center font-mono font-bold text-slate-800">
                                @if(!$hasGrade || !$edomDone)
                                    <span class="text-slate-300">-</span>
                                @else
                                    {{ number_format($mutu, 2) }}
                                @endif
                            </td>

                            <td class="py-4 px-6 text-center">
                                @if($edomDone)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check text-[10px]"></i> Selesai
                                    </span>
                                @else
                                    <a href="/edom/{{ $item->id }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-sm transition-colors">
                                        <i class="fa-solid fa-pen"></i> Isi EDOM
                                    </a>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center">
                                <button type="button" 
                                        @click="loadBreakdown('{{ $item->id }}')" 
                                        class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] border border-indigo-200 transition-colors inline-flex items-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-chart-pie text-[10px]"></i> Sanggah
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-xs">
                    <tr>
                        <td colspan="2" class="py-3 px-6 text-slate-700">Total Semester</td>
                        <td class="py-3 px-4 text-center text-slate-900 font-mono">{{ $totalSemesterSks }} SKS</td>
                        <td colspan="7" class="py-3 px-4 text-right text-slate-600">Total Mutu:</td>
                        <td class="py-3 px-4 text-center font-mono text-uph-navy font-black">{{ number_format($totalQualityPoints, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    <!-- OBE Score Breakdown & Grade Appeal Modal -->
    <div x-data="{
        openModal: false,
        loading: false,
        itemId: null,
        data: null,
        appealReason: '',
        submitting: false,
        async loadBreakdown(id) {
            this.itemId = id;
            this.openModal = true;
            this.loading = true;
            try {
                const res = await fetch('/portal/student/khs/breakdown/' + id);
                this.data = await res.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async submitAppeal() {
            if (!this.appealReason || this.appealReason.length < 15) {
                alert('Tuliskan alasan sanggah nilai minimal 15 karakter.');
                return;
            }
            this.submitting = true;
            const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
            const formData = new FormData(this.$refs.appealForm);
            try {
                const res = await fetch('/portal/student/khs/appeal/' + this.itemId, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    alert('Sanggah nilai berhasil diajukan ke Dosen Pengampu.');
                    this.loadBreakdown(this.itemId);
                } else {
                    alert(result.message || 'Gagal mengajukan sanggah nilai.');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi.');
            } finally {
                this.submitting = false;
            }
        }
    }" 
    @keydown.window.escape="openModal = false">
        <div x-show="openModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
             style="display: none;">
            <div @click.away="openModal = false" class="relative w-full max-w-2xl bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                        <h3 class="font-bold text-base text-slate-900 tracking-wide">
                            Transparansi Rincian Nilai OBE & Sanggah Nilai
                        </h3>
                    </div>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div x-show="loading" class="py-12 text-center text-slate-500 text-sm">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-indigo-600 mb-2"></i>
                    <p>Memuat rincian rubrik penilaian OBE...</p>
                </div>

                <div x-show="!loading && data && data.success" class="mt-4 space-y-6">
                    <!-- Course Banner -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-mono font-bold text-indigo-600" x-text="data?.course?.code"></span>
                            <h4 class="font-bold text-slate-900 text-base" x-text="data?.course?.name"></h4>
                            <p class="text-xs text-slate-500" x-text="'Dosen Pengampu: ' + data?.course?.lecturer"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-slate-900" x-text="data?.summary?.final_numeric_score"></span>
                            <span class="block text-xs font-bold text-emerald-600 font-mono" x-text="'Huruf: ' + data?.summary?.letter_grade + ' (' + data?.summary?.grade_point + ')'"></span>
                        </div>
                    </div>

                    <!-- Components Breakdown -->
                    <div>
                        <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Komponen Evaluasi Standar OBE:</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-400 block text-[10px]">Presensi (10%)</span>
                                <span class="font-bold text-slate-800 text-sm" x-text="data?.components?.presence?.raw"></span>
                                <span class="text-[10px] text-slate-500 block" x-text="'Kontribusi: ' + data?.components?.presence?.weighted"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-400 block text-[10px]">Tugas Kuliah (20%)</span>
                                <span class="font-bold text-slate-800 text-sm" x-text="data?.components?.task?.raw"></span>
                                <span class="text-[10px] text-slate-500 block" x-text="'Kontribusi: ' + data?.components?.task?.weighted"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-400 block text-[10px]">Kuis Mandiri (10%)</span>
                                <span class="font-bold text-slate-800 text-sm" x-text="data?.components?.quiz?.raw"></span>
                                <span class="text-[10px] text-slate-500 block" x-text="'Kontribusi: ' + data?.components?.quiz?.weighted"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-400 block text-[10px]">UTS Standar (25%)</span>
                                <span class="font-bold text-slate-800 text-sm" x-text="data?.components?.uts?.raw"></span>
                                <span class="text-[10px] text-slate-500 block" x-text="'Kontribusi: ' + data?.components?.uts?.weighted"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-400 block text-[10px]">UAS Akhir (25%)</span>
                                <span class="font-bold text-slate-800 text-sm" x-text="data?.components?.uas?.raw"></span>
                                <span class="text-[10px] text-slate-500 block" x-text="'Kontribusi: ' + data?.components?.uas?.weighted"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-400 block text-[10px]">Proyek OBE (10%)</span>
                                <span class="font-bold text-slate-800 text-sm" x-text="data?.components?.project?.raw"></span>
                                <span class="text-[10px] text-slate-500 block" x-text="'Kontribusi: ' + data?.components?.project?.weighted"></span>
                            </div>
                        </div>
                    </div>

                    <!-- CPMK Attainments -->
                    <div x-show="data?.cpmk_attainments && data.cpmk_attainments.length > 0">
                        <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Capaian Pembelajaran (CPMK):</h5>
                        <div class="space-y-2">
                            <template x-for="cpmk in data?.cpmk_attainments" :key="cpmk.code">
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                                    <div class="flex-1 pr-4">
                                        <div class="font-bold text-slate-800" x-text="cpmk.code"></div>
                                        <div class="text-[11px] text-slate-500" x-text="cpmk.description"></div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-slate-900" x-text="cpmk.attainment_percentage + '%'"></span>
                                        <span class="block text-[10px]" :class="cpmk.attainment_percentage >= 70 ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold'" x-text="cpmk.status"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Grade Appeal Form / Status -->
                    <div class="pt-4 border-t border-slate-200">
                        <template x-if="data?.appeal">
                            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-indigo-900 uppercase">Status Pengajuan Sanggah Nilai:</span>
                                    <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] bg-indigo-600 text-white" x-text="data?.appeal?.status"></span>
                                </div>
                                <p class="text-slate-700" x-text="'Alasan: ' + data?.appeal?.reason"></p>
                                <template x-if="data?.appeal?.lecturer_response">
                                    <div class="p-2.5 rounded-xl bg-white border border-indigo-100 text-slate-800 mt-2">
                                        <strong class="text-indigo-800">Tanggapan Dosen:</strong>
                                        <p class="mt-0.5" x-text="data?.appeal?.lecturer_response"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="!data?.appeal">
                            <form x-ref="appealForm" @submit.prevent="submitAppeal" class="space-y-3">
                                <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wide flex items-center gap-1.5">
                                    <i class="fa-solid fa-bullhorn text-amber-500"></i>
                                    Formulir Pengajuan Sanggah Nilai (Grade Appeal)
                                </h5>
                                <p class="text-[11px] text-slate-500">
                                    Jika terdapat selisih catatan presensi, nilai tugas, atau UTS/UAS, ajukan sanggah nilai beserta bukti pendukung ke dosen pengampu sebelum sinkronisasi BAAK.
                                </p>
                                <textarea name="reason" x-model="appealReason" rows="3" required placeholder="Tuliskan alasan diskrepansi nilai secara spesifik (minimal 15 karakter)..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600/20"></textarea>
                                
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Unggah Bukti Pendukung (PDF/PNG/ZIP max 10MB)</label>
                                    <input type="file" name="evidence_file" accept=".pdf,.png,.jpg,.zip" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                                </div>

                                <button type="submit" :disabled="submitting || appealReason.length < 15" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all disabled:opacity-50">
                                    <span x-show="!submitting">Kirim Sanggah Nilai ke Dosen Pengampu</span>
                                    <span x-show="submitting"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Mengirim Pengajuan...</span>
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal & Signature Footer -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 text-lg">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <span class="font-bold text-slate-700 block">Dokumen Resmi Digital SIAKAD UPH</span>
                <span>Divalidasi secara digital melalui Enterprise Ledger Hash &bull; Tanpa tanda tangan basah</span>
            </div>
        </div>

        <div class="text-right">
            <span>Tangerang, {{ now()->translatedFormat('d F Y') }}</span>
            <span class="block font-bold text-slate-700 mt-0.5">Biro Administrasi Akademik & Kemahasiswaan (BAAK)</span>
        </div>
    </div>
</div>
@endsection
