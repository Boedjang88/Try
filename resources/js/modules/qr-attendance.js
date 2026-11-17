/**
 * UPH Enterprise QR Attendance & Geolocation Engine
 * 15-Second Rotating Signed Token Generator, Live Roster Poller, and HTML5 Haversine Distance Geofence Validator.
 */

document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // 1. LECTURER MODE: 15-Second Rotating QR & Live Roster Poller
    // -------------------------------------------------------------
    const lecturerApp = document.getElementById('qr-lecturer-app');
    if (lecturerApp) {
        const sessionId = lecturerApp.dataset.sessionId;
        const qrCanvas = document.getElementById('qr-code-canvas');
        const tokenDisplay = document.getElementById('qr-token-string');
        const countdownBar = document.getElementById('qr-countdown-bar');
        const countdownText = document.getElementById('qr-countdown-text');
        const liveRosterList = document.getElementById('live-roster-list');
        const liveCountDisplay = document.getElementById('live-present-count');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let remainingSeconds = 15;
        let tokenFetchTimer = null;
        let countdownTimer = null;

        async function fetchFreshQrToken() {
            try {
                const response = await fetch(`/api/attendance/session/${sessionId}/qr-token`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.success) {
                    if (tokenDisplay) tokenDisplay.textContent = data.token;
                    // Render Visual QR Code representation or external QR code
                    renderQrCode(data.token);
                    startCountdown(15);
                }
            } catch (e) {
                console.error('[QR Token Error]:', e);
            }
        }

        function renderQrCode(token) {
            if (!qrCanvas) return;
            // Generate visual QR using QuickChart API or SVG
            qrCanvas.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=${encodeURIComponent(token)}&color=00205B" alt="QR Code Presensi" class="mx-auto rounded-xl shadow-lg border-4 border-uph-gold">`;
        }

        function startCountdown(duration) {
            remainingSeconds = duration;
            clearInterval(countdownTimer);

            countdownTimer = setInterval(() => {
                remainingSeconds--;
                if (countdownText) countdownText.textContent = `${remainingSeconds}s`;
                if (countdownBar) {
                    const pct = Math.round((remainingSeconds / 15) * 100);
                    countdownBar.style.width = `${pct}%`;
                    if (remainingSeconds <= 4) {
                        countdownBar.className = 'h-full bg-uph-crimson transition-all duration-300';
                    } else {
                        countdownBar.className = 'h-full bg-uph-gold transition-all duration-300';
                    }
                }

                if (remainingSeconds <= 0) {
                    clearInterval(countdownTimer);
                    fetchFreshQrToken();
                }
            }, 1000);
        }

        // Live Roster Poller (every 4 seconds)
        async function pollLiveRoster() {
            try {
                const response = await fetch(`/api/attendance/session/${sessionId}/roster`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.success && liveRosterList) {
                    if (liveCountDisplay) liveCountDisplay.textContent = data.present_count;
                    
                    if (data.attendances.length === 0) {
                        liveRosterList.innerHTML = '<div class="py-8 text-center text-slate-400 text-xs">Belum ada mahasiswa yang memindai QR Code.</div>';
                        return;
                    }

                    liveRosterList.innerHTML = data.attendances.map(a => `
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs animate-fadeIn">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <div>
                                    <span class="font-bold text-slate-800">${a.name}</span>
                                    <span class="font-mono text-[11px] text-slate-400 block">${a.nim}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">${a.status}</span>
                                <span class="text-[10px] text-slate-400 font-mono block mt-0.5">${a.scanned_at}</span>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (e) {
                console.error('[Roster Poll Error]:', e);
            }
        }

        // Initialize Lecturer Pollers
        fetchFreshQrToken();
        const rosterInterval = setInterval(pollLiveRoster, 4000);

        window.addEventListener('beforeunload', () => {
            clearInterval(countdownTimer);
            clearInterval(rosterInterval);
        });
    }

    // -------------------------------------------------------------
    // 2. STUDENT MODE: GPS Geolocation & QR Verification
    // -------------------------------------------------------------
    const studentScanApp = document.getElementById('qr-student-app');
    if (studentScanApp) {
        const gpsStatusBadge = document.getElementById('student-gps-status');
        const latDisplay = document.getElementById('student-lat-display');
        const lonDisplay = document.getElementById('student-lon-display');
        const accuracyDisplay = document.getElementById('student-accuracy-display');
        const tokenInput = document.getElementById('student-token-input');
        const verifyBtn = document.getElementById('student-verify-btn');
        const scanResultBox = document.getElementById('student-scan-result');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let currentCoords = null;

        // Obtain Geolocation
        function requestGeolocation() {
            if (!navigator.geolocation) {
                if (gpsStatusBadge) {
                    gpsStatusBadge.className = 'px-3 py-1 rounded-full bg-red-100 text-uph-crimson text-xs font-bold';
                    gpsStatusBadge.textContent = 'GPS Tidak Didukung Browser';
                }
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentCoords = {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: Math.round(pos.coords.accuracy)
                    };

                    if (gpsStatusBadge) {
                        gpsStatusBadge.className = 'px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold';
                        gpsStatusBadge.textContent = `GPS Terkunci (Akurasi: ±${currentCoords.accuracy}m)`;
                    }
                    if (latDisplay) latDisplay.textContent = currentCoords.latitude.toFixed(6);
                    if (lonDisplay) lonDisplay.textContent = currentCoords.longitude.toFixed(6);
                    if (accuracyDisplay) accuracyDisplay.textContent = `${currentCoords.accuracy} meter`;

                    if (verifyBtn) verifyBtn.disabled = false;
                },
                (err) => {
                    console.warn('[GPS Error]:', err.message);
                    if (gpsStatusBadge) {
                        gpsStatusBadge.className = 'px-3 py-1 rounded-full bg-red-100 text-uph-crimson text-xs font-bold';
                        gpsStatusBadge.textContent = 'Izin Lokasi (GPS) Ditolak';
                    }
                    if (window.EnterpriseToast) {
                        window.EnterpriseToast.error('Aktifkan izin lokasi browser untuk memverifikasi geofence presensi kelas UPH.', 'GPS Diperlukan');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        requestGeolocation();

        // Submit Scan
        if (verifyBtn) {
            verifyBtn.addEventListener('click', async () => {
                const token = tokenInput?.value?.trim();
                if (!token) {
                    if (window.EnterpriseToast) window.EnterpriseToast.warning('Ketik atau pindai token QR dari layar dosen.');
                    return;
                }

                if (!currentCoords) {
                    requestGeolocation();
                    if (window.EnterpriseToast) window.EnterpriseToast.error('Menunggu koordinat GPS... Pastikan izin lokasi aktif.');
                    return;
                }

                verifyBtn.disabled = true;
                const originalText = verifyBtn.innerHTML;
                verifyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Memvalidasi Geofence...';

                try {
                    const response = await fetch('/api/attendance/scan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            qr_token: token,
                            latitude: currentCoords.latitude,
                            longitude: currentCoords.longitude
                        })
                    });

                    const data = await response.json();
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = originalText;

                    if (scanResultBox) {
                        scanResultBox.classList.remove('hidden');
                        if (data.success) {
                            scanResultBox.className = 'p-6 rounded-2xl bg-emerald-50 border-2 border-emerald-500 text-emerald-950 space-y-2';
                            scanResultBox.innerHTML = `
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0"><i class="fa-solid fa-check"></i></div>
                                    <div>
                                        <h4 class="font-display font-bold text-sm text-emerald-900">${data.message}</h4>
                                        <p class="text-xs text-emerald-700">Waktu Presensi: ${data.scanned_at} WIB &bull; Jarak: ${data.distance}m dari pusat ruang kuliah.</p>
                                    </div>
                                </div>
                            `;
                            if (window.EnterpriseToast) window.EnterpriseToast.success(data.message, 'Presensi Berhasil');
                        } else {
                            scanResultBox.className = 'p-6 rounded-2xl bg-red-50 border-2 border-uph-crimson text-red-950 space-y-2';
                            scanResultBox.innerHTML = `
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-uph-crimson text-white flex items-center justify-center text-xl shrink-0"><i class="fa-solid fa-xmark"></i></div>
                                    <div>
                                        <h4 class="font-display font-bold text-sm text-red-900">${data.message}</h4>
                                        <p class="text-xs text-red-700">Kode Error: ${data.code || 'VALIDATION_FAILED'}</p>
                                    </div>
                                </div>
                            `;
                            if (window.EnterpriseToast) window.EnterpriseToast.error(data.message, 'Presensi Gagal');
                        }
                    }
                } catch (e) {
                    console.error('[Scan Error]:', e);
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = originalText;
                    if (window.EnterpriseToast) window.EnterpriseToast.error('Gagal terhubung ke server.');
                }
            });
        }
    }
});
