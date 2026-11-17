<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display Enterprise Portal Login Screen with 1-Click Persona Switcher.
     */
    public function showLoginForm(): View
    {
        if (Auth::check()) {
            $role = Auth::user()->primary_role ?? Auth::user()->role ?? 'student';
            return redirect(\App\Http\Middleware\RedirectByRole::getDashboardForRole($role));
        }

        return view('auth.login');
    }

    /**
     * Handle Authentication via Email, NIM, NIDN/NIP, or Username.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'nullable|string',
            'identifier' => 'nullable|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        $rawIdentifier = trim($request->input('identifier') ?? $request->input('email') ?? '');

        if (empty($rawIdentifier)) {
            return back()->withErrors([
                'email' => 'Silakan masukkan Email Resmi UPH, NIM, atau NIDN/NIP.',
            ])->onlyInput('email', 'identifier');
        }

        // 1. Resolve User Model based on identifier type
        $user = null;
        if (filter_var($rawIdentifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $rawIdentifier)->first();
        } else {
            // Check Student NIM
            $student = Student::where('nim', $rawIdentifier)->first();
            if ($student && $student->user) {
                $user = $student->user;
            }

            // Check Lecturer NIDN or NIP
            if (!$user) {
                $lecturer = Lecturer::where('nidn', $rawIdentifier)
                    ->orWhere('nip', $rawIdentifier)
                    ->first();
                if ($lecturer && $lecturer->user) {
                    $user = $lecturer->user;
                }
            }

            // Check Username
            if (!$user) {
                $user = User::where('username', $rawIdentifier)->first();
            }
        }

        // 2. Validate Password Match
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Kombinasi kredensial atau kata sandi tidak cocok dengan data resmi sivitas akademika UPH.',
                'identifier' => 'Kombinasi kredensial atau kata sandi tidak cocok dengan data resmi sivitas akademika UPH.',
            ])->onlyInput('email', 'identifier');
        }

        // 3. Strict Account Status Gate: Suspended Accounts Rejected
        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan / ditangguhkan. Silakan hubungi Biro Administrasi Akademik (BAAK) UPH.',
                'identifier' => 'Akun Anda telah dinonaktifkan / ditangguhkan. Silakan hubungi Biro Administrasi Akademik (BAAK) UPH.',
            ])->onlyInput('email', 'identifier');
        }

        // 4. Authenticate & Regenerate Session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 5. Immutable Audit Trail Logging
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'USER_LOGIN_SUCCESS',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => [
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'identifier' => $rawIdentifier,
            ],
        ]);

        $role = $user->primary_role ?? $user->role ?? 'student';
        $targetUrl = \App\Http\Middleware\RedirectByRole::getDashboardForRole($role);

        return redirect()->intended($targetUrl);
    }

    /**
     * 1-Click Persona Switcher for Evaluation and Demonstration.
     */
    public function quickLogin(string $role): RedirectResponse
    {
        $user = User::where('role', $role)->first();

        if (!$user) {
            $user = User::firstOrCreate(
                ['email' => "{$role}@uph.edu"],
                [
                    'username' => $role,
                    'full_name' => 'Demo ' . ucfirst($role),
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'is_active' => true,
                ]
            );
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('success', "Berhasil masuk sebagai {$user->full_name} ({$user->role})");
    }

    /**
     * Terminate Session and Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'USER_LOGOUT_SUCCESS',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'new_values' => [
                    'ip' => $request->ip(),
                ],
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('info', 'Anda telah keluar dari Portal SIAKAD & ERP UPH.');
    }

    /**
     * Centralized Portal Dashboard Router (Dispatches based on RBAC role).
     */
    public function portalRouter(): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->primary_role ?? $user->role ?? 'student';
        $targetUrl = \App\Http\Middleware\RedirectByRole::getDashboardForRole($role);

        return redirect($targetUrl);
    }

    /**
     * Password Management & Privilege Viewer Form.
     */
    public function showPasswordForm(): View
    {
        $user = Auth::user();
        $permissions = \App\Models\Permission::orderBy('module')->orderBy('name')->get();

        return view('portal.account.password', compact('user', 'permissions'));
    }

    /**
     * Handle Password Update with Current Password Verification.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'USER_PASSWORD_CHANGED',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => [
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ],
        ]);

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}
