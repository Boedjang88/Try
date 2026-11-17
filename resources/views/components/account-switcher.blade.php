@if(auth()->check() && config('app.env') !== 'production' && config('app.enable_demo_switcher', true))
@php
    $currentRole = auth()->user() ? (auth()->user()->primary_role ?? auth()->user()->role ?? 'guest') : 'guest';
    $currentEmail = auth()->user()->email ?? '';
    $accounts = \App\Http\Controllers\Auth\QuickSwitchAccountController::getAvailableAccounts();
@endphp

<!-- Enterprise Demo Control Center for Presentation & Evaluation -->
<div x-data="{ 
    open: false,
    tab: 'accounts', // 'accounts' or 'scenarios'
    isResetting: false,
    resetMessage: '',
    async resetDemoData() {
        if (!confirm('Reset seluruh data simulasi Loop 1 s.d. Loop 5 ke kondisi awal?')) return;
        this.isResetting = true;
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            const res = await fetch('/portal/demo/reset-state', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Gagal mereset state demo.');
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi.');
        } finally {
            this.isResetting = false;
        }
    }
}" 
class="fixed bottom-4 left-4 z-50 font-sans"
x-cloak>
    
    <!-- Collapsible Drawer -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-3 w-84 sm:w-96 rounded-3xl bg-slate-900/95 backdrop-blur-xl border border-amber-500/30 shadow-2xl text-white overflow-hidden divide-y divide-white/10">
        
        <!-- Drawer Header with Reset Action -->
        <div class="px-4 py-3 bg-gradient-to-r from-uph-navy to-slate-950 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-display font-black text-xs uppercase tracking-wider text-amber-300">UPH Demo Control Center</span>
            </div>
            
            <!-- 1-Click Fast Reset Button -->
            <button type="button" 
                    @click="resetDemoData()"
                    :disabled="isResetting"
                    title="Kembalikan state data 5 Loop ke titik awal (< 500ms)"
                    class="px-2.5 py-1 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 font-mono text-[10px] font-bold transition-all flex items-center gap-1 active:scale-95 disabled:opacity-50">
                <svg x-show="!isResetting" class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span x-show="!isResetting">1-Click Reset</span>
                <span x-show="isResetting">Resetting...</span>
            </button>
        </div>

        <!-- Navigation Tabs: Accounts vs Scenario Guide -->
        <div class="flex bg-slate-950/70 p-1 gap-1 text-[11px] font-bold">
            <button type="button" 
                    @click="tab = 'accounts'"
                    :class="tab === 'accounts' ? 'bg-amber-400 text-slate-950 shadow' : 'text-slate-400 hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl transition-all text-center">
                Akun Persona ({{ count($accounts) }})
            </button>
            <button type="button" 
                    @click="tab = 'scenarios'"
                    :class="tab === 'scenarios' ? 'bg-amber-400 text-slate-950 shadow' : 'text-slate-400 hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl transition-all text-center">
                Panduan Skenario 3-Menit
            </button>
        </div>

        <!-- TAB 1: Account List -->
        <div x-show="tab === 'accounts'" class="max-h-96 overflow-y-auto p-2 space-y-1.5 text-xs">
            @foreach($accounts as $key => $acc)
                @php
                    $isActive = ($currentEmail === $acc['email']);
                @endphp
                <a href="{{ route('auth.quick-switch', $key) }}" 
                   class="group block p-2.5 rounded-2xl border transition-all duration-150 {{ $isActive ? 'bg-amber-400/10 border-amber-400/60 shadow-lg' : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/20' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-white group-hover:text-amber-300 transition-colors">
                                    {{ $acc['title'] }}
                                </span>
                                @if($isActive)
                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-400 text-slate-950">
                                        Aktif
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-300 truncate mt-0.5">
                                {{ $acc['subtitle'] }}
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:text-white mt-1 shrink-0 group-hover:translate-x-0.5 transition-transform"></i>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[10px]">
                        <span class="px-2 py-0.5 rounded-md font-semibold border {{ $acc['badge_color'] }}">
                            {{ $acc['badge'] }}
                        </span>
                        <span class="font-mono text-slate-400 text-[10px]">{{ $acc['email'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- TAB 2: Guided Scenario Checklist -->
        <div x-show="tab === 'scenarios'" class="max-h-96 overflow-y-auto p-3 space-y-3 text-xs">
            <div class="text-[11px] text-slate-400 mb-2">
                Pilih skenario operasional berikut untuk demonstrasi alur end-to-end tanpa konfigurasi manual:
            </div>

            <!-- Scenario 1: Loop 1 -->
            <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-300">1. Loop 1: Tunggakan & Hold BAUK</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">Loop 1</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Jessica (student.blocked) diblokir dari KRS War. Staf BAUK verifikasi tagihan dan membuka hold secara atomik.
                </p>
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('auth.quick-switch', 'student_blocked') }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[10px] font-bold text-slate-200">
                        1. Login Jessica
                    </a>
                    <a href="{{ route('auth.quick-switch', 'bauk') }}" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-300 text-[10px] font-bold text-slate-950">
                        2. Verifikasi BAUK
                    </a>
                </div>
            </div>

            <!-- Scenario 2: Loop 2 -->
            <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-300">2. Loop 2: KRS War & Perwalian PA</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 font-mono">Loop 2</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Christian mengisi KRS, sistem mendeteksi tabrakan jadwal (clash 422), lalu Dosen PA menyetujui pengajuan.
                </p>
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('auth.quick-switch', 'student') }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[10px] font-bold text-slate-200">
                        1. Ambil KRS
                    </a>
                    <a href="{{ route('auth.quick-switch', 'lecturer') }}" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-300 text-[10px] font-bold text-slate-950">
                        2. Setujui Dosen PA
                    </a>
                </div>
            </div>

            <!-- Scenario 3: Loop 3 -->
            <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-300">3. Loop 3: Presensi QR Dinamis & BAP</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300 font-mono">Loop 3</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Dosen buka proyektor QR token (15 detik berotasi HMAC), mahasiswa scan QR dan dosen mengesahkan BAP digital.
                </p>
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('auth.quick-switch', 'lecturer') }}" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-300 text-[10px] font-bold text-slate-950">
                        Buka Sesi & QR
                    </a>
                </div>
            </div>

            <!-- Scenario 4: Loop 4 -->
            <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-300">4. Loop 4: Penilaian & Gate EDOM</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 font-mono">Loop 4</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Dosen kunci nilai di spreadsheet, mahasiswa terblokir melihat nilai KHS sebelum mengisi kuesioner EDOM.
                </p>
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('auth.quick-switch', 'lecturer') }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[10px] font-bold text-slate-200">
                        1. Kunci Nilai
                    </a>
                    <a href="{{ route('auth.quick-switch', 'student') }}" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-300 text-[10px] font-bold text-slate-950">
                        2. Isi EDOM & Buka KHS
                    </a>
                </div>
            </div>

            <!-- Scenario 5: Loop 5 -->
            <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-300">5. Loop 5: Sidang Skripsi & Yudisium</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300 font-mono">Loop 5</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Bimbingan skripsi 8 log, approval Kaprodi, dan penetapan SK Yudisium kelulusan BAAK.
                </p>
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('auth.quick-switch', 'baak') }}" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-300 text-[10px] font-bold text-slate-950">
                        Buka Yudisium BAAK
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="px-4 py-2.5 bg-black/50 text-[10px] text-slate-400 flex items-center justify-between border-t border-white/10">
            <span class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Active: {{ auth()->user()->email ?? '' }}</span>
            </span>
            <form method="POST" action="/logout" class="inline">
                @csrf
                <button type="submit" class="text-rose-400 hover:text-white font-bold flex items-center gap-1 transition-colors">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout ke /login
                </button>
            </form>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button @click="open = !open" 
            class="flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900 text-white border-2 border-amber-400/50 shadow-2xl hover:bg-slate-800 hover:border-amber-400 transition-all duration-200 group">
        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
        <span class="text-xs font-bold text-slate-200 group-hover:text-amber-300 tracking-wide">
            Demo Center ({{ ucfirst($currentRole) }})
        </span>
        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-white transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
    </button>
</div>
@endif
