<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class Loop4GradeLockKaprodiOverrideAndEdomTest extends TestCase
{
    /**
     * Operational Loop 4: OBE Grade Input, Immutable Lock, Kaprodi Authorized Override,
     * and Student Mandatory EDOM Survey Gate unlocking final KHS grade.
     */
    public function test_loop4_grading_lock_kaprodi_override_and_edom_flow(): void
    {
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();
        $lecturer = $lecturerUser->lecturer;

        $kaprodiUser = User::where('email', 'kaprodi.if@uph.edu')->firstOrFail();

        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();
        $course = Course::where('study_program_id', $kaprodiUser->study_program_id)->firstOrFail();
        $room = Room::firstOrFail();

        // Setup Class Section in Kaprodi's study program taught by lecturer1
        $section = ClassSection::create([
            'id' => (string) Str::uuid(),
            'course_id' => $course->id,
            'academic_period_id' => $activePeriod->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $room->id,
            'section_code' => 'OBE-L4',
            'day_of_week' => 2,
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'max_quota' => 30,
            'enrolled_count' => 1,
            'is_closed' => false,
        ]);

        $submission = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
        ], [
            'total_sks' => 3,
            'status' => 'approved',
        ]);
        $submission->status = 'approved';
        $submission->save();

        $krsItem = KrsItem::create([
            'id' => (string) Str::uuid(),
            'krs_submission_id' => $submission->id,
            'class_section_id' => $section->id,
            'is_locked' => false,
            'edom_completed' => false,
        ]);

        // Step 1: Lecturer bulk saves OBE weighted grades
        $saveGradesResponse = $this->actingAs($lecturerUser)->postJson("/grading/{$section->id}/save", [
            'grades' => [
                [
                    'krs_item_id' => $krsItem->id,
                    'score_presence' => 95,
                    'score_task' => 90,
                    'score_uts' => 88,
                    'score_uas' => 92,
                ],
            ],
        ]);

        $saveGradesResponse->assertStatus(200);
        $saveGradesResponse->assertJson(['success' => true]);

        $krsItem->refresh();
        $this->assertEquals('A', $krsItem->letter_grade);
        $this->assertEquals(4.00, (float) $krsItem->grade_point);

        // Step 2: Lecturer officially locks/freezes the grades ledger
        $lockResponse = $this->actingAs($lecturerUser)->postJson("/grading/{$section->id}/lock");
        $lockResponse->assertStatus(200);
        $lockResponse->assertJson(['success' => true]);

        $krsItem->refresh();
        $this->assertTrue((bool) $krsItem->is_locked);

        // Step 3: Lecturer attempts to edit while grades are locked -> MUST receive 403 Forbidden
        $editAttemptWhileLocked = $this->actingAs($lecturerUser)->postJson("/grading/{$section->id}/save", [
            'grades' => [
                [
                    'krs_item_id' => $krsItem->id,
                    'score_presence' => 80,
                    'score_task' => 80,
                    'score_uts' => 80,
                    'score_uas' => 80,
                ],
            ],
        ]);
        $editAttemptWhileLocked->assertStatus(403);

        // Step 4: Kaprodi attempts unlock with empty reason -> 422 Unprocessable
        $invalidUnlock = $this->actingAs($kaprodiUser)->postJson("/portal/kaprodi/grade-locks/{$section->id}/unlock", [
            'audit_reason' => '',
        ]);
        $invalidUnlock->assertStatus(422);

        // Step 5: Kaprodi unlocks with valid mandatory audit reason
        $validUnlock = $this->actingAs($kaprodiUser)->postJson("/portal/kaprodi/grade-locks/{$section->id}/unlock", [
            'audit_reason' => 'Perbaikan nilai tugas susulan atas persetujuan Ketua Program Studi.',
        ]);
        $validUnlock->assertStatus(200);
        $validUnlock->assertJson(['success' => true, 'is_locked' => false]);

        $krsItem->refresh();
        $this->assertFalse((bool) $krsItem->is_locked);

        // Assert AuditLog created for Kaprodi override
        $overrideLog = AuditLog::where('action', 'GRADE_LOCK_OVERRIDDEN')->latest()->first();
        $this->assertNotNull($overrideLog);
        $this->assertEquals($kaprodiUser->id, $overrideLog->user_id);
        $this->assertStringContainsString('Perbaikan nilai tugas susulan', $overrideLog->description);

        // Step 6: EDOM Survey Gate - Student cannot view final grade until EDOM is completed
        $this->assertFalse((bool) $krsItem->edom_completed);

        $answers = [
            1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5,
            6 => 5, 7 => 5, 8 => 5, 9 => 5, 10 => 5,
        ];

        $edomSubmitResponse = $this->actingAs($studentUser)->post("/edom/{$krsItem->id}", [
            'answers' => $answers,
            'feedback_positive' => 'Metode pengajaran sangat interaktif dan mudah dipahami.',
            'feedback_constructive' => 'Waktu pengerjaan tugas dapat diperpanjang.',
        ]);

        $edomSubmitResponse->assertRedirect(route('khs.index'));

        $krsItem->refresh();
        $this->assertTrue((bool) $krsItem->edom_completed);

        // Step 7: Final KHS grade is fully visible to student
        $khsResponse = $this->actingAs($studentUser)->get('/portal/student/khs');
        $khsResponse->assertStatus(200);
        $khsResponse->assertSee($course->code);
        $khsResponse->assertSee('A');
    }
}
