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
        // 1. Dual-Control Maker-Checker Approval Engine Table
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('request_code')->unique();
            $table->string('workflow_type'); // grade_change_after_lock, tuition_waiver_or_discount, krs_late_override, account_privilege_elevation
            $table->foreignId('maker_user_id')->constrained('users')->onDelete('cascade');
            $table->string('checker_role_required'); // kaprodi, dean, bauk, superadmin
            $table->string('target_model_type');
            $table->string('target_model_id');
            $table->json('payload_diff'); // {"before": {...}, "after": {...}}
            $table->text('maker_reason');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('checker_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('checker_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_type', 'status']);
            $table->index('checker_role_required');
        });

        // 2. Real-Time Academic Consultation Messages Table
        Schema::create('consultation_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('lecturer_id')->constrained('lecturers')->onDelete('cascade');
            $table->foreignId('sender_user_id')->constrained('users')->onDelete('cascade');
            $table->string('context_type')->default('krs'); // krs, thesis, general
            $table->string('context_id')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'lecturer_id', 'context_type']);
        });

        // 3. Campus SLA Helpdesk Support Tickets Table
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('department'); // bauk, baak, it_support
            $table->string('category');
            $table->string('subject');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->timestamp('sla_due_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['department', 'status']);
            $table->index('sla_due_at');
        });

        // 4. Helpdesk Support Ticket Replies Table
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_internal_note')->default(false);
            $table->timestamps();
        });

        // 5. Multi-Channel Outbound Notification Dispatch Log Table
        Schema::create('outbound_dispatches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('dispatch_id')->unique();
            $table->string('channel'); // whatsapp, email
            $table->string('recipient');
            $table->string('subject_or_title');
            $table->text('payload_body');
            $table->string('status')->default('sent'); // sent, delivered, simulated
            $table->string('event_type'); // VA_PAID, KRS_APPROVED, GRADE_LOCKED, EXAM_SEAT_ASSIGNED, etc.
            $table->timestamps();

            $table->index(['channel', 'event_type']);
        });

        // 6. Anti-Cheating Exam Seat Column on krs_items
        if (!Schema::hasColumn('krs_items', 'exam_seat_number')) {
            Schema::table('krs_items', function (Blueprint $table) {
                $table->string('exam_seat_number')->nullable()->after('is_exam_blocked');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('krs_items', 'exam_seat_number')) {
            Schema::table('krs_items', function (Blueprint $table) {
                $table->dropColumn('exam_seat_number');
            });
        }

        Schema::dropIfExists('outbound_dispatches');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('consultation_messages');
        Schema::dropIfExists('approval_requests');
    }
};
