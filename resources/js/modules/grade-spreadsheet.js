/**
 * UPH Enterprise Keyboard-Navigable Spreadsheet Grading Engine
 * Real-time Client-Side OBE Weighted Calculation, Arrow-Key Cell Navigation, and Bulk AJAX Persistence.
 */

document.addEventListener('DOMContentLoaded', () => {
    const spreadsheetApp = document.getElementById('grade-spreadsheet-app');
    if (!spreadsheetApp) return;

    const classSectionId = spreadsheetApp.dataset.sectionId;
    const isLocked = spreadsheetApp.dataset.isLocked === 'true';

    const wPres = parseFloat(spreadsheetApp.dataset.weightPresence || '10');
    const wTask = parseFloat(spreadsheetApp.dataset.weightTask || '20');
    const wUts = parseFloat(spreadsheetApp.dataset.weightUts || '30');
    const wUas = parseFloat(spreadsheetApp.dataset.weightUas || '40');
    const totalWeight = wPres + wTask + wUts + wUas || 100.0;

    const saveBtn = document.getElementById('spreadsheet-save-btn');
    const lockBtn = document.getElementById('spreadsheet-lock-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Grade Conversion Helper (Indonesian Higher Education 4.00 Scale)
    function convertToGradePoint(score) {
        if (score >= 85.00) return { letter: 'A', point: '4.00', bg: 'bg-emerald-100 text-emerald-800' };
        if (score >= 80.00) return { letter: 'A-', point: '3.70', bg: 'bg-emerald-100 text-emerald-800' };
        if (score >= 75.00) return { letter: 'B+', point: '3.30', bg: 'bg-blue-100 text-blue-800' };
        if (score >= 70.00) return { letter: 'B', point: '3.00', bg: 'bg-blue-100 text-blue-800' };
        if (score >= 65.00) return { letter: 'B-', point: '2.70', bg: 'bg-blue-100 text-blue-800' };
        if (score >= 60.00) return { letter: 'C+', point: '2.30', bg: 'bg-amber-100 text-amber-800' };
        if (score >= 55.00) return { letter: 'C', point: '2.00', bg: 'bg-amber-100 text-amber-800' };
        if (score >= 40.00) return { letter: 'D', point: '1.00', bg: 'bg-red-100 text-uph-crimson' };
        return { letter: 'E', point: '0.00', bg: 'bg-red-200 text-red-900 font-bold' };
    }

    // 2. Real-time Row Calculation
    function calculateRow(row) {
        const pres = parseFloat(row.querySelector('.input-score-pres')?.value || '0');
        const task = parseFloat(row.querySelector('.input-score-task')?.value || '0');
        const uts = parseFloat(row.querySelector('.input-score-uts')?.value || '0');
        const uas = parseFloat(row.querySelector('.input-score-uas')?.value || '0');

        const finalScore = ((pres * wPres) + (task * wTask) + (uts * wUts) + (uas * wUas)) / totalWeight;
        const roundedFinal = Math.round(finalScore * 100) / 100;
        const gradeInfo = convertToGradePoint(roundedFinal);

        const finalDisplay = row.querySelector('.display-final-score');
        const letterBadge = row.querySelector('.display-letter-badge');
        const pointDisplay = row.querySelector('.display-grade-point');

        if (finalDisplay) finalDisplay.textContent = roundedFinal.toFixed(2);
        if (letterBadge) {
            letterBadge.textContent = gradeInfo.letter;
            letterBadge.className = `display-letter-badge px-2 py-0.5 rounded font-mono font-bold text-xs ${gradeInfo.bg}`;
        }
        if (pointDisplay) pointDisplay.textContent = gradeInfo.point;

        row.dataset.dirty = 'true';
    }

    // Bind real-time input listeners
    document.querySelectorAll('.krs-grade-row').forEach(row => {
        row.querySelectorAll('.grade-cell-input').forEach(input => {
            input.addEventListener('input', () => calculateRow(row));
        });
    });

    // 3. Excel-like Keyboard Navigation
    const gridInputs = Array.from(document.querySelectorAll('.grade-cell-input'));
    gridInputs.forEach((input, index) => {
        input.addEventListener('keydown', (e) => {
            const currentCol = parseInt(input.dataset.col || '0', 10);
            const currentRow = parseInt(input.dataset.row || '0', 10);

            let targetInput = null;

            if (e.key === 'ArrowRight') {
                targetInput = document.querySelector(`.grade-cell-input[data-row="${currentRow}"][data-col="${currentCol + 1}"]`);
            } else if (e.key === 'ArrowLeft') {
                targetInput = document.querySelector(`.grade-cell-input[data-row="${currentRow}"][data-col="${currentCol - 1}"]`);
            } else if (e.key === 'ArrowDown' || e.key === 'Enter') {
                e.preventDefault();
                targetInput = document.querySelector(`.grade-cell-input[data-row="${currentRow + 1}"][data-col="${currentCol}"]`);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                targetInput = document.querySelector(`.grade-cell-input[data-row="${currentRow - 1}"][data-col="${currentCol}"]`);
            }

            if (targetInput) {
                targetInput.focus();
                targetInput.select();
            }
        });
    });

    // 4. Bulk Save via AJAX
    if (saveBtn && !isLocked) {
        saveBtn.addEventListener('click', async () => {
            const gradesPayload = [];
            document.querySelectorAll('.krs-grade-row').forEach(row => {
                const krsItemId = row.dataset.krsItemId;
                gradesPayload.push({
                    krs_item_id: krsItemId,
                    score_presence: row.querySelector('.input-score-pres')?.value || 0,
                    score_task: row.querySelector('.input-score-task')?.value || 0,
                    score_uts: row.querySelector('.input-score-uts')?.value || 0,
                    score_uas: row.querySelector('.input-score-uas')?.value || 0,
                });
            });

            saveBtn.disabled = true;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';

            try {
                const response = await fetch(`/api/grading/${classSectionId}/save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ grades: gradesPayload })
                });

                const data = await response.json();
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;

                if (data.success) {
                    if (window.EnterpriseToast) {
                        window.EnterpriseToast.success(data.message, 'Nilai Tersimpan');
                    }
                } else {
                    if (window.EnterpriseToast) {
                        window.EnterpriseToast.error(data.message || 'Gagal menyimpan nilai.');
                    }
                }
            } catch (error) {
                console.error('[Grading Bulk Save Error]:', error);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
                if (window.EnterpriseToast) {
                    window.EnterpriseToast.error('Koneksi terputus saat menyimpan nilai.');
                }
            }
        });
    }

    // 5. Lock Grades Button
    if (lockBtn && !isLocked) {
        lockBtn.addEventListener('click', async () => {
            if (!confirm('Kunci nilai kelas ini secara permanen? Nilai yang telah dikunci akan resmi masuk ke KHS mahasiswa dan tidak dapat diubah tanpa persetujuan tertulis Kaprodi.')) {
                return;
            }

            lockBtn.disabled = true;

            try {
                const response = await fetch(`/api/grading/${classSectionId}/lock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();
                if (data.success) {
                    if (window.EnterpriseToast) {
                        window.EnterpriseToast.success(data.message, 'Nilai Terkunci');
                    }
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    lockBtn.disabled = false;
                    if (window.EnterpriseToast) window.EnterpriseToast.error(data.message);
                }
            } catch (e) {
                lockBtn.disabled = false;
                if (window.EnterpriseToast) window.EnterpriseToast.error('Gagal mengunci nilai.');
            }
        });
    }
});
