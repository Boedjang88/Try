@extends('layouts.portal')

@section('content')
<div id="qr-student-app" class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center space-y-2">
        <span class="px-3 py-1 rounded-full bg-uph-gold/20 text-uph-navy font-bold text-xs uppercase tracking-wider">
            Presensi Kuliah Terintegrasi
        </span>
        <h1 class="text-2xl font-display font-black text-uph-navy">Pindai Presensi QR Code</h1>
        <p class="text-xs text-slate-500">
            Validasi kehadiran perkuliahan dengan proteksi token 15-detik dan verifikasi Geofence GPS UPH.
        </p>
    </div>

    <!-- GPS Geolocation Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-satellite-dish text-uph-navy"></i>
                <h3 class="font-display font-bold text-xs uppercase tracking-wider text-slate-800">Status Koordinat GPS Anda</h3>
            </div>
            <span id="student-gps-status" class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">
                Mencari Sinyal GPS...
            </span>
        </div>

        <div class="grid grid-cols-3 gap-3 bg-surface-slate p-3 rounded-xl border border-slate-100 text-center text-xs">
            <div>
                <span class="text-[10px] text-slate-400 block font-bold">LATITUDE</span>
                <span id="student-lat-display" class="font-mono font-bold text-slate-800 text-xs">-</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block font-bold">LONGITUDE</span>
                <span id="student-lon-display" class="font-mono font-bold text-slate-800 text-xs">-</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block font-bold">AKURASI</span>
                <span id="student-accuracy-display" class="font-mono font-bold text-uph-navy text-xs">-</span>
            </div>
        </div>

        <p class="text-[11px] text-slate-400">
            *Pastikan GPS perangkat Anda dalam mode High Accuracy dan Anda berada di dalam radius 50 meter dari ruang kelas.
        </p>
    </div>

    <!-- Scan Input Panel -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div>
            <label for="student-token-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Token QR Code Presensi (15-Detik) <span class="text-uph-crimson">*</span>
            </label>
            <div class="relative">
                <i class="fa-solid fa-qrcode absolute left-4 top-3.5 text-slate-400 text-lg"></i>
                <input type="text" 
                       id="student-token-input" 
                       placeholder="Salin atau ketik token QR dari layar dosen..." 
                       class="w-full h-12 pl-12 pr-4 text-xs font-mono font-bold border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-uph-navy">
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">
                Token diperbarui secara otomatis setiap 15 detik pada layar dosen pengampu.
            </p>
        </div>

        <button type="button" 
                id="student-verify-btn" 
                disabled
                class="w-full py-4 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover disabled:bg-slate-300 text-white font-display font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-location-arrow"></i> Verifikasi Presensi & Geofence
        </button>

        <!-- Dynamic Result Box -->
        <div id="student-scan-result" class="hidden"></div>
    </div>

    <!-- Today's Schedule Reference -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-xs uppercase tracking-wider text-slate-600 border-b pb-2">
            Jadwal Kuliah Anda Hari Ini
        </h3>
        
        <div class="space-y-3">
            @forelse($activeSessions as $ses)
            <div class="p-4 rounded-xl bg-surface-slate border border-slate-200 flex items-center justify-between text-xs">
                <div>
                    <span class="font-mono font-bold text-uph-navy">{{ $ses->classSection->course->code }}</span>
                    <h4 class="font-bold text-slate-900 mt-0.5">{{ $ses->classSection->course->name_id }}</h4>
                    <span class="text-[11px] text-slate-500">
                        {{ substr($ses->classSection->start_time, 0, 5) }} - {{ substr($ses->classSection->end_time, 0, 5) }} WIB &bull; Ruang: {{ $ses->room->code ?? ($ses->classSection->room->code ?? 'TBA') }}
                    </span>
                </div>
                <span class="px-2.5 py-1 rounded bg-uph-navy/10 text-uph-navy font-bold text-[10px] uppercase">
                    Pertemuan #{{ $ses->session_number }}
                </span>
            </div>
            @empty
            <div class="py-6 text-center text-slate-400 text-xs">
                Tidak ada jadwal perkuliahan terjadwal untuk hari ini.
            </div>
            @endforelse
        </div>
    </div>
</div>

<script src="/resources/js/modules/qr-attendance.js"></script>
@endsection
