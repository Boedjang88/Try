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
        // 1. E-Library Book Loans
        Schema::create('library_loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('book_title');
            $table->string('book_code');
            $table->dateTime('borrowed_at');
            $table->dateTime('due_date');
            $table->dateTime('returned_at')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0.00);
            $table->enum('status', ['borrowed', 'returned', 'overdue'])->default('borrowed');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'status']);
        });

        // 2. Smart Lab & Facility Bookings
        Schema::create('facility_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('facility_name');
            $table->enum('facility_type', ['lab', 'discussion_room', 'equipment'])->default('lab');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->text('purpose')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'status']);
        });

        // 3. Alumni Tracer Study & Kemdiktisaintek Accreditation Questionnaire
        Schema::create('tracer_studies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->integer('graduation_year');
            $table->enum('employment_status', ['employed', 'entrepreneur', 'continuing_study', 'seeking'])->default('employed');
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->integer('waiting_time_months')->default(0);
            $table->string('monthly_income_range')->nullable();
            $table->enum('job_major_alignment', ['high', 'moderate', 'low'])->default('high');
            $table->dateTime('submitted_at');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'employment_status']);
        });

        // 4. Add lab_cleared to yudisium_clearances if not present
        if (!Schema::hasColumn('yudisium_clearances', 'lab_cleared')) {
            Schema::table('yudisium_clearances', function (Blueprint $table) {
                $table->boolean('lab_cleared')->default(false)->after('library_cleared');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('yudisium_clearances', 'lab_cleared')) {
            Schema::table('yudisium_clearances', function (Blueprint $table) {
                $table->dropColumn('lab_cleared');
            });
        }

        Schema::dropIfExists('tracer_studies');
        Schema::dropIfExists('facility_bookings');
        Schema::dropIfExists('library_loans');
    }
};
