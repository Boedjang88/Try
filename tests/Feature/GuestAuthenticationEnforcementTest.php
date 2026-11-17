<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class GuestAuthenticationEnforcementTest extends TestCase
{
    /**
     * Test that all internal workspaces and academic routes strictly reject unauthenticated guests.
     */
    public function test_unauthenticated_guests_are_strictly_redirected_to_login(): void
    {
        $protectedRoutes = [
            '/portal',
            '/portal/student/dashboard',
            '/portal/lecturer/dashboard',
            '/portal/kaprodi/dashboard',
            '/portal/dean/dashboard',
            '/portal/baak/dashboard',
            '/portal/bauk/dashboard',
            '/portal/executive/dashboard',
            '/portal/pmb/dashboard',
            '/portal/student/krs',
            '/portal/student/attendance',
            '/portal/student/lms',
            '/portal/student/khs',
            '/portal/student/finance',
            '/portal/student/thesis',
            '/portal/student/yudisium',
            '/portal/print/krs',
            '/portal/print/kwitansi',
            '/krs',
            '/krs/print',
            '/attendance',
            '/lms',
            '/grading',
            '/khs',
            '/finance',
            '/thesis',
            '/yudisium',
            '/bkd',
            '/dashboard/rector',
            '/pddikti',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test that unauthenticated JSON requests receive HTTP 401 Unauthorized instead of HTML redirect.
     */
    public function test_unauthenticated_json_requests_receive_401_unauthorized(): void
    {
        $jsonRoutes = [
            '/api/attendance/scan',
            '/api/krs/add',
        ];

        foreach ($jsonRoutes as $route) {
            $response = $this->postJson($route, []);
            $response->assertStatus(401);
        }
    }

    /**
     * Test that logout properly terminates the session and redirects to public home.
     */
    public function test_authenticated_user_can_logout_cleanly(): void
    {
        $user = User::where('email', 'student1@uph.edu')->firstOrFail();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
