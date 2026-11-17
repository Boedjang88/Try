<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enterprise Dynamic Role Redirector Middleware
 *
 * Inspects `auth()->user()->primary_role` and strictly redirects to the dedicated
 * isolated workspace dashboard for that specific persona.
 */
class RedirectByRole
{
    /**
     * Map of roles to dedicated dashboard routes.
     */
    public const ROLE_DASHBOARDS = [
        'applicant' => '/portal/pmb/dashboard',
        'student'   => '/portal/student/dashboard',
        'lecturer'  => '/portal/lecturer/dashboard',
        'kaprodi'   => '/portal/kaprodi/dashboard',
        'dean'      => '/portal/dean/dashboard',
        'baak'      => '/portal/baak/dashboard',
        'bauk'      => '/portal/bauk/dashboard',
        'rector'    => '/portal/executive/dashboard',
        'superadmin'=> '/portal/executive/dashboard',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $role = $user->primary_role ?? $user->role ?? 'student';

        $targetUrl = self::ROLE_DASHBOARDS[$role] ?? '/portal/student/dashboard';

        // Prevent infinite redirect if already on target URL
        if ($request->path() === ltrim($targetUrl, '/')) {
            return $next($request);
        }

        return redirect($targetUrl);
    }

    /**
     * Helper to get target URL by role directly.
     */
    public static function getDashboardForRole(string $role): string
    {
        return self::ROLE_DASHBOARDS[$role] ?? '/portal/student/dashboard';
    }
}
