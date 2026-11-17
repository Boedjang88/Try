@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{ assignmentModalOpen: false, gradeModalOpen: false, activeSubmissionId: '', gradeScore: 100, gradeFeedback: '', isUploading: false, uploadProgress: 0 }">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/lms?class_section_id={{ $session->class_section_id }}" class="text-xs text-uph-navy font-bold hover:underline">
                    &larr; Kembali ke Timeline 16 Sesi
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs font-bold font-mono text-uph-gold">Pertemuan {{ $session->session_number }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">{{ $session->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $session->classSection->course->name_id }} ({{ $session->classSection->section_code }}) &bull; 
                Dosen: <strong class="text-slate-800">{{ $session->classSection->lecturer->formatted_name ?? ($session->classSection->lecturer->user->full_name ?? '-') }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/attendance/session/{{ $session->id }}" class="px-4 py-2.5 rounded-xl border border-uph-navy text-uph-navy hover:bg-uph-navy hover:text-white font-bold text-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-qrcode"></i> Presensi Kelas
            </a>
            @if(auth()->user()->role === 'lecturer' || auth()->user()->role === 'superadmin')
            <button @click="assignmentModalOpen = true" class="px-4 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow">
                <i class="fa-solid fa-plus mr-1"></i> Buat Tugas Baru
            </button>
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
        <i class="fa-solid fa-circle-exclamation text-uph-crimson text-base"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Assignments List & Submission Area -->
    <div class="space-y-6">
        @forelse($session->assignments as $assignment)
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b pb-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-uph-crimson">Tugas Kuliah Terstruktur</span>
                    <h2 class="text-xl font-display font-bold text-uph-navy mt-0.5">{{ $assignment->title }}</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Batas Pengumpulan: <strong class="text-uph-crimson">{{ $assignment->deadline->format('d F Y, H:i') }} WIB</strong> &bull; 
                        Nilai Maksimum: <span class="font-mono font-bold text-slate-800">{{ $assignment->max_score }}</span>
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $assignment->isPastDeadline() ? 'bg-red-100 text-uph-crimson' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $assignment->isPastDeadline() ? 'Tenggat Waktu Berakhir' : 'Pengumpulan Dibuka' }}
                </span>
            </div>

            <div class="text-xs text-slate-700 leading-relaxed bg-surface-slate p-4 rounded-xl border border-slate-100">
                {!! nl2br(e($assignment->description)) !!}
            </div>

            <!-- Student Submission Portal -->
            @if(auth()->user()->role === 'student')
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <h3 class="font-display font-bold text-sm text-uph-navy">Status Pengumpulan Tugas Anda</h3>
                
                @if($studentSubmission)
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-file-circle-check text-2xl text-emerald-600"></i>
                        <div>
                            <span class="font-bold text-emerald-950 block">Tugas Berhasil Dikumpulkan</span>
                            <span class="text-slate-500 text-[11px]">Waktu Unggah: {{ $studentSubmission->submitted_at->format('d/m/Y H:i') }} WIB</span>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($studentSubmission->score !== null)
                        <span class="text-lg font-mono font-black text-emerald-700 block">Nilai: {{ $studentSubmission->score }}/{{ $assignment->max_score }}</span>
                        <span class="text-[11px] text-slate-500">Feedback: {{ $studentSubmission->feedback ?? 'Tugas dinilai sangat baik.' }}</span>
                        @else
                        <span class="px-2.5 py-1 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Menunggu Penilaian Dosen</span>
                        @endif
                    </div>
                </div>
                @else
                    @if(!$assignment->isPastDeadline())
                    <form method="POST" action="/lms/assignments/{{ $assignment->id }}/submit" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="border-2 border-dashed border-slate-300 hover:border-uph-navy rounded-2xl p-6 text-center space-y-2 bg-slate-50">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400"></i>
                            <p class="text-xs font-bold text-slate-700">Pilih berkas tugas untuk diunggah</p>
                            <p class="text-[11px] text-slate-400">Maksimum ukuran berkas 20 MB (Format: PDF, ZIP, DOCX)</p>
                            <input type="file" name="submission_file" required class="block mx-auto text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-uph-navy file:text-white hover:file:bg-uph-navy-dark">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow">
                                <i class="fa-solid fa-upload mr-1.5"></i> Unggah Jawaban Tugas
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="p-4 rounded-xl bg-red-50 text-uph-crimson text-xs font-bold border border-red-200">
                        Pengumpulan tugas ditutup karena batas waktu telah berakhir.
                    </div>
                    @endif
                @endif
            </div>
            @endif

            <!-- Lecturer Grading Roster -->
            @if(auth()->user()->role === 'lecturer' || auth()->user()->role === 'superadmin')
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-display font-bold text-sm text-uph-navy">Daftar Pengumpulan Mahasiswa</h3>
                    <span class="text-xs text-slate-400">{{ $assignment->submissions->count() }} Pengumpulan Terdata</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                            <tr>
                                <th class="py-2.5 px-3">Mahasiswa</th>
                                <th class="py-2.5 px-3 text-center">Waktu Submit</th>
                                <th class="py-2.5 px-3 text-center">Berkas</th>
                                <th class="py-2.5 px-3 text-center">Nilai (0-100)</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($assignment->submissions as $sub)
                            <tr>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 block">{{ $sub->student->user->full_name }}</span>
                                    <span class="font-mono text-[11px] text-slate-400">{{ $sub->student->nim }}</span>
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-slate-500 text-[11px]">
                                    {{ $sub->submitted_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ $sub->submission_file_path }}" target="_blank" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-uph-navy font-bold text-[11px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-download"></i> Unduh
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-sm" id="score-display-{{ $sub->id }}">
                                    {{ $sub->score ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <button type="button" @click="activeSubmissionId = '{{ $sub->id }}'; gradeScore = {{ $sub->score ?? 100 }}; gradeFeedback = '{{ addslashes($sub->feedback ?? '') }}'; gradeModalOpen = true" class="px-3 py-1.5 rounded-lg bg-uph-navy text-white font-bold text-[11px] shadow">
                                        Beri Nilai
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    Belum ada mahasiswa yang mengunggah berkas tugas ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-dashed text-xs">
            <i class="fa-solid fa-book-open text-4xl text-slate-300 mb-3 block"></i>
            Belum ada tugas atau materi yang diterbitkan pada sesi perkuliahan ini.
        </div>
        @endforelse
    </div>

    <!-- Create Assignment Modal -->
    <div x-show="assignmentModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="assignmentModalOpen = false">
            <h3 class="font-display font-bold text-base text-uph-navy">Terbitkan Tugas Kuliah Baru</h3>
            <form method="POST" action="/lms/session/{{ $session->id }}/assignment" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul Tugas <span class="text-uph-crimson">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Tugas Mandiri Analisis Studi Kasus" class="w-full h-10 px-3 border rounded-lg focus:ring-1 focus:ring-uph-navy">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Petunjuk & Deskripsi Tugas <span class="text-uph-crimson">*</span></label>
                    <textarea name="description" required rows="3" placeholder="Jelaskan instruksi pengerjaan, format berkas, dan rubrik asesmen..." class="w-full p-3 border rounded-lg focus:ring-1 focus:ring-uph-navy"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tenggat Waktu (Deadline) <span class="text-uph-crimson">*</span></label>
                        <input type="datetime-local" name="deadline" required class="w-full h-10 px-3 border rounded-lg focus:ring-1 focus:ring-uph-navy">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Skor Maksimum</label>
                        <input type="number" name="max_score" required value="100" min="10" max="100" class="w-full h-10 px-3 border rounded-lg font-mono">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="assignmentModalOpen = false" class="px-4 py-2 border rounded-lg text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-uph-navy text-white rounded-lg font-bold">Terbitkan Tugas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Grade Submission Modal -->
    <div x-show="gradeModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="gradeModalOpen = false">
            <h3 class="font-display font-bold text-base text-uph-navy">Input Nilai & Umpan Balik</h3>
            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Skor Penilaian (0 - 100)</label>
                    <input type="number" step="1" min="0" max="100" x-model="gradeScore" class="w-full h-10 px-3 border rounded-lg font-mono font-bold text-base">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Umpan Balik Dosen (Feedback)</label>
                    <textarea x-model="gradeFeedback" rows="3" placeholder="Catatan evaluasi untuk mahasiswa..." class="w-full p-3 border rounded-lg"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="gradeModalOpen = false" class="px-4 py-2 border rounded-lg text-slate-600 font-bold">Batal</button>
                    <button type="button" @click="submitGrade()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow">Simpan Nilai</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function submitGrade() {
        const app = Alpine.$data(document.querySelector('[x-data]'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const res = await fetch(`/api/lms/submissions/${app.activeSubmissionId}/grade`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    score: app.gradeScore,
                    feedback: app.gradeFeedback
                })
            });

            const data = await res.json();
            if (data.success) {
                app.gradeModalOpen = false;
                const scoreCell = document.getElementById(`score-display-${app.activeSubmissionId}`);
                if (scoreCell) scoreCell.textContent = data.score;
                if (window.EnterpriseToast) window.EnterpriseToast.success(data.message, 'Nilai Tersimpan');
            }
        } catch (e) {
            if (window.EnterpriseToast) window.EnterpriseToast.error('Gagal menyimpan nilai.');
        }
    }
</script>
@endsection
