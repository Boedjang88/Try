<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\YudisiumClearance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class YudisiumController extends Controller
{
    /**
     * Display Yudisium & Graduation Clearances Dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        $activePeriod = AcademicPeriod::where('is_active', true)->first() 
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        $myClearance = null;
        $clearanceList = collect();

        if ($user->role === 'student' && $student) {
            $myClearance = $this->ensureClearanceExists($student, $activePeriod);
            $myClearance->load(['student.user', 'student.studyProgram.faculty', 'academicPeriod', 'approvedByUser']);
        } else {
            // Staff / Kaprodi / Dean / BAUK / Library view
            $query = YudisiumClearance::with([
                'student.user',
                'student.studyProgram.faculty',
                'academicPeriod',
                'approvedByUser'
            ]);

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            $clearanceList = $query->orderByDesc('created_at')->get();
        }

        return view('yudisium.index', compact('myClearance', 'clearanceList', 'activePeriod', 'student'));
    }

    /**
     * Ensure Student Yudisium Clearance record exists and automatically syncs Financial status.
     */
    public function ensureClearanceExists(Student $student, AcademicPeriod $period): YudisiumClearance
    {
        $clearance = YudisiumClearance::firstOrCreate(
            [
                'student_id' => $student->id,
                'academic_period_id' => $period->id,
            ],
            [
                'id' => (string) Str::uuid(),
                'finance_cleared' => false,
                'library_cleared' => false,
                'repository_cleared' => false,
                'faculty_cleared' => false,
                'status' => 'pending',
            ]
        );

        // Auto-sync finance status from invoices
        $unpaidInvoices = StudentInvoice::where('student_id', $student->id)
            ->where('payment_status', '!=', 'paid')
            ->count();

        $shouldBeFinanceCleared = ($unpaidInvoices === 0 && $student->is_financially_cleared);

        if ($clearance->finance_cleared !== $shouldBeFinanceCleared) {
            $clearance->finance_cleared = $shouldBeFinanceCleared;
        }

        // Auto-sync library status: active unreturned loans ('borrowed' or 'overdue') or unpaid fine locks library_cleared
        $activeLoansCount = \App\Models\LibraryLoan::where('student_id', $student->id)
            ->where(function ($q) {
                $q->whereIn('status', ['borrowed', 'overdue'])
                  ->orWhere('fine_amount', '>', 0);
            })
            ->count();

        if ($activeLoansCount > 0) {
            $clearance->library_cleared = false;
        }

        // Auto-sync lab status: pending equipment or lab bookings locks lab_cleared
        $activeLabIssues = \App\Models\FacilityBooking::where('student_id', $student->id)
            ->where('status', 'pending')
            ->count();

        if ($activeLabIssues > 0) {
            $clearance->lab_cleared = false;
        }

        $clearance->save();

        return $clearance;
    }

    /**
     * Departmental Staff Verifies a Specific Clearance Checkpoint.
     */
    public function verifyCheckpoint(Request $request, YudisiumClearance $clearance): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'department' => 'required|in:finance,library,lab,repository,faculty',
            'cleared' => 'required|boolean',
        ]);

        // Enforcement: Cannot clear library if there are unreturned books or unpaid fines
        if ($validated['department'] === 'library' && $validated['cleared']) {
            $activeLoans = \App\Models\LibraryLoan::where('student_id', $clearance->student_id)
                ->where(function ($q) {
                    $q->whereIn('status', ['borrowed', 'overdue'])
                      ->orWhere('fine_amount', '>', 0);
                })
                ->count();

            if ($activeLoans > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bebas Pustaka ditolak: Mahasiswa memiliki ' . $activeLoans . ' buku pinjaman belum dikembalikan atau denda aktif.',
                ], 422);
            }
        }

        $field = $validated['department'] . '_cleared';
        $clearance->{$field} = $validated['cleared'];

        // If all 4 checkpoints are satisfied, grant official graduation clearance
        if ($clearance->isFullyCleared()) {
            $prodiCode = $clearance->student->studyProgram->code ?? 'UPH';
            $year = date('Y');
            $randomPin = strtoupper(Str::random(8));

            $clearance->status = 'approved';
            $clearance->national_pin_number = "PIN-UPH/{$year}/{$prodiCode}/{$randomPin}";
            $clearance->graduation_date = now();
            $clearance->approved_by_user_id = Auth::id();

            // Set student academic status to graduated
            $student = $clearance->student;
            $student->academic_status = 'graduated';
            $student->save();

            $clearance->recordAudit(
                'YUDISIUM_APPROVED',
                "Student {$student->nim} officially graduated. National PIN: {$clearance->national_pin_number}"
            );
        } else {
            $clearance->status = 'pending';
        }

        $clearance->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Verifikasi departemen {$validated['department']} berhasil diperbarui.",
                'is_fully_cleared' => $clearance->isFullyCleared(),
                'status' => $clearance->status,
                'national_pin' => $clearance->national_pin_number,
            ]);
        }

        return redirect()->back()->with('success', "Verifikasi departemen {$validated['department']} berhasil disimpan.");
    }

    /**
     * Official Surat Keterangan Pendamping Ijazah (SKPI) Document View / Print.
     */
    public function skpi(YudisiumClearance $clearance): View
    {
        $clearance->load([
            'student.user',
            'student.studyProgram.faculty',
            'student.studyProgram.obeCpls',
            'academicPeriod',
            'approvedByUser'
        ]);

        return view('yudisium.skpi', compact('clearance'));
    }
}
