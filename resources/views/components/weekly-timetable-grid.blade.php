@props([
    'items' => collect(), // Collection of KrsItem or ClassSection
    'title' => 'Jadwal Kuliah Mingguan (Visual Timetable)',
    'subtitle' => 'Peta alokasi waktu perkuliahan Senin – Sabtu (07:00 – 21:00 WIB)',
])

@php
    $days = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    $timeSlots = [
        '07:00', '08:00', '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00', '17:00', '18:00',
        '19:00', '20:00', '21:00'
    ];

    $colorPalette = [
        'from-blue-600/30 to-indigo-600/20 border-blue-500/40 text-blue-200',
        'from-emerald-600/30 to-teal-600/20 border-emerald-500/40 text-emerald-200',
        'from-amber-600/30 to-orange-600/20 border-amber-500/40 text-amber-200',
        'from-purple-600/30 to-pink-600/20 border-purple-500/40 text-purple-200',
        'from-cyan-600/30 to-sky-600/20 border-cyan-500/40 text-cyan-200',
        'from-rose-600/30 to-red-600/20 border-rose-500/40 text-rose-200',
    ];

    $currentDayOfWeek = (int) date('N'); // 1 = Monday, 7 = Sunday
    $currentTimeStr = date('H:i');
@endphp

<div class="bg-slate-900/80 backdrop-blur-md border border-slate-800 rounded-2xl p-6 shadow-xl overflow-hidden">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-800 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-500/10 text-amber-400 border border-amber-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </span>
                <h3 class="text-lg font-bold text-white tracking-wide">{{ $title }}</h3>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ $subtitle }}</p>
        </div>

        <!-- Legend / Status -->
        <div class="flex items-center gap-4 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Live Now Active</span>
            </span>
            <span class="text-slate-400">Total: <strong class="text-white">{{ $items->count() }}</strong> Sesi Perkuliahan</span>
        </div>
    </div>

    <!-- Timetable Grid -->
    <div class="overflow-x-auto mt-6">
        <div class="min-w-[900px]">
            <!-- Day Columns Header -->
            <div class="grid grid-cols-7 gap-2 pb-3 border-b border-slate-800 text-center font-semibold text-xs text-slate-300">
                <div class="text-slate-500 uppercase tracking-wider py-1">Jam (WIB)</div>
                @foreach ($days as $dayNum => $dayName)
                    <div class="py-1 px-2 rounded-lg {{ $currentDayOfWeek === $dayNum ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold' : 'bg-slate-800/40 text-slate-300' }}">
                        {{ $dayName }}
                        @if ($currentDayOfWeek === $dayNum)
                            <span class="block text-[10px] font-normal text-amber-300/80">Hari Ini</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Schedule Body -->
            <div class="divide-y divide-slate-800/40 mt-2">
                @foreach ($timeSlots as $slot)
                    <div class="grid grid-cols-7 gap-2 py-3 min-h-[72px] items-start">
                        <!-- Hour Marker -->
                        <div class="text-xs font-mono text-slate-500 pt-1 text-center font-medium">
                            {{ $slot }}
                        </div>

                        <!-- 6 Days Columns -->
                        @foreach ($days as $dayNum => $dayName)
                            <div class="min-h-[64px] rounded-xl bg-slate-950/30 border border-slate-800/30 p-1 flex flex-col gap-1 transition-all hover:border-slate-700/50">
                                @php
                                    $matchingClasses = $items->filter(function ($item) use ($dayNum, $slot) {
                                        $section = $item instanceof \App\Models\KrsItem ? $item->classSection : $item;
                                        if (!$section) return false;

                                        $secDay = (int) $section->day_of_week;
                                        $startTime = substr($section->start_time, 0, 5);
                                        $endTime = substr($section->end_time, 0, 5);

                                        return ($secDay === $dayNum) && ($slot >= $startTime && $slot < $endTime);
                                    });
                                @endphp

                                @foreach ($matchingClasses as $classItem)
                                    @php
                                        $section = $classItem instanceof \App\Models\KrsItem ? $classItem->classSection : $classItem;
                                        $course = $section->course ?? null;
                                        $startTime = substr($section->start_time, 0, 5);
                                        $endTime = substr($section->end_time, 0, 5);

                                        $isLiveNow = ($currentDayOfWeek === $dayNum) && ($currentTimeStr >= $startTime && $currentTimeStr <= $endTime);
                                        $color = $colorPalette[abs(crc32($course->code ?? 'UPH')) % count($colorPalette)];
                                    @endphp

                                    <div class="rounded-lg p-2 bg-gradient-to-br {{ $color }} border shadow-md relative group transition-transform hover:scale-[1.02]">
                                        @if ($isLiveNow)
                                            <div class="absolute -top-1.5 -right-1 px-1.5 py-0.5 rounded-full bg-emerald-500 text-slate-950 font-black text-[9px] uppercase tracking-wider flex items-center gap-1 shadow-lg">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                                LIVE NOW
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="truncate">{{ $course->code ?? 'N/A' }}</span>
                                            <span class="text-[10px] font-mono opacity-80">{{ $startTime }}–{{ $endTime }}</span>
                                        </div>

                                        <div class="text-[11px] font-medium text-white truncate mt-0.5" title="{{ $course->name ?? '' }}">
                                            {{ $course->name ?? 'Mata Kuliah' }}
                                        </div>

                                        <div class="flex items-center justify-between text-[10px] text-slate-300/80 mt-1 pt-1 border-t border-white/10">
                                            <span class="truncate flex items-center gap-1">
                                                <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                {{ $section->room->name ?? 'R. Kelas' }}
                                            </span>
                                            <span class="truncate font-semibold text-amber-300">
                                                {{ $section->section_code ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
