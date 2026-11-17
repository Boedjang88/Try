@extends('layouts.portal', ['title' => 'Campus Life: E-Library & Smart Lab Booking'])

@section('content')
<div class="space-y-6">
    <!-- Executive Header -->
    <div class="bg-gradient-to-r from-uph-navy via-slate-900 to-uph-navy p-6 sm:p-8 rounded-2xl text-white shadow-xl border border-uph-gold/30 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 mb-2">
                    <i class="fa-solid fa-book-bookmark"></i>
                    UPH Smart Campus Ecosystem &bull; Integrated Clearances
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">E-Library & Fasilitas Smart Lab</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Peminjaman buku teks akademik terintegrasi dengan verifikasi Bebas Pustaka Yudisium, serta reservasi laboratorium komputer, ruang diskusi, dan fasilitas riset.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/portal/student/yudisium" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap"></i>
                    Status Bebas Pustaka
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: E-Library Loans -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-book-open text-uph-navy dark:text-uph-gold"></i>
                    Riwayat Peminjaman Buku Perpustakaan
                </h2>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $loans->count() }} Transaksi
                </span>
            </div>

            <!-- Yudisium Warning Banner if Active Loan Exists -->
            @php
                $unreturnedLoans = $loans->filter(function($l) {
                    return in_array($l->status, ['borrowed', 'overdue']) || $l->fine_amount > 0;
                });
            @endphp

            @if($unreturnedLoans->count() > 0)
                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-500 text-sm"></i>
                    <div>
                        <p class="font-bold">Kunci Bebas Pustaka Aktif:</p>
                        <p class="mt-0.5 text-[11px] text-amber-800 dark:text-amber-300">
                            Anda memiliki {{ $unreturnedLoans->count() }} buku yang belum dikembalikan atau denda tertunda. Checkpoint Bebas Pustaka Yudisium akan otomatis terkunci hingga seluruh tanggungan diselesaikan.
                        </p>
                    </div>
                </div>
            @endif

            <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-96 overflow-y-auto">
                @forelse($loans as $loan)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-uph-navy/10 text-uph-navy dark:bg-uph-navy dark:text-uph-gold flex items-center justify-center text-sm font-bold shrink-0">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ $loan->book_title }}</h3>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">Kode: {{ $loan->book_code }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Pinjam: {{ $loan->borrowed_at->format('d M Y') }} &bull; 
                                    Jatuh Tempo: {{ $loan->due_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $loan->status === 'returned' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : '' }}
                                {{ $loan->status === 'borrowed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200' : '' }}
                                {{ $loan->status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : '' }}">
                                {{ $loan->status }}
                            </span>
                            @if($loan->fine_amount > 0)
                                <p class="text-[11px] font-bold text-uph-crimson mt-1 font-mono">Denda: Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        <i class="fa-regular fa-bookmark text-3xl mb-2 block"></i>
                        Tidak ada catatan peminjaman buku perpustakaan.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Facility / Smart Lab Booking -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-microscope text-uph-gold"></i>
                    Reservasi Laboratorium & Fasilitas Kampus
                </h2>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    Smart Booking
                </span>
            </div>

            <!-- Booking Form -->
            <form action="/portal/student/campus-life/book" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Nama Fasilitas / Lab</label>
                        <input type="text" name="facility_name" required placeholder="Contoh: Lab Komputasi Awan Gedung D"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium focus:ring-2 focus:ring-uph-navy">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Tipe Fasilitas</label>
                        <select name="facility_type" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                            <option value="lab">Laboratorium Komputer / Sains</option>
                            <option value="discussion_room">Ruang Diskusi Mandiri</option>
                            <option value="equipment">Peralatan Multimedia & Riset</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Waktu Mulai</label>
                        <input type="datetime-local" name="start_time" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Waktu Selesai</label>
                        <input type="datetime-local" name="end_time" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Tujuan Penggunaan / Riset</label>
                    <textarea name="purpose" rows="2" required placeholder="Jelaskan kebutuhan riset skripsi, tugas akhir, atau praktikum..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-uph-navy text-white hover:bg-uph-navy-dark dark:bg-uph-gold dark:text-uph-navy font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    Ajukan Reservasi Fasilitas
                </button>
            </form>

            <!-- Bookings List -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase text-slate-400 mb-2">Riwayat Reservasi Saya</h3>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @forelse($bookings as $b)
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $b->facility_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $b->start_time->format('d M H:i') }} - {{ $b->end_time->format('H:i') }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $b->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $b->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $b->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $b->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-2">Belum ada reservasi fasilitas aktif.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
