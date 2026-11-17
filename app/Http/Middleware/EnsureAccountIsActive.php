<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     * Enforces active account status. Suspended users are forcefully logged out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Akun Anda telah dinonaktifkan / ditangguhkan. Silakan hubungi Biro Administrasi Akademik (BAAK) UPH.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'code' => 'ACCOUNT_SUSPENDED',
                    ], 403);
                }

                return redirect()->route('login')->with('error', $message);
            }
        }

        return $next($request);
    }
}
