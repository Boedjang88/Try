@extends('layouts.portal')

@section('title', 'Kwitansi Pembayaran Kuliah - UPH')

@section('content')
<div class="space-y-6">
    <!-- Top Actions (Hidden on Print) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/finance" class="text-xs font-bold text-uph-navy hover:underline">
                    &larr; Kembali ke Portal Tagihan
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Biro Administrasi Keuangan (BAUK)</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Bukti Pembayaran Biaya Kuliah (Kwitansi Sah)</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Dokumen tanda terima pembayaran resmi universitas yang diakui secara hukum dan terdaftar pada buku kas besar.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Kwitansi Resmi
            </button>
        </div>
    </div>

    <!-- Official Kwitansi Canvas -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-12 print:p-0 print:border-none print:shadow-none print:rounded-none max-w-4xl mx-auto text-slate-900 relative">
        
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
            <span class="text-9xl font-black font-display tracking-widest text-uph-navy rotate-[-30deg]">UPH PAID</span>
        </div>

        <!-- Institutional Header / Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-6 mb-8 text-center relative">
            <div class="flex items-center justify-center gap-4 mb-2">
                <div class="w-14 h-14 rounded-full bg-uph-navy text-uph-gold flex items-center justify-center text-2xl font-black font-display shrink-0 border-2 border-uph-gold">
                    UPH
                </div>
                <div class="text-left">
                    <h2 class="text-xl sm:text-2xl font-display font-black text-uph-navy tracking-tight uppercase">
                        UNIVERSITAS PELITA HARAPAN
                    </h2>
                    <p class="text-xs font-bold tracking-widest text-slate-600 uppercase">
                        BIRO ADMINISTRASI KEUANGAN (BAUK) & REKENING KAS PENDIDIKAN
                    </p>
                    <p class="text-[11px] text-slate-400">
                        MH Thamrin Boulevard 1100, Lippo Village, Tangerang 15811 &bull; bauk.uph.edu
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs">
                <div>
                    <h3 class="text-base font-bold font-display uppercase tracking-wider text-slate-900">
                        KWITANSI PEMBAYARAN PENDIDIKAN
                    </h3>
                    <span class="text-[11px] font-mono text-slate-500">OFFICIAL TUITION PAYMENT RECEIPT</span>
                </div>
                <div class="text-right mt-2 sm:mt-0 font-mono">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Kwitansi:</span>
                    <strong class="text-slate-900 text-xs">KWT/{{ date('Y') }}/{{ $invoice->invoice_number }}</strong>
                </div>
            </div>
        </div>

        <!-- Student & Transaction Details Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-8 text-xs">
            <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                Identitas Pembayar & Data Akademik
            </div>
            <div class="p-4 grid grid-cols-2 gap-y-2 gap-x-6">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Mahasiswa</span>
                    <strong class="text-slate-900 text-sm">{{ $invoice->student->user->full_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Induk Mahasiswa (NIM)</span>
                    <strong class="text-slate-900 font-mono text-sm">{{ $invoice->student->nim }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Program Studi & Jenjang</span>
                    <span class="text-slate-800 font-bold">{{ $invoice->student->studyProgram->name_id }} (S1)</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Fakultas</span>
                    <span class="text-slate-800">{{ $invoice->student->studyProgram->faculty->name_id ?? 'UPH' }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Particulars -->
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-8 text-xs">
            <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                Rincian Transaksi Perbankan
            </div>
            <div class="p-4 space-y-3">
                <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                    <div>
                        <strong class="text-slate-900 text-sm block">{{ $invoice->title }}</strong>
                        <span class="text-slate-500 text-[11px]">Periode: {{ $invoice->academicPeriod->name ?? 'Semester Aktif' }}</span>
                    </div>
                    <span class="font-mono font-black text-slate-900 text-sm">
                        Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-y-2 pt-1 text-[11px]">
                    <div>
                        <span class="text-slate-400 block">Metode Pembayaran:</span>
                        <strong class="text-slate-800 font-mono">{{ str_replace('_', ' ', $invoice->payment_method ?? 'VIRTUAL ACCOUNT') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Nomor Virtual Account / QRIS:</span>
                        <strong class="text-slate-800 font-mono">{{ $invoice->va_number }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Waktu Transaksi Masuk:</span>
                        <strong class="text-slate-800">{{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d F Y, H:i:s') . ' WIB' : now()->translatedFormat('d F Y, H:i:s') . ' WIB' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Referensi Gateway Bank:</span>
                        <strong class="text-slate-800 font-mono">{{ $invoice->gateway_reference ?? 'REF-AUTO-SETTLED' }}</strong>
                    </div>
                </div>

                <!-- Terbilang Box -->
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mt-4">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Terbilang:</span>
                    <span class="font-bold text-slate-900 italic text-sm">"{{ $amountInWords }}"</span>
                </div>
            </div>
        </div>

        <!-- Total Paid & Paid Stamp -->
        <div class="flex flex-col sm:flex-row items-center justify-between border-y-2 border-slate-900 py-4 mb-8">
            <div>
                <span class="text-xs uppercase font-bold text-slate-500">Jumlah Dibayar Sah:</span>
                <div class="text-3xl font-black text-emerald-700 font-mono">
                    Rp {{ number_format($invoice->paid_amount ?: $invoice->amount, 0, ',', '.') }}
                </div>
            </div>

            <!-- Official Paid Stamp -->
            <div class="mt-3 sm:mt-0 px-6 py-2 rounded-xl border-4 border-emerald-600 bg-emerald-50 text-emerald-800 font-black font-display uppercase tracking-widest text-lg rotate-[-3deg] shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> LUNAS / PAID
            </div>
        </div>

        <!-- Legal Signatures & Digital Verification -->
        <div class="grid grid-cols-2 gap-8 text-xs pt-4">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-2xl shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <span class="font-bold text-slate-800 block text-xs">Validasi Digital BAUK:</span>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">
                        Kwitansi ini diterbitkan secara otomatis oleh sistem sentralisasi keuangan UPH dan dijamin keabsahannya oleh perbankan mitra.
                    </p>
                    <span class="font-mono text-[10px] text-slate-400 mt-1 block">
                        HASH: {{ hash('sha256', $invoice->invoice_number . '|' . $invoice->amount . '|' . $invoice->student->nim) }}
                    </span>
                </div>
            </div>

            <div class="text-right">
                <p class="text-slate-500">Tangerang, {{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-800 mt-0.5">Universitas Pelita Harapan</p>
                <p class="text-slate-600 text-[11px]">Kepala Biro Administrasi Keuangan (BAUK)</p>
                <div class="h-16 flex items-end justify-end">
                    <span class="text-[11px] text-slate-400 italic">[Tanda Tangan Digital Tersertifikasi]</span>
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1 inline-block min-w-[200px]">
                    Santoso Wibowo, S.E., M.M., Ak., CA.
                </p>
                <p class="text-[10px] text-slate-400 font-mono">NIP: 198205192008121001</p>
            </div>
        </div>

    </div>
</div>
@endsection
