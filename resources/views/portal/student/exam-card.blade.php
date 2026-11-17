@extends('layouts.portal')

@section('title', 'Kartu Ujian Resmi Mahasiswa (UTS/UAS)')

@section('content')
<div class="space-y-6">

    <!-- Header & Hero -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 uppercase tracking-widest">
                    Dokumen Ujian Resmi
                </span>
                <span class="text-xs text-slate-400">Anti-Cheating Randomized Seating</span>
            </div>
            <h1 class="text-2xl font-bold font-serif text-white flex items-center gap-2">
                <i class="fa-solid fa-id-card-clip text-uph-gold"></i>
                Kartu Ujian Semester &amp; Alokasi Kursi
            </h1>
            <p class="text-xs text-slate-300">
                Kartu peserta resmi Ujian Tengah Semester (UTS) dan Ujian Akhir Semester (UAS) dengan nomor kursi acak anti-kecurangan.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.print.krs') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-uph-gold text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg flex items-center gap-2 transition-all">
                <i class="fa-solid fa-print"></i>
                Cetak Format A4 Resmi
            </a>
        </div>
    </div>

    <!-- Student Exam Clearance Profile Card -->
    <div class="bg-slate-900/80 p-6 rounded-2xl border border-white/10 shadow-xl grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Nama Mahasiswa</span>
            <p class="text-base font-bold text-white mt-0.5">{{ $student->user->full_name ?? 'Mahasiswa UPH' }}</p>
            <p class="text-xs font-mono text-uph-gold">NIM: {{ $student->nim }}</p>
        </div>

        <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Program Studi &amp; Fakultas</span>
            <p class="text-xs font-bold text-white mt-0.5">{{ $student->studyProgram->name_id ?? 'Informatika' }}</p>
            <p class="text-xs text-slate-400">{{ $student->studyProgram->faculty->name_id ?? 'Fakultas Ilmu Komputer' }}</p>
        </div>

        <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Periode Akademik</span>
            <p class="text-xs font-bold text-white mt-0.5">{{ $activePeriod->name ?? 'Semester Ganjil 2025/2026' }}</p>
            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                SEMESTER AKTIF
            </span>
        </div>

        <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Status Kliring Finansial</span>
            <div class="mt-1">
                @if($student->is_financially_cleared)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i>
                        Lunas (Eligible Ujian)
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-ban"></i>
                        Cekal Finansial (Hold)
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Exam Schedule & Anti-Cheating Allocated Seats Table -->
    <div class="bg-slate-900/80 rounded-2xl border border-white/10 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-white/10 flex items-center justify-between bg-white/[0.02]">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-chair text-uph-gold"></i>
                Jadwal Ujian &amp; Alokasi Nomor Kursi Acak
            </h2>
            <span class="text-xs text-slate-400">Total: {{ $krsSubmission ? $krsSubmission->items->count() : 0 }} Matakuliah</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-white/5 uppercase tracking-wider text-[10px] text-slate-400 font-bold border-b border-white/10">
                    <tr>
                        <th class="py-3 px-4">No.</th>
                        <th class="py-3 px-4">Kode &amp; Matakuliah</th>
                        <th class="py-3 px-4">Seksi &amp; SKS</th>
                        <th class="py-3 px-4">Ruang Ujian</th>
                        <th class="py-3 px-4">Syarat Kehadiran (75%)</th>
                        <th class="py-3 px-4 text-center">Nomor Kursi Ujian Acak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @if($krsSubmission)
                        @forelse($krsSubmission->items as $idx => $item)
                            @php
                                $sec = $item->classSection;
                                $attPct = (float)($item->attendance_percentage ?? 100.0);
                                $isBlocked = $item->is_exam_blocked;
                                $seat = $item->exam_seat_number ?: 'BELUM DI-GENERATE';
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-4 font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-mono text-uph-gold font-bold">{{ $sec->course->code ?? 'N/A' }}</span>
                                    <p class="font-bold text-white text-xs">{{ $sec->course->name_id ?? 'N/A' }}</p>
                                    <span class="text-[10px] text-slate-400">{{ $sec->lecturer->user->full_name ?? 'Dosen' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono">{{ $sec->section_code ?? '01' }}</span> &bull; 
                                    <span class="font-bold text-slate-200">{{ $sec->course->sks ?? 3 }} SKS</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-white">R. {{ $sec->room->code ?? 'UPH' }}</span>
                                    <p class="text-[10px] text-slate-400">{{ $sec->room->building ?? 'Gedung' }} Lt. {{ $sec->room->floor ?? 1 }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    @if($isBlocked)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            CEKAL ({{ number_format($attPct, 0) }}% < 75%)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            LOLOS ({{ number_format($attPct, 0) }}%)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(str_contains($seat, 'BLOCKED'))
                                        <span class="px-3 py-1.5 rounded-xl font-mono text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">
                                            {{ $seat }}
                                        </span>
                                    @elseif($seat === 'BELUM DI-GENERATE')
                                        <span class="text-[11px] text-slate-500 italic">
                                            Menunggu Rilis BAAK
                                        </span>
                                    @else
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gradient-to-r from-uph-gold/20 to-amber-500/20 border-2 border-uph-gold text-uph-gold font-mono font-black text-sm shadow-md">
                                            <i class="fa-solid fa-chair text-xs"></i>
                                            <span>{{ $seat }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <p>Tidak ada matakuliah terdaftar pada KRS periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p>KRS belum diajukan untuk periode aktif ini.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
