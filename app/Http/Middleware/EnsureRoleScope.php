<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enterprise RBAC & Workspace Isolation Middleware
 *
 * Enforces strict workspace boundaries between personas. Prevents cross-role access
 * (e.g., student accessing lecturer grading, lecturer modifying other's classes,
 * Kaprodi modifying another study program, etc.).
 */
class EnsureRoleScope
{
    /**
     * Route prefix to required roles mapping.
     */
    protected array $prefixRoleMap = [
        'portal/student'   => ['student', 'superadmin'],
        'portal/lecturer'  => ['lecturer', 'superadmin'],
        'portal/kaprodi'   => ['kaprodi', 'superadmin'],
        'portal/dean'      => ['dean', 'superadmin'],
        'portal/baak'      => ['baak', 'superadmin', 'rector'],
        'portal/bauk'      => ['bauk', 'superadmin'],
        'portal/executive' => ['rector', 'superadmin', 'dean'],
        'portal/pmb'       => ['applicant', 'superadmin'],
        'portal/iam'        => ['superadmin', 'baak'],
        'portal/governance' => ['superadmin', 'kaprodi', 'dean', 'bauk', 'lecturer', 'baak'],
        'portal/developer'  => ['superadmin', 'baak', 'bauk', 'rector'],
        'portal/helpdesk'   => ['student', 'lecturer', 'kaprodi', 'dean', 'baak', 'bauk', 'superadmin', 'rector', 'applicant'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$allowedRoles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi autentikasi telah berakhir. Silakan login kembali.',
                ], 401);
            }
            return redirect()->guest('/login')->with('warning', 'Silakan masuk terlebih dahulu untuk mengakses workspace.');
        }

        $user = Auth::user();
        $primaryRole = $user->primary_role ?? $user->role;

        // Superadmin has full bypass for administrative troubleshooting
        if ($primaryRole === 'superadmin') {
            return $next($request);
        }

        // 1. Direct explicit role argument check (if provided in route definition)
        if (!empty($allowedRoles) && !$user->hasRole($allowedRoles)) {
            return $this->denyAccess($request, $user, "Peran [{$primaryRole}] tidak memiliki izin untuk rute ini.");
        }

        // 2. Automatic URL prefix workspace boundary enforcement
        $currentPath = $request->path();
        foreach ($this->prefixRoleMap as $prefix => $validRoles) {
            if (str_starts_with($currentPath, $prefix)) {
                if (!in_array($primaryRole, $validRoles, true)) {
                    return $this->denyAccess(
                        $request, 
                        $user, 
                        "Workspace [{$prefix}] terbatas hanya untuk peran: " . implode(', ', $validRoles) . "."
                    );
                }
                break;
            }
        }

        // 3. ABAC Granular Verification (Faculty Scope for Dean)
        if ($primaryRole === 'dean') {
            $targetFacultyId = $request->route('faculty_id') ?? $request->input('faculty_id');
            if ($targetFacultyId && !$user->canAccessFaculty($targetFacultyId)) {
                return $this->denyAccess($request, $user, 'Pelanggaran wewenang: Anda hanya diizinkan mengelola Fakultas yang ditugaskan.');
            }
        }

        // 4. ABAC Granular Verification (Study Program Scope for Kaprodi)
        if ($primaryRole === 'kaprodi') {
            $targetProdiId = $request->route('study_program_id') ?? $request->input('study_program_id');
            if ($targetProdiId && !$user->canAccessStudyProgram($targetProdiId)) {
                return $this->denyAccess($request, $user, 'Pelanggaran wewenang: Anda hanya diizinkan mengelola Program Studi yang ditugaskan.');
            }
        }

        // 5. Self-record isolation for Students
        if ($primaryRole === 'student') {
            $targetStudentId = $request->route('student') ?? $request->route('student_id') ?? $request->input('student_id');
            if ($targetStudentId && is_string($targetStudentId)) {
                $myStudentId = $user->student?->id;
                if ($myStudentId && $targetStudentId !== $myStudentId) {
                    return $this->denyAccess($request, $user, 'Isolasi Akun: Anda tidak memiliki akses ke data mahasiswa lain.');
                }
            }
        }

        // 6. Teaching class isolation for Lecturers
        if ($primaryRole === 'lecturer' && $request->route('classSection')) {
            $classSection = $request->route('classSection');
            $myLecturerId = $user->lecturer?->id;
            
            // If model or ID passed
            $sectionLecturerId = is_object($classSection) ? $classSection->lecturer_id : null;
            if ($sectionLecturerId && $myLecturerId && $sectionLecturerId !== $myLecturerId) {
                return $this->denyAccess($request, $user, 'Isolasi Akun: Anda hanya berwenang menilai dan mengajar pada kelas yang ditugaskan kepada Anda.');
            }
        }

        return $next($request);
    }

    /**
     * Standardized access denial response with redirection to own workspace.
     */
    protected function denyAccess(Request $request, $user, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Akses Ditolak: {$message}",
            ], 403);
        }

        abort(403, "Akses Ditolak: {$message}");
    }
}
