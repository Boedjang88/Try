/**
 * Interactive Grade Sheet & OBE Rubric Calculator
 * Keyboard-navigable spreadsheet grid with live formula evaluation and bulk AJAX sync.
 */

export class GradeSheet {
    constructor(tableSelector, options = {}) {
        this.table = document.querySelector(tableSelector);
        this.weights = options.weights || {
            attendance: 0.10,
            assignments: 0.20,
            quizzes: 0.15,
            uts: 0.25,
            uas: 0.30,
            project: 0.00
        };
        this.saveEndpoint = options.saveEndpoint || '/api/grades/batch-store';
        this.csrfToken = options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.init();
    }

    init() {
        if (!this.table) return;
        this.bindEvents();
        this.recalculateAll();
    }

    bindEvents() {
        this.table.addEventListener('input', (e) => {
            if (e.target.matches('input[data-component]')) {
                this.validateInput(e.target);
                const row = e.target.closest('tr');
                this.recalculateRow(row);
            }
        });

        // Keyboard navigation (Arrow keys, Enter, Tab)
        this.table.addEventListener('keydown', (e) => {
            if (!e.target.matches('input[data-component]')) return;
            const currentInput = e.target;
            const currentTd = currentInput.closest('td');
            const currentRow = currentInput.closest('tr');
            const colIndex = Array.from(currentRow.children).indexOf(currentTd);

            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                e.preventDefault();
                const nextRow = currentRow.nextElementSibling;
                if (nextRow) {
                    const target = nextRow.children[colIndex]?.querySelector('input');
                    if (target) target.focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevRow = currentRow.previousElementSibling;
                if (prevRow) {
                    const target = prevRow.children[colIndex]?.querySelector('input');
                    if (target) target.focus();
                }
            }
        });
    }

    validateInput(input) {
        let val = parseFloat(input.value);
        if (isNaN(val)) val = 0;
        if (val < 0) input.value = 0;
        if (val > 100) input.value = 100;
    }

    recalculateRow(row) {
        const getVal = (name) => {
            const input = row.querySelector(`input[data-component="${name}"]`);
            return input ? (parseFloat(input.value) || 0) : 0;
        };

        const attendance = getVal('attendance');
        const assignments = getVal('assignments');
        const quizzes = getVal('quizzes');
        const uts = getVal('uts');
        const uas = getVal('uas');
        const project = getVal('project');

        const finalNumeric = (attendance * this.weights.attendance)
                           + (assignments * this.weights.assignments)
                           + (quizzes * this.weights.quizzes)
                           + (uts * this.weights.uts)
                           + (uas * this.weights.uas)
                           + (project * this.weights.project);

        const rounded = Math.round(finalNumeric * 100) / 100;
        const letterData = this.getLetterGrade(rounded);

        const numericCell = row.querySelector('[data-role="final-numeric"]');
        if (numericCell) numericCell.textContent = rounded.toFixed(2);

        const letterCell = row.querySelector('[data-role="final-letter"]');
        if (letterCell) {
            letterCell.textContent = `${letterData.letter} (${letterData.point.toFixed(2)})`;
            letterCell.className = `badge ${letterData.passed ? 'badge-success' : 'badge-danger'}`;
        }

        const cplCell = row.querySelector('[data-role="cpl-attainment"]');
        if (cplCell) {
            const cplAttainment = Math.min(100, Math.round((rounded / 85) * 100));
            cplCell.textContent = `${cplAttainment}%`;
        }
    }

    recalculateAll() {
        const rows = this.table.querySelectorAll('tbody tr');
        rows.forEach(row => this.recalculateRow(row));
    }

    getLetterGrade(score) {
        if (score >= 85) return { letter: 'A', point: 4.00, passed: true };
        if (score >= 80) return { letter: 'A-', point: 3.75, passed: true };
        if (score >= 75) return { letter: 'B+', point: 3.50, passed: true };
        if (score >= 70) return { letter: 'B', point: 3.00, passed: true };
        if (score >= 65) return { letter: 'B-', point: 2.75, passed: true };
        if (score >= 60) return { letter: 'C+', point: 2.50, passed: true };
        if (score >= 55) return { letter: 'C', point: 2.00, passed: true };
        if (score >= 40) return { letter: 'D', point: 1.00, passed: false };
        return { letter: 'E', point: 0.00, passed: false };
    }

    async saveBatch(classOfferingId) {
        const rows = this.table.querySelectorAll('tbody tr[data-student-id]');
        const records = Array.from(rows).map(row => {
            const studentId = row.getAttribute('data-student-id');
            const getVal = (name) => parseFloat(row.querySelector(`input[data-component="${name}"]`)?.value || 0);
            const finalScore = parseFloat(row.querySelector('[data-role="final-numeric"]')?.textContent || 0);
            const gradeInfo = this.getLetterGrade(finalScore);

            return {
                student_id: studentId,
                score_attendance: getVal('attendance'),
                score_assignments: getVal('assignments'),
                score_quizzes: getVal('quizzes'),
                score_uts_midterm: getVal('uts'),
                score_uas_final: getVal('uas'),
                score_project: getVal('project'),
                final_score_numeric: finalScore,
                final_grade_letter: gradeInfo.letter,
                grade_point: gradeInfo.point,
                is_passed: gradeInfo.passed
            };
        });

        const response = await fetch(this.saveEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                class_offering_id: classOfferingId,
                grades: records
            })
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Gagal menyimpan nilai ke server.');
        }

        return data;
    }
}
