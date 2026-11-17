<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\FacilityBooking;
use App\Models\Faculty;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Lecturer;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\StudyProgram;
use App\Models\TracerStudy;
use App\Models\User;
use App\Models\YudisiumClearance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage13SmartCampusEcosystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $studentUser;
    protected Student $student;
    protected User $baakUser;
    protected AcademicPeriod $period;
    protected ClassSection $section;
    protected KrsSubmission $krsSubmission;
    protected KrsItem $krsItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = AcademicPeriod::where('is_active', true)->first() ?: AcademicPeriod::firstOrFail();
        $this->studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $this->student = $this->studentUser->student;
        $this->student->update([
            'current_semester' => 5,
            'current_gpa_ipk' => 3.85,
            'total_sks_completed' => 80,
            'academic_status' => 'active',
            'is_financially_cleared' => true,
        ]);

        $this->baakUser = User::where('email', 'baak@uph.edu')->firstOrFail();

        $this->section = ClassSection::where('academic_period_id', $this->period->id)->first()
            ?: ClassSection::firstOrFail();

        $this->krsSubmission = KrsSubmission::firstOrCreate(
            ['student_id' => $this->student->id, 'academic_period_id' => $this->period->id],
            [
                'id' => (string) Str::uuid(),
                'status' => 'approved',
                'total_sks' => 3,
                'submitted_at' => now(),
                'approved_at' => now(),
            ]
        );

        $this->krsItem = KrsItem::firstOrCreate(
            ['krs_submission_id' => $this->krsSubmission->id, 'class_section_id' => $this->section->id],
            [
                'id' => (string) Str::uuid(),
                'score_presence' => 90,
                'score_task' => 85,
                'score_quiz' => 85,
                'score_uts' => 90,
                'score_uas' => 90,
                'score_project' => 95,
                'final_numeric_score' => 89.5,
                'letter_grade' => 'A',
                'grade_point' => 4.00,
            ]
        );
    }

    /**
     * 1. Test Smart Academic Copilot: Target GPA & Cum Laude Simulation.
     */
    public function test_target_gpa_and_cum_laude_simulation_accuracy(): void
    {
        $this->actingAs($this->studentUser);

        // View Smart Advisor page
        $res = $this->get('/portal/student/smart-advisor');
        $res->assertStatus(200);
        $res->assertSee('Smart Academic Advisor');

        // Clean historical D/E for honors test isolation
        KrsItem::whereHas('krsSubmission', function ($q) {
            $q->where('student_id', $this->student->id);
        })->whereIn('letter_grade', ['D', 'E'])->update(['letter_grade' => 'B', 'grade_point' => 3.00]);

        $activeItems = KrsItem::whereHas('krsSubmission', function ($q) {
            $q->where('student_id', $this->student->id)
              ->where('academic_period_id', $this->period->id);
        })->get();

        $allAGrades = [];
        foreach ($activeItems as $ai) {
            $allAGrades[$ai->id] = 'A';
        }

        // Simulate grade of A for all active courses
        $simResponse = $this->postJson('/portal/student/smart-advisor/simulate', [
            'simulated_grades' => $allAGrades,
        ]);

        $simResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'projected_ips' => 4.00,
                    'graduation_predicate' => 'Summa Cum Laude',
                    'honors_eligible' => true,
                ],
            ]);

        // Simulate grade of D on first active course (should disqualify from Cum Laude)
        $gradesWithD = $allAGrades;
        $gradesWithD[$this->krsItem->id] = 'D';

        $simDisqualified = $this->postJson('/portal/student/smart-advisor/simulate', [
            'simulated_grades' => $gradesWithD,
        ]);

        $simDisqualified->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'honors_eligible' => false,
                ],
            ]);
    }

    /**
     * 2. Test BAAK/BAUK Streaming CSV Export and Validated Bulk CSV Import.
     */
    public function test_streaming_csv_export_and_bulk_csv_import_transaction(): void
    {
        $this->actingAs($this->baakUser);

        // 1. Streaming CSV Exports
        $exportStudents = $this->get('/portal/bulk-data/export/students');
        $exportStudents->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportStudents->headers->get('Content-Type'));

        $exportSchedules = $this->get('/portal/bulk-data/export/schedules');
        $exportSchedules->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportSchedules->headers->get('Content-Type'));

        $exportInvoices = $this->get('/portal/bulk-data/export/invoices');
        $exportInvoices->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportInvoices->headers->get('Content-Type'));

        // 2. Dry-Run Import Preview with CSV content
        $csvContent = "krs_item_id,nim,nama,kode_mk,kehadiran,tugas,kuis,uts,uas,proyek\n";
        $csvContent .= "{$this->krsItem->id},01011230099,Christian,CS101,95,90,90,92,92,95\n";
        $csvContent .= "invalid-uuid,01011230000,Ghost,CS101,80,80,80,80,80,80\n";

        $tempFile = UploadedFile::fake()->createWithContent('grades.csv', $csvContent);

        $previewRes = $this->postJson('/portal/bulk-data/preview', [
            'csv_file' => $tempFile,
            'import_type' => 'grades',
        ]);

        $previewRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid_count' => 1,
                'invalid_count' => 1,
            ]);

        // 3. Commit Import Transaction
        $commitRes = $this->postJson('/portal/bulk-data/commit', [
            'import_type' => 'grades',
            'items' => [
                [
                    'krs_item_id' => $this->krsItem->id,
                    'scores' => [
                        'presence' => 95,
                        'task' => 90,
                        'quiz' => 90,
                        'uts' => 92,
                        'uas' => 92,
                        'project' => 95,
                    ],
                ],
            ],
        ]);

        $commitRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'processed_count' => 1,
            ]);

        // Verify database update
        $this->krsItem->refresh();
        $this->assertEquals(95, (int)$this->krsItem->score_presence);
        $this->assertEquals('A', $this->krsItem->letter_grade);
    }

    /**
     * 3. Test Campus Life: Active Library Loan blocks Yudisium Clearance until returned.
     */
    public function test_active_library_loan_blocks_yudisium_clearance(): void
    {
        // 1. Create an active unreturned library loan for student
        $loan = LibraryLoan::create([
            'id' => (string) Str::uuid(),
            'student_id' => $this->student->id,
            'book_title' => 'Modern Operating Systems 4th Edition',
            'book_code' => 'LIB-CS-0042',
            'borrowed_at' => now()->subDays(10),
            'due_date' => now()->addDays(4),
            'status' => 'borrowed',
            'fine_amount' => 0.00,
        ]);

        // 2. Yudisium controller ensures clearance exists
        $yudisiumController = app(\App\Http\Controllers\YudisiumController::class);
        $clearance = $yudisiumController->ensureClearanceExists($this->student, $this->period);

        // Assert library_cleared is false due to active loan
        $this->assertFalse($clearance->library_cleared);

        // 3. Attempting to force-clear library via BAAK/Staff endpoint is rejected
        $this->actingAs($this->baakUser);
        $rejectRes = $this->postJson("/portal/yudisium/{$clearance->id}/verify", [
            'department' => 'library',
            'cleared' => true,
        ]);

        $rejectRes->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // 4. Return book
        $loan->status = 'returned';
        $loan->returned_at = now();
        $loan->save();

        // 5. Verification now succeeds
        $approveRes = $this->postJson("/portal/yudisium/{$clearance->id}/verify", [
            'department' => 'library',
            'cleared' => true,
        ]);

        $approveRes->assertStatus(200);
        $clearance->refresh();
        $this->assertTrue($clearance->library_cleared);
    }

    /**
     * 4. Test Alumni Tracer Study Submission and Accreditation Metrics.
     */
    public function test_alumni_tracer_study_submission_and_accreditation_metrics(): void
    {
        $this->actingAs($this->studentUser);

        // Submit Tracer Study questionnaire
        $submitRes = $this->post('/portal/student/career-tracer/submit', [
            'graduation_year' => 2026,
            'employment_status' => 'employed',
            'company_name' => 'PT Bank Central Asia Tbk',
            'job_title' => 'Software Engineer',
            'waiting_time_months' => 2,
            'monthly_income_range' => '10 - 20 Juta',
            'job_major_alignment' => 'high',
        ]);

        $submitRes->assertRedirect();
        $submitRes->assertSessionHas('success');

        // Assert record saved in database
        $this->assertDatabaseHas('tracer_studies', [
            'student_id' => $this->student->id,
            'company_name' => 'PT Bank Central Asia Tbk',
            'job_major_alignment' => 'high',
        ]);

        // Visit Career & Tracer portal and verify metrics display
        $pageRes = $this->get('/portal/student/career-tracer');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('PT Bank Central Asia Tbk');
        $pageRes->assertSee('Tingkat Penyerapan Kerja');
    }

    /**
     * 5. Test Security Telemetry and Active Session Profile.
     */
    public function test_security_telemetry_and_active_session_metadata(): void
    {
        $this->actingAs($this->studentUser);

        $res = $this->get('/portal/account/security');
        $res->assertStatus(200);
        $res->assertSee('Telemetri Sesi');
        $res->assertSee('Keamanan Akun');
        $res->assertSee('Alamat IP Aktif');
        $res->assertSee('STUDENT');
    }
}
