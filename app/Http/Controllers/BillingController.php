<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\KrsSubmission;
use App\Models\Student;
use App\Models\StudentInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * Display Student Invoices & Tuition Payment Portal.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Resolve target student
        if ($user->role === 'student') {
            $student = $user->student;
        } else {
            $studentId = $request->get('student_id');
            $nim = $request->get('nim');
            if ($studentId) {
                $student = Student::with(['user', 'studyProgram.faculty'])->find($studentId);
            } elseif ($nim) {
                $student = Student::with(['user', 'studyProgram.faculty'])->where('nim', $nim)->first();
            } else {
                $student = Student::with(['user', 'studyProgram.faculty'])->first();
            }
        }

        if (!$student) {
            abort(404, 'Data mahasiswa tidak ditemukan.');
        }

        $student->load(['studyProgram.faculty', 'user']);

        $activePeriod = AcademicPeriod::where('is_active', true)->first() 
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        // Ensure current active period invoice exists
        if ($activePeriod) {
            $this->ensureSemesterInvoiceExists($student, $activePeriod);
        }

        // Fetch all invoices for student
        $invoices = StudentInvoice::with('academicPeriod')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $currentInvoice = $invoices->firstWhere('academic_period_id', $activePeriod?->id) ?? $invoices->first();

        // Financial Metrics
        $totalBilled = $invoices->sum('amount');
        $totalPaid = $invoices->sum('paid_amount');
        $outstandingBalance = max(0, $totalBilled - $totalPaid);
        $isCleared = $student->is_financially_cleared;

        return view('finance.index', compact(
            'student',
            'invoices',
            'currentInvoice',
            'activePeriod',
            'totalBilled',
            'totalPaid',
            'outstandingBalance',
            'isCleared'
        ));
    }

    /**
     * Generate or Update Semester Tuition Invoice based on Fixed + Variable SKS rate.
     */
    public function ensureSemesterInvoiceExists(Student $student, AcademicPeriod $period): StudentInvoice
    {
        $existing = StudentInvoice::where('student_id', $student->id)
            ->where('academic_period_id', $period->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Calculate SKS taken from approved KRS
        $krs = KrsSubmission::with('krsItems.classSection.course')
            ->where('student_id', $student->id)
            ->where('academic_period_id', $period->id)
            ->first();

        $sksCount = $krs ? $krs->total_sks : 18; // Default 18 SKS if KRS not yet finalized

        // UPH Enterprise Fee Structure:
        // 1. Fixed Tuition (BPP Pokok): IDR 12,500,000
        // 2. Variable SKS (BPP SKS): IDR 450,000 / SKS
        // 3. Laboratory / Development Facility: IDR 1,500,000
        $fixedTuition = 12500000.00;
        $variableSksRate = 450000.00;
        $labFacility = 1500000.00;
        $totalAmount = $fixedTuition + ($sksCount * $variableSksRate) + $labFacility;

        $semesterLabel = $period->semester === 'odd' ? 'Ganjil' : ($period->semester === 'even' ? 'Genap' : 'Pendek');
        $invoiceNumber = 'INV/UPH/' . $period->year_start . '/' . strtoupper(substr($period->semester, 0, 3)) . '/' . $student->nim;
        $vaNumber = '98877' . $student->nim;

        $invoice = StudentInvoice::create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'academic_period_id' => $period->id,
            'invoice_number' => $invoiceNumber,
            'va_number' => $vaNumber,
            'title' => "Biaya Kuliah (BPP Pokok + {$sksCount} SKS) Semester {$semesterLabel} {$period->year_start}/{$period->year_end}",
            'amount' => $totalAmount,
            'paid_amount' => 0.00,
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(30),
            'payment_method' => 'BCA_VA',
        ]);

        $student->recordAudit(
            'INVOICE_GENERATED',
            "Generated tuition invoice [{$invoiceNumber}] totaling IDR " . number_format($totalAmount, 0, ',', '.')
        );

        return $invoice;
    }

    /**
     * Generate or switch Payment Virtual Account / QRIS.
     */
    public function generateVA(Request $request, StudentInvoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:BCA_VA,BNI_VA,MANDIRI_VA,CIMB_VA,QRIS',
        ]);

        $prefix = match($validated['payment_method']) {
            'BCA_VA' => '98877',
            'BNI_VA' => '88081',
            'MANDIRI_VA' => '89201',
            'CIMB_VA' => '71120',
            'QRIS' => 'QRIS-UPH',
        };

        $newVa = $validated['payment_method'] === 'QRIS' 
            ? '00020101021226670016ID.CO.UPH.WWW0118936005030000089874' 
            : $prefix . $invoice->student->nim;

        $invoice->payment_method = $validated['payment_method'];
        $invoice->va_number = $newVa;
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran dan Nomor Virtual Account berhasil diperbarui.',
            'payment_method' => $invoice->payment_method,
            'va_number' => $invoice->va_number,
            'amount' => (float)$invoice->amount,
            'formatted_amount' => 'Rp ' . number_format($invoice->amount, 0, ',', '.'),
        ]);
    }

    /**
     * Printable Official Tuition Payment Receipt (Kwitansi Pembayaran Biaya Kuliah UPH).
     */
    public function kwitansi(StudentInvoice $invoice): View
    {
        $invoice->load([
            'student.user',
            'student.studyProgram.faculty',
            'academicPeriod'
        ]);

        $amountInWords = self::terbilangRupiah((int)$invoice->paid_amount ?: (int)$invoice->amount);

        return view('finance.receipt', compact('invoice', 'amountInWords'));
    }

    /**
     * Indonesian Currency Words (Terbilang Rupiah).
     */
    public static function terbilangRupiah(int $number): string
    {
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            $result = ' ' . $words[$number];
        } elseif ($number < 20) {
            $result = self::terbilangRupiah($number - 10) . ' Belas';
        } elseif ($number < 100) {
            $result = self::terbilangRupiah(intdiv($number, 10)) . ' Puluh' . self::terbilangRupiah($number % 10);
        } elseif ($number < 200) {
            $result = ' Seratus' . self::terbilangRupiah($number - 100);
        } elseif ($number < 1000) {
            $result = self::terbilangRupiah(intdiv($number, 100)) . ' Ratus' . self::terbilangRupiah($number % 100);
        } elseif ($number < 2000) {
            $result = ' Seribu' . self::terbilangRupiah($number - 1000);
        } elseif ($number < 1000000) {
            $result = self::terbilangRupiah(intdiv($number, 1000)) . ' Ribu' . self::terbilangRupiah($number % 1000);
        } elseif ($number < 1000000000) {
            $result = self::terbilangRupiah(intdiv($number, 1000000)) . ' Juta' . self::terbilangRupiah($number % 1000000);
        } else {
            $result = self::terbilangRupiah(intdiv($number, 1000000000)) . ' Miliar' . self::terbilangRupiah($number % 1000000000);
        }

        return trim($result) . ' Rupiah';
    }
}
