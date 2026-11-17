/**
 * UPH Enterprise Monolith - Global JavaScript Engine
 * Bundled with Alpine.js, Enterprise Toast System, Ctrl+K Command Palette, and Secure Fetch API Helper.
 */

import Alpine from 'alpinejs';

// Expose Alpine on window
window.Alpine = Alpine;

/**
 * 1. Global Secure Fetch API Wrapper with Automatic CSRF & JSON Handling
 */
window.apiFetch = async function (url, options = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        ...(options.headers || {})
    };

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(options.body);
    }

    const defaultOptions = {
        method: 'GET',
        headers: headers,
        credentials: 'same-origin',
        ...options
    };

    try {
        const response = await fetch(url, defaultOptions);
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const errorMsg = data.message || `Request failed with HTTP ${response.status}`;
            window.EnterpriseToast.error(errorMsg, 'Kesalahan Jaringan');
            throw new Error(errorMsg);
        }

        return data;
    } catch (error) {
        console.error('[API Fetch Error]:', error);
        throw error;
    }
};

/**
 * 2. Enterprise Toast Notification Engine
 * Dispatches CustomEvents to Alpine reactive stack.
 */
window.EnterpriseToast = function ({ type = 'info', title = '', message = '', duration = 4000 }) {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { type, title, message, duration }
    }));
};

window.EnterpriseToast.success = function (message, title = 'Berhasil') {
    window.EnterpriseToast({ type: 'success', title, message, duration: 4000 });
};

window.EnterpriseToast.error = function (message, title = 'Kesalahan') {
    window.EnterpriseToast({ type: 'error', title, message, duration: 5000 });
};

window.EnterpriseToast.info = function (message, title = 'Informasi') {
    window.EnterpriseToast({ type: 'info', title, message, duration: 4000 });
};

window.EnterpriseToast.warning = function (message, title = 'Peringatan') {
    window.EnterpriseToast({ type: 'warning', title, message, duration: 4500 });
};

/**
 * 3. Global Command Palette (Ctrl+K) Controller Data Model for Alpine
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('commandPalette', () => ({
        isOpen: false,
        query: '',
        selectedIndex: 0,
        recentSearches: JSON.parse(localStorage.getItem('uph_recent_searches') || '[]'),
        
        staticItems: [
            { title: 'KRS War & Pemilihan Jadwal', url: '/krs', group: 'Akademik', icon: 'fa-calendar-check' },
            { title: 'Presensi QR Code 15 Detik', url: '/attendance/scan', group: 'Perkuliahan', icon: 'fa-qrcode' },
            { title: 'LMS Perkuliahan & Tugas 16-Sesi', url: '/lms', group: 'Perkuliahan', icon: 'fa-book-open' },
            { title: 'Spreadsheet Penilaian Nilai Huruf & OBE', url: '/grading', group: 'Dosen', icon: 'fa-table-cells' },
            { title: 'KHS & Transkrip Akademik (EWS)', url: '/khs', group: 'Akademik', icon: 'fa-file-invoice' },
            { title: 'Evaluasi Dosen Oleh Mahasiswa (EDOM)', url: '/edom', group: 'Akademik', icon: 'fa-clipboard-check' },
            { title: 'Tagihan UKT & Virtual Account', url: '/finance/invoices', group: 'Keuangan', icon: 'fa-credit-card' },
            { title: 'Skripsi & Logbook Bimbingan', url: '/thesis', group: 'Tugas Akhir', icon: 'fa-graduation-cap' },
            { title: 'Verifikasi Yudisium & SKPI Digital', url: '/yudisium', group: 'Kelulusan', icon: 'fa-award' },
            { title: 'Pemetaan Kurikulum OBE, CPL & CPMK', url: '/curriculum/obe', group: 'Kaprodi', icon: 'fa-diagram-project' },
            { title: 'Jadwal Kuliah & Deteksi Bentrok', url: '/timetable', group: 'BAAK', icon: 'fa-clock' },
            { title: 'PMB & Ujian CBT Online', url: '/pmb/cbt', group: 'Admissions', icon: 'fa-laptop-code' },
            { title: 'Sinkronisasi PDDIKTI Neo Feeder', url: '/pddikti/sync', group: 'Admin', icon: 'fa-cloud-arrow-up' },
            { title: 'Dashboard Eksekutif Rektorat (KPI)', url: '/rector/dashboard', group: 'Rektorat', icon: 'fa-chart-pie' }
        ],

        get results() {
            if (!this.query.trim()) {
                return this.staticItems.slice(0, 8);
            }
            const q = this.query.toLowerCase();
            return this.staticItems.filter(item => 
                item.title.toLowerCase().includes(q) || 
                item.group.toLowerCase().includes(q)
            );
        },

        open() {
            this.isOpen = true;
            this.query = '';
            this.selectedIndex = 0;
            this.$nextTick(() => {
                const input = this.$el.querySelector('input');
                if (input) input.focus();
            });
        },

        close() {
            this.isOpen = false;
        },

        navigate(direction) {
            const count = this.results.length;
            if (count === 0) return;
            if (direction === 'down') {
                this.selectedIndex = (this.selectedIndex + 1) % count;
            } else if (direction === 'up') {
                this.selectedIndex = (this.selectedIndex - 1 + count) % count;
            }
        },

        selectCurrent() {
            const item = this.results[this.selectedIndex];
            if (item) {
                this.saveRecent(item);
                window.location.href = item.url;
            }
        },

        saveRecent(item) {
            let recent = this.recentSearches.filter(i => i.url !== item.url);
            recent.unshift(item);
            if (recent.length > 5) recent = recent.slice(0, 5);
            this.recentSearches = recent;
            localStorage.setItem('uph_recent_searches', JSON.stringify(recent));
        }
    }));
});

// Start Alpine
Alpine.start();
console.log('%c[UPH SIAKAD]%c Global Enterprise JS Engine Initialized', 'color:#D4AF37;font-weight:bold;background:#00205B;padding:2px 6px;border-radius:3px;', 'color:#10B981;font-weight:semibold;');
