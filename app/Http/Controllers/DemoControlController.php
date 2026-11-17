<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\InAppNotification;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class DemoControlController extends Controller
{
    /**
     * Fast 1-Click State Resetter for Evaluation & Demo Scenarios (< 500ms).
     * Strictly gated to local and testing environments.
     */
    public function resetState(Request $request): JsonResponse
    {
        if (!App::environment(['local', 'testing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Demo Control Center is strictly disabled in production environment.',
            ], 403);
        }

        $startTime = microtime(true);

        DB::transaction(function () {
            // 1. Reset Academic Periods: Ensure 20251 is the active period
            AcademicPeriod::where('code', '20251')->update([
                'is_active' => true,
                'is_krs_active' => true,
                'is_grading_active' => true,
                'krs_start_date' => now()->subDays(5),
                'krs_end_date' => now()->addDays(10),
                'grade_input_start_date' => now()->subDays(5),
                'grade_input_end_date' => now()->addDays(15),
            ]);

            AcademicPeriod::where('code', '!=', '20251')->update([
                'is_active' => false,
            ]);

            $activePeriod = AcademicPeriod::where('code', '20251')->first();

            // 2. Reset Loop 1: Jessica / Blocked Student (01082200002)
            $blockedStudent = Student::where('nim', '01082200002')->first();
            if ($blockedStudent) {
                $blockedStudent->update([
                    'is_financially_cleared' => false,
                    'has_academic_hold' => true,
                    'academic_status' => 'active',
                ]);

                StudentInvoice::where('student_id', $blockedStudent->id)->update([
                    'payment_status' => 'unpaid',
                    'paid_amount' => 0.00,
                    'paid_at' => null,
                ]);
            }

            // 3. Reset Loop 2: Christian / Student 1 (01082200001)
            $student1 = Student::where('nim', '01082200001')->first();
            if ($student1) {
                $student1->update([
                    'is_financially_cleared' => true,
                    'has_academic_hold' => false,
                    'academic_status' => 'active',
                ]);

                if ($activePeriod) {
                    $krsSub = KrsSubmission::where('student_id', $student1->id)
                        ->where('academic_period_id', $activePeriod->id)
                        ->first();

                    if ($krsSub) {
                        $krsSub->update([
                            'status' => 'draft',
                            'qr_token' => null,
                            'rejection_note' => null,
                            'approved_by_lecturer_id' => null,
                            'approved_at' => null,
                        ]);
                    }
                }
            }

            // 4. Reset Loop 4: Unlock Grades & Grade Locks
            ClassSection::query()->update([
                'is_closed' => false,
            ]);

            KrsItem::query()->update([
                'is_locked' => false,
                'is_exam_blocked' => false,
                'exam_unblocked_reason' => null,
            ]);

            // 5. Clear transient test in-app notifications
            InAppNotification::where('created_at', '<', now()->subHours(1))->delete();

            // 6. Log Reset Execution
            AuditLog::create([
                'user_id' => auth()->id() ?? User::where('role', 'superadmin')->first()?->id ?? User::first()->id,
                'action' => 'DEMO_STATE_RESET',
                'description' => 'Demo Control Center 1-Click State Reset executed successfully.',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'success' => true,
            'message' => "Demo State berhasil direset ke kondisi awal simulasi dalam {$elapsedMs} ms.",
            'elapsed_ms' => $elapsedMs,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
