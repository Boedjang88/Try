@extends('layouts.portal')

@section('title', 'Yudisium & Kelulusan - UPH')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    Biro Administrasi Akademik (BAAK) & SPMI
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Universitas Pelita Harapan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Verifikasi Yudisium & Kelulusan Sarjana</h1>
            <p class="text-xs text-slate-500 mt-1">
                Verifikasi 4-departemen bebas pustaka, bebas keuangan, repositori skripsi, kelayakan akademik, dan penerbitan PIN Ijazah Nasional.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                <i class="fa-solid fa-graduation-cap text-uph-crimson"></i>
                Periode {{ $activePeriod->name ?? 'Semester Aktif' }}
            </span>
        </div>
    </div>

    <!-- STUDENT VIEW -->
    @if(Auth::user()->role === 'student' && $myClearance)
        <!-- Graduation / PIN Banner if Fully Cleared -->
        @if($myClearance->isFullyCleared())
        <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-uph-navy rounded-2xl p-6 sm:p-8 text-white shadow-uph-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-10 pointer-events-none">
                <i class="fa-solid fa-award text-9xl"></i>
            </div>
            <div class="relative z-10 max-w-2xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold">
                    <i class="fa-solid fa-circle-check"></i> RESMI DINYATAKAN LULUS YUDISIUM
                </div>
                <h2 class="text-2xl sm:text-3xl font-display font-black tracking-tight">
                    Selamat, {{ $student->user->full_name }}!
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Seluruh persyaratan akademik, keuangan, dan kepustakaan Anda telah lengkap dan disahkan oleh Senat Universitas. Penomoran Ijazah Nasional (PIN) telah resmi diterbitkan oleh Kementerian.
                </p>

                <div class="pt-2 flex flex-wrap items-center gap-4 text-xs font-mono">
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">PIN Ijazah Nasional:</span>
                        <strong class="text-uph-gold text-sm">{{ $myClearance->national_pin_number }}</strong>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Kelulusan:</span>
                        <strong class="text-white text-sm">{{ $myClearance->graduation_date->translatedFormat('d F Y') }}</strong>
                    </div>
                </div>

                <div class="pt-4 flex items-center gap-3">
                    <a href="/yudisium/skpi/{{ $myClearance->id }}" target="_blank"
                       class="px-5 py-2.5 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-bold text-xs uppercase tracking-wider shadow-md transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-file-contract"></i> Buka SKPI Digital (Bilingual)
                    </a>
                    <a href="/khs/transcript" target="_blank"
                       class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors flex items-center gap-2 border border-white/20">
                        <i class="fa-solid fa-scroll"></i> Transkrip Final
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- 4-Checkpoint Connected Horizontal Stepper -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Kelulusan & Verifikasi Yudisium</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                    {{ $myClearance->isFullyCleared() ? '4 dari 4 Departemen Tuntas' : 'Verifikasi Berjalan' }}
                </span>
            </div>

            <!-- Connected Horizontal Stepper Nodes -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 relative">
                <!-- Node 1: Keuangan -->
                <div class="flex items-center gap-3 p-3 rounded-xl border {{ $myClearance->finance_cleared ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $myClearance->finance_cleared ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        <i class="fa-solid {{ $myClearance->finance_cleared ? 'fa-check' : 'fa-receipt' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-xs truncate">1. Keuangan</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ $myClearance->finance_cleared ? 'CLEARED' : 'PENDING' }}</p>
                    </div>
                </div>

                <!-- Node 2: Perpustakaan -->
                <div class="flex items-center gap-3 p-3 rounded-xl border {{ $myClearance->library_cleared ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $myClearance->library_cleared ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        <i class="fa-solid {{ $myClearance->library_cleared ? 'fa-check' : 'fa-book-bookmark' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-xs truncate">2. Pustaka</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ $myClearance->library_cleared ? 'CLEARED' : 'PENDING' }}</p>
                    </div>
                </div>

                <!-- Node 3: Repository -->
                <div class="flex items-center gap-3 p-3 rounded-xl border {{ $myClearance->repository_cleared ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $myClearance->repository_cleared ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        <i class="fa-solid {{ $myClearance->repository_cleared ? 'fa-check' : 'fa-cloud-arrow-up' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-xs truncate">3. Repository</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ $myClearance->repository_cleared ? 'CLEARED' : 'PENDING' }}</p>
                    </div>
                </div>

                <!-- Node 4: Fakultas -->
                <div class="flex items-center gap-3 p-3 rounded-xl border {{ $myClearance->faculty_cleared ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $myClearance->faculty_cleared ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        <i class="fa-solid {{ $myClearance->faculty_cleared ? 'fa-check' : 'fa-user-check' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-xs truncate">4. Fakultas</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ $myClearance->faculty_cleared ? 'CLEARED' : 'PENDING' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4-Way Clearance Checklist Grid -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10 p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Lembar Kendali Verifikasi 4-Departemen (Yudisium Checklist)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Seluruh 4 verifikasi wajib berstatus lengkap (centang hijau) agar dapat mengikuti prosesi wisuda sarjana.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 1. Finance Clearance -->
                <div class="p-5 rounded-2xl border transition-all {{ $myClearance->finance_cleared ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $myClearance->finance_cleared ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="fa-solid {{ $myClearance->finance_cleared ? 'fa-check' : 'fa-receipt' }}"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">1. Bebas Keuangan (BAUK)</h4>
                                <span class="text-[10px] text-slate-400">Verifikasi Biro Administrasi Keuangan</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono uppercase {{ $myClearance->finance_cleared ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $myClearance->finance_cleared ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $myClearance->finance_cleared ? 'CLEARED' : 'PENDING' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Memastikan tidak ada saldo tertunggak pada BPP Pokok, BPP SKS, dan denda keterlambatan buku/fasilitas.
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800/60 flex justify-between items-center text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">Status Buku Besar:</span>
                        <strong class="{{ $myClearance->finance_cleared ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $myClearance->finance_cleared ? 'Lunas 100%' : 'Ada Tagihan Belum Lunas' }}
                        </strong>
                    </div>
                </div>

                <!-- 2. Library Clearance -->
                <div class="p-5 rounded-2xl border transition-all {{ $myClearance->library_cleared ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $myClearance->library_cleared ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="fa-solid {{ $myClearance->library_cleared ? 'fa-check' : 'fa-book-bookmark' }}"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">2. Bebas Pustaka (Perpustakaan)</h4>
                                <span class="text-[10px] text-slate-400">UPH Library & Learning Commons</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono uppercase {{ $myClearance->library_cleared ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $myClearance->library_cleared ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $myClearance->library_cleared ? 'CLEARED' : 'PENDING' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Pengembalian seluruh buku pinjaman fisik dan penyerahan 1 eksemplar hardcopy skripsi bersampul tebal (hardcover).
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800/60 flex justify-between items-center text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">Surat Bebas Pustaka:</span>
                        <strong class="{{ $myClearance->library_cleared ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                            {{ $myClearance->library_cleared ? 'Terverifikasi Staf Perpus' : 'Menunggu Penyerahan Buku' }}
                        </strong>
                    </div>
                </div>

                <!-- 3. Repository Clearance -->
                <div class="p-5 rounded-2xl border transition-all {{ $myClearance->repository_cleared ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $myClearance->repository_cleared ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="fa-solid {{ $myClearance->repository_cleared ? 'fa-check' : 'fa-cloud-arrow-up' }}"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">3. Repositori Digital & Publikasi</h4>
                                <span class="text-[10px] text-slate-400">Lembaga Riset & Publikasi Ilmiah</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono uppercase {{ $myClearance->repository_cleared ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $myClearance->repository_cleared ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $myClearance->repository_cleared ? 'CLEARED' : 'PENDING' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Pengunggahan file digital lengkap (Bab 1 s.d. 5, ringkasan eksekutif, naskah publikasi jurnal, dan kode sumber/aplikasi).
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800/60 flex justify-between items-center text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">Unggah Naskah Akhir:</span>
                        <strong class="{{ $myClearance->repository_cleared ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                            {{ $myClearance->repository_cleared ? 'Selesai & Disetujui' : 'Menunggu Review Berkas' }}
                        </strong>
                    </div>
                </div>

                <!-- 4. Faculty Academic Clearance -->
                <div class="p-5 rounded-2xl border transition-all {{ $myClearance->faculty_cleared ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $myClearance->faculty_cleared ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="fa-solid {{ $myClearance->faculty_cleared ? 'fa-check' : 'fa-user-check' }}"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">4. Verifikasi Akademik Fakultas</h4>
                                <span class="text-[10px] text-slate-400">Program Studi & Dekanat</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono uppercase {{ $myClearance->faculty_cleared ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $myClearance->faculty_cleared ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $myClearance->faculty_cleared ? 'CLEARED' : 'PENDING' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Verifikasi pemenuhan beban 144 SKS, tidak ada nilai D/E pada kurikulum inti, bebas pelanggaran kode etik, dan IPK >= 2.00.
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800/60 flex justify-between items-center text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">Validasi Dekanat:</span>
                        <strong class="{{ $myClearance->faculty_cleared ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                            {{ $myClearance->faculty_cleared ? 'Disahkan Kaprodi' : 'Sedang Ditinjau' }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

    @else
    <!-- STAFF / KAPRODI / DEAN / BAAK PERSPECTIVE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Mahasiswa Calon Yudisium & Wisuda</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola verifikasi 4-departemen untuk menerbitkan Penomoran Ijazah Nasional (PIN).</p>
            </div>
        </div>

        @if($clearanceList->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Tidak ada pengajuan yudisium</h3>
            <p class="text-xs text-slate-400 mt-1">Belum ada mahasiswa yang mengajukan proses kelulusan pada periode akademik ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-6">Mahasiswa & NIM</th>
                        <th class="py-4 px-4">Program Studi</th>
                        <th class="py-4 px-4 text-center">Keuangan</th>
                        <th class="py-4 px-4 text-center">Pustaka</th>
                        <th class="py-4 px-4 text-center">Repository</th>
                        <th class="py-4 px-4 text-center">Fakultas</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($clearanceList as $cl)
                    <tr class="hover:bg-slate-50/75 transition-colors">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block text-xs">{{ $cl->student->user->full_name }}</span>
                            <span class="font-mono text-slate-500 text-[11px]">NIM: {{ $cl->student->nim }}</span>
                        </td>
                        <td class="py-4 px-4 font-bold text-slate-700">
                            {{ $cl->student->studyProgram->name_id }}
                        </td>

                        <!-- Checkpoint Buttons -->
                        <td class="py-4 px-4 text-center">
                            <form method="POST" action="/yudisium/{{ $cl->id }}/verify" class="inline">
                                @csrf
                                <input type="hidden" name="department" value="finance">
                                <input type="hidden" name="cleared" value="{{ $cl->finance_cleared ? '0' : '1' }}">
                                <button type="submit" class="p-1.5 rounded-lg border text-xs {{ $cl->finance_cleared ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-400 border-slate-300 hover:text-slate-700' }}" title="Toggle Finance Clearance">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <form method="POST" action="/yudisium/{{ $cl->id }}/verify" class="inline">
                                @csrf
                                <input type="hidden" name="department" value="library">
                                <input type="hidden" name="cleared" value="{{ $cl->library_cleared ? '0' : '1' }}">
                                <button type="submit" class="p-1.5 rounded-lg border text-xs {{ $cl->library_cleared ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-400 border-slate-300 hover:text-slate-700' }}" title="Toggle Library Clearance">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <form method="POST" action="/yudisium/{{ $cl->id }}/verify" class="inline">
                                @csrf
                                <input type="hidden" name="department" value="repository">
                                <input type="hidden" name="cleared" value="{{ $cl->repository_cleared ? '0' : '1' }}">
                                <button type="submit" class="p-1.5 rounded-lg border text-xs {{ $cl->repository_cleared ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-400 border-slate-300 hover:text-slate-700' }}" title="Toggle Repository Clearance">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <form method="POST" action="/yudisium/{{ $cl->id }}/verify" class="inline">
                                @csrf
                                <input type="hidden" name="department" value="faculty">
                                <input type="hidden" name="cleared" value="{{ $cl->faculty_cleared ? '0' : '1' }}">
                                <button type="submit" class="p-1.5 rounded-lg border text-xs {{ $cl->faculty_cleared ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-400 border-slate-300 hover:text-slate-700' }}" title="Toggle Faculty Clearance">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        </td>

                        <td class="py-4 px-4 text-center">
                            @if($cl->status === 'approved')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    LULUS
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    PROSES
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-6 text-right">
                            @if($cl->status === 'approved')
                                <a href="/yudisium/skpi/{{ $cl->id }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs transition-colors">
                                    <i class="fa-solid fa-file-contract"></i> SKPI
                                </a>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
