/**
 * UPH Enterprise KRS War Cart Engine
 * Real-time Seat Quota Tracking, Client-side Timetable Clash Detection, Reactive SKS Meter, and Atomic Cart Updates.
 */

document.addEventListener('DOMContentLoaded', () => {
    const krsApp = document.getElementById('krs-war-app');
    if (!krsApp) return;

    const maxSks = parseInt(krsApp.dataset.maxSks || '24', 10);
    let currentSks = parseInt(krsApp.dataset.currentSks || '0', 10);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // State Elements
    const sksDisplay = document.getElementById('krs-current-sks-display');
    const sksProgressBar = document.getElementById('krs-sks-progress-bar');
    const sksPercentageText = document.getElementById('krs-sks-percentage');
    const sksWarningBanner = document.getElementById('krs-sks-warning-banner');

    function updateSksMeter() {
        if (sksDisplay) sksDisplay.textContent = currentSks;
        const percentage = Math.min(100, Math.round((currentSks / maxSks) * 100));

        if (sksProgressBar) {
            sksProgressBar.style.width = `${percentage}%`;
            if (percentage >= 100) {
                sksProgressBar.className = 'h-full bg-uph-crimson transition-all duration-300';
            } else if (percentage >= 80) {
                sksProgressBar.className = 'h-full bg-uph-gold transition-all duration-300';
            } else {
                sksProgressBar.className = 'h-full bg-uph-navy transition-all duration-300';
            }
        }

        if (sksPercentageText) sksPercentageText.textContent = `${percentage}% Terpakai`;

        if (sksWarningBanner) {
            if (currentSks > maxSks) {
                sksWarningBanner.classList.remove('hidden');
            } else {
                sksWarningBanner.classList.add('hidden');
            }
        }
    }

    // Helper: Client-side Time Overlap Check
    function isTimeClashing(startA, endA, startB, endB) {
        const toSeconds = (t) => {
            const parts = t.split(':').map(Number);
            return parts[0] * 3600 + parts[1] * 60;
        };
        const sA = toSeconds(startA);
        const eA = toSeconds(endA);
        const sB = toSeconds(startB);
        const eB = toSeconds(endB);

        return Math.max(sA, sB) < Math.min(eA, eB);
    }

    // Check schedule clash among selected rows on client-side
    function validateClientClash(candidateRow) {
        const candidateDay = candidateRow.dataset.day;
        const candidateStart = candidateRow.dataset.startTime;
        const candidateEnd = candidateRow.dataset.endTime;

        const selectedRows = document.querySelectorAll('.krs-course-row[data-enrolled="true"]');
        for (const row of selectedRows) {
            if (row === candidateRow) continue;
            if (row.dataset.day === candidateDay) {
                if (isTimeClashing(candidateStart, candidateEnd, row.dataset.startTime, row.dataset.endTime)) {
                    return {
                        clashedCourse: row.dataset.courseName,
                        clashedTime: `${row.dataset.dayName}, ${row.dataset.startTime} - ${row.dataset.endTime}`
                    };
                }
            }
        }
        return null;
    }

    // Add Course to KRS Cart
    async function addCourseToCart(button, row) {
        const sectionId = row.dataset.sectionId;
        const courseSks = parseInt(row.dataset.sks || '3', 10);

        // Pre-check 1: SKS Limit
        if (currentSks + courseSks > maxSks) {
            if (window.EnterpriseToast) {
                window.EnterpriseToast.error(`Batas maksimum SKS terlampaui (${maxSks} SKS). Kurangi mata kuliah lain terlebih dahulu.`, 'Batas SKS');
            }
            return;
        }

        // Pre-check 2: Client schedule clash
        const clash = validateClientClash(row);
        if (clash) {
            if (window.EnterpriseToast) {
                window.EnterpriseToast.error(`Jadwal bentrok dengan mata kuliah ${clash.clashedCourse} (${clash.clashedTime}).`, 'Jadwal Bentrok');
            }
            return;
        }

        // Optimistic button state
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengunci...';

        try {
            const response = await fetch('/api/krs/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ class_section_id: sectionId })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (window.EnterpriseToast) {
                    window.EnterpriseToast.error(result.message || 'Gagal menambahkan mata kuliah.', 'KRS War');
                }
                return;
            }

            // Success Update
            currentSks = result.current_sks;
            updateSksMeter();

            row.dataset.enrolled = 'true';
            row.classList.add('bg-blue-50/50');

            // Update remaining seats badge
            const seatsBadge = row.querySelector('.krs-remaining-seats-badge');
            if (seatsBadge) {
                seatsBadge.textContent = `${result.remaining_seats} Kursi Sisa`;
                if (result.remaining_seats <= 3) {
                    seatsBadge.className = 'krs-remaining-seats-badge px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-red-100 text-uph-crimson';
                }
            }

            button.className = 'krs-toggle-btn px-3 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-uph-crimson font-bold text-xs transition-colors';
            button.innerHTML = '<i class="fa-solid fa-trash-can mr-1"></i> Batalkan';
            button.dataset.action = 'remove';
            button.disabled = false;

            if (window.EnterpriseToast) {
                window.EnterpriseToast.success(result.message, 'Kursi Berhasil Dikunci');
            }

            // Refresh cart section dynamically or trigger custom event
            window.dispatchEvent(new CustomEvent('krs-cart-updated', { detail: result }));
        } catch (error) {
            console.error('[KRS Add Error]:', error);
            button.disabled = false;
            button.innerHTML = originalText;
            if (window.EnterpriseToast) {
                window.EnterpriseToast.error('Terjadi kesalahan jaringan saat mengunci kursi.', 'Koneksi Gagal');
            }
        }
    }

    // Remove Course from KRS Cart
    async function removeCourseFromCart(button, row) {
        const sectionId = row.dataset.sectionId;

        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Melepas...';

        try {
            const response = await fetch('/api/krs/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ class_section_id: sectionId })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (window.EnterpriseToast) {
                    window.EnterpriseToast.error(result.message || 'Gagal membatalkan mata kuliah.', 'KRS War');
                }
                return;
            }

            currentSks = result.current_sks;
            updateSksMeter();

            row.dataset.enrolled = 'false';
            row.classList.remove('bg-blue-50/50');

            const seatsBadge = row.querySelector('.krs-remaining-seats-badge');
            if (seatsBadge) {
                seatsBadge.textContent = `${result.remaining_seats} Kursi Sisa`;
                seatsBadge.className = 'krs-remaining-seats-badge px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-slate-100 text-slate-700';
            }

            button.className = 'krs-toggle-btn px-3 py-1.5 rounded-lg bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs transition-colors shadow';
            button.innerHTML = '<i class="fa-solid fa-plus mr-1"></i> Pilih Kelas';
            button.dataset.action = 'add';
            button.disabled = false;

            if (window.EnterpriseToast) {
                window.EnterpriseToast.info(result.message, 'Kursi Dilepaskan');
            }

            window.dispatchEvent(new CustomEvent('krs-cart-updated', { detail: result }));
        } catch (error) {
            console.error('[KRS Remove Error]:', error);
            button.disabled = false;
            button.innerHTML = originalText;
            if (window.EnterpriseToast) {
                window.EnterpriseToast.error('Gagal melepaskan mata kuliah dari KRS.', 'Koneksi Gagal');
            }
        }
    }

    // Delegate Click Events on Add/Remove Buttons
    document.querySelectorAll('.krs-toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.krs-course-row');
            if (!row) return;

            const action = btn.dataset.action;
            if (action === 'add') {
                addCourseToCart(btn, row);
            } else if (action === 'remove') {
                removeCourseFromCart(btn, row);
            }
        });
    });

    // Course Search & Day Filter
    const searchInput = document.getElementById('krs-course-search');
    const dayFilter = document.getElementById('krs-day-filter');

    function filterCourses() {
        const query = (searchInput?.value || '').toLowerCase();
        const selectedDay = dayFilter?.value || 'all';

        document.querySelectorAll('.krs-course-row').forEach(row => {
            const courseName = (row.dataset.courseName || '').toLowerCase();
            const courseCode = (row.dataset.courseCode || '').toLowerCase();
            const lecturerName = (row.dataset.lecturer || '').toLowerCase();
            const day = row.dataset.day;

            const matchesQuery = courseName.includes(query) || courseCode.includes(query) || lecturerName.includes(query);
            const matchesDay = selectedDay === 'all' || day === selectedDay;

            if (matchesQuery && matchesDay) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterCourses);
    if (dayFilter) dayFilter.addEventListener('change', filterCourses);

    // Initial Meter Computation
    updateSksMeter();
});
