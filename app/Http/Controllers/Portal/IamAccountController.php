<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\UserCustomPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IamAccountController extends Controller
{
    /**
     * User Directory & Account Lifecycle Manager.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roleModel', 'faculty', 'studyProgram', 'student', 'lecturer']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        if ($facultyId = $request->input('faculty_id')) {
            $query->where('faculty_id', $facultyId);
        }

        $users = $query->orderBy('role')->orderBy('full_name')->paginate(20)->withQueryString();
        $roles = Role::orderByDesc('level')->get();
        $faculties = Faculty::with('studyPrograms')->get();
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'suspended' => User::where('is_active', false)->count(),
            'students' => User::where('role', 'student')->count(),
            'lecturers' => User::whereIn('role', ['lecturer', 'kaprodi'])->count(),
        ];

        return view('portal.iam.users', compact('users', 'roles', 'faculties', 'stats'));
    }

    /**
     * Create New User Account with ABAC Binding.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username|max:50',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'faculty_id' => 'nullable|uuid|exists:faculties,id',
            'study_program_id' => 'nullable|uuid|exists:study_programs,id',
        ]);

        $roleModel = Role::where('name', $validated['role'])->first();

        $user = User::create([
            'id' => (string) Str::uuid(),
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'role_id' => $roleModel?->id,
            'faculty_id' => $validated['faculty_id'] ?? null,
            'study_program_id' => $validated['study_program_id'] ?? null,
            'is_active' => true,
        ]);

        // Provision accompanying persona profile
        if ($user->role === 'student' && $user->study_program_id) {
            Student::firstOrCreate(['user_id' => $user->id], [
                'id' => (string) Str::uuid(),
                'study_program_id' => $user->study_program_id,
                'nim' => $user->username,
                'entry_year' => (int) date('Y'),
                'current_semester' => 1,
                'max_sks_allowed' => 20,
                'current_gpa_ipk' => 0.00,
                'is_financially_cleared' => true,
                'has_academic_hold' => false,
            ]);
        } elseif (in_array($user->role, ['lecturer', 'kaprodi']) && $user->study_program_id) {
            Lecturer::firstOrCreate(['user_id' => $user->id], [
                'id' => (string) Str::uuid(),
                'study_program_id' => $user->study_program_id,
                'nidn' => $user->username,
                'employment_status' => 'permanent',
                'is_active' => true,
            ]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'IAM_USER_CREATED',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => [
                'email' => $user->email,
                'role' => $user->role,
                'created_by' => Auth::user()->email,
            ],
        ]);

        return back()->with('success', "Akun sivitas {$user->full_name} ({$user->email}) berhasil dibuat.");
    }

    /**
     * Toggle User Account Status (Active <-> Suspended).
     */
    public function toggleUserStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Operasi ditolak: Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'IAM_USER_STATUS_TOGGLED',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => [
                'is_active' => $user->is_active,
                'target_email' => $user->email,
            ],
        ]);

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'ditangguhkan (suspended)';
        return back()->with('success', "Status akun {$user->full_name} berhasil {$statusText}.");
    }

    /**
     * Reset User Password to Default or Specified Value.
     */
    public function resetUserPassword(User $user, Request $request): RedirectResponse
    {
        $newPassword = $request->input('new_password', 'password');
        $user->password = Hash::make($newPassword);
        $user->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'IAM_USER_PASSWORD_RESET',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => [
                'target_email' => $user->email,
                'reset_by' => Auth::user()->email,
            ],
        ]);

        return back()->with('success', "Kata sandi untuk {$user->full_name} telah di-reset menjadi '{$newPassword}'.");
    }

    /**
     * Role & Privilege Matrix Dashboard.
     */
    public function privileges(Request $request): View
    {
        $roles = Role::with('permissions')->orderByDesc('level')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        $selectedUser = null;
        if ($userId = $request->input('user_id')) {
            $selectedUser = User::with(['roleModel.permissions', 'customPermissions.permission'])->find($userId);
        }

        $allUsers = User::orderBy('full_name')->select('id', 'full_name', 'email', 'role')->get();

        return view('portal.iam.privileges', compact('roles', 'permissions', 'selectedUser', 'allUsers'));
    }

    /**
     * Grant or Revoke Custom Privilege Override for a Specific User.
     */
    public function overridePrivilege(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'permission_slug' => 'required|string|exists:permissions,slug',
            'action' => 'required|in:grant,revoke,clear',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $permission = Permission::where('slug', $validated['permission_slug'])->firstOrFail();

        if ($validated['action'] === 'clear') {
            UserCustomPermission::where('user_id', $user->id)
                ->where('permission_id', $permission->id)
                ->delete();

            $msg = "Override hak akses [{$permission->slug}] untuk {$user->full_name} dihapus (kembali ke default role).";
        } elseif ($validated['action'] === 'grant') {
            $user->grantPrivilege($permission->slug, Auth::id(), $validated['reason'] ?? 'Diberikan oleh Superadmin');
            $msg = "Izin [{$permission->slug}] berhasil DIBERIKAN secara khusus ke {$user->full_name}.";
        } else {
            $user->revokePrivilege($permission->slug, Auth::id(), $validated['reason'] ?? 'Dicabut oleh Superadmin');
            $msg = "Izin [{$permission->slug}] berhasil DICABUT secara khusus dari {$user->full_name}.";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'IAM_PRIVILEGE_OVERRIDDEN',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => [
                'target_email' => $user->email,
                'permission_slug' => $permission->slug,
                'action' => $validated['action'],
                'reason' => $validated['reason'] ?? null,
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
