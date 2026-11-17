@extends('layouts.portal')

@section('title', 'Campus Room Occupancy & Anti-Cheating Exam Seats')

@section('content')
<div class="space-y-6">

    <!-- Header & Hero -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest">
                    Smart Campus Operations
                </span>
                <span class="text-xs text-slate-400">BAAK Central Schedule Control</span>
            </div>
            <h1 class="text-2xl font-bold font-serif text-white flex items-center gap-2">
                <i class="fa-solid fa-building-columns text-uph-gold"></i>
                Peta Okupansi Ruang &amp; Anti-Cheating Seat Generator
            </h1>
            <p class="text-xs text-slate-300">
                Visualisasi real-time ketersediaan ruang kuliah, status praktikum lab, dan generator penomoran kursi ujian acak anti-kecurangan.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('portal.baak.exam-seats.generate') }}" onsubmit="return confirm('Generate nomor kursi ujian acak untuk seluruh mahasiswa aktif yang memenuhi syarat hadir >= 75% dan lunas finansial?')">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-uph-gold to-amber-500 text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-dice text-sm"></i>
                    Generate Nomor Kursi Ujian Acak
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Controls & Filters Bar -->
    <div class="bg-slate-900/80 p-4 rounded-2xl border border-white/10 flex flex-wrap items-center justify-between gap-3 shadow-xl">
        <form method="GET" action="{{ route('portal.baak.rooms-occupancy') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-300">Hari:</label>
                <select name="sim_day" onchange="this.form.submit()" class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="1" {{ $dayOfWeek == 1 ? 'selected' : '' }}>Senin</option>
                    <option value="2" {{ $dayOfWeek == 2 ? 'selected' : '' }}>Selasa</option>
                    <option value="3" {{ $dayOfWeek == 3 ? 'selected' : '' }}>Rabu</option>
                    <option value="4" {{ $dayOfWeek == 4 ? 'selected' : '' }}>Kamis</option>
                    <option value="5" {{ $dayOfWeek == 5 ? 'selected' : '' }}>Jumat</option>
                    <option value="6" {{ $dayOfWeek == 6 ? 'selected' : '' }}>Sabtu</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-300">Jam:</label>
                <input type="text" name="sim_time" value="{{ $currentTime }}" placeholder="HH:MM" class="w-20 bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2 py-1.5 text-center font-mono">
                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-white/10 text-xs font-bold text-white hover:bg-white/20">Cek</button>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-300">Gedung:</label>
                <select name="building" onchange="this.form.submit()" class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Gedung</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b }}" {{ request('building') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-300">Tipe:</label>
                <select name="room_type" onchange="this.form.submit()" class="bg-slate-800 text-xs text-slate-200 border border-white/10 rounded-lg px-2.5 py-1.5 focus:border-uph-gold">
                    <option value="">Semua Tipe Ruang</option>
                    @foreach($roomTypes as $rt)
                        <option value="{{ $rt }}" {{ request('room_type') === $rt ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $rt)) }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1.5 text-rose-400 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span> Sedang Kuliah
            </span>
            <span class="flex items-center gap-1.5 text-amber-400 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Reservasi Lab
            </span>
            <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Kosong
            </span>
        </div>
    </div>

    <!-- Bento Grid Live Rooms -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($occupancyMatrix as $item)
            @php
                $r = $item['room'];
            @endphp
            <div class="bg-slate-900/80 rounded-2xl border border-white/10 p-5 space-y-3 hover:border-uph-gold/40 transition-all shadow-lg flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div>
                            <span class="font-mono text-base font-extrabold text-white tracking-wide">{{ $r->code }}</span>
                            <p class="text-xs text-slate-400">{{ $r->name }} &bull; {{ $r->building }} Lt. {{ $r->floor }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $item['badge_class'] }}">
                            {{ $item['label'] }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-black/40 border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-white truncate">{{ $item['course_name'] }}</span>
                            <span class="font-mono text-uph-gold text-[11px]">{{ $item['course_code'] }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-user-tie text-[10px] text-slate-500"></i>
                            {{ $item['lecturer_name'] }}
                        </p>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-white/5">
                            <span><i class="fa-regular fa-clock mr-1"></i>{{ $item['time_window'] }}</span>
                            @if($item['status'] === 'IN_LECTURE')
                                <span class="text-slate-300 font-semibold">{{ $item['enrolled_count'] }} Mahasiswa</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-white/5">
                    <span><i class="fa-solid fa-chair mr-1"></i>Kapasitas: {{ $r->capacity }} Kursi</span>
                    <span><i class="fa-solid fa-location-dot mr-1"></i>Geofence: {{ $r->geofence_radius_meters }}m</span>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center text-slate-400">
                <i class="fa-solid fa-building-circle-xmark text-3xl mb-2 text-slate-600"></i>
                <p class="font-semibold">Tidak ada ruangan yang cocok dengan filter kriteria.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
