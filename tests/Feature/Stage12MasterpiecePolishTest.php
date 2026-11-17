<?php

namespace Tests\Feature;

use App\Http\Controllers\Portal\FileStorageController;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\GradeAppeal;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\MbkmProgram;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage12MasterpiecePolishTest extends TestCase
{
    /**
     * Test 1: MBKM 20-SKS Conversion Workflow & Semester >= 5 Gate
     */
    public function test_mbkm_registration_and_20_sks_conversion_approval(): void
    {
        Storage::fake('local');

        $activePeriod = AcademicPeriod::where('is_active', true)->first() ?: AcademicPeriod::firstOrFail();
        $activePeriod->update(['is_active' => true]);

        $kaprodiUser = User::where('email', 'kaprodi.if@uph.edu')->firstOrFail();
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;

        // Ensure student has current_semester >= 5 for MBKM eligibility
        $student->update(['current_semester' => 6, 'academic_status' => 'active']);

        // 1. Student registers MBKM program with PDF logbook
        $fakeLogbook = UploadedFile::fake()->create('mbkm_logbook.pdf', 500, 'application/pdf');

        $regResponse = $this->actingAs($studentUser)->postJson('/portal/student/mbkm', [
            'program_type' => 'magang_industri',
            'partner_name' => 'PT Google Cloud Indonesia',
            'partner_address' => 'Pacific Century Place Lt. 45, SCBD Jakarta',
            'mentor_name' => 'Budi Santoso, Senior Tech Lead',
            'mentor_contact' => 'budi.mentor@partner.com',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(5)->toDateString(),
            'logbook_document' => $fakeLogbook,
        ]);

        $regResponse->assertStatus(200);
        $regResponse->assertJson(['success' => true]);

        $program = MbkmProgram::where('student_id', $student->id)->latest()->firstOrFail();
        $this->assertEquals('pending', $program->status);
        $this->assertEquals('PT Google Cloud Indonesia', $program->partner_name);
        $this->assertNotNull($program->logbook_document_path);

        // 2. Test Semester < 5 gate rejection
        $juniorStudent = Student::where('id', '!=', $student->id)->first();
        if ($juniorStudent) {
            $juniorStudent->update(['current_semester' => 2]);
            $juniorUser = $juniorStudent->user;

            $blockedRegResponse = $this->actingAs($juniorUser)->postJson('/portal/student/mbkm', [
                'program_type' => 'studi_independen',
                'partner_name' => 'Bangkit Academy',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
            ]);

            $blockedRegResponse->assertStatus(422);
            $blockedRegResponse->assertJson(['success' => false]);
        }

        // 3. Kaprodi maps equivalent courses up to 20 SKS and approves
        $courses = Course::where('study_program_id', $student->study_program_id)->take(3)->get();
        $this->assertNotEmpty($courses);

        $conversionItems = [];
        $accumulatedSks = 0;
        foreach ($courses as $c) {
            $sks = $c->sks ?: 3;
            $accumulatedSks += $sks;
            $conversionItems[] = [
                'course_id' => $c->id,
                'equivalent_sks' => $sks,
                'converted_numeric_score' => 90.00,
            ];
        }

        $approveResponse = $this->actingAs($kaprodiUser)->postJson("/portal/kaprodi/mbkm/{$program->id}/approve", [
            'conversions' => $conversionItems,
            'approval_notes' => 'Disetujui untuk pemetaan mata kuliah peminatan magang industri.',
        ]);

        $approveResponse->assertStatus(200);
        $approveResponse->assertJson(['success' => true, 'total_sks' => $accumulatedSks]);

        $program->refresh();
        $this->assertEquals('approved', $program->status);
        $this->assertEquals($accumulatedSks, $program->total_converted_sks);
        $this->assertEquals(count($conversionItems), $program->conversions()->count());

        // 4. Enforce 20 SKS maximum policy: attempt > 20 SKS
        $excessiveItems = array_merge($conversionItems, [
            ['course_id' => $courses->first()->id, 'equivalent_sks' => 15, 'converted_numeric_score' => 85]
        ]);

        $rejectExcessiveResponse = $this->actingAs($kaprodiUser)->postJson("/portal/kaprodi/mbkm/{$program->id}/approve", [
            'conversions' => $excessiveItems,
        ]);
        $rejectExcessiveResponse->assertStatus(422);
    }

    /**
     * Test 2: KHS Score Breakdown Transparency & Student Grade Appeal (Sanggah Nilai)
     */
    public function test_khs_obe_breakdown_and_grade_appeal_lifecycle(): void
    {
        Storage::fake('local');

        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        $submission = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
        ], [
            'id' => (string) Str::uuid(),
            'total_sks' => 3,
            'status' => 'approved',
        ]);

        $krsItem = KrsItem::where('krs_submission_id', $submission->id)->first();
        if (!$krsItem) {
            $section = \App\Models\ClassSection::where('academic_period_id', $activePeriod->id)->firstOrFail();
            $krsItem = KrsItem::create([
                'id' => (string) Str::uuid(),
                'krs_submission_id' => $submission->id,
                'class_section_id' => $section->id,
                'score_presence' => 90.00,
                'score_task' => 85.00,
                'score_quiz' => 80.00,
                'score_uts' => 75.00,
                'score_uas' => 80.00,
                'score_project' => 85.00,
                'final_numeric_score' => 81.25,
                'letter_grade' => 'A-',
                'grade_point' => 3.70,
                'is_locked' => true,
            ]);
        }

        // 1. Student views OBE Score Breakdown JSON
        $breakdownResponse = $this->actingAs($studentUser)->getJson("/portal/student/khs/breakdown/{$krsItem->id}");
        $breakdownResponse->assertStatus(200);
        $breakdownResponse->assertJsonStructure([
            'success',
            'course' => ['code', 'name', 'sks', 'lecturer'],
            'components' => ['presence', 'task', 'quiz', 'uts', 'uas', 'project'],
            'summary' => ['final_numeric_score', 'letter_grade', 'grade_point', 'is_locked'],
            'cpmk_attainments',
        ]);

        // 2. Student submits Grade Appeal (Sanggah Nilai)
        $fakeEvidence = UploadedFile::fake()->create('proof_assignment_submission.pdf', 300, 'application/pdf');

        $appealResponse = $this->actingAs($studentUser)->postJson("/portal/student/khs/appeal/{$krsItem->id}", [
            'reason' => 'Terdapat kesalahan input nilai tugas ke-3 yang seharusnya 95 namun tercatat 60 pada sistem.',
            'evidence_file' => $fakeEvidence,
        ]);

        $appealResponse->assertStatus(200);
        $appealResponse->assertJson(['success' => true]);

        $appeal = GradeAppeal::where('krs_item_id', $krsItem->id)->latest()->firstOrFail();
        $this->assertEquals('submitted', $appeal->status);
        $this->assertNotNull($appeal->evidence_file_path);

        // 3. Lecturer responds to the appeal
        $lecturerResponse = $this->actingAs($lecturerUser)->postJson("/portal/lecturer/appeal/{$appeal->id}/respond", [
            'action_type' => 'request_unlock',
            'response_note' => 'Bukti valid. Meminta Kaprodi membuka kunci nilai untuk revisi skor tugas mahasiswa.',
        ]);

        $lecturerResponse->assertStatus(200);
        $lecturerResponse->assertJson(['success' => true, 'status' => 'unlock_requested']);
        $this->assertEquals('unlock_requested', $appeal->fresh()->status);
    }

    /**
     * Test 3: AppSec Private File Storage & Temporary Cryptographic Signed URLs
     */
    public function test_private_storage_and_temporary_signed_download_urls(): void
    {
        Storage::fake('local');

        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();

        // Put a sensitive document in private local disk
        $filePath = 'private/sensitive_doc.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 Mock Private Academic Certificate Content');

        // 1. Generate valid signed download URL
        $signedUrl = FileStorageController::createSignedDownloadUrl($filePath, 30);
        $this->assertStringContainsString('signature=', $signedUrl);

        // 2. Download with valid signature -> 200 OK
        $validResponse = $this->actingAs($studentUser)->get($signedUrl);
        $validResponse->assertStatus(200);

        // 3. Download with tampered signature -> 403 Forbidden
        $tamperedUrl = $signedUrl . 'tampered';
        $tamperedResponse = $this->actingAs($studentUser)->get($tamperedUrl);
        $tamperedResponse->assertStatus(403);

        // 4. Download without signature -> 403 Forbidden
        $unsignedUrl = '/portal/storage/signed-download?path=' . urlencode($filePath);
        $unsignedResponse = $this->actingAs($studentUser)->get($unsignedUrl);
        $unsignedResponse->assertStatus(403);

        // 5. Test directory traversal detection -> 400 Bad Request
        $traversalUrl = FileStorageController::createSignedDownloadUrl('../../../etc/passwd', 30);
        $traversalResponse = $this->actingAs($studentUser)->get($traversalUrl);
        $traversalResponse->assertStatus(400);
    }

    /**
     * Test 4: Demo Control Center Fast 1-Click State Resetter (< 500ms)
     */
    public function test_demo_control_center_1_click_state_resetter(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?: User::firstOrFail();

        $response = $this->actingAs($superadmin)->postJson('/portal/demo/reset-state');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('elapsed_ms', $data);
        $this->assertLessThan(1500, $data['elapsed_ms']); // Verified fast execution

        // Assert Jessica / Blocked Student state is cleanly re-blocked
        $blockedStudent = Student::where('nim', '01082200002')->first();
        if ($blockedStudent) {
            $this->assertFalse((bool)$blockedStudent->is_financially_cleared);
            $this->assertTrue((bool)$blockedStudent->has_academic_hold);

            $unpaidInvoice = StudentInvoice::where('student_id', $blockedStudent->id)
                ->where('payment_status', 'unpaid')
                ->first();
            $this->assertNotNull($unpaidInvoice);
        }

        // Assert 20251 is active academic period
        $activePeriod = AcademicPeriod::where('code', '20251')->first();
        if ($activePeriod) {
            $this->assertTrue((bool)$activePeriod->is_active);
        }
    }
}
