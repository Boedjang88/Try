<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CBT Online Examination - Universitas Pelita Harapan</title>

    <!-- Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'uph-navy': '#00205B',
                        'uph-navy-dark': '#0A2540',
                        'uph-crimson': '#C8102E',
                        'uph-gold': '#D4AF37',
                        'uph-gold-warm': '#FFB800',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        display: ['Montserrat', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col select-none">

    <!-- CBT Examination Container App -->
    <div id="cbt-exam-app" 
         data-applicant-id="{{ $applicant->id }}" 
         data-duration="{{ $durationSeconds }}"
         class="flex-1 flex flex-col h-full">

        <!-- Top Command Header -->
        <header class="h-16 bg-uph-navy-dark text-white border-b-2 border-uph-gold px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded bg-uph-navy border border-uph-gold flex items-center justify-center text-uph-gold font-serif font-black text-lg">
                    U
                </div>
                <div>
                    <h1 class="font-display font-bold text-sm tracking-wide text-white">UPH Computer-Based Testing (CBT)</h1>
                    <p class="text-[10px] text-slate-300">Peserta: <strong class="text-uph-gold">{{ auth()->user()->full_name }}</strong> ({{ $applicant->registration_number }})</p>
                </div>
            </div>

            <!-- Countdown Timer Indicator -->
            <div id="cbt-timer-container" class="flex items-center gap-2.5 px-4 py-1.5 rounded-lg bg-uph-navy text-uph-gold border border-uph-gold/40 shadow">
                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                <div class="text-right">
                    <span class="text-[9px] uppercase tracking-widest block text-slate-300">Sisa Waktu</span>
                    <span id="cbt-timer-display" class="font-mono font-bold text-sm sm:text-base tracking-wider tabular-nums">30:00</span>
                </div>
            </div>
        </header>

        <!-- Exam Workspace Area -->
        <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col lg:flex-row gap-6 overflow-hidden">
            <!-- Left: Question Workspace (scrollable) -->
            <main class="flex-1 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between overflow-hidden">
                <!-- Question Top Indicator -->
                <div class="p-4 sm:p-6 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <span id="cbt-progress-text" class="text-xs font-bold text-slate-600">
                        0 dari {{ count($questions) }} Soal Terjawab
                    </span>
                    <span class="text-[11px] text-slate-400">
                        Pintas Tombol: <kbd class="px-1.5 py-0.5 rounded bg-white border font-mono">A</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white border font-mono">B</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white border font-mono">C</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white border font-mono">D</kbd> &bull; Panah untuk Navigasi
                    </span>
                </div>

                <!-- Questions Container -->
                <div class="flex-1 p-6 sm:p-8 overflow-y-auto space-y-6">
                    @foreach($questions as $index => $q)
                    <div class="cbt-question-card hidden space-y-6" data-question-id="{{ $q['id'] }}">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded bg-uph-navy/10 text-uph-navy font-bold text-xs uppercase">
                                Soal No. {{ $index + 1 }}
                            </span>
                            <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-600 text-xs font-semibold">
                                {{ $q['category'] }}
                            </span>
                        </div>

                        <div class="text-sm sm:text-base font-semibold text-slate-900 leading-relaxed">
                            {{ $q['question'] }}
                        </div>

                        <!-- Options List -->
                        <div class="space-y-3 pt-2">
                            @foreach($q['options'] as $key => $optionText)
                            <button type="button" 
                                    data-option="{{ $key }}"
                                    class="cbt-option-btn w-full p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 text-xs sm:text-sm font-medium flex items-center gap-4 transition-all text-left">
                                <span class="h-8 w-8 rounded-lg bg-slate-100 font-bold flex items-center justify-center shrink-0 border border-slate-200">
                                    {{ $key }}
                                </span>
                                <span class="flex-1">{{ $optionText }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Bottom Navigation Toolbar -->
                <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
                    <button type="button" id="cbt-prev-btn" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-white text-xs font-bold transition-colors disabled:opacity-40">
                        <i class="fa-solid fa-arrow-left mr-1.5"></i> Soal Sebelumnya
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="button" id="cbt-next-btn" class="px-6 py-2.5 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-white text-xs font-bold transition-colors shadow">
                            Soal Berikutnya <i class="fa-solid fa-arrow-right ml-1.5"></i>
                        </button>
                        <button type="button" id="cbt-submit-btn" class="hidden px-7 py-2.5 rounded-lg bg-uph-crimson hover:bg-uph-crimson-hover text-white text-xs font-bold uppercase tracking-wider transition-colors shadow-uph-crimson">
                            <i class="fa-solid fa-check-double mr-1.5"></i> Selesai & Kirim Ujian
                        </button>
                    </div>
                </div>
            </main>

            <!-- Right: Question Palette Sidebar -->
            <aside class="w-full lg:w-72 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between shrink-0">
                <div class="space-y-4">
                    <h3 class="font-display font-bold text-xs uppercase tracking-wider text-slate-500 border-b pb-2">
                        Nomor Soal Ujian
                    </h3>
                    
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($questions as $index => $q)
                        <button type="button" 
                                data-question-id="{{ $q['id'] }}" 
                                class="cbt-palette-item h-10 w-10 rounded-lg text-xs font-bold transition-all flex items-center justify-center border border-slate-200 bg-white text-slate-700">
                            {{ $index + 1 }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 space-y-2 text-[11px] text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded bg-emerald-600"></span>
                        <span>Sudah Terjawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded border border-slate-300 bg-white"></span>
                        <span>Belum Dijawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded ring-2 ring-uph-gold bg-uph-navy"></span>
                        <span>Sedang Dikerjakan</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Tab-Switch Warning Modal -->
    <div id="cbt-tab-warning-modal" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 text-center space-y-4 shadow-2xl border-2 border-uph-crimson">
            <div class="h-16 w-16 mx-auto rounded-full bg-red-100 text-uph-crimson flex items-center justify-center text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="font-display font-black text-lg text-slate-900">Peringatan Integritas Ujian!</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Anda terdeteksi berpindah tab atau meninggalkan jendela ujian. Aktivitas ini dicatat dalam log integritas audit. Ujian akan dihentikan dan disubmit secara paksa apabila Anda berpindah tab melebihi batas.
            </p>
            <p id="cbt-warning-count-text" class="text-xs font-bold text-uph-crimson font-mono"></p>
            <button type="button" id="cbt-warning-dismiss" class="w-full py-2.5 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider">
                Saya Mengerti & Kembali ke Ujian
            </button>
        </div>
    </div>

    <!-- Exam Result & LoA Modal -->
    <div id="cbt-result-modal" class="hidden fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-8 text-center space-y-6 shadow-2xl border-2 border-emerald-500">
            <div class="h-16 w-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl">
                <i class="fa-solid fa-award"></i>
            </div>

            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">Hasil Resmi CBT UPH</span>
                <h2 class="text-2xl font-display font-black text-uph-navy mt-1">Ujian CBT Selesai</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl text-left border text-xs">
                <div>
                    <span class="text-slate-400 block font-bold text-[10px] uppercase">Nilai CBT</span>
                    <span id="cbt-result-score" class="text-xl font-black text-uph-navy font-mono">0 / 100</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-bold text-[10px] uppercase">Tier Beasiswa</span>
                    <span id="cbt-result-tier" class="text-base font-black text-uph-crimson font-display">Regular (0%)</span>
                </div>
                <div class="col-span-2 pt-2 border-t">
                    <span class="text-slate-400 block font-bold text-[10px] uppercase">Status Penerimaan</span>
                    <span id="cbt-result-status" class="text-sm font-bold text-emerald-700">LULUS</span>
                </div>
                <div id="cbt-result-nim-container" class="hidden col-span-2 pt-2 border-t bg-emerald-50 p-2.5 rounded">
                    <span class="text-emerald-800 block font-bold text-[10px] uppercase">NIM Resmi Mahasiswa Baru:</span>
                    <span id="cbt-result-nim" class="text-lg font-mono font-black text-uph-navy tracking-widest"></span>
                </div>
            </div>

            <a href="/pmb/status" class="block w-full py-3.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-display font-bold text-xs uppercase tracking-wider shadow">
                Lihat Surat Penerimaan (LoA) & Status
            </a>
        </div>
    </div>

    <!-- Load CBT Engine Script -->
    <script src="/resources/js/modules/cbt-engine.js"></script>
</body>
</html>
