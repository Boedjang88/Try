<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enterprise Financial Clearance Middleware
 * Ensures students with outstanding financial holds cannot access KRS War Cart, Exam Cards, or Transcripts.
 */
class VerifyFinancialClearance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Non-student roles (Kaprodi, Dosen, BAAK, BAUK, Superadmin) bypass financial checks
        if ($user->role !== 'student') {
            return $next($request);
        }

        $student = $user->student;

        if (!$student) {
            abort(403, 'Profil mahasiswa tidak ditemukan.');
        }

        // Strict Financial Hold Verification
        if (!$student->is_financially_cleared) {
            $message = 'Akses ditangguhkan: Anda memiliki tagihan biaya kuliah (BPP Pokok/SKS) yang belum terverifikasi lunas oleh BAUK. Silakan lakukan pembayaran tagihan atau verifikasi Virtual Account Anda.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'FINANCIAL_HOLD',
                    'message' => $message,
                    'redirect_url' => '/finance/invoices',
                ], 403);
            }

            return redirect('/finance/invoices')->with('error', $message);
        }

        return $next($request);
    }
}
