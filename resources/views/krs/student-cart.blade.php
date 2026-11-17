@extends('layouts.portal')

@section('content')
<div id="krs-war-app" 
     data-max-sks="{{ $maxSks }}" 
     data-current-sks="{{ $submission->total_sks }}"
     class="space-y-6">

    <!-- Top Command & SKS Status Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded bg-uph-crimson text-white text-[10px] font-bold uppercase tracking-wider">
                    High-Concurrency KRS War Engine
                </span>
                <span class="text-xs text-slate-400 font-mono">&bull; Periode: {{ $activePeriod->name }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Kartu Rencana Studi (KRS) Online</h1>
            <p class="text-xs text-slate-500 mt-1">
                Mahasiswa: <strong class="text-slate-800">{{ auth()->user()->full_name }}</strong> &bull; 
                NIM: <span class="font-mono text-uph-navy font-bold">{{ $student->nim }}</span> &bull; 
                Dosen PA: <span class="text-slate-800 font-semibold">{{ $student->academicAdvisor->formatted_name ?? 'Belum Ditugaskan' }}</span>
            </p>
        </div>

        <!-- SKS Load Meter Widget -->
        <div class="w-full md:w-80 bg-surface-slate p-4 rounded-xl border border-slate-200 space-y-2">
            <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Beban SKS Semester</span>
                <span class="font-mono font-bold text-uph-navy text-sm">
                    <span id="krs-current-sks-display">{{ $submission->total_sks }}</span> / {{ $maxSks }} SKS
                </span>
            </div>
            <div class="w-full h-3 rounded-full bg-slate-200 overflow-hidden">
                <div id="krs-sks-progress-bar" 
                     class="h-full bg-uph-navy transition-all duration-300"
                     style="width: {{ min(100, round(($submission->total_sks / $maxSks) * 100)) }}%;">
                </div>
            </div>
            <div class="flex justify-between items-center text-[10px] text-slate-400">
                <span id="krs-sks-percentage">{{ min(100, round(($submission->total_sks / $maxSks) * 100)) }}% Terpakai</span>
                <span>Batas Maksimum: {{ $maxSks }} SKS (IPS: {{ $student->last_semester_gpa_ips }})</span>
            </div>
        </div>
    </div>

    <!-- Status Alert Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-center p-4 rounded-xl border {{ $submission->status === 'approved' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($submission->status === 'submitted' ? 'bg-blue-50 border-blue-200 text-blue-900' : ($submission->status === 'rejected' ? 'bg-red-50 border-red-200 text-red-900' : 'bg-amber-50 border-amber-200 text-amber-900')) }} text-xs gap-3">
        <div class="flex items-center gap-3">
            <i class="fa-solid {{ $submission->status === 'approved' ? 'fa-circle-check text-emerald-600' : ($submission->status === 'submitted' ? 'fa-paper-plane text-blue-600' : ($submission->status === 'rejected' ? 'fa-triangle-exclamation text-uph-crimson' : 'fa-pen-to-square text-amber-600')) }} text-lg"></i>
            <div>
                <span class="font-bold uppercase tracking-wider">Status KRS: {{ strtoupper($submission->status) }}</span>
                @if($submission->status === 'rejected')
                    <p class="text-uph-crimson mt-0.5">Catatan Dosen PA: {{ $submission->rejection_note }}</p>
                @else
                    <p class="text-slate-600 mt-0.5">
                        {{ $submission->status === 'approved' ? 'KRS telah disetujui Dosen PA. Anda dapat mengunduh/mencetak dokumen resmi ber-QR.' : ($submission->status === 'submitted' ? 'KRS telah diajukan dan sedang menunggu peninjauan oleh Dosen Pembimbing Akademik.' : 'Silakan pilih mata kuliah di bawah. Pastikan tidak ada jadwal bentrok dan kuota masih tersedia.') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($submission->status === 'draft' || $submission->status === 'rejected')
            <form method="POST" action="/krs/submit">
                @csrf
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Ajukan ke Dosen PA
                </button>
            </form>
            @endif

            @if($submission->status === 'approved')
            <a href="/krs/print/{{ $submission->id }}" target="_blank" class="px-5 py-2.5 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-uph-gold font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak KRS Resmi (PDF)
            </a>
            @endif
        </div>
    </div>

    <!-- SKS Warning Limit Alert (Dynamic) -->
    <div id="krs-sks-warning-banner" class="hidden p-4 rounded-xl bg-red-100 border border-red-300 text-xs text-red-800 font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span>Peringatan: Total SKS yang Anda pilih telah melebihi batas hak pengambilan semester ini!</span>
    </div>

    <!-- Available Class Sections Catalog -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 bg-surface-slate border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Daftar Jadwal Kelas Dibuka</h2>
                <p class="text-xs text-slate-500">Pilih kelas kuliah sesuai dengan alokasi semester dan ketersediaan kursi.</p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    <input type="text" id="krs-course-search" placeholder="Cari mata kuliah, kode, dosen..." class="w-full h-9 pl-9 pr-3 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-uph-navy">
                </div>
                <select id="krs-day-filter" class="h-9 px-3 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-uph-navy">
                    <option value="all">Semua Hari</option>
                    <option value="1">Senin</option>
                    <option value="2">Selasa</option>
                    <option value="3">Rabu</option>
                    <option value="4">Kamis</option>
                    <option value="5">Jumat</option>
                    <option value="6">Sabtu</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-3 px-4">Mata Kuliah</th>
                        <th class="py-3 px-2 text-center">Kelas</th>
                        <th class="py-3 px-2 text-center">SKS</th>
                        <th class="py-3 px-4">Jadwal & Waktu</th>
                        <th class="py-3 px-3">Ruangan</th>
                        <th class="py-3 px-4">Dosen Pengampu</th>
                        <th class="py-3 px-3 text-center">Kuota Kursi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($availableSections as $section)
                    @php
                        $isEnrolled = in_array($section->id, $enrolledSectionIds, true);
                        $remaining = $section->remaining_seats;
                    @endphp
                    <tr class="krs-course-row transition-colors {{ $isEnrolled ? 'bg-blue-50/50' : 'hover:bg-slate-50' }}"
                        data-section-id="{{ $section->id }}"
                        data-course-code="{{ $section->course->code }}"
                        data-course-name="{{ $section->course->name_id }}"
                        data-sks="{{ $section->course->sks }}"
                        data-day="{{ $section->day_of_week }}"
                        data-day-name="{{ $section->day_name }}"
                        data-start-time="{{ substr($section->start_time, 0, 5) }}"
                        data-end-time="{{ substr($section->end_time, 0, 5) }}"
                        data-lecturer="{{ $section->lecturer->user->full_name ?? '' }}"
                        data-enrolled="{{ $isEnrolled ? 'true' : 'false' }}">
                        
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-uph-navy text-xs">{{ $section->course->code }}</span>
                            <span class="font-bold text-slate-900 block text-xs">{{ $section->course->name_id }}</span>
                            @if($section->course->prerequisites->count() > 0)
                            <span class="text-[10px] text-slate-400">
                                Prasyarat: 
                                @foreach($section->course->prerequisites as $prereq)
                                    <span class="font-mono text-slate-600 font-semibold">{{ $prereq->code }} (Min. {{ $prereq->pivot->minimum_grade }})</span>
                                @endforeach
                            </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-2 text-center">
                            <span class="px-2 py-0.5 rounded font-mono font-bold bg-slate-100 text-slate-800 border">
                                {{ $section->section_code }}
                            </span>
                        </td>

                        <td class="py-3.5 px-2 text-center font-mono font-bold text-slate-800">
                            {{ $section->course->sks }}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-800 block">{{ $section->day_name }}</span>
                            <span class="font-mono text-slate-500 text-[11px]">
                                {{ substr($section->start_time, 0, 5) }} - {{ substr($section->end_time, 0, 5) }} WIB
                            </span>
                        </td>

                        <td class="py-3.5 px-3">
                            <span class="font-mono font-semibold text-slate-700 block">{{ $section->room->code ?? 'TBA' }}</span>
                            <span class="text-[10px] text-slate-400">{{ $section->room->building ?? 'Kampus Lippo' }}</span>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-800 block truncate max-w-xs">
                                {{ $section->lecturer->formatted_name ?? ($section->lecturer->user->full_name ?? '-') }}
                            </span>
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            <div class="space-y-1">
                                <span class="krs-remaining-seats-badge px-2 py-0.5 rounded font-mono font-bold text-[10px] {{ $remaining <= 3 ? 'bg-red-100 text-uph-crimson' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $remaining }} Kursi Sisa
                                </span>
                                <div class="w-16 h-1.5 mx-auto rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full {{ $section->enrolled_count >= $section->max_quota ? 'bg-uph-crimson' : 'bg-emerald-500' }}" style="width: {{ min(100, round(($section->enrolled_count / $section->max_quota) * 100)) }}%;"></div>
                                </div>
                                <span class="text-[9px] text-slate-400 font-mono">{{ $section->enrolled_count }}/{{ $section->max_quota }}</span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            @if($submission->status === 'approved')
                                <span class="text-emerald-600 font-bold text-xs"><i class="fa-solid fa-check"></i> Terdaftar</span>
                            @else
                                @if($isEnrolled)
                                <button type="button" 
                                        data-action="remove"
                                        class="krs-toggle-btn px-3 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-uph-crimson font-bold text-xs transition-colors">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Batalkan
                                </button>
                                @else
                                <button type="button" 
                                        data-action="add"
                                        {{ ($section->is_closed || $remaining <= 0) ? 'disabled' : '' }}
                                        class="krs-toggle-btn px-3 py-1.5 rounded-lg {{ ($section->is_closed || $remaining <= 0) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-uph-navy hover:bg-uph-navy-dark text-white shadow' }} font-bold text-xs transition-colors">
                                    {{ ($section->is_closed || $remaining <= 0) ? 'Penuh' : '+ Pilih Kelas' }}
                                </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    <!-- Real-Time Dosen PA Consultation Drawer -->
    <x-consultation-drawer 
        :studentId="$student->id" 
        :lecturerId="$student->academic_advisor_id" 
        contextType="krs" 
        :advisorName="$student->academicAdvisor->user->full_name ?? 'Dosen Pembimbing Akademik'" 
    />
</div>

<!-- Load KRS War Cart Engine Script -->
<script src="/resources/js/modules/krs-war-cart.js"></script>
@endsection
