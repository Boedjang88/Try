<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicDocumentVerificationController;
use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Models\Thesis;
use App\Models\ThesisGuidanceLog;
use App\Models\User;
use App\Models\YudisiumClearance;
use Illuminate\Support\Str;
use Tests\TestCase;

class Loop5ThesisYudisiumAndSkpiPinTest extends TestCase
{
    /**
     * Operational Loop 5: Thesis Guidance Verification, 4-Checkpoint Yudisium Clearance,
     * National Diploma PIN (PIN Ijazah Nasional) Generation, and Cryptographic SKPI Verification.
     */
    public function test_loop5_thesis_yudisium_pin_generation_and_skpi_verification(): void
    {
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();
        $lecturer = $lecturerUser->lecturer;

        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;

        $baakUser = User::where('email', 'baak@uph.edu')->firstOrFail();
        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        // 1. Setup Thesis & Guidance Log
        $thesis = Thesis::firstOrCreate([
            'student_id' => $student->id,
        ], [
            'id' => (string) Str::uuid(),
            'title' => 'Implementasi Arsitektur Monolith Skalabel pada Sistem Informasi Akademik Universitas',
            'abstract' => 'Penelitian ini menganalisis kinerja dan ketahanan sistem akademik terintegrasi...',
            'supervisor1_lecturer_id' => $lecturer->id,
            'proposal_status' => 'approved',
            'defense_status' => 'passed',
        ]);

        $guidanceLog = ThesisGuidanceLog::create([
            'id' => (string) Str::uuid(),
            'thesis_id' => $thesis->id,
            'lecturer_id' => $lecturer->id,
            'guidance_date' => now()->toDateString(),
            'discussion_summary' => 'Pembahasan Bab 4 Analisis Data, Pengujian Beban, dan Diagram Sekuensial.',
            'student_notes' => 'Telah disempurnakan sesuai masukan bimbingan sebelumnya.',
            'is_verified' => false,
        ]);

        // Step 1: Lecturer approves/verifies the guidance log
        $verifyLogResponse = $this->actingAs($lecturerUser)->postJson("/thesis/guidance/{$guidanceLog->id}/verify", [
            'is_verified' => true,
            'supervisor_feedback' => 'Analisis Bab 4 sangat komprehensif dan memenuhi standar skripsi.',
        ]);

        $verifyLogResponse->assertStatus(200);
        $verifyLogResponse->assertJson(['success' => true]);

        $guidanceLog->refresh();
        $this->assertTrue((bool) $guidanceLog->is_verified);
        $this->assertNotNull($guidanceLog->verified_at);

        // 2. Setup Yudisium Clearance Record
        $clearance = YudisiumClearance::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
        ], [
            'id' => (string) Str::uuid(),
            'finance_cleared' => false,
            'library_cleared' => false,
            'repository_cleared' => false,
            'faculty_cleared' => false,
            'status' => 'pending',
        ]);

        // Step 2: Department staff verify all 4 checkpoints
        $departments = ['finance', 'library', 'repository', 'faculty'];
        foreach ($departments as $dept) {
            $resp = $this->actingAs($baakUser)->post("/yudisium/{$clearance->id}/verify", [
                'department' => $dept,
                'cleared' => true,
            ]);
            $resp->assertRedirect();
        }

        // Step 3: Assert full clearance, National PIN generation, and student graduation status
        $clearance->refresh();
        $this->assertTrue($clearance->isFullyCleared(), 'Expected all 4 checkpoints to be cleared.');
        $this->assertEquals('approved', $clearance->status);
        $this->assertNotEmpty($clearance->national_pin_number);
        $this->assertStringStartsWith('PIN-UPH/', $clearance->national_pin_number);
        $this->assertNotNull($clearance->graduation_date);

        $student->refresh();
        $this->assertEquals('graduated', $student->academic_status);

        // Step 4: Official Bilingual SKPI document printing
        $skpiPrintResponse = $this->actingAs($studentUser)->get("/portal/print/skpi/{$clearance->id}");
        $skpiPrintResponse->assertStatus(200);
        $skpiPrintResponse->assertSee('SURAT KETERANGAN PENDAMPING IJAZAH');
        $skpiPrintResponse->assertSee($clearance->national_pin_number);

        // Step 5: Public Cryptographic Document Verification Route (/verify/document/skpi/{hash})
        $validHash = PublicDocumentVerificationController::generateSignature(
            'skpi',
            $clearance->id,
            $clearance->national_pin_number
        );

        $publicVerifyUrl = "/verify/document/skpi/{$validHash}?id={$clearance->id}&ref={$clearance->national_pin_number}";
        $publicVerifyResponse = $this->get($publicVerifyUrl);

        $publicVerifyResponse->assertStatus(200);
        $publicVerifyResponse->assertSee('DOKUMEN AKADEMIK ASLI & TERVERIFIKASI DI PDDIKTI/SIAKAD', false);
        $publicVerifyResponse->assertSee($clearance->national_pin_number);
        $publicVerifyResponse->assertSee($student->user->full_name);

        // Step 6: Anti-tamper assertion - Red badge on forged/tampered hash
        $tamperedHash = hash_hmac('sha256', 'FORGED_DATA_TAMPERED', 'FAKE_KEY');
        $forgedVerifyUrl = "/verify/document/skpi/{$tamperedHash}?id={$clearance->id}&ref={$clearance->national_pin_number}";
        $forgedVerifyResponse = $this->get($forgedVerifyUrl);

        $forgedVerifyResponse->assertStatus(200);
        $forgedVerifyResponse->assertSee('PERINGATAN: DOKUMEN TIDAK VALID ATAU TELAH DIMANIPULASI');
    }
}
