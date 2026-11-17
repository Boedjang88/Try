<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for Stage 11: Real-World Academic Engines.
     */
    public function up(): void
    {
        // 1. Add Retake & Exam Gate Columns to krs_items
        Schema::table('krs_items', function (Blueprint $table) {
            if (!Schema::hasColumn('krs_items', 'is_retake')) {
                $table->boolean('is_retake')->default(false)->after('edom_completed');
            }
            if (!Schema::hasColumn('krs_items', 'is_exam_blocked')) {
                $table->boolean('is_exam_blocked')->default(false)->after('is_retake');
            }
            if (!Schema::hasColumn('krs_items', 'exam_unblocked_reason')) {
                $table->text('exam_unblocked_reason')->nullable()->after('is_exam_blocked');
            }
            if (!Schema::hasColumn('krs_items', 'attendance_percentage')) {
                $table->decimal('attendance_percentage', 5, 2)->default(100.00)->after('exam_unblocked_reason');
            }
        });

        // 2. Academic Leave Requests (Cuti Akademik)
        if (!Schema::hasTable('academic_leave_requests')) {
            Schema::create('academic_leave_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('student_id');
                $table->uuid('academic_period_id');
                $table->text('reason');
                $table->string('document_path', 255)->nullable();
                $table->enum('status', ['pending', 'advisor_approved', 'approved', 'rejected'])->default('pending');
                $table->uuid('approved_by_advisor_id')->nullable();
                $table->uuid('approved_by_kaprodi_id')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
                $table->foreign('approved_by_advisor_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('approved_by_kaprodi_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['student_id', 'status']);
            });
        }

        // 3. Student AKM Histories (Aktivitas Kuliah Mahasiswa for Semester Rollover Archive)
        if (!Schema::hasTable('student_akm_histories')) {
            Schema::create('student_akm_histories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('student_id');
                $table->uuid('academic_period_id');
                $table->unsignedSmallInteger('semester_number')->default(1);
                $table->unsignedTinyInteger('sks_semester')->default(0);
                $table->unsignedSmallInteger('sks_total')->default(0);
                $table->decimal('ips', 3, 2)->default(0.00);
                $table->decimal('ipk', 3, 2)->default(0.00);
                $table->string('status', 10)->default('AK'); // AK (Aktif), C (Cuti), etc.
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('academic_period_id')->references('id')->on('academic_periods')->onDelete('cascade');
                $table->unique(['student_id', 'academic_period_id']);
                $table->index(['academic_period_id', 'status']);
            });
        }

        // 4. In-App Notifications (Notification Center & Topbar Activity Bell)
        if (!Schema::hasTable('in_app_notifications')) {
            Schema::create('in_app_notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id');
                $table->string('type', 60); // financial_unblock, krs_submitted, krs_approved, grade_locked, assignment_published, leave_status
                $table->string('title', 150);
                $table->text('message');
                $table->string('action_url', 255)->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'is_read']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
        Schema::dropIfExists('student_akm_histories');
        Schema::dropIfExists('academic_leave_requests');

        Schema::table('krs_items', function (Blueprint $table) {
            $table->dropColumn(['is_retake', 'is_exam_blocked', 'exam_unblocked_reason', 'attendance_percentage']);
        });
    }
};
