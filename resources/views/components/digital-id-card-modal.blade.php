@props([
    'user' => auth()->user(),
    'student' => auth()->user()?->student,
    'lecturer' => auth()->user()?->lecturer,
])

<div x-data="{ openKtm: false, flipped: false }" @open-ktm.window="openKtm = true" class="inline-block">
    <!-- Trigger Button -->
    <button type="button" 
            @click="openKtm = true"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all hover:scale-105 active:scale-95">
        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
        </svg>
        <span>{{ $student ? 'KTM Digital UPH' : 'Kartu NIDN Digital' }}</span>
    </button>

    <!-- Modal Overlay -->
    <div x-show="openKtm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">

        <div @click.away="openKtm = false" 
             class="relative w-full max-w-lg bg-slate-900 border border-slate-700/80 rounded-3xl p-6 shadow-2xl overflow-hidden">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <h3 class="font-bold text-sm text-white tracking-wide uppercase">
                        {{ $student ? 'Kartu Tanda Mahasiswa (KTM) Digital' : 'Kartu Dosen & Peneliti (NIDN) Digital' }}
                    </h3>
                </div>
                <button @click="openKtm = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Card Body Container with 3D Flip -->
            <div class="mt-6 flex flex-col items-center">
                <!-- Digital Card (Front) -->
                <div class="w-full aspect-[85.6/53.98] rounded-2xl bg-gradient-to-br from-[#0A192F] via-[#102A45] to-[#1E3A8A] border-2 border-amber-400/40 shadow-2xl p-6 relative overflow-hidden flex flex-col justify-between text-white">
                    <!-- Metallic Foil Watermark -->
                    <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-gradient-to-br from-amber-400/10 to-transparent pointer-events-none blur-xl"></div>
                    <div class="absolute top-0 right-0 left-0 h-1 bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>

                    <!-- Top Bar of Card -->
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-400/20 border border-amber-400/40 flex items-center justify-center font-black text-amber-300 text-lg shadow-inner">
                                UPH
                            </div>
                            <div>
                                <h4 class="font-black text-sm tracking-widest text-amber-300 uppercase">UNIVERSITAS PELITA HARAPAN</h4>
                                <p class="text-[10px] text-slate-300 tracking-wider">LIPPO VILLAGE CAMPUS • TANGERANG</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/40">
                            {{ $student ? 'STUDENT' : 'FACULTY' }}
                        </span>
                    </div>

                    <!-- Middle: Photo & Details -->
                    <div class="flex items-center gap-5 my-auto relative z-10">
                        <div class="relative">
                            <div class="w-20 h-24 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 border-2 border-amber-400/60 p-0.5 shadow-xl flex items-center justify-center overflow-hidden">
                                <div class="w-full h-full bg-slate-800 flex flex-col items-center justify-center text-slate-400 text-xs font-bold">
                                    <svg class="w-8 h-8 text-amber-400/80 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    UPH PHOTO
                                </div>
                            </div>
                            <!-- Hologram Chip -->
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 rounded-md bg-gradient-to-tr from-amber-300 via-amber-100 to-yellow-500 border border-amber-600 shadow-md flex items-center justify-center">
                                <span class="text-[7px] font-black text-slate-900">UPH</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <h2 class="text-base font-extrabold text-white tracking-wide">
                                {{ $user->full_name }}
                            </h2>
                            <p class="text-xs font-mono font-bold text-amber-400 tracking-wider">
                                {{ $student ? 'NIM: ' . $student->nim : 'NIDN: ' . ($lecturer->nidn ?? '0314058201') }}
                            </p>
                            <p class="text-xs text-slate-200 font-medium">
                                {{ $student?->studyProgram?->name ?? $lecturer?->studyProgram?->name ?? 'Teknik Informatika' }}
                            </p>
                            <p class="text-[10px] text-slate-400">
                                {{ $student?->studyProgram?->faculty?->name ?? 'Fakultas Ilmu Komputer' }}
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Bar: Barcode & Security Validity -->
                    <div class="flex items-end justify-between pt-2 border-t border-white/10 text-[10px] relative z-10">
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Status Akademik</span>
                            <span class="font-bold text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                AKTIF • SEMESTER {{ $student?->current_semester ?? 'GENAP' }}
                            </span>
                        </div>

                        <!-- Barcode Simulation -->
                        <div class="flex flex-col items-end">
                            <div class="flex items-center gap-0.5 h-6 px-2 py-0.5 bg-white/90 rounded text-slate-950 font-mono tracking-widest text-[9px]">
                                ||| | | |||| || | || |||
                            </div>
                            <span class="text-[8px] font-mono text-slate-400 mt-0.5">VALID THROUGH 2028</span>
                        </div>
                    </div>
                </div>

                <!-- Action Controls below Card -->
                <div class="flex items-center justify-between w-full mt-6 pt-4 border-t border-slate-800">
                    <p class="text-xs text-slate-400">
                        Tunjukkan kartu digital ini untuk presensi perpustakaan & fasilitas kampus.
                    </p>
                    <button type="button" 
                            onclick="window.print()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak KTM</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
