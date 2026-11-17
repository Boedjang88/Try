<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\InAppNotification;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\LectureSession;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\Student;
use App\Models\StudentAkmHistory;
use App\Models\StudentInvoice;
use App\Models\User;
use App\Services\SemesterRolloverService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage11RealWorldAcademicGapsTest extends TestCase
{
    /**
     * Test 1: 75% Attendance Gate for UAS & Kaprodi Override
     */
    public function test_75_percent_attendance_exam_gate_blocks_exam_and_shows_cekal_badge_unless_unblocked_by_kaprodi(): void
    {
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;
        $kaprodiUser = User::where('email', 'kaprodi.if@uph.edu')->firstOrFail();

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            $activePeriod = AcademicPeriod::where('code', '20251')->first() ?: AcademicPeriod::firstOrFail();
            $activePeriod->update(['is_active' => true]);
        }

        $course = Course::where('study_program_id', $student->study_program_id)->firstOrFail();
        $lecturer = \App\Models\Lecturer::firstOrFail();

        // Find or create class section with 10 sessions
        $section = ClassSection::firstOrCreate([
            'academic_period_id' => $activePeriod->id,
            'course_id' => $course->id,
            'section_code' => 'TEST-101',
        ], [
            'id' => (string) Str::uuid(),
            'lecturer_id' => $lecturer->id,
            'max_quota' => 40,
            'enrolled_count' => 1,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:30',
        ]);

        // Ensure 10 lecture sessions exist
        for ($i = 1; $i <= 10; $i++) {
            LectureSession::firstOrCreate([
                'class_section_id' => $section->id,
                'session_number' => $i,
            ], [
                'id' => (string) Str::uuid(),
                'title' => "Pertemuan Ke-{$i}",
                'planned_date' => now()->subDays(10 - $i)->toDateString(),
                'actual_date' => now()->subDays(10 - $i)->toDateString(),
            ]);
        }

        $submission = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
        ], [
            'id' => (string) Str::uuid(),
            'total_sks' => 3,
            'status' => 'approved',
        ]);

        $krsItem = KrsItem::firstOrCreate([
            'krs_submission_id' => $submission->id,
            'class_section_id' => $section->id,
        ], [
            'id' => (string) Str::uuid(),
            'score_presence' => 0.00,
            'score_task' => 0.00,
            'score_uts' => 0.00,
            'score_uas' => 0.00,
        ]);

        // Reset any existing attendance for this student in this section
        $sessionIds = $section->lectureSessions()->pluck('id');
        Attendance::whereIn('lecture_session_id', $sessionIds)->where('student_id', $student->id)->delete();

        // Create 6 present attendances out of 10 held sessions = 60% attendance (< 75%)
        $sessions = $section->lectureSessions()->take(6)->get();
        foreach ($sessions as $session) {
            Attendance::create([
                'id' => (string) Str::uuid(),
                'lecture_session_id' => $session->id,
                'student_id' => $student->id,
                'attendance_status' => 'present',
                'verified_method' => 'qr_scan',
            ]);
        }

        // 1. Assert Kartu Ujian (KRS Print) displays CEKAL / BLOCKED FOR UAS
        $printResponse = $this->actingAs($studentUser)->get('/portal/print/krs');
        $printResponse->assertStatus(200);
        $printResponse->assertSee('CEKAL / BLOCKED FOR UAS');

        // 2. Assert Student is blocked from starting the Online Exam
        $examResponse = $this->actingAs($studentUser)->get("/portal/student/lms/exam/{$section->id}");
        $examResponse->assertStatus(403);
        $this->assertTrue((bool)$krsItem->fresh()->is_exam_blocked);

        // 3. Kaprodi unlocks with valid audit reason
        $overrideResponse = $this->actingAs($kaprodiUser)->postJson("/portal/kaprodi/exam-unblock/{$krsItem->id}", [
            'audit_reason' => 'Mahasiswa mewakili UPH dalam kompetisi gemastik nasional resmi rektorat.',
        ]);
        $overrideResponse->assertStatus(200);
        $overrideResponse->assertJson(['success' => true]);

        // Refresh and assert exam is unblocked
        $krsItem->refresh();
        $this->assertFalse((bool)$krsItem->is_exam_blocked);
        $this->assertNotEmpty($krsItem->exam_unblocked_reason);

        // 4. Student can now access and take the Online Exam
        $allowedExamResponse = $this->actingAs($studentUser)->get("/portal/student/lms/exam/{$section->id}");
        $allowedExamResponse->assertStatus(200);
        $allowedExamResponse->assertSee('Lembar Soal Ujian Standar OBE');

        // 5. Student submits exam answers
        $submitExamResponse = $this->actingAs($studentUser)->postJson("/portal/student/lms/exam/{$section->id}/submit", [
            'exam_type' => 'uas',
            'score' => 90.00,
        ]);
        $submitExamResponse->assertStatus(200);
        $submitExamResponse->assertJson(['success' => true, 'score' => 90.00]);

        $this->assertEquals(90.00, (float)$krsItem->fresh()->score_uas);
    }

    /**
     * Test 2: Course Retake (Mengulang) & Highest Grade Replacement in Cumulative IPK
     */
    public function test_course_retake_flagged_and_recalculates_cumulative_ipk_with_highest_grade(): void
    {
        $studentUser = User::where('email', 'student2@uph.edu')->first() ?: User::where('email', 'student1@uph.edu')->first();
        $student = $studentUser->student;

        $p1 = AcademicPeriod::firstOrCreate(['code' => 'TEST_P1'], [
            'id' => (string) Str::uuid(),
            'name' => '2025/2026 Ganjil Test',
            'semester_type' => 'Ganjil',
            'start_date' => '2025-08-01',
            'end_date' => '2025-12-31',
            'krs_start_date' => '2025-08-01 00:00:00',
            'krs_end_date' => '2025-08-15 00:00:00',
            'grade_input_start_date' => '2025-12-01 00:00:00',
            'grade_input_end_date' => '2025-12-31 00:00:00',
        ]);

        $p2 = AcademicPeriod::firstOrCreate(['code' => 'TEST_P2'], [
            'id' => (string) Str::uuid(),
            'name' => '2025/2026 Genap Test',
            'semester_type' => 'Genap',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'krs_start_date' => '2026-01-01 00:00:00',
            'krs_end_date' => '2026-01-15 00:00:00',
            'grade_input_start_date' => '2026-06-01 00:00:00',
            'grade_input_end_date' => '2026-06-30 00:00:00',
        ]);

        $course = Course::where('study_program_id', $student->study_program_id)->firstOrFail();
        $lecturer = \App\Models\Lecturer::firstOrFail();

        // Section 1 in Period 1
        $sec1 = ClassSection::firstOrCreate([
            'course_id' => $course->id,
            'academic_period_id' => $p1->id,
            'section_code' => 'T1',
        ], [
            'id' => (string) Str::uuid(),
            'lecturer_id' => $lecturer->id,
            'max_quota' => 40,
            'enrolled_count' => 1,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:30',
        ]);

        // Section 2 in Period 2 (Retake)
        $sec2 = ClassSection::firstOrCreate([
            'course_id' => $course->id,
            'academic_period_id' => $p2->id,
            'section_code' => 'T2',
        ], [
            'id' => (string) Str::uuid(),
            'lecturer_id' => $lecturer->id,
            'max_quota' => 40,
            'enrolled_count' => 1,
            'day_of_week' => 2,
            'start_time' => '08:00',
            'end_time' => '10:30',
        ]);

        // Period 1 Submission: Grade D (Point 1.00)
        $sub1 = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $p1->id,
        ], [
            'id' => (string) Str::uuid(),
            'total_sks' => $course->sks,
            'status' => 'approved',
        ]);

        $item1 = KrsItem::firstOrCreate([
            'krs_submission_id' => $sub1->id,
            'class_section_id' => $sec1->id,
        ], [
            'id' => (string) Str::uuid(),
            'letter_grade' => 'D',
            'grade_point' => 1.00,
            'final_numeric_score' => 45.00,
            'is_locked' => true,
        ]);

        // Period 2 Submission: Grade A (Point 4.00)
        $sub2 = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $p2->id,
        ], [
            'id' => (string) Str::uuid(),
            'total_sks' => $course->sks,
            'status' => 'approved',
        ]);

        $item2 = KrsItem::firstOrCreate([
            'krs_submission_id' => $sub2->id,
            'class_section_id' => $sec2->id,
        ], [
            'id' => (string) Str::uuid(),
            'is_retake' => true,
            'letter_grade' => 'A',
            'grade_point' => 4.00,
            'final_numeric_score' => 92.00,
            'is_locked' => true,
        ]);

        $this->assertTrue((bool)$item2->is_retake);

        // Recalculate GPA: must pick Grade A (4.00) and count credits only once
        $recalculatedIpk = $student->recalculateCumulativeGpa();
        $this->assertGreaterThanOrEqual(3.00, $recalculatedIpk);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'current_gpa_ipk' => $recalculatedIpk,
        ]);
    }

    /**
     * Test 3: Real File Storage Engine Upload & Download
     */
    public function test_real_file_storage_upload_and_download_with_mime_validation(): void
    {
        Storage::fake('public');

        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();

        $session = LectureSession::firstOrFail();

        // 1. Lecturer publishes assignment with PDF file attachment
        $fakePdf = UploadedFile::fake()->create('syllabus_material.pdf', 500, 'application/pdf');

        $assignmentResponse = $this->actingAs($lecturerUser)->postJson("/portal/lms/session/{$session->id}/assignment", [
            'title' => 'Tugas Mandiri 01 - Cloud Architecture',
            'description' => 'Pelajari arsitektur microservices dan kumpulkan resume.',
            'deadline' => now()->addDays(7)->toDateTimeString(),
            'max_score' => 100,
            'attachment' => $fakePdf,
        ]);

        $assignmentResponse->assertStatus(200);
        $assignment = LmsAssignment::where('lecture_session_id', $session->id)->latest()->firstOrFail();
        $this->assertNotNull($assignment->attachment_path);
        Storage::disk('public')->assertExists($assignment->attachment_path);

        // Download assignment attachment
        $downloadAssignmentRes = $this->actingAs($studentUser)->get("/portal/storage/download/assignment/{$assignment->id}");
        $downloadAssignmentRes->assertStatus(200);

        // 2. Student submits assignment with PDF file
        $submissionPdf = UploadedFile::fake()->create('resume_student.pdf', 300, 'application/pdf');
        $submitRes = $this->actingAs($studentUser)->postJson("/portal/lms/assignment/{$assignment->id}/submit", [
            'submission_file' => $submissionPdf,
        ]);
        $submitRes->assertStatus(200);

        $submission = LmsSubmission::where('lms_assignment_id', $assignment->id)
            ->where('student_id', $studentUser->student->id)
            ->firstOrFail();

        $this->assertNotNull($submission->submission_file_path);
        Storage::disk('public')->assertExists($submission->submission_file_path);

        // Download student submission
        $downloadSubmissionRes = $this->actingAs($lecturerUser)->get("/portal/storage/download/submission/{$submission->id}");
        $downloadSubmissionRes->assertStatus(200);

        // 3. Security validation: Reject invalid executable file types
        $maliciousFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');
        $maliciousRes = $this->actingAs($studentUser)->postJson("/portal/lms/assignment/{$assignment->id}/submit", [
            'submission_file' => $maliciousFile,
        ]);
        $maliciousRes->assertStatus(422);
    }

    /**
     * Test 4: Semester Rollover State Transition, Mass Billing & In-App Notification Center
     */
    public function test_semester_rollover_state_transition_akm_archive_and_in_app_notifications(): void
    {
        $baakUser = User::where('email', 'baak@uph.edu')->firstOrFail();
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;
        $student->update(['academic_status' => 'active']);

        $pFrom = AcademicPeriod::where('code', '20251')->first() ?: AcademicPeriod::where('is_active', true)->firstOrFail();
        $pFrom->update(['is_active' => true]);

        $targetCode = 'TARGET_' . strtoupper(Str::random(6));
        $pTo = AcademicPeriod::create([
            'id' => (string) Str::uuid(),
            'code' => $targetCode,
            'name' => '2026/2027 Rollover ' . $targetCode,
            'semester_type' => 'Genap',
            'start_date' => '2027-02-01',
            'end_date' => '2027-07-31',
            'krs_start_date' => '2027-02-01 00:00:00',
            'krs_end_date' => '2027-02-15 00:00:00',
            'grade_input_start_date' => '2027-07-01 00:00:00',
            'grade_input_end_date' => '2027-07-31 00:00:00',
            'is_active' => false,
        ]);

        $initialSemester = $student->current_semester;

        // Execute Rollover Service
        $service = new SemesterRolloverService();
        $summary = $service->rollover($pFrom, $pTo, $baakUser);

        $this->assertGreaterThan(0, $summary['students_processed']);
        $this->assertGreaterThan(0, $summary['invoices_generated']);

        // Assert Period status updated
        $this->assertFalse((bool)$pFrom->fresh()->is_active);
        $this->assertTrue((bool)$pTo->fresh()->is_active);

        // Assert AKM history archived
        $this->assertDatabaseHas('student_akm_histories', [
            'student_id' => $student->id,
            'academic_period_id' => $pFrom->id,
        ]);

        // Assert Student semester incremented and hold reset
        $student->refresh();
        $this->assertEquals($initialSemester + 1, $student->current_semester);
        $this->assertFalse((bool)$student->is_financially_cleared);

        // Assert New term invoice generated
        $this->assertDatabaseHas('student_invoices', [
            'student_id' => $student->id,
            'academic_period_id' => $pTo->id,
            'payment_status' => 'unpaid',
        ]);

        // Assert In-App notification generated for student
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $studentUser->id,
            'type' => 'semester_rollover',
            'is_read' => false,
        ]);

        // Test Notification Center endpoint
        $notifResponse = $this->actingAs($studentUser)->getJson('/portal/notifications/unread');
        $notifResponse->assertStatus(200);
        $notifResponse->assertJsonStructure(['count', 'notifications']);
        $this->assertGreaterThan(0, $notifResponse->json('count'));

        // Mark notification as read
        $notif = InAppNotification::where('user_id', $studentUser->id)->latest()->firstOrFail();
        $readRes = $this->actingAs($studentUser)->postJson("/portal/notifications/{$notif->id}/read");
        $readRes->assertStatus(200);
        $this->assertTrue((bool)$notif->fresh()->is_read);

        // Clean up test period state back to original pFrom
        $pTo->update(['is_active' => false]);
        $pFrom->update(['is_active' => true, 'is_krs_active' => true]);
    }
}
