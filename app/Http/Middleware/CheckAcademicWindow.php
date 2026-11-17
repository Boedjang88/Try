<?php

namespace App\Http\Middleware;

use App\Models\AcademicPeriod;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enterprise Academic Window Middleware
 * Enforces active calendar windows for KRS enrollment and Faculty Grading.
 */
class CheckAcademicWindow
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $windowType  'krs' | 'grading'
     */
    public function handle(Request $request, Closure $next, string $windowType = 'krs'): Response
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        if (!$activePeriod) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'NO_ACTIVE_ACADEMIC_PERIOD',
                    'message' => 'Operasi ditolak: Tidak ada Tahun Akademik yang aktif pada sistem.',
                ], 422);
            }
            return redirect('/portal/dashboard')->with('error', 'Tidak ada periode akademik aktif saat ini. Hubungi BAAK.');
        }

        // 1. KRS Window Enforcement
        if ($windowType === 'krs') {
            if (!$activePeriod->isKrsWindowOpen()) {
                $start = $activePeriod->krs_start_date ? $activePeriod->krs_start_date->format('d/m/Y H:i') : '-';
                $end = $activePeriod->krs_end_date ? $activePeriod->krs_end_date->format('d/m/Y H:i') : '-';
                $message = "Periode Pengisian KRS Online saat ini sedang ditutup. Jadwal resmi KRS: {$start} s/d {$end} WIB.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'code' => 'KRS_WINDOW_CLOSED',
                        'message' => $message,
                    ], 403);
                }
                return redirect('/portal/dashboard')->with('error', $message);
            }
        }

        // 2. Grading Window Enforcement
        if ($windowType === 'grading') {
            if (!$activePeriod->isGradingWindowOpen()) {
                $start = $activePeriod->grade_input_start_date ? $activePeriod->grade_input_start_date->format('d/m/Y H:i') : '-';
                $end = $activePeriod->grade_input_end_date ? $activePeriod->grade_input_end_date->format('d/m/Y H:i') : '-';
                $message = "Periode Penginputan Nilai Dosen telah berakhir atau belum dibuka. Jadwal: {$start} s/d {$end} WIB. Hubungi Kaprodi untuk izin pembukaan kunci nilai.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'code' => 'GRADING_WINDOW_CLOSED',
                        'message' => $message,
                    ], 403);
                }
                return redirect('/portal/dashboard')->with('error', $message);
            }
        }

        // Attach active period to request attributes for downstream consumption
        $request->attributes->set('active_academic_period', $activePeriod);

        return $next($request);
    }
}
