<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * KaprodiGradeLockController
 *
 * Scoped to the Head of Study Program (Kaprodi). Manages grade verification,
 * ledger locking, and authorized unlock overrides with mandatory audit reason.
 */
class KaprodiGradeLockController extends Controller
{
    /**
     * Display all class sections in this Study Program with Grade Lock status.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $prodiId = $user->study_program_id;

        $studyProgram = $prodiId ? StudyProgram::find($prodiId) : StudyProgram::first();
        $prodiId = $studyProgram?->id;

        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        // Query class sections belonging to this study program
        $classSections = ClassSection::with([
            'course.curriculum',
            'lecturer.user',
            'room',
            'krsItems.krsSubmission.student.user'
        ])
        ->whereHas('course.curriculum', fn($q) => $q->where('study_program_id', $prodiId))
        ->where('academic_period_id', $activePeriod?->id)
        ->get();

        // Audit logs related to grade overrides
        $auditLogs = AuditLog::with('user')
            ->whereIn('action', ['GRADE_LOCK_OVERRIDDEN', 'GRADES_LOCKED', 'GRADES_BULK_SAVED'])
            ->latest()
            ->limit(10)
            ->get();

        return view('portal.kaprodi.grade-locks', [
            'studyProgram' => $studyProgram,
            'activePeriod' => $activePeriod,
            'classSections' => $classSections,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Kaprodi authorizes unlocking class grades with mandatory audit reason (Loop 4).
     */
    public function unlock(Request $request, ClassSection $classSection): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (!in_array($user?->role, ['kaprodi', 'superadmin'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Hanya Kaprodi yang berwenang membuka kunci nilai.',
                ], 403);
            }
            abort(403, 'Akses ditolak: Hanya Kaprodi yang berwenang membuka kunci nilai.');
        }

        // Anti-IDOR Authorization: Kaprodi cannot unlock grades outside their scoped study program
        if ($user->role === 'kaprodi') {
            $courseProdiId = $classSection->course->study_program_id
                ?? $classSection->course->curriculum->study_program_id
                ?? null;

            if (!$user->study_program_id || ($courseProdiId && $courseProdiId !== $user->study_program_id)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak: Anda tidak berwenang membuka kunci nilai untuk program studi lain.',
                    ], 403);
                }
                abort(403, 'Akses ditolak: Anda tidak berwenang membuka kunci nilai untuk program studi lain.');
            }
        }

        $validated = $request->validate([
            'audit_reason' => 'required|string|min:8|max:500',
        ]);

        // Unlock all student grades in this section
        $classSection->krsItems()->update(['is_locked' => false]);

        // Record immutable audit trail
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'GRADE_LOCK_OVERRIDDEN',
            'description' => "Grade lock overridden by Kaprodi [{$user->full_name}] for {$classSection->course->code} - Seksi {$classSection->section_code}. Alasan: {$validated['audit_reason']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $successMsg = "Kunci nilai matakuliah {$classSection->course->name_id} (Seksi {$classSection->section_code}) berhasil dibuka. Dosen kini dapat merevisi nilai.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'is_locked' => false,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Kaprodi enforces final freeze on class section grades.
     */
    public function lock(Request $request, ClassSection $classSection): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (!in_array($user?->role, ['kaprodi', 'superadmin'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Hanya Kaprodi yang berwenang mengunci nilai.',
                ], 403);
            }
            abort(403, 'Akses ditolak: Hanya Kaprodi yang berwenang mengunci nilai.');
        }

        // Anti-IDOR Authorization: Kaprodi cannot lock grades outside their scoped study program
        if ($user->role === 'kaprodi') {
            $courseProdiId = $classSection->course->study_program_id
                ?? $classSection->course->curriculum->study_program_id
                ?? null;

            if (!$user->study_program_id || ($courseProdiId && $courseProdiId !== $user->study_program_id)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak: Anda tidak berwenang mengunci nilai untuk program studi lain.',
                    ], 403);
                }
                abort(403, 'Akses ditolak: Anda tidak berwenang mengunci nilai untuk program studi lain.');
            }
        }

        $classSection->krsItems()->update(['is_locked' => true]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'GRADES_LOCKED',
            'description' => "Final grade freeze enforced by Kaprodi [{$user->full_name}] for {$classSection->course->code} - Seksi {$classSection->section_code}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $successMsg = "Nilai matakuliah {$classSection->course->name_id} resmi dikunci.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'is_locked' => true,
            ]);
        }

        return back()->with('success', $successMsg);
    }
}
