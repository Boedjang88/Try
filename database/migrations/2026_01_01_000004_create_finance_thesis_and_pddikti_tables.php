<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Student Invoices (Billing & VA), Thesis & Guidance, Yudisium & SKPI, and PDDIKTI Sync Logs.
     */
    public function up(): void
    {
        // 1. Student Invoices (Tagihan Keuangan UKT & SKS)
        Schema::create('student_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('academic_period_id');
            $table->string('invoice_number', 64)->unique();
            $table->string('va_number', 32);
            $table->string('title', 150);
            $table->decimal('amount', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'expired'])->default('unpaid');
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable(); // BCA_VA, CIMB_VA, QRIS
            $table->string('gateway_reference', 100)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->index(['student_id', 'payment_status']);
            $table->index('va_number');
        });

        // 2. Theses (Tugas Akhir / Skripsi)
        Schema::create('theses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('title', 255);
            $table->text('abstract')->nullable();
            $table->uuid('supervisor1_lecturer_id'); // Pembimbing 1
            $table->uuid('supervisor2_lecturer_id')->nullable(); // Pembimbing 2
            $table->uuid('examiner1_lecturer_id')->nullable(); // Penguji 1
            $table->uuid('examiner2_lecturer_id')->nullable(); // Penguji 2
            $table->enum('proposal_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('defense_status', ['not_ready', 'scheduled', 'passed', 'revisions_needed', 'failed'])->default('not_ready');
            $table->dateTime('defense_date')->nullable();
            $table->json('rubric_scores')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('letter_grade', 2)->nullable();
            $table->string('repository_url', 255)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('supervisor1_lecturer_id')->references('id')->on('lecturers')->onDelete('cascade');
            $table->foreign('supervisor2_lecturer_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->foreign('examiner1_lecturer_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->foreign('examiner2_lecturer_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->index(['student_id', 'defense_status']);
        });

        // 3. Thesis Guidance Logs (Logbook Bimbingan Digital Dosen & Mahasiswa)
        Schema::create('thesis_guidance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('thesis_id');
            $table->uuid('lecturer_id');
            $table->date('guidance_date');
            $table->text('discussion_summary');
            $table->text('student_notes')->nullable();
            $table->text('supervisor_feedback')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('thesis_id')->references('id')->on('theses')->onDelete('cascade');
            $table->foreign('lecturer_id')->references('id')->on('lecturers')->onDelete('cascade');
            $table->index(['thesis_id', 'is_verified']);
        });

        // 4. Yudisium Clearances (Verifikasi Bebas Pustaka, Bebas Keuangan & SKPI)
        Schema::create('yudisium_clearances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('academic_period_id');
            $table->boolean('finance_cleared')->default(false);
            $table->boolean('library_cleared')->default(false);
            $table->boolean('repository_cleared')->default(false);
            $table->boolean('faculty_cleared')->default(false);
            $table->string('national_pin_number', 64)->nullable(); // Penomoran Ijazah Nasional
            $table->string('skpi_document_path', 255)->nullable();
            $table->string('transcript_document_path', 255)->nullable();
            $table->date('graduation_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->uuid('approved_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
            $table->foreign('approved_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['student_id', 'academic_period_id']);
            $table->index('status');
        });

        // 5. PDDIKTI Sync Logs (Neo Feeder Transaction & State Sync Tracker)
        Schema::create('pddikti_sync_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 50); // Mahasiswa, Dosen, KelasKuliah, Nilai, AKM
            $table->string('batch_reference', 64);
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->json('error_payload')->nullable();
            $table->uuid('synced_by_user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('synced_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['entity_type', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pddikti_sync_logs');
        Schema::dropIfExists('yudisium_clearances');
        Schema::dropIfExists('thesis_guidance_logs');
        Schema::dropIfExists('theses');
        Schema::dropIfExists('student_invoices');
    }
};
