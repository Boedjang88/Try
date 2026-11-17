<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RedirectByRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * QuickSwitchAccountController
 *
 * Provides 1-click instant account switching for evaluation, QA, and demonstrations
 * across all 7 requested enterprise personas with strict workspace isolation.
 */
class QuickSwitchAccountController extends Controller
{
    /**
     * Map of demo persona keys to account credentials and details.
     */
    public const DEMO_ACCOUNTS = [
        'applicant' => [
            'email' => 'applicant1@uph.edu',
            'role' => 'applicant',
            'title' => 'Calon Mahasiswa',
            'subtitle' => 'Alexander Michael (PMB-2026-001)',
            'badge' => 'CBT Lulus & LoA Terbit',
            'badge_color' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
            'target' => '/portal/pmb/dashboard',
        ],
        'student_cleared' => [
            'email' => 'student1@uph.edu',
            'role' => 'student',
            'title' => 'Mahasiswa Aktif Smt 3',
            'subtitle' => 'Christian Jonathan (NIM: 01082200001)',
            'badge' => 'IPK 3.65 • Financially Cleared',
            'badge_color' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
            'target' => '/portal/student/dashboard',
        ],
        'student_blocked' => [
            'email' => 'student.blocked@uph.edu',
            'role' => 'student',
            'title' => 'Mahasiswa Smt 5 (Blocked)',
            'subtitle' => 'Jessica Aurelia Tan (NIM: 01082200002)',
            'badge' => 'Tunggakan UKT • Financial Hold',
            'badge_color' => 'bg-red-500/20 text-red-300 border-red-500/40',
            'target' => '/portal/student/dashboard',
        ],
        'lecturer' => [
            'email' => 'lecturer1@uph.edu',
            'role' => 'lecturer',
            'title' => 'Dosen Pengajar & PA',
            'subtitle' => 'Dr. Ir. Johanes Tan, M.Sc., Ph.D.',
            'badge' => 'Pengampu 2 Kelas • 15 Advisees',
            'badge_color' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
            'target' => '/portal/lecturer/dashboard',
        ],
        'kaprodi' => [
            'email' => 'kaprodi.if@uph.edu',
            'role' => 'kaprodi',
            'title' => 'Kaprodi Informatika',
            'subtitle' => 'Dr. Veronica Siregar, M.Kom.',
            'badge' => 'Scope: Prodi IF • Kurikulum OBE',
            'badge_color' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40',
            'target' => '/portal/kaprodi/dashboard',
        ],
        'baak' => [
            'email' => 'baak@uph.edu',
            'role' => 'baak',
            'title' => 'Admin BAAK',
            'subtitle' => 'Biro Administrasi Akademik',
            'badge' => 'Kalender & PDDIKTI Feeder',
            'badge_color' => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
            'target' => '/portal/baak/dashboard',
        ],
        'bauk' => [
            'email' => 'bauk@uph.edu',
            'role' => 'bauk',
            'title' => 'Admin BAUK (Keuangan)',
            'subtitle' => 'Biro Administrasi Keuangan',
            'badge' => 'Billing & Rekonsiliasi VA',
            'badge_color' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40',
            'target' => '/portal/bauk/dashboard',
        ],
        'rector' => [
            'email' => 'rector@uph.edu',
            'role' => 'rector',
            'title' => 'Rektor / Eksekutif',
            'subtitle' => 'Dr. (Hon.) Jonathan L. Parapak',
            'badge' => 'Executive EDSS Analytics',
            'badge_color' => 'bg-uph-gold/20 text-uph-gold-warm border-uph-gold/40',
            'target' => '/portal/executive/dashboard',
        ],
        'dean' => [
            'email' => 'dean.fik@uph.edu',
            'role' => 'dean',
            'title' => 'Dekan Fakultas (FIK)',
            'subtitle' => 'Prof. Dr. Samuel H. Tirtamulia',
            'badge' => 'Scope: Fak. Ilmu Komputer',
            'badge_color' => 'bg-teal-500/20 text-teal-300 border-teal-500/40',
            'target' => '/portal/dean/dashboard',
        ],
    ];

    /**
     * Perform instant 1-click account switch.
     */
    public function switch(string $accountKey, Request $request): RedirectResponse
    {
        // Production Gate: Never allow demo switcher in production or if disabled
        if (config('app.env') === 'production' || !config('app.enable_demo_switcher', true)) {
            abort(404, 'Demo account switcher is disabled in production.');
        }

        $accountConfig = self::DEMO_ACCOUNTS[$accountKey] ?? null;

        if (!$accountConfig) {
            return back()->with('error', "Akun demo [{$accountKey}] tidak ditemukan dalam konfigurasi.");
        }

        // Look up by email
        $user = User::where('email', $accountConfig['email'])->first();

        // Fallback by role
        if (!$user) {
            $user = User::where('role', $accountConfig['role'])->first();
        }

        if (!$user) {
            return back()->with('error', "User [{$accountConfig['email']}] belum tersedia di database. Jalankan db:seed.");
        }

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        $targetUrl = $accountConfig['target'] ?? RedirectByRole::getDashboardForRole($user->primary_role);

        return redirect($targetUrl)->with('success', "Beralih akun: Masuk sebagai {$user->full_name} ({$accountConfig['title']})");
    }

    /**
     * Get all available demo accounts.
     */
    public static function getAvailableAccounts(): array
    {
        return self::DEMO_ACCOUNTS;
    }
}
