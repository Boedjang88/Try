<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class Loop2KrsWarAndAdvisorApprovalTest extends TestCase
{
    /**
     * Operational Loop 2: KRS War, Atomic Seat Allocation, Schedule Conflict Detection,
     * and Academic Advisor Approval Unlocking Printable KRS.
     */
    public function test_loop2_krs_war_schedule_conflict_seat_locking_and_advisor_approval(): void
    {
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;

        // Ensure student is financially cleared
        $student->is_financially_cleared = true;
        $student->has_academic_hold = false;
        $student->save();

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();
        $activePeriod->is_krs_active = true;
        $activePeriod->krs_start_date = now()->subDays(2);
        $activePeriod->krs_end_date = now()->addDays(5);
        $activePeriod->save();

        // Clean previous submissions for test isolation
        KrsItem::whereHas('krsSubmission', fn($q) => $q->where('student_id', $student->id)->where('academic_period_id', $activePeriod->id))->delete();
        KrsSubmission::where('student_id', $student->id)->where('academic_period_id', $activePeriod->id)->delete();

        // Prepare two sections with identical time slots to test schedule clash
        $course1 = Course::firstOrFail();
        $course2 = Course::where('id', '!=', $course1->id)->firstOrFail();
        $room = Room::firstOrFail();
        $lecturer = $student->academicAdvisor;

        $sectionA = ClassSection::create([
            'id' => (string) Str::uuid(),
            'course_id' => $course1->id,
            'academic_period_id' => $activePeriod->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $room->id,
            'section_code' => 'TEST-A',
            'day_of_week' => 1, // Monday
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
            'max_quota' => 30,
            'enrolled_count' => 10,
            'is_closed' => false,
        ]);

        $sectionB = ClassSection::create([
            'id' => (string) Str::uuid(),
            'course_id' => $course2->id,
            'academic_period_id' => $activePeriod->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $room->id,
            'section_code' => 'TEST-B',
            'day_of_week' => 1, // Same day (Monday)
            'start_time' => '09:00:00', // Overlaps with 08:00 - 10:30
            'end_time' => '11:30:00',
            'max_quota' => 30,
            'enrolled_count' => 5,
            'is_closed' => false,
        ]);

        // Step 1: Enroll into Section A -> Should succeed and increment enrolled_count
        $initialEnrolled = $sectionA->enrolled_count;

        $enrollResponse = $this->actingAs($studentUser)->postJson('/krs/enroll', [
            'class_section_id' => $sectionA->id,
        ]);

        $enrollResponse->assertStatus(200);
        $enrollResponse->assertJson(['success' => true]);

        $sectionA->refresh();
        $this->assertEquals($initialEnrolled + 1, $sectionA->enrolled_count, 'Expected enrolled_count to increment atomically.');

        // Step 2: Attempt to enroll into Section B -> Should detect schedule clash (422)
        $clashResponse = $this->actingAs($studentUser)->postJson('/krs/enroll', [
            'class_section_id' => $sectionB->id,
        ]);

        $clashResponse->assertStatus(422);
        $clashResponse->assertJson([
            'success' => false,
            'code' => 'SCHEDULE_CLASH',
        ]);

        // Step 3: Submit KRS to Academic Advisor
        $submitResponse = $this->actingAs($studentUser)->postJson('/krs/submit');
        $submitResponse->assertStatus(200);
        $submitResponse->assertJson(['success' => true]);

        $submission = KrsSubmission::where('student_id', $student->id)
            ->where('academic_period_id', $activePeriod->id)
            ->firstOrFail();
        $this->assertEquals('submitted', $submission->status);

        // Step 4: Before Dosen PA approval, student CANNOT print official KRS (403 Forbidden)
        $printBeforeApproval = $this->actingAs($studentUser)->get("/portal/print/krs/{$submission->id}");
        $printBeforeApproval->assertStatus(403);

        // Step 5: Dosen PA (lecturer1) reviews and approves KRS
        $lecturerUser = $lecturer->user;
        $approveResponse = $this->actingAs($lecturerUser)->postJson("/krs/advisor/{$submission->id}/approve");
        $approveResponse->assertStatus(200);
        $approveResponse->assertJson(['success' => true]);

        $submission->refresh();
        $this->assertEquals('approved', $submission->status);
        $this->assertNotNull($submission->approved_at);

        // Step 6: After Dosen PA approval, official printable KRS is unlocked (200 OK)
        $printAfterApproval = $this->actingAs($studentUser)->get("/portal/print/krs/{$submission->id}");
        $printAfterApproval->assertStatus(200);
        $printAfterApproval->assertSee('KARTU RENCANA STUDI');
        $printAfterApproval->assertSee('Disetujui Dosen PA');
        $printAfterApproval->assertSee('UPH Digital Signature');
    }
}
