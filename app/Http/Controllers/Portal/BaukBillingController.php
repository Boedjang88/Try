<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * BaukBillingController
 *
 * Dedicated BAUK financial administration workspace for managing student tuition invoices,
 * Virtual Account reconciliation, and releasing/imposing academic financial holds.
 */
class BaukBillingController extends Controller
{
    /**
     * Display all tuition billing records and financial holds.
     */
    public function index(Request $request): View
    {
        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        $statusFilter = $request->get('status', 'all');

        $invoicesQuery = StudentInvoice::with(['student.user', 'student.studyProgram.faculty', 'academicPeriod'])
            ->orderByDesc('created_at');

        if ($statusFilter !== 'all') {
            $invoicesQuery->where('payment_status', $statusFilter);
        }

        $invoices = $invoicesQuery->paginate(15)->withQueryString();

        // Financial Metrics
        $totalBilled = StudentInvoice::sum('amount') ?? 0;
        $totalCollected = StudentInvoice::where('payment_status', 'paid')->sum('paid_amount') ?? 0;
        $totalPending = StudentInvoice::whereIn('payment_status', ['pending', 'unpaid'])->sum('amount') ?? 0;
        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

        // Blocked / Hold Students Count
        $heldStudents = Student::where('is_financially_cleared', false)
            ->orWhere('has_academic_hold', true)
            ->with(['user', 'studyProgram', 'invoices' => fn($q) => $q->whereIn('payment_status', ['pending', 'unpaid'])])
            ->get();

        return view('portal.bauk.billing', [
            'invoices' => $invoices,
            'activePeriod' => $activePeriod,
            'totalBilled' => $totalBilled,
            'totalCollected' => $totalCollected,
            'totalPending' => $totalPending,
            'collectionRate' => $collectionRate,
            'heldStudents' => $heldStudents,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Atomically verify payment, clear financial hold, and record audit log (Loop 1).
     */
    public function verifyPayment(Request $request, StudentInvoice $invoice): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        return DB::transaction(function () use ($request, $invoice, $user) {
            $student = $invoice->student;

            // 1. Mark invoice as paid
            $invoice->payment_status = 'paid';
            $invoice->paid_amount = $invoice->amount;
            $invoice->paid_at = now();
            $invoice->gateway_reference = 'BAUK-MANUAL-SETTLED-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $invoice->save();

            // 2. Check if student has any remaining unpaid invoices
            $remainingUnpaid = StudentInvoice::where('student_id', $student->id)
                ->where('id', '!=', $invoice->id)
                ->whereIn('payment_status', ['pending', 'unpaid'])
                ->count();

            if ($remainingUnpaid === 0) {
                $student->is_financially_cleared = true;
                $student->has_academic_hold = false;
                $student->save();

                if ($student->user) {
                    \App\Services\NotificationService::notify(
                        $student->user,
                        'financial_unblock',
                        'Penangguhan Finansial Dibuka (Cleared)',
                        "Pembayaran tagihan [{$invoice->invoice_number}] telah diverifikasi oleh BAUK. Penangguhan finansial telah dibuka dan akses KRS telah aktif.",
                        '/portal/student/krs'
                    );
                }
            }

            // 3. Record Audit Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'PAYMENT_VERIFIED_AND_HOLD_RELEASED',
                'description' => "Invoice [{$invoice->invoice_number}] settled. Financial clearance granted to NIM: {$student->nim} ({$student->user->full_name}).",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Clear cache
            Cache::forget('active_academic_period');

            $successMsg = "Pembayaran tagihan [{$invoice->invoice_number}] sebesar Rp " . number_format($invoice->amount, 0, ',', '.') . " berhasil diverifikasi. Status finansial mahasiswa kini AKTIF (Hold Dilepas).";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'is_financially_cleared' => $student->is_financially_cleared,
                    'invoice' => $invoice,
                ]);
            }

            return back()->with('success', $successMsg);
        });
    }

    /**
     * Direct toggle to release or impose academic financial hold on a student.
     */
    public function toggleHold(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $newClearedState = !$student->is_financially_cleared;

        $student->is_financially_cleared = $newClearedState;
        $student->has_academic_hold = !$newClearedState;
        $student->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => $newClearedState ? 'FINANCIAL_HOLD_RELEASED' : 'FINANCIAL_HOLD_IMPOSED',
            'description' => "Financial hold state for NIM: {$student->nim} updated to: " . ($newClearedState ? 'CLEARED' : 'BLOCKED'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $msg = $newClearedState 
            ? "Status hold finansial untuk mahasiswa NIM {$student->nim} berhasil dilepas." 
            : "Mahasiswa NIM {$student->nim} berhasil dimasukkan ke dalam daftar Financial Hold.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'is_financially_cleared' => $student->is_financially_cleared,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Simulate Virtual Account payment settlement from BAUK portal.
     */
    public function simulateVa(Request $request, StudentInvoice $invoice): JsonResponse|RedirectResponse
    {
        return $this->verifyPayment($request, $invoice);
    }
}
