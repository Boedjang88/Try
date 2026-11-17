<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class MultiAccountIsolationTest extends TestCase
{
    /**
     * Test that each of the 8 roles correctly redirects to their dedicated isolated dashboard.
     */
    public function test_all_eight_roles_redirect_to_exact_workspace_dashboard(): void
    {
        $roleDashboardMap = [
            'applicant1@uph.edu' => '/portal/pmb/dashboard',
            'student1@uph.edu' => '/portal/student/dashboard',
            'lecturer1@uph.edu' => '/portal/lecturer/dashboard',
            'kaprodi.if@uph.edu' => '/portal/kaprodi/dashboard',
            'dean.fik@uph.edu' => '/portal/dean/dashboard',
            'baak@uph.edu' => '/portal/baak/dashboard',
            'bauk@uph.edu' => '/portal/bauk/dashboard',
            'rector@uph.edu' => '/portal/executive/dashboard',
        ];

        foreach ($roleDashboardMap as $email => $expectedDashboard) {
            $user = User::where('email', $email)->firstOrFail();

            $response = $this->actingAs($user)->get('/portal');

            $response->assertRedirect($expectedDashboard);
        }
    }

    /**
     * Test that each role receives strict 403 Forbidden when attempting cross-workspace intrusion.
     */
    public function test_cross_workspace_intrusion_returns_strict_403_forbidden(): void
    {
        $crossAccessAttempts = [
            'student1@uph.edu' => '/portal/lecturer/dashboard',
            'lecturer1@uph.edu' => '/portal/kaprodi/dashboard',
            'applicant1@uph.edu' => '/portal/student/dashboard',
            'kaprodi.if@uph.edu' => '/portal/bauk/dashboard',
            'bauk@uph.edu' => '/portal/student/dashboard',
            'dean.fik@uph.edu' => '/portal/bauk/dashboard',
            'baak@uph.edu' => '/portal/student/dashboard',
            'rector@uph.edu' => '/portal/student/dashboard',
        ];

        foreach ($crossAccessAttempts as $email => $prohibitedRoute) {
            $user = User::where('email', $email)->firstOrFail();

            $response = $this->actingAs($user)->get($prohibitedRoute);

            $response->assertStatus(403);
        }
    }

    /**
     * Test that student attempting Horizontal IDOR on printable documents receives strict 403 Forbidden.
     */
    public function test_student_horizontal_idor_on_printable_documents_strictly_blocked(): void
    {
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $otherStudent = Student::where('nim', '01082200002')->firstOrFail();

        // 1. Parameter query spoofing (?student_id=...)
        $resQueryId = $this->actingAs($studentUser)->get("/portal/print/khs-transcript?student_id={$otherStudent->id}");
        $resQueryId->assertStatus(403);

        // 2. Parameter query spoofing (?nim=...)
        $resQueryNim = $this->actingAs($studentUser)->get("/portal/print/khs-transcript?nim={$otherStudent->nim}");
        $resQueryNim->assertStatus(403);

        // 3. Route parameter manipulation (/portal/print/khs-transcript/{otherStudent})
        $resRouteParam = $this->actingAs($studentUser)->get("/portal/print/khs-transcript/{$otherStudent->id}");
        $resRouteParam->assertStatus(403);

        // 4. KRS print document IDOR attempt
        $resKrsIdor = $this->actingAs($studentUser)->get("/portal/print/krs?student_id={$otherStudent->id}");
        $resKrsIdor->assertStatus(403);

        // 5. Kwitansi receipt IDOR attempt
        $resKwitansiIdor = $this->actingAs($studentUser)->get("/portal/print/kwitansi?student_id={$otherStudent->id}");
        $resKwitansiIdor->assertStatus(403);

        // 6. SKPI document IDOR attempt
        $resSkpiIdor = $this->actingAs($studentUser)->get("/portal/print/skpi?student_id={$otherStudent->id}");
        $resSkpiIdor->assertStatus(403);
    }
}
