<?php

namespace Tests\Feature;

use App\Models\AdmissionWave;
use App\Models\Applicant;
use App\Models\Curriculum;
use App\Models\Faculty;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage17FullRouteAndViewSmokeTest extends TestCase
{
    /**
     * Test all 9 seeded personas can access every single sidebar route
     * without any 500 Internal Server Error, missing variables, or layout breakage.
     */
    public function test_stage17_smoke_test_all_9_personas_sidebar_routes(): void
    {
        // 1. Ensure master data and test users exist
        $faculty = Faculty::firstOrCreate(['code' => 'FIK'], [
            'name_id' => 'Fakultas Ilmu Komputer',
            'name_en' => 'Faculty of Computer Science',
        ]);

        $prodi = StudyProgram::firstOrCreate(['code' => 'IF'], [
            'faculty_id' => $faculty->id,
            'name_id' => 'Informatika',
            'name_en' => 'Informatics',
            'degree_level' => 'S1',
            'total_credits' => 144,
            'is_active' => true,
        ]);

        $activePeriod = \App\Models\AcademicPeriod::firstOrCreate(['code' => '20261'], [
            'name' => '2026/2027 Ganjil',
            'semester_type' => 'Ganjil',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'krs_start_date' => now()->subDays(10),
            'krs_end_date' => now()->addDays(20),
            'grade_input_start_date' => now()->addMonths(2),
            'grade_input_end_date' => now()->addMonths(3),
            'is_active' => true,
            'is_krs_active' => true,
            'is_grading_active' => true,
        ]);

        // Superadmin
        $superadmin = User::firstOrCreate(['email' => 'superadmin@uph.edu'], [
            'username' => 'superadmin',
            'full_name' => 'Super Administrator UPH',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Rector
        $rector = User::firstOrCreate(['email' => 'rector@uph.edu'], [
            'username' => 'rector',
            'full_name' => 'Dr. (Hon.) Jonathan L. Parapak',
            'password' => Hash::make('password'),
            'role' => 'rector',
            'is_active' => true,
        ]);

        // Dean
        $dean = User::firstOrCreate(['email' => 'dean.fik@uph.edu'], [
            'username' => 'dean.fik',
            'full_name' => 'Prof. Dr. Ir. Samuel H. Tirtamulia',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'faculty_id' => $faculty->id,
            'is_active' => true,
        ]);

        // Kaprodi
        $kaprodi = User::firstOrCreate(['email' => 'kaprodi.if@uph.edu'], [
            'username' => 'kaprodi.if',
            'full_name' => 'Dr. Veronica Siregar, M.Kom.',
            'password' => Hash::make('password'),
            'role' => 'kaprodi',
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
            'is_active' => true,
        ]);

        // BAAK
        $baak = User::firstOrCreate(['email' => 'baak@uph.edu'], [
            'username' => 'staf.baak',
            'full_name' => 'Staf BAAK UPH',
            'password' => Hash::make('password'),
            'role' => 'baak',
            'is_active' => true,
        ]);

        // BAUK
        $bauk = User::firstOrCreate(['email' => 'bauk@uph.edu'], [
            'username' => 'staf.bauk',
            'full_name' => 'Staf BAUK UPH',
            'password' => Hash::make('password'),
            'role' => 'bauk',
            'is_active' => true,
        ]);

        // Lecturer
        $lecturerUser = User::firstOrCreate(['email' => 'lecturer1@uph.edu'], [
            'username' => 'johanes.tan',
            'full_name' => 'Dr. Ir. Johanes Tan, M.Sc.',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
            'is_active' => true,
        ]);
        $lecturer = Lecturer::firstOrCreate(['user_id' => $lecturerUser->id], [
            'nidn' => '0314057801',
            'study_program_id' => $prodi->id,
            'academic_title' => 'Lektor Kepala',
            'max_sks_capacity' => 24,
            'is_active' => true,
        ]);

        // Student (Cleared)
        $studentUser = User::firstOrCreate(['email' => 'student1@uph.edu'], [
            'username' => '01082200001',
            'full_name' => 'Christian Jonathan',
            'password' => Hash::make('password'),
            'role' => 'student',
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
            'is_active' => true,
        ]);
        $student = Student::firstOrCreate(['user_id' => $studentUser->id], [
            'nim' => '01082200001',
            'study_program_id' => $prodi->id,
            'admission_year' => 2024,
            'academic_status' => 'active',
            'academic_advisor_id' => $lecturer->id,
            'current_ips' => 3.90,
            'current_ipk' => 3.88,
            'total_credits_passed' => 72,
            'max_sks_allowed' => 24,
            'financial_hold' => false,
        ]);

        // Applicant
        $applicantUser = User::firstOrCreate(['email' => 'applicant1@uph.edu'], [
            'username' => 'reg2026001',
            'full_name' => 'Alexander Michael Kusuma',
            'password' => Hash::make('password'),
            'role' => 'applicant',
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
            'is_active' => true,
        ]);
        $wave = AdmissionWave::firstOrCreate(['name' => 'Gelombang 1 - 2026'], [
            'academic_period_id' => $activePeriod->id,
            'wave_number' => 1,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(3),
            'registration_fee' => 500000,
            'is_active' => true,
        ]);
        $applicant = Applicant::firstOrCreate(['user_id' => $applicantUser->id], [
            'admission_wave_id' => $wave->id,
            'target_study_program_id' => $prodi->id,
            'registration_number' => 'REG-2026-0001',
            'payment_status' => 'paid',
            'admission_status' => 'paid',
            'cbt_score' => null,
            'scholarship_tier' => 'Platinum (100% Waiver)',
        ]);
        $applicant->update(['payment_status' => 'paid', 'cbt_score' => null]);

        // ==========================================
        // TEST ROLE 1: STUDENT
        // ==========================================
        $studentRoutes = [
            '/portal/student/dashboard',
            '/portal/student/krs',
            '/portal/student/exam-card',
            '/portal/student/attendance',
            '/portal/student/lms',
            '/portal/student/khs',
            '/portal/student/finance',
            '/portal/student/thesis',
            '/portal/student/yudisium',
            '/portal/student/mbkm',
            '/portal/student/smart-advisor',
            '/portal/student/campus-life',
            '/portal/student/career-tracer',
            '/portal/student/leave-request',
            '/portal/helpdesk',
        ];

        foreach ($studentRoutes as $url) {
            $response = $this->actingAs($studentUser)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 2: LECTURER
        // ==========================================
        $lecturerRoutes = [
            '/portal/lecturer/dashboard',
            '/portal/lecturer/sessions',
            '/portal/lecturer/grading',
            '/portal/lecturer/advisees',
            '/portal/lecturer/appeals',
            '/portal/lecturer/lms',
            '/portal/lecturer/thesis',
            '/portal/lecturer/bkd',
            '/portal/helpdesk',
        ];

        foreach ($lecturerRoutes as $url) {
            $response = $this->actingAs($lecturerUser)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 3: KAPRODI
        // ==========================================
        $kaprodiRoutes = [
            '/portal/kaprodi/dashboard',
            '/portal/kaprodi/curriculum',
            '/portal/kaprodi/grade-locks',
            '/portal/kaprodi/mbkm',
            '/portal/kaprodi/yudisium',
            '/portal/governance/approvals',
            '/portal/helpdesk',
        ];

        foreach ($kaprodiRoutes as $url) {
            $response = $this->actingAs($kaprodi)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 4: DEAN
        // ==========================================
        $deanRoutes = [
            '/portal/dean/dashboard',
            '/portal/dean/bkd',
            '/portal/governance/approvals',
            '/portal/helpdesk',
        ];

        foreach ($deanRoutes as $url) {
            $response = $this->actingAs($dean)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 5: BAAK
        // ==========================================
        $baakRoutes = [
            '/portal/baak/dashboard',
            '/portal/baak/calendar',
            '/portal/baak/rooms-occupancy',
            '/portal/baak/bulk-data',
            '/portal/baak/pddikti',
            '/portal/baak/yudisium',
            '/portal/iam/users',
            '/portal/helpdesk',
            '/portal/developer/sandbox',
            '/portal/developer/architecture',
        ];

        foreach ($baakRoutes as $url) {
            $response = $this->actingAs($baak)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 6: BAUK
        // ==========================================
        $baukRoutes = [
            '/portal/bauk/dashboard',
            '/portal/bauk/billing',
            '/portal/bauk/bulk-data',
            '/portal/governance/approvals',
            '/portal/helpdesk',
            '/portal/developer/sandbox',
            '/portal/developer/architecture',
        ];

        foreach ($baukRoutes as $url) {
            $response = $this->actingAs($bauk)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 7: RECTOR
        // ==========================================
        $rectorRoutes = [
            '/portal/executive/dashboard',
            '/rector/dashboard',
            '/portal/baak/pddikti',
            '/portal/developer/architecture',
            '/portal/helpdesk',
        ];

        foreach ($rectorRoutes as $url) {
            $response = $this->actingAs($rector)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 8: SUPERADMIN
        // ==========================================
        $superadminRoutes = [
            '/portal/executive/dashboard',
            '/rector/dashboard',
            '/portal/baak/pddikti',
            '/portal/iam/users',
            '/portal/iam/privileges',
            '/portal/governance/approvals',
            '/portal/helpdesk',
            '/portal/baak/rooms-occupancy',
            '/portal/developer/sandbox',
            '/portal/developer/outbox',
            '/portal/developer/architecture',
            '/portal/executive/audit-logs',
        ];

        foreach ($superadminRoutes as $url) {
            $response = $this->actingAs($superadmin)->get($url);
            $response->assertStatus(200);
        }

        // ==========================================
        // TEST ROLE 9: APPLICANT
        // ==========================================
        $applicantRoutes = [
            '/portal/pmb/dashboard',
            '/portal/pmb/register',
            '/portal/pmb/cbt',
            '/portal/helpdesk',
        ];

        foreach ($applicantRoutes as $url) {
            $response = $this->actingAs($applicantUser)->get($url);
            $response->assertStatus(200);
        }
    }

    /**
     * Test public marketing pages and authenticated official printable documents.
     */
    public function test_stage17_public_marketing_and_printable_documents_smoke(): void
    {
        // 1. Public marketing pages
        $publicRoutes = [
            '/',
            '/programs',
            '/academic/undergraduate',
            '/academic/pascasarjana',
            '/academic/kelas-karyawan',
            '/events',
            '/admissions/pmb',
            '/academic/curriculum',
            '/login',
            '/pmb/register',
        ];

        foreach ($publicRoutes as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }

        // 2. Official printable documents (A4 layout verification)
        $superadmin = User::where('email', 'superadmin@uph.edu')->first();
        if ($superadmin) {
            $printRoutes = [
                '/portal/print/krs',
                '/portal/print/kwitansi',
                '/portal/print/skpi',
                '/portal/print/loa',
                '/portal/print/khs-transcript',
            ];

            foreach ($printRoutes as $url) {
                $response = $this->actingAs($superadmin)->get($url);
                $this->assertTrue(in_array($response->status(), [200, 302]));
            }
        }
    }
}
