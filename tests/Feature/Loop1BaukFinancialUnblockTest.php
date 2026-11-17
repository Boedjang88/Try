<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\User;
use Tests\TestCase;

class Loop1BaukFinancialUnblockTest extends TestCase
{
    /**
     * Operational Loop 1: BAUK Financial Clearance & Unblocking.
     *
     * Flow:
     * 1. Student with financial hold is blocked from accessing KRS War.
     * 2. BAUK staff verifies tuition fee payment via official portal action.
     * 3. Student's financial hold is atomically released (is_financially_cleared = true).
     * 4. Immutable audit trail entry is persisted.
     * 5. Student immediately gains unblocked access to KRS War Cart.
     */
    public function test_loop1_bauk_financial_verification_and_unblock_lifecycle(): void
    {
        $blockedUser = User::where('email', 'student.blocked@uph.edu')->firstOrFail();
        $student = $blockedUser->student;

        // Ensure baseline blocked state
        $student->is_financially_cleared = false;
        $student->has_academic_hold = true;
        $student->save();

        // Ensure test isolation by clearing any lingering unpaid invoices for this student
        StudentInvoice::where('student_id', $student->id)->where('payment_status', 'unpaid')->delete();

        $period = \App\Models\AcademicPeriod::first();
        $invoice = StudentInvoice::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'student_id' => $student->id,
            'academic_period_id' => $period->id,
            'invoice_number' => 'INV-UPH-TEST-' . mt_rand(1000, 9999),
            'title' => 'Tagihan Biaya Kuliah BPP Pokok Test',
            'va_number' => '98888' . mt_rand(100000, 999999),
            'amount' => 15000000,
            'due_date' => now()->addDays(30),
            'payment_status' => 'unpaid',
        ]);

        // Step 1: Blocked student attempts to access KRS Cart -> Should be blocked/redirected
        $blockedResponse = $this->actingAs($blockedUser)->get('/portal/student/krs');
        $blockedResponse->assertRedirect('/portal/student/finance');
        $blockedResponse->assertSessionHas('error');

        // Step 2: BAUK staff performs payment verification via POST
        $baukUser = User::where('email', 'bauk@uph.edu')->firstOrFail();
        $initialAuditCount = AuditLog::where('action', 'PAYMENT_VERIFIED_AND_HOLD_RELEASED')->count();

        $verifyResponse = $this->actingAs($baukUser)->post("/portal/bauk/billing/{$invoice->id}/verify");
        $verifyResponse->assertStatus(302); // Redirect back with success flash

        // Step 3: Assert Student model is cleared atomically
        $student->refresh();
        $this->assertTrue($student->is_financially_cleared, 'Expected is_financially_cleared to be true after BAUK settlement.');
        $this->assertFalse($student->has_academic_hold, 'Expected has_academic_hold to be false after BAUK settlement.');

        // Step 4: Assert Invoice status is paid
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertNotNull($invoice->paid_at);

        // Step 5: Assert AuditLog entry was persisted
        $newAuditCount = AuditLog::where('action', 'PAYMENT_VERIFIED_AND_HOLD_RELEASED')->count();
        $this->assertGreaterThan($initialAuditCount, $newAuditCount);

        $latestLog = AuditLog::where('action', 'PAYMENT_VERIFIED_AND_HOLD_RELEASED')->latest()->first();
        $this->assertEquals($baukUser->id, $latestLog->user_id);
        $this->assertStringContainsString($student->nim, $latestLog->description);

        // Step 6: Assert student can now successfully access KRS War Cart
        $unblockedResponse = $this->actingAs($blockedUser)->get('/portal/student/krs');
        $unblockedResponse->assertStatus(200);
    }
}
