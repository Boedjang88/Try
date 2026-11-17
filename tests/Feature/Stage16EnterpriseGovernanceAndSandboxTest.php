<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\ConsultationMessage;
use App\Models\KrsItem;
use App\Models\Lecturer;
use App\Models\OutboundDispatch;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage16EnterpriseGovernanceAndSandboxTest extends TestCase
{
    /**
     * Test 1: Maker-Checker Dual Approval Engine.
     * Maker initiates post-lock grade change -> Self-approval is rejected -> Checker approves ->
     * Target KrsItem mutates atomically and AuditLog records both Maker and Checker IDs.
     */
    public function test_maker_checker_dual_control_workflow_and_atomic_execution(): void
    {
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();
        $kaprodiUser = User::where('email', 'kaprodi.if@uph.edu')->firstOrFail();
        $krsItem = KrsItem::firstOrFail();

        $originalScore = (float)$krsItem->final_numeric_score;

        // 1. Maker creates pending approval request
        $this->actingAs($lecturerUser);
        $createResponse = $this->post('/portal/governance/approvals', [
            'workflow_type' => 'grade_change_after_lock',
            'checker_role_required' => 'kaprodi',
            'target_model_type' => KrsItem::class,
            'target_model_id' => (string) $krsItem->id,
            'maker_reason' => 'Perbaikan nilai ujian susulan resmi terverifikasi.',
            'payload_diff' => [
                'before' => [
                    'score_uts' => $krsItem->score_uts,
                    'score_uas' => $krsItem->score_uas,
                    'final_numeric_score' => $krsItem->final_numeric_score,
                    'letter_grade' => $krsItem->letter_grade,
                ],
                'after' => [
                    'score_uts' => 95.0,
                    'score_uas' => 98.0,
                    'final_numeric_score' => 96.5,
                    'letter_grade' => 'A',
                ],
            ],
        ]);

        $createResponse->assertSessionHas('success');

        $approvalReq = ApprovalRequest::where('target_model_id', $krsItem->id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $this->assertEquals($lecturerUser->id, $approvalReq->maker_user_id);
        $this->assertEquals('kaprodi', $approvalReq->checker_role_required);

        // 2. Zero-Trust Dual Control Gate: Maker CANNOT approve own request
        $selfApprovalResponse = $this->post("/portal/governance/approvals/{$approvalReq->id}/approve");
        $selfApprovalResponse->assertStatus(403);
        $this->assertEquals('pending', $approvalReq->fresh()->status);

        // 3. Checker (Kaprodi) approves request
        $this->actingAs($kaprodiUser);
        $approveResponse = $this->post("/portal/governance/approvals/{$approvalReq->id}/approve", [
            'checker_notes' => 'Disetujui berdasarkan lampiran berita acara ujian susulan.',
        ]);

        $approveResponse->assertSessionHas('success');

        $updatedReq = $approvalReq->fresh();
        $this->assertEquals('approved', $updatedReq->status);
        $this->assertEquals($kaprodiUser->id, $updatedReq->checker_user_id);
        $this->assertNotNull($updatedReq->resolved_at);

        // 4. Verify Target Model mutated atomically
        $updatedKrsItem = $krsItem->fresh();
        $this->assertEquals(95.0, (float)$updatedKrsItem->score_uts);
        $this->assertEquals(98.0, (float)$updatedKrsItem->score_uas);
        $this->assertEquals(96.5, (float)$updatedKrsItem->final_numeric_score);
        $this->assertEquals('A', $updatedKrsItem->letter_grade);

        // 5. Verify AuditLog recorded both Maker and Checker IDs
        $auditLog = AuditLog::where('action', 'MAKER_CHECKER_APPROVED')
            ->where('auditable_id', (string) $krsItem->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertEquals($kaprodiUser->id, $auditLog->user_id);
        $this->assertArrayHasKey('maker_user_id', $auditLog->new_values);
        $this->assertArrayHasKey('checker_user_id', $auditLog->new_values);
        $this->assertEquals($lecturerUser->id, $auditLog->new_values['maker_user_id']);
        $this->assertEquals($kaprodiUser->id, $auditLog->new_values['checker_user_id']);
    }

    /**
     * Test 2: Live Academic Consultation Messages Thread (Student <-> Advisor).
     */
    public function test_academic_consultation_messages_thread(): void
    {
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();

        $student = $studentUser->student;
        $lecturer = $lecturerUser->lecturer;

        $this->actingAs($studentUser);

        // 1. Post consultation message via AJAX
        $postResponse = $this->postJson('/portal/consultation/messages', [
            'student_id' => $student->id,
            'lecturer_id' => $lecturer->id,
            'context_type' => 'krs',
            'message' => 'Mohon cek jadwal pengganti untuk kelas Algoritma Dok.',
        ]);

        $postResponse->assertStatus(201);
        $postResponse->assertJsonPath('success', true);
        $postResponse->assertJsonPath('data.message', 'Mohon cek jadwal pengganti untuk kelas Algoritma Dok.');

        // 2. Lecturer retrieves messages and marks unread messages as read
        $this->actingAs($lecturerUser);
        $getResponse = $this->getJson("/portal/consultation/messages?student_id={$student->id}&lecturer_id={$lecturer->id}&context_type=krs");

        $getResponse->assertStatus(200);
        $getResponse->assertJsonPath('success', true);
        $this->assertNotEmpty($getResponse->json('data'));

        // Verify message was stored in database
        $this->assertDatabaseHas('consultation_messages', [
            'student_id' => $student->id,
            'lecturer_id' => $lecturer->id,
            'message' => 'Mohon cek jadwal pengganti untuk kelas Algoritma Dok.',
        ]);
    }

    /**
     * Test 3: SLA Helpdesk Support Ticketing Center.
     */
    public function test_sla_helpdesk_support_ticketing_lifecycle(): void
    {
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $baukUser = User::where('email', 'bauk@uph.edu')->firstOrFail();

        // 1. Student opens a high-priority ticket with 24-hour SLA
        $this->actingAs($studentUser);
        $openResponse = $this->postJson('/portal/helpdesk/tickets', [
            'department' => 'bauk',
            'category' => 'Tagihan Pembayaran UKT',
            'subject' => 'Kendala Virtual Account Mandiri Belum Terbit',
            'priority' => 'high',
            'message' => 'Virtual Account Mandiri untuk semester baru belum muncul pada dasbor finance saya.',
        ]);

        $openResponse->assertStatus(201);
        $ticketId = $openResponse->json('data.id');
        $this->assertNotNull($ticketId);

        $ticket = SupportTicket::findOrFail($ticketId);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals('bauk', $ticket->department);
        $this->assertNotNull($ticket->sla_due_at);

        // SLA should be approximately +24 hours
        $this->assertTrue(now()->addHours(23)->isBefore($ticket->sla_due_at));

        // 2. BAUK Staff replies and ticket status automatically transitions to in_progress
        $this->actingAs($baukUser);
        $replyResponse = $this->postJson("/portal/helpdesk/tickets/{$ticket->id}/replies", [
            'message' => 'Halo Christian, nomor VA Mandiri Anda telah berhasil di-generate secara manual. Silakan refresh halaman.',
        ]);

        $replyResponse->assertStatus(201);
        $this->assertEquals('in_progress', $ticket->fresh()->status);

        // 3. BAUK Staff marks ticket as resolved
        $statusResponse = $this->postJson("/portal/helpdesk/tickets/{$ticket->id}/status", [
            'status' => 'resolved',
        ]);

        $statusResponse->assertStatus(200);
        $this->assertEquals('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    /**
     * Test 4: Anti-Cheating Randomized Exam Seat Generator.
     * Assigns unique seat coordinates only to eligible students (attendance >= 75% & financially cleared).
     */
    public function test_anti_cheating_exam_seat_generator_and_eligibility(): void
    {
        $baakUser = User::where('email', 'baak@uph.edu')->firstOrFail();
        $this->actingAs($baakUser);

        // Execute generator endpoint
        $genResponse = $this->postJson('/portal/baak/exam-seats/generate');
        $genResponse->assertStatus(200);
        $genResponse->assertJsonPath('success', true);

        // Check eligible student's KRS items have valid seat numbers (e.g. ROW-A / SEAT-01)
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;

        $eligibleItems = KrsItem::whereHas('krsSubmission', fn($q) => $q->where('student_id', $student->id))->get();
        $this->assertNotEmpty($eligibleItems);

        $hasEligibleSeat = false;
        $hasBlockedSeat = false;
        foreach ($eligibleItems as $item) {
            $this->assertNotNull($item->exam_seat_number);
            if (str_contains($item->exam_seat_number, 'ROW-')) {
                $hasEligibleSeat = true;
                $this->assertStringContainsString('SEAT-', $item->exam_seat_number);
            } elseif (str_contains($item->exam_seat_number, 'BLOCKED')) {
                $hasBlockedSeat = true;
            }
        }
        $this->assertTrue($hasEligibleSeat, 'Eligible courses must have ROW- assigned seat');
        $this->assertTrue($hasBlockedSeat, 'Blocked course (< 75% attendance) must have BLOCKED status');

        // Student Exam Card view renders seat number
        $this->actingAs($studentUser);
        $cardView = $this->get('/portal/student/exam-card');
        $cardView->assertStatus(200);
        $cardView->assertSee('Kartu Ujian Semester');
        $cardView->assertSee('ROW-');
    }

    /**
     * Test 5: OpenAPI Webhook Sandbox (HMAC Signature Verification & Outbound Dispatch Log).
     */
    public function test_webhook_hmac_signature_verification_and_outbox_logging(): void
    {
        $superadmin = User::where('email', 'superadmin@uph.edu')->firstOrFail();
        $this->actingAs($superadmin);

        $secret = 'test-uph-secret-hmac-key';

        // 1. Valid Signature Test
        $timestamp = now()->toISOString();
        $payload = [
            'invoice_number' => 'INV-TEST-001',
            'va_number' => '880812345678',
            'amount_paid' => 15000000.0,
            'timestamp' => $timestamp,
            'gateway_reference' => 'WEBHOOK-SIM-TEST',
        ];
        $payloadJson = json_encode($payload);
        $validSignature = hash_hmac('sha256', $payloadJson, $secret);

        $validResponse = $this->postJson('/portal/developer/sandbox/simulate-webhook', [
            'invoice_number' => 'INV-TEST-001',
            'va_number' => '880812345678',
            'amount_paid' => 15000000.0,
            'timestamp' => $timestamp,
            'gateway_reference' => 'WEBHOOK-SIM-TEST',
            'secret_key' => $secret,
            'provided_signature' => $validSignature,
            'execute_settlement' => false,
        ]);

        $validResponse->assertStatus(200);
        $validResponse->assertJsonPath('success', true);
        $validResponse->assertJsonPath('data.is_signature_valid', true);

        // 2. Tampered / Invalid Signature Test
        $invalidResponse = $this->postJson('/portal/developer/sandbox/simulate-webhook', [
            'invoice_number' => 'INV-TEST-001',
            'va_number' => '880812345678',
            'amount_paid' => 15000000.0,
            'secret_key' => $secret,
            'provided_signature' => 'tampered_invalid_signature_hash_0000',
            'execute_settlement' => false,
        ]);

        $invalidResponse->assertStatus(401);
        $invalidResponse->assertJsonPath('success', false);
        $invalidResponse->assertJsonPath('data.is_signature_valid', false);

        // 3. Outbound Notification Dispatch Log View
        $outboxView = $this->get('/portal/developer/outbox');
        $outboxView->assertStatus(200);
        $outboxView->assertSee('Log Pengiriman Notifikasi Eksternal');

        $this->assertDatabaseHas('outbound_dispatches', [
            'channel' => 'whatsapp',
        ]);
        $this->assertDatabaseHas('outbound_dispatches', [
            'channel' => 'email',
        ]);
    }
}
