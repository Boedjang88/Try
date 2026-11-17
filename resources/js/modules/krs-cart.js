/**
 * KRS Cart & War Engine - ES6 Reactive Module
 * Handles client-side cart management, schedule collision checks, and async submission.
 */

export class KRSCart {
    constructor(config = {}) {
        this.maxSks = config.maxSks || 24;
        this.studentId = config.studentId || null;
        this.csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.submitEndpoint = config.submitEndpoint || '/api/krs/submit';
        this.items = new Map();
        this.listeners = [];
        this.init();
    }

    init() {
        const saved = sessionStorage.getItem(`krs_cart_${this.studentId}`);
        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                parsed.forEach(item => this.items.set(item.class_offering_id, item));
            } catch (e) {
                console.error('Failed to restore KRS Cart state', e);
            }
        }
        this.notify();
    }

    subscribe(fn) {
        this.listeners.push(fn);
        fn(this.getState());
    }

    notify() {
        const state = this.getState();
        sessionStorage.setItem(`krs_cart_${this.studentId}`, JSON.stringify(Array.from(this.items.values())));
        this.listeners.forEach(fn => fn(state));
    }

    getState() {
        const itemsArray = Array.from(this.items.values());
        const totalSks = itemsArray.reduce((acc, curr) => acc + curr.sks, 0);
        return {
            items: itemsArray,
            totalSks,
            maxSks: this.maxSks,
            remainingSks: this.maxSks - totalSks,
            isOverLimit: totalSks > this.maxSks,
        };
    }

    /**
     * Detect time slot conflict with existing cart items.
     */
    hasScheduleClash(newClass) {
        for (const [id, item] of this.items.entries()) {
            if (item.day_of_week === newClass.day_of_week) {
                const existingStart = this._toMinutes(item.start_time);
                const existingEnd = this._toMinutes(item.end_time);
                const newStart = this._toMinutes(newClass.start_time);
                const newEnd = this._toMinutes(newClass.end_time);

                if (Math.max(existingStart, newStart) < Math.min(existingEnd, newEnd)) {
                    return {
                        clash: true,
                        conflictingWith: item
                    };
                }
            }
        }
        return { clash: false };
    }

    _toMinutes(timeStr) {
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    addClass(classOffering) {
        if (this.items.has(classOffering.class_offering_id)) {
            throw new Error(`Kelas ${classOffering.course_name} sudah berada di dalam keranjang KRS.`);
        }

        const state = this.getState();
        if (state.totalSks + classOffering.sks > this.maxSks) {
            throw new Error(`Batas SKS terlampaui. Maksimal SKS Anda adalah ${this.maxSks} SKS.`);
        }

        const clashCheck = this.hasScheduleClash(classOffering);
        if (clashCheck.clash) {
            throw new Error(`Bentrok jadwal terdeteksi dengan ${clashCheck.conflictingWith.course_name} (${classOffering.day_of_week}, ${classOffering.start_time}-${classOffering.end_time}).`);
        }

        this.items.set(classOffering.class_offering_id, classOffering);
        this.notify();
        return true;
    }

    removeClass(classOfferingId) {
        if (this.items.has(classOfferingId)) {
            this.items.delete(classOfferingId);
            this.notify();
            return true;
        }
        return false;
    }

    clear() {
        this.items.clear();
        this.notify();
    }

    /**
     * Submit KRS asynchronously to Laravel backend.
     */
    async submit() {
        const state = this.getState();
        if (state.items.length === 0) {
            throw new Error('Keranjang KRS masih kosong. Silakan pilih mata kuliah terlebih dahulu.');
        }

        if (state.isOverLimit) {
            throw new Error(`Total SKS (${state.totalSks}) melebihi kuota maksimal (${state.maxSks} SKS).`);
        }

        const payload = {
            student_id: this.studentId,
            class_offering_ids: state.items.map(i => i.class_offering_id),
            total_sks: state.totalSks
        };

        const response = await fetch(this.submitEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Gagal mengajukan KRS. Terjadi kendala pada server.');
        }

        this.clear();
        return result;
    }
}
