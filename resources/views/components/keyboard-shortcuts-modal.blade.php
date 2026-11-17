<div x-data="{ shortcutsOpen: false }"
     @open-shortcuts.window="shortcutsOpen = true"
     @keydown.window.prevent.slash="if (!$event.target.matches('input, textarea, select')) { shortcutsOpen = true; }"
     x-cloak>

    <!-- Modal Backdrop -->
    <div x-show="shortcutsOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         @click="shortcutsOpen = false"
         @keydown.escape.window="shortcutsOpen = false">

        <!-- Modal Dialog -->
        <div @click.stop 
             class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl border border-surface-border dark:border-slate-800 p-6 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-uph-navy/10 text-uph-navy dark:bg-uph-navy dark:text-uph-gold flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-keyboard"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pintasan Keyboard (Power-User Shortcuts)</h3>
                        <p class="text-[11px] text-slate-400">Navigasi cepat portal tanpa sentuh mouse</p>
                    </div>
                </div>
                <button @click="shortcutsOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Buka Pencarian Global (Command Palette)</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-900 border text-slate-600 dark:text-slate-300 font-mono text-[11px] font-bold shadow-sm">Ctrl + K</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Navigasi ke Dashboard Utama</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-900 border text-slate-600 dark:text-slate-300 font-mono text-[11px] font-bold shadow-sm">Alt + D</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Buka Demo Control Center Switcher</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-900 border text-slate-600 dark:text-slate-300 font-mono text-[11px] font-bold shadow-sm">Alt + S</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Ganti Mode Gelap / Terang (Dark/Light)</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-900 border text-slate-600 dark:text-slate-300 font-mono text-[11px] font-bold shadow-sm">Alt + T</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Buka Panduan Pintasan Ini</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-900 border text-slate-600 dark:text-slate-300 font-mono text-[11px] font-bold shadow-sm">?</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Tutup Modal / Drawer Aktif</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-900 border text-slate-600 dark:text-slate-300 font-mono text-[11px] font-bold shadow-sm">Esc</kbd>
                </div>
            </div>

            <div class="pt-2 text-center text-[11px] text-slate-400">
                Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono">Esc</kbd> untuk menutup
            </div>
        </div>
    </div>
</div>
