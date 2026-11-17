<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Timetabling (Class Sections), KRS War, 16-Week Lecture Sessions, QR Attendance, and LMS.
     */
    public function up(): void
    {
        // 1. Class Sections (Jadwal Kelas Kuliah)
        Schema::create('class_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_period_id');
            $table->uuid('course_id');
            $table->uuid('room_id')->nullable();
            $table->uuid('lecturer_id');
            $table->string('section_code', 10); // e.g. IF-A, CS-01
            $table->unsignedTinyInteger('day_of_week'); // 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('max_quota')->default(40);
            $table->unsignedSmallInteger('enrolled_count')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('lecturer_id')->references('id')->on('lecturers')->onDelete('cascade');
            
            $table->index(['academic_period_id', 'day_of_week']);
            $table->index(['room_id', 'day_of_week']);
            $table->index(['lecturer_id', 'day_of_week']);
        });

        // 2. KRS Submissions (Pengajuan Rencana Studi)
        Schema::create('krs_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('academic_period_id');
            $table->unsignedTinyInteger('total_sks')->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_note')->nullable();
            $table->uuid('approved_by_lecturer_id')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('qr_token', 100)->unique()->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->foreign('approved_by_lecturer_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->unique(['student_id', 'academic_period_id']);
        });

        // 3. KRS Items (Detail Mata Kuliah KRS & Grade Ledger)
        Schema::create('krs_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('krs_submission_id');
            $table->uuid('class_section_id');
            $table->decimal('score_presence', 5, 2)->default(0.00);
            $table->decimal('score_task', 5, 2)->default(0.00);
            $table->decimal('score_uts', 5, 2)->default(0.00);
            $table->decimal('score_uas', 5, 2)->default(0.00);
            $table->decimal('final_numeric_score', 5, 2)->nullable();
            $table->string('letter_grade', 2)->nullable(); // A, A-, B+, B, B-, C+, C, D, E
            $table->decimal('grade_point', 3, 2)->nullable(); // 4.00, 3.70, etc.
            $table->boolean('is_locked')->default(false);
            $table->boolean('edom_completed')->default(false);
            $table->timestamps();

            $table->foreign('krs_submission_id')->references('id')->on('krs_submissions')->onDelete('cascade');
            $table->foreign('class_section_id')->references('id')->on('class_sections')->onDelete('cascade');
            $table->unique(['krs_submission_id', 'class_section_id']);
            $table->index('is_locked');
        });

        // 4. Lecture Sessions (16 Pertemuan Perkuliahan + UTS + UAS + BAP)
        Schema::create('lecture_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('class_section_id');
            $table->unsignedTinyInteger('session_number'); // 1-16
            $table->string('title', 200);
            $table->enum('session_type', ['lecture', 'uts', 'uas'])->default('lecture');
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->uuid('room_id')->nullable();
            $table->string('qr_code_token', 128)->nullable();
            $table->dateTime('qr_expires_at')->nullable();
            $table->text('bap_summary')->nullable(); // Berita Acara Perkuliahan
            $table->dateTime('lecturer_signed_at')->nullable();
            $table->timestamps();

            $table->foreign('class_section_id')->references('id')->on('class_sections')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->unique(['class_section_id', 'session_number']);
            $table->index('planned_date');
        });

        // 5. Attendances (Presensi Mahasiswa via Geofence QR / Manual)
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lecture_session_id');
            $table->uuid('student_id');
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->enum('verified_method', ['qr_scan', 'lecturer_manual', 'geolocation'])->default('qr_scan');
            $table->decimal('scanned_latitude', 10, 8)->nullable();
            $table->decimal('scanned_longitude', 11, 8)->nullable();
            $table->dateTime('scanned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('lecture_session_id')->references('id')->on('lecture_sessions')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['lecture_session_id', 'student_id']);
            $table->index(['student_id', 'attendance_status']);
        });

        // 6. LMS Assignments (Tugas Kuliah & RPS)
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lecture_session_id');
            $table->string('title', 200);
            $table->text('description');
            $table->dateTime('deadline');
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->string('attachment_path', 255)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->foreign('lecture_session_id')->references('id')->on('lecture_sessions')->onDelete('cascade');
        });

        // 7. LMS Submissions (Pengumpulan Tugas Mahasiswa)
        Schema::create('lms_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lms_assignment_id');
            $table->uuid('student_id');
            $table->string('submission_file_path', 255);
            $table->dateTime('submitted_at');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->uuid('graded_by_lecturer_id')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->timestamps();

            $table->foreign('lms_assignment_id')->references('id')->on('lms_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('graded_by_lecturer_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->unique(['lms_assignment_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_submissions');
        Schema::dropIfExists('lms_assignments');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('lecture_sessions');
        Schema::dropIfExists('krs_items');
        Schema::dropIfExists('krs_submissions');
        Schema::dropIfExists('class_sections');
    }
};
