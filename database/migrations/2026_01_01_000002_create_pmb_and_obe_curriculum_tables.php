<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for PMB Admissions (Waves & Applicants) and OBE Curriculum Master.
     */
    public function up(): void
    {
        // 1. Admission Waves (Gelombang PMB)
        Schema::create('admission_waves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_period_id');
            $table->string('name', 100);
            $table->unsignedTinyInteger('wave_number')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('registration_fee', 12, 2)->default(500000.00);
            $table->dateTime('cbt_start_time')->nullable();
            $table->dateTime('cbt_end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->index(['academic_period_id', 'is_active']);
        });

        // 2. Applicants (Calon Mahasiswa Baru / PMB)
        Schema::create('applicants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('admission_wave_id');
            $table->uuid('target_study_program_id');
            $table->string('registration_number', 30)->unique();
            $table->string('va_number', 30);
            $table->enum('payment_status', ['unpaid', 'paid', 'expired'])->default('unpaid');
            $table->decimal('payment_amount', 12, 2)->default(500000.00);
            $table->dateTime('paid_at')->nullable();
            $table->decimal('cbt_score', 5, 2)->nullable();
            $table->enum('scholarship_tier', ['Tier 1 (100%)', 'Tier 2 (75%)', 'Tier 3 (50%)', 'Tier 4 (25%)', 'Regular (0%)'])->default('Regular (0%)');
            $table->enum('admission_status', ['registered', 'paid', 'exam_taken', 'passed', 'rejected', 'enrolled'])->default('registered');
            $table->string('generated_nim', 25)->nullable();
            $table->string('document_identity_path', 255)->nullable();
            $table->string('document_certificate_path', 255)->nullable();
            $table->dateTime('enrolled_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admission_wave_id')->references('id')->on('admission_waves')->onDelete('cascade');
            $table->foreign('target_study_program_id')->references('id')->on('study_programs')->onDelete('cascade');
            $table->index(['admission_wave_id', 'admission_status', 'payment_status']);
        });

        // 3. Curriculums (Kurikulum Program Studi)
        Schema::create('curriculums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('study_program_id');
            $table->string('code', 30);
            $table->string('name', 150);
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedSmallInteger('total_sks')->default(144);
            $table->unsignedSmallInteger('mandatory_sks')->default(130);
            $table->unsignedSmallInteger('elective_sks')->default(14);
            $table->boolean('is_obe_compliant')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->foreign('study_program_id')->references('id')->on('study_programs')->onDelete('cascade');
            $table->unique(['study_program_id', 'code']);
            $table->index(['study_program_id', 'is_active']);
        });

        // 4. OBE CPL (Capaian Pembelajaran Lulusan)
        Schema::create('obe_cpls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('curriculum_id');
            $table->string('code', 20); // e.g. CPL-01, CPL-KU1
            $table->enum('category', ['Sikap', 'Keterampilan Umum', 'Pengetahuan', 'Keterampilan Khusus']);
            $table->text('description_id');
            $table->text('description_en')->nullable();
            $table->unsignedSmallInteger('order_sequence')->default(1);
            $table->timestamps();

            $table->foreign('curriculum_id')->references('id')->on('curriculums')->onDelete('cascade');
            $table->unique(['curriculum_id', 'code']);
        });

        // 5. Courses (Mata Kuliah)
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('curriculum_id');
            $table->uuid('study_program_id');
            $table->string('code', 20);
            $table->string('name_id', 150);
            $table->string('name_en', 150)->nullable();
            $table->unsignedTinyInteger('sks')->default(3);
            $table->unsignedTinyInteger('sks_theory')->default(2);
            $table->unsignedTinyInteger('sks_practice')->default(1);
            $table->unsignedTinyInteger('semester_level')->default(1);
            $table->boolean('is_mandatory')->default(true);
            $table->string('syllabus_path', 255)->nullable();
            $table->string('passing_grade_letter', 2)->default('C');
            $table->decimal('weight_presence', 5, 2)->default(10.00);
            $table->decimal('weight_task', 5, 2)->default(20.00);
            $table->decimal('weight_uts', 5, 2)->default(30.00);
            $table->decimal('weight_uas', 5, 2)->default(40.00);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('curriculum_id')->references('id')->on('curriculums')->onDelete('cascade');
            $table->foreign('study_program_id')->references('id')->on('study_programs')->onDelete('cascade');
            $table->unique(['curriculum_id', 'code']);
            $table->index(['study_program_id', 'semester_level']);
        });

        // 6. Course Prerequisites (Prasyarat Mata Kuliah)
        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->uuid('prerequisite_course_id');
            $table->string('minimum_grade', 2)->default('D');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('prerequisite_course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->unique(['course_id', 'prerequisite_course_id']);
        });

        // 7. OBE CPMK (Capaian Pembelajaran Mata Kuliah)
        Schema::create('obe_cpmks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->uuid('obe_cpl_id');
            $table->string('code', 20); // e.g. CPMK-01
            $table->text('description');
            $table->decimal('weight_percentage', 5, 2)->default(25.00);
            $table->string('assessment_metric', 100)->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('obe_cpl_id')->references('id')->on('obe_cpls')->onDelete('cascade');
            $table->unique(['course_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obe_cpmks');
        Schema::dropIfExists('course_prerequisites');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('obe_cpls');
        Schema::dropIfExists('curriculums');
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('admission_waves');
    }
};
