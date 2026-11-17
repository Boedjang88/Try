@extends('layouts.portal')

@section('title', 'Tugas Akhir & Skripsi - UPH')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'overview',
    showLogModal: false,
    showDefenseModal: false,
    defenseScore: {
        presentation: 85,
        methodology: 80,
        mastery: 85,
        manuscript: 90,
        get total() {
            return (this.presentation * 0.25) + (this.methodology * 0.30) + (this.mastery * 0.35) + (this.manuscript * 0.10);
        }
    }
}">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    Sistem Skripsi & Sidang Tugas Akhir
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Universitas Pelita Harapan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Manajemen Skripsi & Bimbingan Digital</h1>
            <p class="text-xs text-slate-500 mt-1">
                Alur pengajuan proposal, logbook bimbingan digital terverifikasi, penjadwalan sidang, dan penilaian rubrik dewan penguji.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                <i class="fa-solid fa-graduation-cap text-uph-gold"></i>
                Tahap Akhir Sarjana (S1)
            </span>
        </div>
    </div>

    <!-- STUDENT PERSPECTIVE -->
    @if(Auth::user()->role === 'student')
        @if(!$myThesis)
        <!-- Proposal Submission Form -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-uph-crimson/10 text-uph-crimson">
                    Pengajuan Baru
                </span>
                <h2 class="text-xl font-bold text-slate-900 mt-2">Formulir Pendaftaran Proposal Tugas Akhir</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Ajukan judul penelitian dan rencana dosen pembimbing. Pastikan Anda telah memenuhi minimal 100 SKS lulus.
                </p>
            </div>

            <form action="/thesis/proposal" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Skripsi / Tugas Akhir (Bahasa Indonesia & English) *</label>
                    <input type="text" name="title" required minlength="15" maxlength="255"
                           class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy"
                           placeholder="Contoh: Rancang Bangun Sistem Informasi Manajemen Rumah Sakit Berbasis Cloud Menggunakan Arsitektur Microservices...">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Abstrak & Latar Belakang Masalah Penelitian *</label>
                    <textarea name="abstract" rows="5" required minlength="50" maxlength="3000"
                              class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy leading-relaxed"
                              placeholder="Deskripsikan latar belakang, rumusan masalah, metodologi yang diusulkan, dan luaran yang diharapkan..."></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Usulan Dosen Pembimbing Utama (Pembimbing 1) *</label>
                        <select name="proposed_supervisor1_id" required
                                class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy bg-white">
                            <option value="">-- Pilih Dosen Pembimbing 1 --</option>
                            @foreach($lecturers as $lec)
                                <option value="{{ $lec->id }}">{{ $lec->formatted_name ?? ($lec->user->full_name ?? '-') }} ({{ $lec->nidn }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Usulan Dosen Pembimbing Pendamping (Pembimbing 2)</label>
                        <select name="proposed_supervisor2_id"
                                class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-uph-navy/30 focus:border-uph-navy bg-white">
                            <option value="">-- Opsional: Pilih Dosen Pembimbing 2 --</option>
                            @foreach($lecturers as $lec)
                                <option value="{{ $lec->id }}">{{ $lec->formatted_name ?? ($lec->user->full_name ?? '-') }} ({{ $lec->nidn }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow-sm transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan Proposal Skripsi
                    </button>
                </div>
            </form>
        </div>
        @else
        <!-- Student Active Thesis Dashboard -->
        <div class="space-y-6">
            <!-- Thesis Banner Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            @php
                                $statusBadge = match($myThesis->proposal_status) {
                                    'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-amber-100 text-amber-800 border-amber-200',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border {{ $statusBadge }}">
                                Proposal: {{ strtoupper($myThesis->proposal_status) }}
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="text-xs text-slate-500 font-bold">
                                Status Sidang: <strong class="uppercase text-slate-800">{{ str_replace('_', ' ', $myThesis->defense_status) }}</strong>
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-display font-black text-slate-900 leading-snug">
                            {{ $myThesis->title }}
                        </h2>
                    </div>

                    @if($myThesis->isEligibleForDefense() && $myThesis->defense_status === 'not_ready')
                    <span class="px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-800 font-bold text-xs border border-emerald-200 flex items-center gap-2 shrink-0">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Siap Daftar Sidang (8+ Bimbingan)
                    </span>
                    @endif
                </div>

                <!-- Supervisors & Examiners Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-6 text-xs">
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Pembimbing Utama (1)</span>
                        <strong class="text-slate-800 text-xs mt-1 block">
                            {{ $myThesis->supervisor1->formatted_name ?? ($myThesis->supervisor1->user->full_name ?? '-') }}
                        </strong>
                        <span class="text-slate-500 font-mono text-[10px]">NIDN: {{ $myThesis->supervisor1->nidn ?? '-' }}</span>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Pembimbing Pendamping (2)</span>
                        <strong class="text-slate-800 text-xs mt-1 block">
                            {{ $myThesis->supervisor2->formatted_name ?? ($myThesis->supervisor2->user->full_name ?? 'Tidak Ditentukan') }}
                        </strong>
                        <span class="text-slate-500 font-mono text-[10px]">NIDN: {{ $myThesis->supervisor2->nidn ?? '-' }}</span>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Ketua Penguji (1)</span>
                        <strong class="text-slate-800 text-xs mt-1 block">
                            {{ $myThesis->examiner1->formatted_name ?? ($myThesis->examiner1->user->full_name ?? 'Menunggu Penetapan') }}
                        </strong>
                        <span class="text-slate-500 font-mono text-[10px]">NIDN: {{ $myThesis->examiner1->nidn ?? '-' }}</span>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Anggota Penguji (2)</span>
                        <strong class="text-slate-800 text-xs mt-1 block">
                            {{ $myThesis->examiner2->formatted_name ?? ($myThesis->examiner2->user->full_name ?? 'Menunggu Penetapan') }}
                        </strong>
                        <span class="text-slate-500 font-mono text-[10px]">NIDN: {{ $myThesis->examiner2->nidn ?? '-' }}</span>
                    </div>
                </div>

                <!-- Defense Readiness Meter -->
                @php
                    $verifiedCount = $myThesis->verifiedGuidanceCount();
                    $targetCount = 8;
                    $percent = min(100, round(($verifiedCount / $targetCount) * 100));
                @endphp
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-bold text-slate-700">Persyaratan Bimbingan Menuju Sidang Skripsi:</span>
                        <span class="font-mono font-bold text-uph-navy">
                            {{ $verifiedCount }} / {{ $targetCount }} Sesi Terverifikasi ({{ $percent }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Guidance Logbook Section -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Buku Catatan Bimbingan Digital (Logbook)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Catat setiap sesi bimbingan bersama dosen untuk diverifikasi secara daring.</p>
                    </div>

                    @if($myThesis->proposal_status === 'approved')
                    <button type="button" @click="showLogModal = true" 
                            class="px-4 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow-sm transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah Log Bimbingan
                    </button>
                    @endif
                </div>

                <!-- Guidance Entries Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4 w-32">Tanggal</th>
                                <th class="py-3 px-4 w-48">Dosen Pembimbing</th>
                                <th class="py-3 px-6">Topik Diskusi & Catatan Mahasiswa</th>
                                <th class="py-3 px-6">Umpan Balik Dosen</th>
                                <th class="py-3 px-4 text-center w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($myThesis->guidanceLogs as $idx => $log)
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="py-3 px-4 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $log->guidance_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-700">
                                    {{ $log->lecturer->formatted_name ?? ($log->lecturer->user->full_name ?? '-') }}
                                </td>
                                <td class="py-3 px-6 text-slate-800 leading-relaxed">
                                    <strong class="block text-slate-900">{{ $log->discussion_summary }}</strong>
                                    @if($log->student_notes)
                                        <span class="text-[11px] text-slate-500 mt-1 block">Catatan: {{ $log->student_notes }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-6 text-slate-600 italic">
                                    {{ $log->supervisor_feedback ?: 'Belum ada umpan balik tertulis.' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($log->is_verified)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-check text-[9px]"></i> Disetujui
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-clock text-[9px]"></i> Menunggu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                    Belum ada sesi bimbingan yang tercatat. Klik "+ Tambah Log Bimbingan" untuk menambahkan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Logbook Modal -->
            <div x-show="showLogModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4" @click.outside="showLogModal = false">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-900 text-base">Tambah Catatan Bimbingan Skripsi</h3>
                        <button type="button" @click="showLogModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="/thesis/{{ $myThesis->id }}/guidance" method="POST" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Dosen yang Membimbing *</label>
                            <select name="lecturer_id" required class="w-full rounded-xl border border-slate-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                                <option value="{{ $myThesis->supervisor1_lecturer_id }}">
                                    Pembimbing 1: {{ $myThesis->supervisor1->formatted_name ?? ($myThesis->supervisor1->user->full_name ?? '-') }}
                                </option>
                                @if($myThesis->supervisor2_lecturer_id)
                                <option value="{{ $myThesis->supervisor2_lecturer_id }}">
                                    Pembimbing 2: {{ $myThesis->supervisor2->formatted_name ?? ($myThesis->supervisor2->user->full_name ?? '-') }}
                                </option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Sesi Bimbingan *</label>
                            <input type="date" name="guidance_date" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                   class="w-full rounded-xl border border-slate-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Rangkuman Materi & Topik Diskusi *</label>
                            <textarea name="discussion_summary" rows="3" required minlength="15"
                                      class="w-full rounded-xl border border-slate-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30"
                                      placeholder="Contoh: Pembahasan Bab 3 Metodologi Penelitian dan perancangan diagram sequence..."></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tindak Lanjut / Action Items</label>
                            <textarea name="student_notes" rows="2"
                                      class="w-full rounded-xl border border-slate-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-uph-navy/30"
                                      placeholder="Contoh: Merevisi Bab 3 bagian uji validitas instrumen sebelum sesi bimbingan berikutnya..."></textarea>
                        </div>

                        <div class="pt-3 flex justify-end gap-2">
                            <button type="button" @click="showLogModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-uph-navy text-white font-bold hover:bg-uph-navy-dark shadow-sm">
                                Simpan Logbook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @else
    <!-- LECTURER / KAPRODI PERSPECTIVE -->
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Mahasiswa Bimbingan & Sidang Skripsi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau kemajuan penelitian mahasiswa, verifikasi log bimbingan, dan masukkan penilaian rubrik dewan penguji.</p>
                </div>
            </div>

            @php
                $thesesToDisplay = $supervisingTheses->isNotEmpty() ? $supervisingTheses : $allTheses;
            @endphp

            @if($thesesToDisplay->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="text-base font-bold text-slate-700">Tidak ada data tugas akhir aktif</h3>
                <p class="text-xs text-slate-400 mt-1">Belum ada mahasiswa yang ditugaskan bimbingan atau ujian skripsi kepada Anda.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-4 px-6">Mahasiswa & NIM</th>
                            <th class="py-4 px-6">Judul Skripsi</th>
                            <th class="py-4 px-4 text-center">Log Bimbingan</th>
                            <th class="py-4 px-4 text-center">Status Proposal</th>
                            <th class="py-4 px-4 text-center">Status Sidang</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($thesesToDisplay as $th)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900 block text-xs">{{ $th->student->user->full_name }}</span>
                                <span class="font-mono text-slate-500 text-[11px]">NIM: {{ $th->student->nim }}</span>
                            </td>
                            <td class="py-4 px-6 max-w-xs">
                                <span class="font-bold text-slate-800 line-clamp-2">{{ $th->title }}</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="font-mono font-bold text-xs text-slate-800">
                                    {{ $th->verifiedGuidanceCount() }} / {{ $th->guidanceLogs->count() }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">Terverifikasi</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $th->proposal_status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $th->proposal_status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $th->defense_status === 'passed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700' }}">
                                    {{ str_replace('_', ' ', $th->defense_status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($th->guidanceLogs->where('is_verified', false)->count() > 0)
                                        @php $pendingLog = $th->guidanceLogs->where('is_verified', false)->first(); @endphp
                                        <form method="POST" action="/thesis/guidance/{{ $pendingLog->id }}/verify" class="inline">
                                            @csrf
                                            <input type="hidden" name="is_verified" value="1">
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i> Verif Log
                                            </button>
                                        </form>
                                    @endif

                                    @if($th->defense_status === 'scheduled')
                                    <button type="button" @click="showDefenseModal = true"
                                            class="px-3 py-1.5 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-[11px] shadow-sm flex items-center gap-1">
                                        <i class="fa-solid fa-gavel"></i> Nilai Sidang
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
