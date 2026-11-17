@extends('layouts.portal')

@section('title', 'Dasbor PMB & Status Seleksi - UPH')

@section('content')
<div class="space-y-6">

    <!-- Header Section with UPH Midnight Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-uph-navy via-[#0A192F] to-[#060E1E] dark:from-[#060E1E] dark:to-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 text-white shadow-uph ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/10 border-2 border-uph-gold/80 flex items-center justify-center text-uph-gold text-2xl sm:text-3xl font-serif font-black shadow-lg shrink-0">
                    PMB
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-uph-gold/20 text-uph-gold border border-uph-gold/30 font-mono">
                            Penerimaan Mahasiswa Baru 2026/2027
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-xs text-slate-300 font-mono font-bold tabular-nums">No. Reg: {{ $applicant->registration_number ?? 'REG202600001' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-black text-white tracking-tight">{{ $user->full_name }}</h1>
                    <p class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-graduation-cap text-uph-gold mr-1.5"></i> Prodi: {{ $applicant->studyProgram->name_id ?? 'Informatika' }}</span>
                        <span><i class="fa-solid fa-calendar-check text-slate-400 mr-1.5"></i> {{ $applicant->admissionWave->name ?? 'Gelombang 1 Reguler' }}</span>
                        <span><i class="fa-solid fa-school text-slate-400 mr-1.5"></i> Asal Sekolah: {{ $applicant->high_school_name ?? 'SMA Kristen 1 BPK Penabur' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                <a href="{{ route('pmb.cbt.result', $applicant->id ?? '') }}" class="px-4 py-2.5 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-slate-950 text-xs font-bold transition-all flex items-center gap-2 shadow-sm hover:scale-[1.02]">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Unduh Surat Penerimaan (LoA)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Multi-Step Admissions Connected Stepper (Visual Workflow Timeline) -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-navy dark:text-uph-gold">Tahapan Admisi</span>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                    <i class="fa-solid fa-list-check text-uph-gold"></i>
                    <span>Progres Seleksi Masuk Universitas</span>
                </h2>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold font-mono bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                Status: DITERIMA (LULUS)
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <!-- Step 1: Registrasi Formulir -->
            <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-950/30 relative">
                <div class="flex items-center justify-between">
                    <span class="h-6 w-6 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase text-emerald-800 dark:text-emerald-300 font-mono">Selesai</span>
                </div>
                <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-2">1. Registrasi Formulir</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Biodata & berkas terunggah lengkap</p>
            </div>

            <!-- Step 2: Biaya Formulir -->
            <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-950/30 relative">
                <div class="flex items-center justify-between">
                    <span class="h-6 w-6 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase text-emerald-800 dark:text-emerald-300 font-mono">Lunas</span>
                </div>
                <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-2">2. Biaya Formulir (VA)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Rp 250.000 terverifikasi otomatis</p>
            </div>

            <!-- Step 3: Ujian Seleksi CBT -->
            <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-950/30 relative">
                <div class="flex items-center justify-between">
                    <span class="h-6 w-6 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase text-emerald-800 dark:text-emerald-300 font-mono">Skor 88.5</span>
                </div>
                <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-2">3. Ujian Seleksi CBT</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Logika & Matematika Terlampaui</p>
            </div>

            <!-- Step 4: Hasil Seleksi & LoA -->
            <div class="p-4 rounded-xl border-2 border-uph-gold bg-amber-50/70 dark:bg-amber-950/30 relative shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="h-6 w-6 rounded-full bg-uph-gold text-slate-950 font-bold text-xs flex items-center justify-center">
                        <i class="fa-solid fa-award text-[10px]"></i>
                    </span>
                    <span class="text-[10px] font-black uppercase text-amber-900 dark:text-amber-300 font-mono">LULUS</span>
                </div>
                <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-2">4. Hasil Seleksi & LoA</h3>
                <p class="text-[11px] text-amber-900 dark:text-amber-200 font-bold mt-0.5">Beasiswa: Tier 1 (50% Waiver)</p>
            </div>
        </div>
    </div>

    <!-- Details Bento Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Official Acceptance Letter (LoA Preview) -->
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-uph-gold">Dokumen Resmi</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-0.5">
                            <i class="fa-solid fa-certificate text-uph-navy dark:text-uph-gold"></i>
                            <span>Surat Resmi Penerimaan (Letter of Acceptance)</span>
                        </h2>
                    </div>
                </div>

                <div class="p-6 rounded-xl border-2 border-dashed border-uph-gold/50 bg-amber-50/30 dark:bg-amber-950/20 text-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-amber-200 dark:border-amber-800">
                        <div>
                            <p class="font-black text-sm text-uph-navy dark:text-uph-gold tracking-wide">UNIVERSITAS PELITA HARAPAN</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-bold">Office of Admissions & Financial Aid</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-slate-400">Ref: UPH/PMB/2026/00918</span>
                    </div>

                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
                        Berdasarkan hasil evaluasi dokumen akademik dan Ujian Seleksi Berbasis Komputer (CBT) yang telah dilaksanakan, dengan sukacita Rektorat dan Komite Penerimaan Mahasiswa Baru UPH menyatakan bahwa:
                    </p>

                    <div class="p-3.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Nama Lengkap:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $user->full_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Nomor Pendaftaran:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white tabular-nums">{{ $applicant->registration_number ?? 'REG202600001' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Program Studi:</span>
                            <span class="font-bold text-uph-navy dark:text-uph-gold">{{ $applicant->studyProgram->name_id ?? 'Informatika' }} (S1)</span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Penghargaan Beasiswa:</span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ $applicant->scholarship_tier ?? 'Tier 1 - 50% Tuition Waiver' }}</span>
                        </div>
                    </div>

                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
                        <strong>DITERIMA</strong> sebagai Calon Mahasiswa Baru Tahun Akademik 2026/2027. Silakan lakukan konfirmasi pembayaran biaya registrasi ulang (BPP Pokok) untuk mengunci Nomor Induk Mahasiswa (NIM) resmi Anda.
                    </p>

                    <div class="flex items-center justify-between pt-3 border-t border-amber-200 dark:border-amber-800 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Tangerang, {{ now()->format('d F Y') }}</span>
                        <a href="{{ route('pmb.cbt.result', $applicant->id ?? '') }}" class="px-4 py-2 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-download text-uph-gold"></i>
                            <span>Unduh Dokumen PDF Resmi</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Col: Registration & VA Billing Info -->
        <div class="space-y-6">

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-uph-gold"></i>
                    <span>Tagihan Registrasi & Ujian</span>
                </h3>

                <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/30 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">Formulir Pendaftaran & CBT</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-100 font-mono">LUNAS</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Nominal:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white tabular-nums">Rp 250.000</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Metode:</span>
                        <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">BCA Virtual Account</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-uph-navy to-[#0F172A] p-6 rounded-2xl border border-uph-gold/30 text-white shadow-md">
                <h4 class="font-bold text-sm text-uph-gold flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-headset"></i>
                    <span>Bantuan Admisi PMB</span>
                </h4>
                <p class="text-xs text-slate-300">
                    Memerlukan asistensi pembayaran BPP atau klaim beasiswa? Tim Admissions Counselor UPH siap membantu Anda.
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <span class="text-xs font-mono font-bold text-white bg-white/10 px-3 py-1.5 rounded-lg border border-white/20">
                        WA: +62 811 1234 5678
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
