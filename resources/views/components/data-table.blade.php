@props(['headers' => []])

<div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-[0_2px_8px_-2px_rgba(0,32,91,0.06)] ring-1 ring-slate-900/5 dark:ring-white/10">
    <div class="overflow-x-auto max-h-[600px]">
        <table class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-800/80 text-left text-xs">
            <thead class="sticky top-0 z-10 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-xs text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
