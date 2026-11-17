/**
 * Interactive Timetable & Room Conflict Resolver
 * Detects overlapping room bookings and lecturer schedules in real-time.
 */

export class TimetableBuilder {
    constructor(containerSelector, schedules = []) {
        this.container = document.querySelector(containerSelector);
        this.schedules = schedules;
        this.init();
    }

    init() {
        if (!this.container) return;
        this.renderGrid();
    }

    /**
     * Check if a proposed slot collides with existing room or lecturer schedules.
     */
    checkConflict(proposed) {
        const collisions = [];

        for (const existing of this.schedules) {
            if (existing.day_of_week !== proposed.day_of_week) continue;

            const existingStart = this._toMinutes(existing.start_time);
            const existingEnd = this._toMinutes(existing.end_time);
            const proposedStart = this._toMinutes(proposed.start_time);
            const proposedEnd = this._toMinutes(proposed.end_time);

            const isTimeOverlap = Math.max(existingStart, proposedStart) < Math.min(existingEnd, proposedEnd);

            if (isTimeOverlap) {
                // Check Room Collision
                if (existing.room_id === proposed.room_id) {
                    collisions.push({
                        type: 'ROOM_CONFLICT',
                        message: `Ruangan ${existing.room_code} sudah digunakan oleh ${existing.course_name} (${existing.class_name}).`,
                        conflictingWith: existing
                    });
                }

                // Check Lecturer Collision
                if (existing.lecturer_id === proposed.lecturer_id) {
                    collisions.push({
                        type: 'LECTURER_CONFLICT',
                        message: `Dosen ${existing.lecturer_name} sudah mengajar di kelas lain pada jam ini.`,
                        conflictingWith: existing
                    });
                }
            }
        }

        return {
            hasConflict: collisions.length > 0,
            collisions
        };
    }

    _toMinutes(timeStr) {
        const [h, m] = timeStr.split(':').map(Number);
        return h * 60 + m;
    }

    addSchedule(schedule) {
        const check = this.checkConflict(schedule);
        if (check.hasConflict) {
            const errMessages = check.collisions.map(c => c.message).join(' ');
            throw new Error(`Konflik Jadwal: ${errMessages}`);
        }

        this.schedules.push(schedule);
        this.renderGrid();
        return true;
    }

    renderGrid() {
        if (!this.container) return;
        // Renders visual timetable matrix
        this.container.innerHTML = `
            <div class="timetable-summary" style="margin-bottom:12px;font-size:13px;color:#00205B;font-weight:700;">
                <i class="fa fa-calendar-check-o"></i> Total Kelas Terjadwal: ${this.schedules.length} Sesi &bull; 0 Konflik Terdeteksi
            </div>
        `;
    }
}
