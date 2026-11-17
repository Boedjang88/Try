<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Lecturer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage15ZeroTrustAuthAndPrivilegeMatrixTest extends TestCase
{
    /**
     * Test 1: Unauthenticated requests to protected portal routes strictly return 302 Redirect to /login.
     */
    public function test_unauthenticated_requests_strictly_redirect_to_login(): void
    {
        $protectedUrls = [
            '/portal',
            '/portal/student/dashboard',
            '/portal/baak/dashboard',
            '/portal/print/krs',
            '/portal/iam/users',
            '/portal/iam/privileges',
            '/krs',
            '/grading',
        ];

        foreach ($protectedUrls as $url) {
            $response = $this->get($url);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test 2: Login works with Email, NIM, and NIDN/NIP.
     */
    public function test_login_supports_email_nim_and_nidn(): void
    {
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;
        $this->assertNotNull($student);
        $studentNim = $student->nim;

        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();
        $lecturer = $lecturerUser->lecturer;
        $this->assertNotNull($lecturer);
        $lecturerNidn = $lecturer->nidn;

        // 1. Login with Email
        $loginEmail = $this->post('/login', [
            'identifier' => 'student1@uph.edu',
            'password' => 'password',
        ]);
        $loginEmail->assertRedirect('/portal/student/dashboard');
        $this->assertAuthenticatedAs($studentUser);
        $this->post('/logout');

        // 2. Login with Student NIM
        $loginNim = $this->post('/login', [
            'identifier' => $studentNim,
            'password' => 'password',
        ]);
        $loginNim->assertRedirect('/portal/student/dashboard');
        $this->assertAuthenticatedAs($studentUser);
        $this->post('/logout');

        // 3. Login with Lecturer NIDN
        $loginNidn = $this->post('/login', [
            'identifier' => $lecturerNidn,
            'password' => 'password',
        ]);
        $loginNidn->assertRedirect('/portal/lecturer/dashboard');
        $this->assertAuthenticatedAs($lecturerUser);
        $this->post('/logout');
    }

    /**
     * Test 3: Suspended accounts (is_active = false) are strictly rejected at login and in active middleware.
     */
    public function test_suspended_accounts_are_rejected(): void
    {
        $user = User::firstOrCreate(['email' => 'test.suspended@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'suspended_user',
            'full_name' => 'Suspended Account Test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => false,
        ]);
        $user->is_active = false;
        $user->save();

        // 1. Attempt login with suspended account
        $response = $this->post('/login', [
            'identifier' => 'test.suspended@uph.edu',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors(['email', 'identifier']);
        $this->assertGuest();

        // 2. If logged in and account becomes suspended, active.account middleware intercepts
        $activeUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $activeUser->is_active = false;
        $activeUser->save();

        $sessionResponse = $this->actingAs($activeUser)->get('/portal/student/dashboard');
        $sessionResponse->assertRedirect('/login');
        $sessionResponse->assertSessionHas('error');

        // Restore status
        $activeUser->is_active = true;
        $activeUser->save();
    }

    /**
     * Test 4: Granular privilege matrix evaluation & per-user custom overrides.
     */
    public function test_granular_privilege_evaluation_and_custom_overrides(): void
    {
        $superadmin = User::where('email', 'superadmin@uph.edu')->firstOrFail();
        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $kaprodiUser = User::where('email', 'kaprodi.if@uph.edu')->firstOrFail();

        // 1. Superadmin has absolute bypass
        $this->assertTrue($superadmin->hasPrivilege('any.imaginary.privilege'));
        $this->assertTrue($superadmin->hasPrivilege('curriculum.manage.prodi'));

        // 2. Default RBAC matrix
        $this->assertTrue($kaprodiUser->hasPrivilege('curriculum.manage.prodi'));
        $this->assertFalse($studentUser->hasPrivilege('curriculum.manage.prodi'));
        $this->assertTrue($studentUser->hasPrivilege('krs.submit'));

        // 3. Grant custom privilege to student
        $studentUser->grantPrivilege('curriculum.manage.prodi', $superadmin->id, 'Penugasan Mahasiswa Riset');
        $this->assertTrue($studentUser->fresh()->hasPrivilege('curriculum.manage.prodi'));

        // 4. Revoke default role privilege via custom override
        $studentUser->revokePrivilege('krs.submit', $superadmin->id, 'Sanksi Disiplin Akademik');
        $this->assertFalse($studentUser->fresh()->hasPrivilege('krs.submit'));

        // Clean up custom overrides
        $studentUser->customPermissions()->delete();
        $this->assertFalse($studentUser->fresh()->hasPrivilege('curriculum.manage.prodi'));
        $this->assertTrue($studentUser->fresh()->hasPrivilege('krs.submit'));
    }

    /**
     * Test 5: IAM User Directory lifecycle management & role access protection.
     */
    public function test_iam_lifecycle_management_and_role_protection(): void
    {
        $superadmin = User::where('email', 'superadmin@uph.edu')->firstOrFail();
        $student = User::where('email', 'student1@uph.edu')->firstOrFail();

        // 1. Student unauthorized to access IAM routes (403 Forbidden)
        $studentForbidden = $this->actingAs($student)->get('/portal/iam/users');
        $studentForbidden->assertStatus(403);

        $studentMatrixForbidden = $this->actingAs($student)->get('/portal/iam/privileges');
        $studentMatrixForbidden->assertStatus(403);

        // 2. Superadmin authorized to view IAM User Directory
        $adminUsers = $this->actingAs($superadmin)->get('/portal/iam/users');
        $adminUsers->assertStatus(200);
        $adminUsers->assertSee('Direktori Akun');

        // 3. Superadmin can toggle user active status
        $toggleResponse = $this->actingAs($superadmin)->post("/portal/iam/users/{$student->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $student->refresh();
        $this->assertFalse($student->is_active);

        // Toggle back to active
        $this->actingAs($superadmin)->post("/portal/iam/users/{$student->id}/toggle-status");
        $student->refresh();
        $this->assertTrue($student->is_active);

        // 4. Superadmin can reset user password
        $resetResponse = $this->actingAs($superadmin)->post("/portal/iam/users/{$student->id}/reset-password", [
            'new_password' => 'secret123',
        ]);
        $resetResponse->assertRedirect();
        $student->refresh();
        $this->assertTrue(Hash::check('secret123', $student->password));

        // Restore password to default
        $student->password = Hash::make('password');
        $student->save();
    }
}
