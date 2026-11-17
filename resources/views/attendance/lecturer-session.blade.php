@extends('layouts.portal')

@section('content')
<div id="qr-lecturer-app" 
     data-session-id="{{ $session->id }}" 
     class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded bg-uph-navy text-uph-gold text-[10px] font-bold font-mono">
                    {{ $session->classSection->course->code }} &bull; Kelas {{ $session->classSection->section_code }}
                </span>
                <span class="text-xs text-slate-400">&bull; Sesi Pertemuan {{ $session->session_number }} dari 16</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">{{ $session->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Ruang: <strong class="text-slate-800">{{ $session->room->code ?? ($session->classSection->room->code ?? 'D-501') }}</strong> ({{ $session->room->building ?? 'Gedung D' }}) &bull; 
                Jadwal: <span class="font-bold text-slate-800">{{ $session->classSection->day_name }}, {{ substr($session->classSection->start_time, 0, 5) }} - {{ substr($session->classSection->end_time, 0, 5) }} WIB</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($session->lecturer_signed_at)
            <span class="px-3.5 py-2 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                <i class="fa-solid fa-lock mr-1.5"></i> BAP Telah Disahkan (Presensi Terkunci)
            </span>
            @else
            <span class="px-3.5 py-2 rounded-xl bg-uph-navy/10 text-uph-navy text-xs font-bold flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                Presensi QR Aktif (15 Detik)
            </span>
            @endif
        </div>
    </div>

    <!-- Main QR & Live Roster Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Rotating 15-Second QR Token Card -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col items-center justify-between text-center space-y-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-uph-gold">Dynamic Cryptographic QR Code</span>
                <h3 class="text-lg font-display font-bold text-uph-navy mt-1">Pindai Presensi Kehadiran</h3>
                <p class="text-xs text-slate-500 mt-1">Tampilkan layar ini kepada mahasiswa di dalam ruang kelas.</p>
            </div>

            <!-- Visual QR Canvas Container -->
            <div id="qr-code-canvas" class="p-4 bg-white rounded-2xl shadow-inner border border-slate-100 min-h-[280px] flex items-center justify-center">
                <i class="fa-solid fa-spinner fa-spin text-3xl text-uph-navy"></i>
            </div>

            <!-- 15-Second Countdown Radial/Bar -->
            <div class="w-full space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-semibold">Regenerasi Token QR Berikutnya:</span>
                    <span id="qr-countdown-text" class="font-mono font-black text-uph-navy text-sm">15s</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-200 overflow-hidden">
                    <div id="qr-countdown-bar" class="h-full bg-uph-gold transition-all duration-300" style="width: 100%;"></div>
                </div>
                <p class="text-[10px] text-slate-400 font-mono truncate max-w-xs mx-auto" id="qr-token-string"></p>
            </div>

            <!-- Geofence Security Reminder -->
            <div class="p-3 rounded-xl bg-surface-slate border border-slate-200 text-left text-xs text-slate-600 space-y-1 w-full">
                <div class="flex items-center gap-2 font-bold text-uph-navy text-xs">
                    <i class="fa-solid fa-location-crosshairs text-uph-gold"></i>
                    <span>Geofence Boundary Aktif (Radius 50m)</span>
                </div>
                <p class="text-[11px] text-slate-500">
                    Sistem otomatis menolak presensi jika koordinat GPS mahasiswa berada di luar jangkauan ruang kuliah UPH.
                </p>
            </div>
        </div>

        <!-- Right: Live Attendance Roster Poller -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-display font-bold text-sm text-uph-navy uppercase tracking-wider">Daftar Kehadiran Real-time</h3>
                    <p class="text-xs text-slate-400">Memperbarui otomatis setiap 4 detik tanpa reload halaman.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Hadir:</span>
                    <span id="live-present-count" class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-mono font-black text-sm">
                        {{ $session->attendances->where('attendance_status', 'present')->count() }}
                    </span>
                    <span class="text-xs text-slate-400">/ {{ $enrolledStudents->count() }} Mahasiswa</span>
                </div>
            </div>

            <!-- Live List Container -->
            <div id="live-roster-list" class="flex-1 max-h-[380px] overflow-y-auto space-y-2 pr-1">
                @forelse($session->attendances as $att)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="h-2 w-2 rounded-full {{ $att->attendance_status === 'present' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <div>
                            <span class="font-bold text-slate-800">{{ $att->student->user->full_name }}</span>
                            <span class="font-mono text-[11px] text-slate-400 block">{{ $att->student->nim }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 rounded {{ $att->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }} font-bold text-[10px] uppercase">
                            {{ $att->attendance_status }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono block mt-0.5">
                            {{ $att->scanned_at ? $att->scanned_at->format('H:i:s') : '-' }} WIB
                        </span>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    Belum ada mahasiswa yang memindai QR Code.
                </div>
                @endforelse
            </div>

            <!-- Digital BAP Signing Form -->
            <div class="pt-4 border-t border-slate-200">
                <form method="POST" action="/attendance/session/{{ $session->id }}/bap" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Berita Acara Perkuliahan (BAP) & Ringkasan Materi <span class="text-uph-crimson">*</span>
                        </label>
                        <textarea name="bap_summary" required rows="2" placeholder="Tuliskan pokok bahasan, capaian pembelajaran, dan catatan perkuliahan hari ini..." class="w-full p-2.5 text-xs border rounded-lg focus:ring-1 focus:ring-uph-navy" {{ $session->lecturer_signed_at ? 'disabled' : '' }}>{{ $session->bap_summary }}</textarea>
                    </div>

                    <input type="hidden" name="actual_date" value="{{ date('Y-m-d') }}">

                    <div class="flex justify-between items-center pt-1">
                        <span class="text-[11px] text-slate-400">
                            {{ $session->lecturer_signed_at ? 'Disahkan pada ' . $session->lecturer_signed_at->format('d/m/Y H:i') . ' WIB' : 'Pengesahan BAP akan otomatis menandai mahasiswa yang belum scan sebagai Absen.' }}
                        </span>

                        @if(!$session->lecturer_signed_at)
                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-signature"></i> Sahkan BAP & Kunci Presensi
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/resources/js/modules/qr-attendance.js"></script>
@endsection
