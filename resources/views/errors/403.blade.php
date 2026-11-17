<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Universitas Pelita Harapan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'uph-navy': '#00205B',
                        'uph-gold': '#D4AF37',
                        'uph-crimson': '#C8102E',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-8 sm:p-10 shadow-2xl text-center space-y-6">
        <!-- Shield Badge -->
        <div class="w-20 h-20 mx-auto rounded-3xl bg-uph-crimson/20 border border-uph-crimson/40 text-uph-crimson flex items-center justify-center text-3xl shadow-inner">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-black uppercase tracking-[0.2em] text-uph-gold">
                Zero-Trust IAM Security Gate
            </span>
            <h1 class="text-3xl sm:text-4xl font-display font-black text-white tracking-tight">
                403 Akses Ditolak
            </h1>
            <p class="text-sm text-slate-300">
                Insufficient Account Privileges &bull; Otoritas Akun Tidak Mencukupi
            </p>
        </div>

        <div class="p-4 rounded-2xl bg-black/30 border border-slate-700/60 text-left text-xs space-y-2 font-mono">
            <div class="flex items-center justify-between text-slate-400">
                <span>Status Kode:</span>
                <span class="text-rose-400 font-bold">HTTP 403 Forbidden</span>
            </div>
            <div class="flex items-center justify-between text-slate-400">
                <span>Akun Aktif:</span>
                <span class="text-slate-200">{{ auth()->user()->full_name ?? 'Tamu / Guest' }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-400">
                <span>Peran Sistem:</span>
                <span class="px-2 py-0.5 rounded bg-uph-navy text-uph-gold font-bold uppercase text-[10px]">
                    {{ auth()->user()->primary_role ?? auth()->user()->role ?? 'Guest' }}
                </span>
            </div>
            @if(isset($exception) && $exception->getMessage())
                <div class="pt-2 border-t border-slate-700/60 text-slate-300">
                    <span class="text-slate-400 block text-[10px] uppercase">Rincian Pelanggaran:</span>
                    <span class="text-amber-300">{{ $exception->getMessage() }}</span>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <a href="/portal" class="flex-1 py-3 px-4 rounded-xl bg-uph-navy hover:bg-uph-navy/80 text-white font-bold text-xs uppercase tracking-wider transition-colors flex items-center justify-center gap-2 border border-uph-gold/30">
                <i class="fa-solid fa-house"></i> Kembali ke Workspace
            </a>
            <a href="/portal/account/security" class="flex-1 py-3 px-4 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-xs uppercase tracking-wider transition-colors flex items-center justify-center gap-2 border border-slate-600">
                <i class="fa-solid fa-user-shield"></i> Cek Hak Akses
            </a>
        </div>

        <div class="text-[11px] text-slate-500">
            Butuh akses khusus? Hubungi Ketua Program Studi atau Biro Administrasi Akademik (BAAK) UPH.
        </div>
    </div>
</body>
</html>
