<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforcePrivilegeMiddleware
{
    /**
     * Handle an incoming request.
     * Enforces granular permission slugs via User::hasPrivilege().
     */
    public function handle(Request $request, Closure $next, ?string $privilege = null): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi diperlukan untuk mengakses layanan ini.',
                ], 401);
            }
            return redirect()->guest('/login')->with('warning', 'Silakan masuk terlebih dahulu.');
        }

        $user = Auth::user();

        // If privilege is specified, verify granular permission
        if ($privilege && !$user->hasPrivilege($privilege)) {
            $message = "Hak akses ditolak: Akun Anda tidak memiliki izin [{$privilege}].";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'INSUFFICIENT_PRIVILEGES',
                    'message' => $message,
                    'required_privilege' => $privilege,
                ], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
