@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => 'fa-line-chart',
    'trend' => null,
    'progress' => null,
    'color' => 'navy', // navy, emerald, amber, crimson, gold
])

@php
    $colorClasses = [
        'navy' => 'text-uph-navy dark:text-uph-gold bg-uph-navy/5 dark:bg-uph-navy/20 border-uph-navy/10',
        'emerald' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200/50',
        'amber' => 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border-amber-200/50',
        'crimson' => 'text-uph-crimson dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border-rose-200/50',
        'gold' => 'text-amber-700 dark:text-uph-gold bg-amber-50 dark:bg-amber-950/40 border-amber-300/50',
    ][$color] ?? 'text-uph-navy dark:text-uph-gold bg-uph-navy/5 dark:bg-uph-navy/20 border-uph-navy/10';

    $strokeColor = [
        'navy' => '#00205B',
        'emerald' => '#10B981',
        'amber' => '#F59E0B',
        'crimson' => '#C8102E',
        'gold' => '#D4AF37',
    ][$color] ?? '#00205B';
@endphp

<div class="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 p-5 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
    <div class="flex items-start justify-between">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 block">{{ $title }}</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums tracking-tight">{{ $value }}</span>
            </div>
        </div>

        @if($progress !== null)
            <!-- Circular SVG Progress Ring -->
            <div class="relative h-12 w-12 shrink-0">
                <svg class="h-12 w-12 -rotate-90 transform" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" 
                            stroke="{{ $strokeColor }}" 
                            stroke-width="3.5" 
                            stroke-dasharray="{{ min(100, max(0, $progress)) }}, 100" 
                            stroke-linecap="round" 
                            class="transition-all duration-1000 ease-out" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold font-mono text-slate-700 dark:text-slate-300">
                    {{ round($progress) }}%
                </div>
            </div>
        @else
            <div class="flex h-10 w-10 items-center justify-center rounded-xl border {{ $colorClasses }} text-sm shadow-xs">
                <i class="fa {{ $icon }}"></i>
            </div>
        @endif
    </div>

    @if($subtitle)
        <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold">
            @if($trend === 'up')
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 font-mono">
                    <i class="fa fa-arrow-trend-up text-[9px]"></i>
                    {{ $subtitle }}
                </span>
            @elseif($trend === 'down')
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-400 font-mono">
                    <i class="fa fa-arrow-trend-down text-[9px]"></i>
                    {{ $subtitle }}
                </span>
            @else
                <span class="text-slate-500 dark:text-slate-400 text-[11px] truncate">{{ $subtitle }}</span>
            @endif
        </div>
    @endif
</div>
