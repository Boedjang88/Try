@extends('layouts.public')

@section('content')
<div class="bg-uph-navy-dark text-white py-12 border-b-2 border-uph-gold">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-uph-gold">Portal Calon Mahasiswa Baru</span>
            <h1 class="text-2xl sm:text-3xl font-display font-black text-white mt-1">Status Pendaftaran PMB</h1>
            <p class="text-xs text-slate-300 mt-1">
                No. Registrasi: <span class="font-mono text-uph-gold font-bold">{{ $applicant->registration_number }}</span> &bull; 
                Program: <span class="text-white font-semibold">{{ $applicant->targetStudyProgram->name_id }} ({{ $applicant->targetStudyProgram->degree_level }})</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $applicant->admission_status === 'enrolled' || $applicant->admission_status === 'passed' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-uph-gold/20 text-uph-gold border border-uph-gold/40' }}">
                Status: {{ strtoupper($applicant->admission_status) }}
            </span>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    <!-- Lifecycle Stepper -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600 mb-6">Tahapan Pendaftaran & Seleksi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl border {{ $applicant->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-amber-50 border-amber-300 text-amber-900' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold">1. Pembayaran VA</span>
                    <i class="fa-solid {{ $applicant->payment_status === 'paid' ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-600' }}"></i>
                </div>
                <p class="text-[11px] {{ $applicant->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $applicant->payment_status === 'paid' ? 'Lunas diverifikasi' : 'Menunggu pembayaran VA' }}
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl border {{ $applicant->cbt_score !== null ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : ($applicant->payment_status === 'paid' ? 'bg-blue-50 border-blue-300 text-blue-900' : 'bg-slate-50 border-slate-200 text-slate-400') }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold">2. Ujian CBT</span>
                    <i class="fa-solid {{ $applicant->cbt_score !== null ? 'fa-circle-check text-emerald-600' : 'fa-laptop-code' }}"></i>
                </div>
                <p class="text-[11px]">
                    {{ $applicant->cbt_score !== null ? 'Skor: ' . $applicant->cbt_score . '/100' : ($applicant->payment_status === 'paid' ? 'Siap Dikerjakan' : 'Terkunci') }}
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl border {{ $applicant->admission_status === 'passed' || $applicant->admission_status === 'enrolled' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold">3. Beasiswa</span>
                    <i class="fa-solid {{ $applicant->admission_status === 'passed' || $applicant->admission_status === 'enrolled' ? 'fa-award text-emerald-600' : 'fa-medal' }}"></i>
                </div>
                <p class="text-[11px]">
                    {{ $applicant->scholarship_tier }}
                </p>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl border {{ $applicant->generated_nim ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold">4. Penerbitan NIM</span>
                    <i class="fa-solid {{ $applicant->generated_nim ? 'fa-id-card text-emerald-600' : 'fa-user-graduate' }}"></i>
                </div>
                <p class="text-[11px] font-mono font-bold">
                    {{ $applicant->generated_nim ?? 'Belum Terbit' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Active Step Action Panels -->
    @if($applicant->payment_status !== 'paid')
    <!-- Panel 1: VA Payment Details -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <span class="text-xs uppercase tracking-wider text-uph-crimson font-bold">Langkah 1: Pembayaran Formulir</span>
                <h3 class="text-lg font-display font-bold text-uph-navy">Instruksi Pembayaran Virtual Account</h3>
            </div>
            <span class="px-3 py-1 rounded bg-amber-100 text-amber-800 text-xs font-bold">
                Belum Terbayar
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-surface-slate p-6 rounded-xl border border-slate-200">
            <div class="space-y-3">
                <span class="text-[11px] text-slate-500 uppercase tracking-widest block font-bold">Nomor Virtual Account (BCA / Mandiri / CIMB)</span>
                <p class="font-mono text-2xl font-black text-uph-navy tracking-wider select-all">{{ $applicant->va_number }}</p>
                <p class="text-xs text-slate-600">Atas Nama: <strong class="text-slate-900">{{ auth()->user()->full_name }}</strong></p>
                <p class="text-xs text-slate-600">Total Tagihan: <strong class="text-uph-crimson text-sm">Rp {{ number_format($applicant->payment_amount, 0, ',', '.') }}</strong></p>
            </div>
            <div class="space-y-3 border-t md:border-t-0 md:border-l md:pl-6 border-slate-200">
                <span class="text-[11px] text-slate-500 uppercase tracking-widest block font-bold">Simulasi Gateway Testing</span>
                <p class="text-xs text-slate-500">
                    Untuk kebutuhan demo dan evaluasi sistem, Anda dapat mensimulasikan webhook pembayaran VA instan dengan menekan tombol berikut:
                </p>
                <form method="POST" action="/pmb/simulate-payment/{{ $applicant->id }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition-colors shadow">
                        <i class="fa-solid fa-credit-card mr-1.5"></i> Simulasikan Pembayaran Lunas (Webhook)
                    </button>
                </form>
            </div>
        </div>
    </div>
    @elseif($applicant->cbt_score === null)
    <!-- Panel 2: CBT Exam Gateway -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <span class="text-xs uppercase tracking-wider text-emerald-600 font-bold">Langkah 2: Ujian CBT Beasiswa</span>
                <h3 class="text-lg font-display font-bold text-uph-navy">Ujian Computer-Based Testing (CBT) Mandiri</h3>
            </div>
            <span class="px-3 py-1 rounded bg-emerald-100 text-emerald-800 text-xs font-bold">
                Pembayaran VA Terverifikasi
            </span>
        </div>

        <div class="p-6 rounded-xl bg-blue-50/60 border border-blue-200 space-y-4 text-xs text-slate-700">
            <h4 class="font-bold text-uph-navy text-sm">Petunjuk Pelaksanaan Ujian CBT:</h4>
            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                <li>Ujian terdiri dari 10 soal penalaran logika, matematika, bahasa Inggris akademik, dan wawasan kepemimpinan.</li>
                <li>Durasi pengerjaan: <strong>30 Menit</strong> dengan timer otomatis.</li>
                <li>Sistem dilengkapi dengan <strong>Anti-Tab Switch Detection</strong>; dilarang meninggalkan jendela ujian.</li>
                <li>Skor akhir akan langsung menentukan tier beasiswa Anda (hingga 100%) dan langsung menerbitkan <strong>NIM Resmi</strong>.</li>
            </ul>

            <div class="pt-4 flex justify-end">
                <a href="/pmb/cbt" class="px-8 py-3.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-uph-gold font-display font-bold text-xs uppercase tracking-wider shadow-uph transition-all flex items-center gap-2">
                    <i class="fa-solid fa-play"></i> Mulai Ujian CBT Sekarang
                </a>
            </div>
        </div>
    </div>
    @else
    <!-- Panel 3: Results & Official Letter of Acceptance (LoA) with NIM -->
    <div class="bg-white rounded-2xl border-2 border-emerald-400 p-6 sm:p-8 shadow-lg space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-emerald-100 pb-4">
            <div>
                <span class="text-xs uppercase tracking-widest text-emerald-600 font-bold">Letter of Acceptance (LoA)</span>
                <h3 class="text-xl font-display font-bold text-uph-navy">Selamat! Anda Diterima di Universitas Pelita Harapan</h3>
            </div>
            <span class="px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                Status: Resmi Terdaftar (Enrolled)
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-surface-slate p-6 rounded-xl border border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 uppercase tracking-widest block font-bold text-[10px]">Skor Ujian CBT</span>
                <p class="text-2xl font-black text-uph-navy font-mono mt-1">{{ $applicant->cbt_score }} / 100</p>
                <span class="text-emerald-600 font-semibold">Tingkat Kelulusan Valid</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase tracking-widest block font-bold text-[10px]">Tier Beasiswa Diperoleh</span>
                <p class="text-xl font-black text-uph-crimson font-display mt-1">{{ $applicant->scholarship_tier }}</p>
                <span class="text-slate-500">Berdasarkan hasil tes CBT</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase tracking-widest block font-bold text-[10px]">Nomor Induk Mahasiswa (NIM)</span>
                <p class="text-2xl font-black text-uph-navy font-mono mt-1 tracking-wider">{{ $applicant->generated_nim }}</p>
                <span class="text-emerald-600 font-semibold">Promosi Akun Mahasiswa Aktif</span>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <span class="text-xs text-slate-500">
                Akun Anda telah dipromosikan menjadi <strong>Mahasiswa Aktif</strong>. Anda dapat langsung mengakses portal SIAKAD dan memilih rencana studi (KRS).
            </span>
            <a href="/portal/dashboard" class="px-7 py-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i> Masuk ke Portal SIAKAD
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
