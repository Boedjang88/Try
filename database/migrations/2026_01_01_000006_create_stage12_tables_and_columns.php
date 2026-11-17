<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add quiz and project component scores to krs_items
        Schema::table('krs_items', function (Blueprint $table) {
            if (!Schema::hasColumn('krs_items', 'score_quiz')) {
                $table->decimal('score_quiz', 5, 2)->default(0.00)->after('score_task');
            }
            if (!Schema::hasColumn('krs_items', 'score_project')) {
                $table->decimal('score_project', 5, 2)->default(0.00)->after('score_uas');
            }
        });

        // 2. MBKM Programs
        Schema::create('mbkm_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('academic_period_id');
            $table->string('program_type', 50); // magang_industri, pertukaran_mahasiswa, studi_independen, riset, proyek_kemanusiaan
            $table->string('partner_name', 255);
            $table->string('partner_address', 255)->nullable();
            $table->string('mentor_name', 150)->nullable();
            $table->string('mentor_contact', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('logbook_document_path', 255)->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, completed
            $table->text('approval_notes')->nullable();
            $table->uuid('approved_by_kaprodi_id')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->foreign('approved_by_kaprodi_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->index(['student_id', 'academic_period_id']);
        });

        // 3. MBKM Course SKS Conversions (Up to 20 SKS)
        Schema::create('mbkm_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mbkm_program_id');
            $table->uuid('course_id');
            $table->unsignedTinyInteger('equivalent_sks')->default(3);
            $table->decimal('converted_numeric_score', 5, 2)->default(85.00);
            $table->string('converted_letter_grade', 2)->default('A');
            $table->decimal('grade_point', 3, 2)->default(4.00);
            $table->uuid('krs_item_id')->nullable();
            $table->timestamps();

            $table->foreign('mbkm_program_id')->references('id')->on('mbkm_programs')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('krs_item_id')->references('id')->on('krs_items')->onDelete('set null');
        });

        // 4. Grade Appeals (Sanggah Nilai)
        Schema::create('grade_appeals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('krs_item_id');
            $table->uuid('student_id');
            $table->text('reason');
            $table->string('evidence_file_path', 255)->nullable();
            $table->string('status', 30)->default('submitted'); // submitted, reviewed_by_lecturer, unlock_requested, approved, rejected
            $table->text('lecturer_response')->nullable();
            $table->dateTime('lecturer_reviewed_at')->nullable();
            $table->dateTime('kaprodi_reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('krs_item_id')->references('id')->on('krs_items')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_appeals');
        Schema::dropIfExists('mbkm_conversions');
        Schema::dropIfExists('mbkm_programs');
        Schema::table('krs_items', function (Blueprint $table) {
            if (Schema::hasColumn('krs_items', 'score_quiz')) {
                $table->dropColumn('score_quiz');
            }
            if (Schema::hasColumn('krs_items', 'score_project')) {
                $table->dropColumn('score_project');
            }
        });
    }
};
