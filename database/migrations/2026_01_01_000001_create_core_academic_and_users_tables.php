<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Core Academic Master, Users (with ABAC Scopes), Lecturers, Students, Rooms & Audit Logs.
     */
    public function up(): void
    {
        // 1. Faculties
        Schema::create('faculties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name_id', 150);
            $table->string('name_en', 150)->nullable();
            $table->uuid('dean_user_id')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // 2. Study Programs (Prodi)
        Schema::create('study_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('faculty_id');
            $table->string('pddikti_code', 20)->unique();
            $table->string('code', 20)->unique();
            $table->string('name_id', 150);
            $table->string('name_en', 150)->nullable();
            $table->enum('degree_level', ['D3', 'D4', 'S1', 'Profesi', 'Spesialis', 'S2', 'S3'])->default('S1');
            $table->string('title_abbr', 30);
            $table->uuid('head_of_program_id')->nullable();
            $table->enum('accreditation_grade', ['Unggul', 'A', 'Baik Sekali', 'Baik', 'B', 'C'])->default('Unggul');
            $table->string('accreditation_sk_number', 100)->nullable();
            $table->date('accreditation_expiry_date')->nullable();
            $table->unsignedSmallInteger('standard_study_period_semesters')->default(8);
            $table->unsignedSmallInteger('minimum_graduation_sks')->default(144);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->index(['faculty_id', 'degree_level', 'is_active']);
        });

        // 3. Users (with RBAC Role & ABAC Scope for Faculty and Study Program)
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username', 64)->unique();
            $table->string('email', 128)->unique();
            $table->string('password');
            $table->string('full_name', 150);
            $table->string('role', 50)->default('applicant'); // superadmin, applicant, student, lecturer, kaprodi, dean, baak, bauk, rector
            $table->uuid('faculty_id')->nullable(); // ABAC Scope: Faculty
            $table->uuid('study_program_id')->nullable(); // ABAC Scope: Study Program
            $table->string('phone_number', 25)->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('avatar_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('set null');
            $table->foreign('study_program_id')->references('id')->on('study_programs')->onDelete('set null');
            $table->index(['role', 'is_active']);
            $table->index(['faculty_id', 'study_program_id']);
        });

        // 4. Lecturers
        Schema::create('lecturers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('nidn', 20)->unique()->nullable();
            $table->string('nip', 30)->unique()->nullable();
            $table->string('title_prefix', 30)->nullable();
            $table->string('title_suffix', 50)->nullable();
            $table->uuid('study_program_id');
            $table->enum('academic_rank', ['Tenaga Pengajar', 'Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'])->default('Asisten Ahli');
            $table->enum('employment_status', ['Tetap', 'Kontrak', 'LB', 'Tamu'])->default('Tetap');
            $table->unsignedTinyInteger('max_supervision_quota')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('study_program_id')->references('id')->on('study_programs')->onDelete('cascade');
            $table->index(['study_program_id', 'is_active']);
        });

        // 5. Academic Periods (Tahun Akademik & Semester Operasional)
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique(); // e.g. 20261 (2026/2027 Ganjil)
            $table->string('name', 60); // 2026/2027 Ganjil
            $table->enum('semester_type', ['Ganjil', 'Genap', 'Antara'])->default('Ganjil');
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('krs_start_date');
            $table->dateTime('krs_end_date');
            $table->dateTime('grade_input_start_date');
            $table->dateTime('grade_input_end_date');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_krs_active')->default(false);
            $table->boolean('is_grading_active')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'is_krs_active', 'is_grading_active']);
        });

        // 6. Students (Mahasiswa)
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('study_program_id');
            $table->uuid('academic_advisor_id')->nullable(); // Dosen Pembimbing Akademik (Dosen PA)
            $table->string('nim', 25)->unique();
            $table->unsignedSmallInteger('entry_year');
            $table->enum('entry_semester', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->unsignedTinyInteger('current_semester')->default(1);
            $table->unsignedTinyInteger('max_sks_allowed')->default(24);
            $table->decimal('current_gpa_ipk', 3, 2)->default(0.00);
            $table->decimal('last_semester_gpa_ips', 3, 2)->default(0.00);
            $table->unsignedSmallInteger('total_sks_completed')->default(0);
            $table->enum('academic_status', ['active', 'on_leave', 'probation', 'graduated', 'dropped_out'])->default('active');
            $table->boolean('is_financially_cleared')->default(false);
            $table->boolean('has_academic_hold')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('study_program_id')->references('id')->on('study_programs')->onDelete('cascade');
            $table->foreign('academic_advisor_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->index(['study_program_id', 'academic_status', 'is_financially_cleared']);
            $table->index('nim');
        });

        // 7. Rooms (Ruang Kuliah dengan Geofence Coordinates)
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 25)->unique(); // e.g. D-501, LAB-AI-01
            $table->string('name', 100);
            $table->string('building', 80); // Gedung D (Johannes Oentoro), Building of Hope
            $table->unsignedTinyInteger('floor')->default(1);
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->enum('room_type', ['classroom', 'laboratory', 'auditorium', 'studio', 'clinic'])->default('classroom');
            $table->decimal('latitude', 10, 8)->default(-6.22530000); // UPH Lippo Village default
            $table->decimal('longitude', 11, 8)->default(106.60830000);
            $table->unsignedInteger('geofence_radius_meters')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['building', 'is_active']);
        });

        // 8. Audit Logs (Immutable State-Change Ledger)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id')->nullable();
            $table->string('action', 100); // e.g. KRS_SEAT_LOCKED, GRADE_PUBLISHED, INVOICE_PAID, BAP_SIGNED
            $table->string('auditable_type', 150);
            $table->string('auditable_id', 64);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['action', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('students');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('lecturers');
        Schema::dropIfExists('users');
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('faculties');
    }
};
