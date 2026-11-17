/**
 * UPH Enterprise CBT Engine (Online Admissions Examination)
 * Features: High-precision Countdown Timer, LocalStorage Persistence, Anti-Tab-Switch Listener, Keyboard Navigation, and Instant Scoring.
 */

document.addEventListener('DOMContentLoaded', () => {
    const examContainer = document.getElementById('cbt-exam-app');
    if (!examContainer) return;

    const applicantId = examContainer.dataset.applicantId;
    const initialDuration = parseInt(examContainer.dataset.duration || '1800', 10);
    const storageKey = `uph_cbt_answers_${applicantId}`;
    const timerStorageKey = `uph_cbt_time_${applicantId}`;

    const appState = {
        currentIndex: 0,
        totalQuestions: 0,
        answers: JSON.parse(localStorage.getItem(storageKey) || '{}'),
        remainingSeconds: parseInt(localStorage.getItem(timerStorageKey) || initialDuration.toString(), 10),
        tabSwitchCount: 0,
        maxTabSwitches: 3,
        isSubmitting: false,
    };

    const questionCards = document.querySelectorAll('.cbt-question-card');
    appState.totalQuestions = questionCards.length;

    // Elements
    const timerDisplay = document.getElementById('cbt-timer-display');
    const timerContainer = document.getElementById('cbt-timer-container');
    const prevBtn = document.getElementById('cbt-prev-btn');
    const nextBtn = document.getElementById('cbt-next-btn');
    const submitBtn = document.getElementById('cbt-submit-btn');
    const paletteButtons = document.querySelectorAll('.cbt-palette-item');
    const warningModal = document.getElementById('cbt-tab-warning-modal');
    const warningCountText = document.getElementById('cbt-warning-count-text');
    const modalDismissBtn = document.getElementById('cbt-warning-dismiss');

    // 1. Timer Worker
    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function updateTimer() {
        if (appState.remainingSeconds <= 0) {
            timerDisplay.textContent = '00:00';
            autoSubmitExam('Waktu Ujian Telah Habis. Jawaban Anda disimpan dan dikirim otomatis.');
            return;
        }

        appState.remainingSeconds--;
        localStorage.setItem(timerStorageKey, appState.remainingSeconds.toString());
        timerDisplay.textContent = formatTime(appState.remainingSeconds);

        // Visual Urgency Trigger (< 5 minutes)
        if (appState.remainingSeconds <= 300) {
            timerContainer.classList.remove('bg-uph-navy', 'text-white');
            timerContainer.classList.add('bg-uph-crimson', 'text-white', 'animate-pulse');
        }
    }

    const timerInterval = setInterval(updateTimer, 1000);
    timerDisplay.textContent = formatTime(appState.remainingSeconds);

    // 2. Question View Management
    function showQuestion(index) {
        if (index < 0 || index >= appState.totalQuestions) return;

        questionCards.forEach((card, idx) => {
            if (idx === index) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });

        appState.currentIndex = index;

        // Button state toggles
        if (prevBtn) prevBtn.disabled = index === 0;
        if (nextBtn) {
            if (index === appState.totalQuestions - 1) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        updatePaletteVisuals();
    }

    function updatePaletteVisuals() {
        paletteButtons.forEach((btn, idx) => {
            const qId = btn.dataset.questionId;
            const isAnswered = Boolean(appState.answers[qId]);
            const isCurrent = idx === appState.currentIndex;

            btn.className = 'cbt-palette-item h-10 w-10 rounded-lg text-xs font-bold transition-all flex items-center justify-center border ';

            if (isCurrent) {
                btn.className += 'ring-2 ring-uph-gold ring-offset-2 bg-uph-navy text-uph-gold border-uph-gold shadow';
            } else if (isAnswered) {
                btn.className += 'bg-emerald-600 text-white border-emerald-700';
            } else {
                btn.className += 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100';
            }
        });

        // Answered counter text
        const answeredCount = Object.keys(appState.answers).length;
        const progressIndicator = document.getElementById('cbt-progress-text');
        if (progressIndicator) {
            progressIndicator.textContent = `${answeredCount} dari ${appState.totalQuestions} Soal Terjawab`;
        }
    }

    // 3. Option Selection Handler
    function selectOption(questionId, optionKey) {
        appState.answers[questionId] = optionKey;
        localStorage.setItem(storageKey, JSON.stringify(appState.answers));

        const card = document.querySelector(`.cbt-question-card[data-question-id="${questionId}"]`);
        if (card) {
            const optionBtns = card.querySelectorAll('.cbt-option-btn');
            optionBtns.forEach(btn => {
                if (btn.dataset.option === optionKey) {
                    btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-800');
                    btn.classList.add('border-uph-navy', 'bg-uph-navy/5', 'text-uph-navy', 'ring-2', 'ring-uph-navy');
                } else {
                    btn.classList.remove('border-uph-navy', 'bg-uph-navy/5', 'text-uph-navy', 'ring-2', 'ring-uph-navy');
                    btn.classList.add('border-slate-200', 'bg-white', 'text-slate-800');
                }
            });
        }

        updatePaletteVisuals();
    }

    // Restore saved answers to DOM
    Object.keys(appState.answers).forEach(qId => {
        selectOption(qId, appState.answers[qId]);
    });

    // 4. Anti-Tab-Switch & Integrity Monitoring
    function handleCheatingAttempt() {
        if (appState.isSubmitting) return;

        appState.tabSwitchCount++;
        if (warningCountText) {
            warningCountText.textContent = `Peringatan ${appState.tabSwitchCount} dari ${appState.maxTabSwitches}`;
        }

        if (warningModal) warningModal.classList.remove('hidden');

        if (window.EnterpriseToast) {
            window.EnterpriseToast.warning(
                `Dilarang berpindah tab browser selama ujian berlangsung! (${appState.tabSwitchCount}/${appState.maxTabSwitches})`,
                'Pelanggaran Integritas Ujian'
            );
        }

        if (appState.tabSwitchCount >= appState.maxTabSwitches) {
            autoSubmitExam('Ujian dihentikan otomatis karena terdeteksi berpindah tab melebihi batas toleransi.');
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            handleCheatingAttempt();
        }
    });

    window.addEventListener('blur', () => {
        handleCheatingAttempt();
    });

    if (modalDismissBtn) {
        modalDismissBtn.addEventListener('click', () => {
            if (warningModal) warningModal.classList.add('hidden');
        });
    }

    // 5. Navigation Buttons & Keyboard Hotkeys
    if (prevBtn) {
        prevBtn.addEventListener('click', () => showQuestion(appState.currentIndex - 1));
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => showQuestion(appState.currentIndex + 1));
    }

    paletteButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => showQuestion(index));
    });

    // Option Clicks
    document.querySelectorAll('.cbt-option-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.cbt-question-card');
            const qId = card.dataset.questionId;
            const opt = btn.dataset.option;
            selectOption(qId, opt);
        });
    });

    // Keyboard Shortcuts: A, B, C, D, Left, Right
    document.addEventListener('keydown', (e) => {
        if (appState.isSubmitting) return;
        const key = e.key.toUpperCase();

        if (['A', 'B', 'C', 'D'].includes(key)) {
            const currentCard = questionCards[appState.currentIndex];
            if (currentCard) {
                const qId = currentCard.dataset.questionId;
                selectOption(qId, key);
            }
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            showQuestion(appState.currentIndex + 1);
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            showQuestion(appState.currentIndex - 1);
        }
    });

    // 6. Final Submission Handler
    async function autoSubmitExam(reason = '') {
        if (appState.isSubmitting) return;
        appState.isSubmitting = true;
        clearInterval(timerInterval);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch('/api/pmb/cbt/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    answers: appState.answers,
                    reason: reason
                })
            });

            const result = await response.json();

            // Clear local storage
            localStorage.removeItem(storageKey);
            localStorage.removeItem(timerStorageKey);

            // Show congratulatory / result overlay
            showResultModal(result);
        } catch (error) {
            console.error('[CBT Submission Error]:', error);
            if (window.EnterpriseToast) {
                window.EnterpriseToast.error('Gagal mengirimkan lembar ujian. Mencoba kirim ulang...', 'Koneksi Terputus');
            }
            appState.isSubmitting = false;
        }
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            const answeredCount = Object.keys(appState.answers).length;
            const unanswered = appState.totalQuestions - answeredCount;
            let msg = 'Apakah Anda yakin ingin menyelesaikan dan mengirim lembar jawaban ujian CBT?';
            if (unanswered > 0) {
                msg = `Masih ada ${unanswered} soal yang belum Anda jawab. Yakin ingin menyelesaikan ujian sekarang?`;
            }

            if (confirm(msg)) {
                autoSubmitExam('Submitted by applicant');
            }
        });
    }

    function showResultModal(result) {
        const modal = document.getElementById('cbt-result-modal');
        if (!modal) {
            window.location.href = result.redirect_url || '/pmb/status';
            return;
        }

        document.getElementById('cbt-result-score').textContent = result.score + ' / 100';
        document.getElementById('cbt-result-tier').textContent = result.scholarship_tier;
        document.getElementById('cbt-result-status').textContent = result.status.toUpperCase();
        
        if (result.generated_nim) {
            document.getElementById('cbt-result-nim-container').classList.remove('hidden');
            document.getElementById('cbt-result-nim').textContent = result.generated_nim;
        }

        modal.classList.remove('hidden');
    }

    // Initialize first question
    showQuestion(0);
});
