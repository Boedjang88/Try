@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{
    qrModalOpen: false,
    bapModalOpen: false,
    activeSessionId: '{{ $activeSession->id ?? '' }}',
    activeSessionNumber: {{ $activeSession->session_number ?? 1 }},
    activeSessionTitle: '{{ addslashes($activeSession->title ?? 'Sesi Perkuliahan') }}',
    activeCourseName: '{{ addslashes($activeSection->course->name_id ?? 'Matakuliah') }}',
    qrToken: '',
    qrExpiresIn: 15,
    qrInterval: null,
    qrCountdownInterval: null,
    bapSummary: '{{ addslashes($activeSession->bap_summary ?? '') }}',
    actualDate: '{{ $activeSession->actual_date ? $activeSession->actual_date->toDateString() : date('Y-m-d') }}',
    isSavingBap: false,
    copiedNotification: false,
    livePresentCount: {{ $activeSession ? $activeSession->attendances->where('attendance_status', 'present')->count() : 0 }},
    isSimulating: false,

    openQrModal(sessionId, sessionNum, title) {
        this.activeSessionId = sessionId;
        this.activeSessionNumber = sessionNum;
        this.activeSessionTitle = title;
        this.qrModalOpen = true;
        this.fetchQrToken();

        // 15s rotating token loop
        if (this.qrInterval) clearInterval(this.qrInterval);
        this.qrInterval = setInterval(() => {
            this.fetchQrToken();
        }, 15000);

        // 1s visual countdown
        if (this.qrCountdownInterval) clearInterval(this.qrCountdownInterval);
        this.qrCountdownInterval = setInterval(() => {
            if (this.qrExpiresIn > 1) {
                this.qrExpiresIn--;
            }
        }, 1000);
    },

    closeQrModal() {
        this.qrModalOpen = false;
        if (this.qrInterval) clearInterval(this.qrInterval);
        if (this.qrCountdownInterval) clearInterval(this.qrCountdownInterval);
    },

    async fetchQrToken() {
        try {
            const res = await fetch(`/portal/lecturer/sessions/${this.activeSessionId}/qr-token`);
            const data = await res.json();
            if (data.success) {
                this.qrToken = data.token;
                this.qrExpiresIn = 15;
            }
        } catch (e) {
            console.error('QR fetch error', e);
        }
    },

    copyToken() {
        if (!this.qrToken) return;
        navigator.clipboard.writeText(this.qrToken);
        this.copiedNotification = true;
        setTimeout(() => this.copiedNotification = false, 2500);
    },

    async simulateStudentScan() {
        this.isSimulating = true;
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            const res = await fetch(`/portal/lecturer/sessions/${this.activeSessionId}/simulate-checkin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
            const data = await res.json();
            if (data.success) {
                this.livePresentCount++;
                if (window.EnterpriseToast) {
                    window.EnterpriseToast.success(data.message, 'Presensi Terverifikasi');
                }
            } else {
                if (window.EnterpriseToast) window.EnterpriseToast.error(data.message);
            }
        } catch (e) {
            alert('Gagal menyimulasikan presensi mahasiswa.');
        } finally {
            this.isSimulating = false;
        }
    },

    openBapModal(sessionId, sessionNum, summary, date) {
        this.activeSessionId = sessionId;
        this.activeSessionNumber = sessionNum;
        this.bapSummary = summary || '';
        this.actualDate = date || '{{ date('Y-m-d') }}';
        this.bapModalOpen = true;
    },

    async submitBap() {
        if (!this.bapSummary || this.bapSummary.trim().length < 10) {
            alert('Uraian materi / BAP wajib diisi minimal 10 karakter.');
            return;
        }

        this.isSavingBap = true;
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            const res = await fetch(`/portal/lecturer/sessions/${this.activeSessionId}/bap`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    bap_summary: this.bapSummary.trim(),
                    actual_date: this.actualDate
                })
            });
            const data = await res.json();
            if (data.success) {
                this.bapModalOpen = false;
                if (window.EnterpriseToast) window.EnterpriseToast.success(data.message, 'BAP Ditandatangani');
                setTimeout(() => window.location.reload(), 800);
            }
        } catch (e) {
            alert('Gagal mengesahkan BAP.');
        } finally {
            this.isSavingBap = false;
        }
    }
}">

    <!-- Top Command Banner -->
    <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-extrabold uppercase tracking-widest">
                    Command Center Perkuliahan 16-Sesi
                </span>
                <span class="text-xs text-slate-300 font-mono">&bull; Periode: {{ $activePeriod->name ?? '2026/2027' }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-white">Presensi QR Dinamis 15s & BAP Digital</h1>
            <p class="text-xs text-slate-300 mt-1">
                Dosen: <strong class="text-uph-gold">{{ $user->full_name }}</strong> &bull; 
                NIDN: <span class="font-mono text-white">{{ $lecturer->nidn ?? '-' }}</span> &bull; 
                Kelas Aktif: <span class="text-white font-semibold">{{ $activeSection->course->name_id ?? '-' }} (Seksi {{ $activeSection->section_code ?? '-' }})</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/portal/lecturer/dashboard" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors flex items-center gap-2 border border-white/10">
                <i class="fa-solid fa-arrow-left"></i> Dasbor Dosen
            </a>
            <a href="/grading/{{ $activeSection->id ?? '' }}" class="px-4 py-2 rounded-xl bg-uph-gold hover:bg-uph-gold-warm text-uph-navy-dark font-extrabold text-xs shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-table-cells"></i> Spreadsheet Nilai
            </a>
        </div>
    </div>

    <!-- Class Section Selector Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih Kelas Mengajar:</span>
            <div class="flex flex-wrap gap-2">
                @foreach($classSections as $cs)
                <a href="/portal/lecturer/sessions?section_id={{ $cs->id }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $activeSection && $activeSection->id === $cs->id ? 'bg-uph-navy text-uph-gold shadow' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    {{ $cs->course->code }} - Seksi {{ $cs->section_code }}
                </a>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs font-mono">
            <span class="text-slate-500">Kemajuan BAP:</span>
            <div class="w-36 bg-slate-200 rounded-full h-2.5 overflow-hidden">
                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $bapProgressPercent }}%"></div>
            </div>
            <span class="font-bold text-emerald-600">{{ $signedBapCount }} / {{ $totalSessions }} Sesi Disahkan</span>
        </div>
    </div>

    <!-- Visual Weekly Time-Grid Timetable for Lecturer -->
    <x-weekly-timetable-grid :items="$classSections" title="Jadwal Mengajar Mingguan (Lecturer Timetable)" subtitle="Peta alokasi jadwal mengajar kelas perkuliahan Anda" />

    <!-- 16-Week Sessions Grid -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 bg-surface-slate border-b border-slate-200 flex justify-between items-center">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Silabus Pertemuan 1 s.d. 16</h2>
                <p class="text-xs text-slate-500">Klik "Buka Presensi QR" untuk memproyeksikan QR code di layar kelas. Mahasiswa scan via aplikasi.</p>
            </div>
            <span class="text-xs font-mono font-bold text-uph-navy bg-uph-navy/10 px-3 py-1 rounded-lg">
                Jadwal: {{ $activeSection->day_name ?? 'Senin' }}, {{ substr($activeSection->start_time ?? '08:00', 0, 5) }} - {{ substr($activeSection->end_time ?? '10:30', 0, 5) }} WIB (Ruang: {{ $activeSection->room->code ?? 'UPH' }})
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-3 px-4 text-center">Sesi</th>
                        <th class="py-3 px-4">Pokok Bahasan / Materi Kuliah</th>
                        <th class="py-3 px-3 text-center">Tipe Pertemuan</th>
                        <th class="py-3 px-3 text-center">Tanggal Pelaksanaan</th>
                        <th class="py-3 px-3 text-center">Kehadiran Mahasiswa</th>
                        <th class="py-3 px-3 text-center">Status BAP</th>
                        <th class="py-3 px-4 text-center">Aksi Sesi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lectureSessions as $session)
                    @php
                        $isSigned = !empty($session->lecturer_signed_at);
                        $presentCount = $session->attendances->where('attendance_status', 'present')->count();
                        $enrolledCount = $activeSection->krsItems->count() ?: 1;
                        $attendPercent = round(($presentCount / $enrolledCount) * 100);
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors {{ $activeSession && $activeSession->id === $session->id ? 'bg-blue-50/40' : '' }}">
                        <td class="py-3.5 px-4 text-center font-mono font-black text-uph-navy text-sm">
                            #{{ $session->session_number }}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $session->title }}</span>
                            @if($session->bap_summary)
                                <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1 italic">
                                    BAP: "{{ $session->bap_summary }}"
                                </p>
                            @else
                                <span class="text-[10px] text-amber-600 font-semibold italic">Belum ada catatan BAP</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            @if($session->session_type === 'uts')
                                <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-red-100 text-uph-crimson uppercase">Ujian Tengah Semester</span>
                            @elseif($session->session_type === 'uas')
                                <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-purple-100 text-purple-800 uppercase">Ujian Akhir Semester</span>
                            @else
                                <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-blue-100 text-uph-navy uppercase">Kuliah Reguler</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-3 text-center font-mono text-slate-700">
                            {{ $session->actual_date ? $session->actual_date->format('d/m/Y') : ($session->planned_date ? $session->planned_date->format('d/m/Y') : '-') }}
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            <div class="space-y-1">
                                <span class="font-mono font-bold text-xs {{ $presentCount > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                    {{ $presentCount }} / {{ $enrolledCount }} Hadir
                                </span>
                                <div class="w-16 h-1.5 mx-auto rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full bg-emerald-500" style="width: {{ $attendPercent }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-3 text-center">
                            @if($isSigned)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Disahkan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-clock text-[9px]"></i> Belum BAP
                                </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" 
                                        @click="openQrModal('{{ $session->id }}', {{ $session->session_number }}, '{{ addslashes($session->title) }}')"
                                        class="px-3 py-1.5 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-uph-gold font-bold text-xs shadow transition-colors flex items-center gap-1.5">
                                    <i class="fa-solid fa-qrcode"></i> Buka Presensi QR
                                </button>

                                <button type="button" 
                                        @click="openBapModal('{{ $session->id }}', {{ $session->session_number }}, '{{ addslashes($session->bap_summary ?? '') }}', '{{ $session->actual_date ? $session->actual_date->toDateString() : date('Y-m-d') }}')"
                                        class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-signature"></i> {{ $isSigned ? 'Edit BAP' : 'Isi BAP' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            Belum ada jadwal sesi perkuliahan dibuat untuk kelas ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 15-Second Rotating Dynamic QR Attendance Modal (Loop 3) -->
    <div x-show="qrModalOpen" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-8 shadow-2xl space-y-6 text-center border-4 border-uph-gold/30" @click.away="closeQrModal()">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b pb-4">
                <div class="text-left">
                    <span class="px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase tracking-widest">
                        Live Attendance Command Center
                    </span>
                    <h3 class="font-display font-black text-xl text-uph-navy mt-1">
                        Presensi Sesi #<span x-text="activeSessionNumber"></span>: <span x-text="activeCourseName"></span>
                    </h3>
                </div>
                <button type="button" @click="closeQrModal()" class="h-9 w-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Rotating Timer Countdown -->
            <div class="flex items-center justify-center gap-3">
                <div class="h-10 w-10 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin flex items-center justify-center"></div>
                <div class="text-left">
                    <p class="text-xs text-slate-500 font-medium">QR Berotasi Otomatis Demi Keamanan (Anti Joki GPS):</p>
                    <p class="font-mono font-black text-lg text-emerald-600">
                        Kedaluwarsa dalam <span x-text="qrExpiresIn"></span> detik
                    </p>
                </div>
            </div>

            <!-- Authentic Simulated SVG QR Code Box -->
            <div class="mx-auto w-64 h-64 p-4 rounded-2xl bg-white border-2 border-slate-200 shadow-inner flex flex-col items-center justify-center relative group">
                <!-- Center University Crest Watermark -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-10">
                    <span class="text-7xl font-serif font-black text-uph-navy">UPH</span>
                </div>

                <!-- Live Dynamic SVG Pattern -->
                <svg class="w-56 h-56" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <rect width="200" height="200" fill="white" />
                    <!-- QR Corner Positioning Squares -->
                    <rect x="10" y="10" width="50" height="50" fill="#00205B" rx="8" />
                    <rect x="18" y="18" width="34" height="34" fill="white" rx="4" />
                    <rect x="26" y="26" width="18" height="18" fill="#C8102E" rx="2" />

                    <rect x="140" y="10" width="50" height="50" fill="#00205B" rx="8" />
                    <rect x="148" y="18" width="34" height="34" fill="white" rx="4" />
                    <rect x="156" y="26" width="18" height="18" fill="#C8102E" rx="2" />

                    <rect x="10" y="140" width="50" height="50" fill="#00205B" rx="8" />
                    <rect x="18" y="148" width="34" height="34" fill="white" rx="4" />
                    <rect x="26" y="156" width="18" height="18" fill="#C8102E" rx="2" />

                    <!-- Decorative Pseudo Data Matrix Bars -->
                    <g fill="#00205B">
                        <rect x="70" y="20" width="15" height="15" />
                        <rect x="95" y="20" width="10" height="15" />
                        <rect x="115" y="20" width="15" height="10" />
                        <rect x="70" y="45" width="20" height="10" />
                        <rect x="100" y="45" width="10" height="20" />
                        <rect x="120" y="40" width="10" height="15" />
                        <rect x="20" y="70" width="15" height="15" />
                        <rect x="45" y="75" width="15" height="10" />
                        <rect x="70" y="70" width="60" height="60" rx="6" fill="#D4AF37" fill-opacity="0.25" />
                        <circle cx="100" cy="100" r="14" fill="#00205B" />
                        <rect x="140" y="70" width="20" height="15" />
                        <rect x="170" y="75" width="15" height="25" />
                        <rect x="20" y="100" width="25" height="10" />
                        <rect x="140" y="95" width="15" height="20" />
                        <rect x="165" y="110" width="20" height="15" />
                        <rect x="70" y="140" width="15" height="25" />
                        <rect x="95" y="145" width="25" height="10" />
                        <rect x="130" y="140" width="15" height="15" />
                        <rect x="155" y="140" width="25" height="20" />
                        <rect x="70" y="175" width="25" height="15" />
                        <rect x="105" y="165" width="20" height="25" />
                        <rect x="135" y="165" width="15" height="15" />
                        <rect x="160" y="170" width="20" height="20" />
                    </g>
                </svg>
            </div>

            <!-- Token Text & Copy / Cross-Account Helper Box -->
            <div class="space-y-3">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                    <div class="text-left font-mono">
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest block font-sans">Token Aktif (HMAC-SHA256):</span>
                        <span class="font-bold text-uph-navy text-sm tracking-wider" x-text="qrToken || 'Memuat token...'"></span>
                    </div>
                    <button type="button" 
                            @click="copyToken()" 
                            class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-copy"></i> Salin Token
                    </button>
                </div>

                <template x-if="copiedNotification">
                    <p class="text-xs text-emerald-600 font-bold flex items-center justify-center gap-1 animate-pulse">
                        <i class="fa-solid fa-check"></i> Token disalin ke clipboard! Buka akun Mahasiswa untuk input presensi.
                    </p>
                </template>

                <!-- Loop 3 Live Cross-Account Instant Simulation Button -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
                    <div class="text-left">
                        <span class="font-bold text-uph-navy block">Demo Testing Helper (Loop 3 Cross-Account):</span>
                        <span class="text-[11px] text-slate-600">Verifikasikan kehadiran Christian Jonathan langsung atau salin token di atas.</span>
                    </div>

                    <button type="button" 
                            @click="simulateStudentScan()" 
                            :disabled="isSimulating"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition-all shrink-0 flex items-center gap-2">
                        <span x-show="!isSimulating"><i class="fa-solid fa-mobile-screen-button"></i> Simulasikan Scan Mahasiswa</span>
                        <span x-show="isSimulating"><i class="fa-solid fa-spinner fa-spin"></i> Mencatat...</span>
                    </button>
                </div>

                <!-- Live Presensi Counter -->
                <div class="text-xs text-slate-500 font-mono">
                    Total Mahasiswa Masuk Ruang: <strong class="text-emerald-600 font-black text-sm" x-text="livePresentCount"></strong> Mahasiswa
                </div>
            </div>
        </div>
    </div>

    <!-- Digital BAP Form Modal -->
    <div x-show="bapModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="bapModalOpen = false">
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2 text-uph-navy font-display font-black text-base">
                    <i class="fa-solid fa-signature text-uph-gold"></i>
                    <span>Tandatangani BAP Sesi #<span x-text="activeSessionNumber"></span></span>
                </div>
                <button type="button" @click="bapModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Riil Pelaksanaan Kuliah</label>
                    <input type="date" x-model="actualDate" class="w-full p-2.5 text-xs border rounded-xl focus:ring-1 focus:ring-uph-navy outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Berita Acara Perkuliahan / Ringkasan Materi Sesi <span class="text-uph-crimson">*</span>
                    </label>
                    <textarea x-model="bapSummary" rows="4" 
                              placeholder="Uraikan pokok materi yang diajarkan, metode pembelajaran, dan penugasan yang diberikan..." 
                              class="w-full p-3 text-xs border rounded-xl focus:ring-1 focus:ring-uph-navy outline-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" @click="bapModalOpen = false" class="px-4 py-2 border rounded-xl text-slate-600 font-bold text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" 
                        @click="submitBap()" 
                        :disabled="isSavingBap"
                        class="px-5 py-2 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-uph-gold font-bold text-xs shadow flex items-center gap-2">
                    <span x-show="!isSavingBap"><i class="fa-solid fa-signature mr-1"></i> Sahkan & Tandatangani BAP</span>
                    <span x-show="isSavingBap"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
