<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\KrsSubmission;
use App\Models\Student;
use App\Models\StudentAkmHistory;
use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SemesterRolloverService
{
    /**
     * Execute atomic semester rollover and mass billing generation.
     *
     * @param AcademicPeriod $fromPeriod Current active period
     * @param AcademicPeriod $toPeriod Target period to be activated
     * @param User $actor BAAK or administrator executing the rollover
     * @return array Summary metrics
     */
    public function rollover(AcademicPeriod $fromPeriod, AcademicPeriod $toPeriod, User $actor): array
    {
        return DB::transaction(function () use ($fromPeriod, $toPeriod, $actor) {
            $students = Student::whereIn('academic_status', ['active', 'on_leave'])
                ->with(['user', 'invoices'])
                ->get();

            $processedCount = 0;
            $invoicesGenerated = 0;
            $akmArchived = 0;

            foreach ($students as $student) {
                // 1. Calculate semester performance from current period
                $krsSubmission = KrsSubmission::where('student_id', $student->id)
                    ->where('academic_period_id', $fromPeriod->id)
                    ->with('items.classSection.course')
                    ->first();

                $semesterSks = 0;
                $weightedPoints = 0.00;

                if ($krsSubmission && $krsSubmission->items) {
                    foreach ($krsSubmission->items as $item) {
                        $sks = $item->classSection->course->sks ?? 3;
                        $semesterSks += $sks;
                        $weightedPoints += ((float)($item->grade_point ?? 0.00)) * $sks;
                    }
                }

                $ips = $semesterSks > 0 ? round($weightedPoints / $semesterSks, 2) : (float)$student->last_semester_gpa_ips;
                $akmStatus = $student->academic_status === 'on_leave' ? 'C' : 'AK';

                // 2. Archive to StudentAkmHistory
                StudentAkmHistory::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_period_id' => $fromPeriod->id,
                    ],
                    [
                        'semester_number' => $student->current_semester,
                        'sks_semester' => $semesterSks,
                        'sks_total' => $student->total_sks_completed,
                        'ips' => $ips,
                        'ipk' => $student->current_gpa_ipk,
                        'status' => $akmStatus,
                    ]
                );
                $akmArchived++;

                // 3. Increment current semester for active students, reset clearance
                if ($student->academic_status === 'active') {
                    $student->current_semester += 1;
                    $student->is_financially_cleared = false;
                    $student->last_semester_gpa_ips = $ips;
                    $student->save();
                }

                // 4. Generate new term invoice (BPP Pokok)
                $invoiceNumber = 'INV/' . $toPeriod->code . '/' . $student->nim . '/' . strtoupper(substr(uniqid(), -4));
                $vaNumber = '9888' . str_pad(substr($student->nim, -8), 8, '0', STR_PAD_LEFT);
                $tuitionAmount = $student->academic_status === 'on_leave' ? 1500000.00 : 12500000.00;

                StudentInvoice::create([
                    'id' => (string) Str::uuid(),
                    'student_id' => $student->id,
                    'academic_period_id' => $toPeriod->id,
                    'invoice_number' => $invoiceNumber,
                    'va_number' => $vaNumber,
                    'title' => 'Biaya Kuliah ' . $toPeriod->name . ' - ' . ($student->academic_status === 'on_leave' ? 'Holding Fee Cuti' : 'BPP Pokok'),
                    'amount' => $tuitionAmount,
                    'paid_amount' => 0.00,
                    'payment_status' => 'unpaid',
                    'due_date' => now()->addDays(30)->toDateString(),
                ]);
                $invoicesGenerated++;

                // 5. Send persistent in-app notification to student
                if ($student->user) {
                    NotificationService::notify(
                        $student->user,
                        'semester_rollover',
                        'Tahun Akademik Baru: ' . $toPeriod->name,
                        'Periode perkuliahan telah beralih ke ' . $toPeriod->name . '. Tagihan biaya kuliah sebesar Rp ' . number_format($tuitionAmount, 0, ',', '.') . ' telah diterbitkan. Lakukan pelunasan untuk membuka akses KRS.',
                        '/portal/student/billing'
                    );
                }

                $processedCount++;
            }

            // 6. Transition Period Flags
            $fromPeriod->update([
                'is_active' => false,
                'is_krs_active' => false,
                'is_grading_active' => false,
            ]);

            $toPeriod->update([
                'is_active' => true,
                'is_krs_active' => true,
                'is_grading_active' => false,
            ]);

            // 7. Audit Log
            AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'SEMESTER_ROLLOVER',
                'auditable_type' => AcademicPeriod::class,
                'auditable_id' => $toPeriod->id,
                'old_values' => ['active_period_id' => $fromPeriod->id, 'code' => $fromPeriod->code],
                'new_values' => ['active_period_id' => $toPeriod->id, 'code' => $toPeriod->code],
                'reason' => 'Transisi massal tahun akademik ' . $fromPeriod->name . ' -> ' . $toPeriod->name . ' oleh ' . $actor->full_name,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Artisan/Console',
            ]);

            return [
                'students_processed' => $processedCount,
                'akm_archived' => $akmArchived,
                'invoices_generated' => $invoicesGenerated,
                'from_period' => $fromPeriod->name,
                'to_period' => $toPeriod->name,
            ];
        });
    }
}
