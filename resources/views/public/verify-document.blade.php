<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isValid ? 'Dokumen Terverifikasi' : 'Verifikasi Gagal' }} - Portal Verifikasi Ijazah & Dokumen Akademik UPH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        uph: {
                            navy: '#00205B',
                            dark: '#061B40',
                            gold: '#D4AF37',
                            'gold-warm': '#FFB800',
                            crimson: '#C8102E',
                            light: '#F8FAFC',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        display: ['Playfair Display', 'serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans selection:bg-uph-gold selection:text-slate-900 antialiased">

    <!-- Header Navigation -->
    <header class="border-b border-white/10 bg-uph-dark/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-uph-navy to-uph-gold p-0.5 shadow-lg flex items-center justify-center">
                    <div class="h-full w-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                        <i class="fa-solid fa-graduation-cap text-uph-gold text-lg"></i>
                    </div>
                </div>
                <div>
                    <h1 class="font-display font-bold text-base sm:text-lg text-white tracking-wide">UNIVERSITAS PELITA HARAPAN</h1>
                    <p class="text-[10px] sm:text-xs text-slate-400 font-mono uppercase tracking-wider">Public Academic Document Verification Gateway</p>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs font-mono text-slate-400 bg-white/5 px-3 py-1.5 rounded-full border border-white/10">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                <span>PDDIKTI / SIAKAD Live Sync</span>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12 flex flex-col justify-center">
        @if($isValid && $documentData)
            <!-- VALID VERIFICATION BADGE -->
            <div class="bg-slate-800/80 border border-emerald-500/30 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-emerald-950/40 relative overflow-hidden backdrop-blur-xl">
                <!-- Background Glow -->
                <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Verified Header Banner -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-6 border-b border-white/10">
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shrink-0 shadow-xl shadow-emerald-500/20">
                        <i class="fa-solid fa-circle-check text-4xl text-white"></i>
                    </div>
                    <div class="text-center sm:text-left flex-1">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold uppercase tracking-wider mb-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Tanda Tangan Kriptografis Sah
                        </div>
                        <h2 class="font-display font-extrabold text-xl sm:text-2xl text-white">
                            DOKUMEN AKADEMIK ASLI & TERVERIFIKASI DI PDDIKTI/SIAKAD
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-1">
                            Arsip dokumen ini telah divalidasi secara real-time terhadap pangkalan data resmi Universitas Pelita Harapan dan Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi.
                        </p>
                    </div>
                </div>

                <!-- Document Details Grid -->
                <div class="mt-8 space-y-6">
                    <div class="bg-slate-900/60 rounded-2xl p-5 border border-white/5 divide-y divide-white/5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Jenis Dokumen</span>
                            <span class="sm:col-span-2 font-bold text-white flex items-center gap-2">
                                <i class="fa-regular fa-file-lines text-uph-gold"></i>
                                {{ $documentData['title'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Nomor Referensi</span>
                            <span class="sm:col-span-2 font-mono font-bold text-uph-gold">
                                {{ $documentData['ref_number'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Nama Pemilik Dokumen</span>
                            <span class="sm:col-span-2 font-bold text-white text-base">
                                {{ $documentData['holder_name'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">{{ $documentData['id_label'] ?? 'Nomor Identitas' }}</span>
                            <span class="sm:col-span-2 font-mono text-slate-200">
                                {{ $documentData['holder_id'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Program Studi / Fakultas</span>
                            <span class="sm:col-span-2 text-slate-200">
                                {{ $documentData['program'] }} &bull; {{ $documentData['faculty'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Tahun / Periode Akademik</span>
                            <span class="sm:col-span-2 text-slate-200">
                                {{ $documentData['period'] }}
                            </span>
                        </div>

                        @if(isset($documentData['status']))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Status Akademik</span>
                            <span class="sm:col-span-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold text-xs">
                                    {{ $documentData['status'] }}
                                </span>
                            </span>
                        </div>
                        @endif

                        @if(isset($documentData['national_pin']))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">PIN Ijazah Nasional (PDDIKTI)</span>
                            <span class="sm:col-span-2 font-mono font-bold text-emerald-300">
                                {{ $documentData['national_pin'] }}
                            </span>
                        </div>
                        @endif

                        @if(isset($documentData['amount']))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 text-sm">
                            <span class="text-slate-400 font-medium">Nominal Pembayaran</span>
                            <span class="sm:col-span-2 font-mono font-extrabold text-emerald-400 text-base">
                                {{ $documentData['amount'] }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Security & Cryptographic Stamp -->
                    <div class="bg-emerald-950/30 border border-emerald-500/20 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-lock text-emerald-400 text-xl"></i>
                            <div>
                                <p class="text-xs font-bold text-white uppercase tracking-wider">HMAC-SHA256 Cryptographic Digest</p>
                                <p class="text-[11px] font-mono text-emerald-300 break-all">{{ $hash }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-[10px] text-slate-400 block">Waktu Verifikasi:</span>
                            <span class="text-xs font-mono text-slate-200">{{ now()->format('d M Y - H:i:s') }} WIB</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- INVALID / TAMPERED WARNING BADGE -->
            <div class="bg-slate-800/80 border border-red-500/40 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-red-950/40 relative overflow-hidden backdrop-blur-xl">
                <div class="absolute -top-24 -right-24 w-72 h-72 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-6 border-b border-white/10">
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-tr from-red-600 to-rose-400 flex items-center justify-center shrink-0 shadow-xl shadow-red-500/20">
                        <i class="fa-solid fa-triangle-exclamation text-4xl text-white"></i>
                    </div>
                    <div class="text-center sm:text-left flex-1">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-bold uppercase tracking-wider mb-2">
                            Peringatan Keamanan
                        </div>
                        <h2 class="font-display font-extrabold text-xl sm:text-2xl text-white">
                            PERINGATAN: DOKUMEN TIDAK VALID ATAU TELAH DIMANIPULASI
                        </h2>
                        <p class="text-xs sm:text-sm text-red-200/90 mt-1">
                            Sistem keamanan kriptografis universitas mendeteksi ketidakcocokan antara token tanda tangan digital QR Code dengan arsip resmi universitas.
                        </p>
                    </div>
                </div>

                <div class="mt-6 bg-red-950/30 border border-red-500/30 rounded-2xl p-5 space-y-3">
                    <p class="text-xs sm:text-sm text-slate-300">
                        <span class="font-bold text-white">Keterangan Diagnostik:</span> {{ $errorMessage }}
                    </p>
                    <p class="text-xs text-slate-400">
                        Jika Anda merasa dokumen ini diterbitkan secara sah oleh UPH, silakan hubungi Biro Administrasi Akademik (BAAK) atau Biro Administrasi Keuangan (BAUK) di Kampus Lippo Village untuk penerbitan ulang dokumen bertanda tangan digital resmi.
                    </p>
                </div>

                <div class="mt-6 text-center sm:text-left">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Portal Utama
                    </a>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Universitas Pelita Harapan (UPH). All rights reserved.</p>
        <p class="text-[10px] mt-1 text-slate-600 font-mono">Monolith Enterprise Architecture &bull; Stage 10 Production Release</p>
    </footer>

</body>
</html>
