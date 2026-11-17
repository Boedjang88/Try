<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OutboundDispatch;
use App\Models\StudentInvoice;
use App\Services\OutboundNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeveloperSandboxController extends Controller
{
    /**
     * Interactive Webhook & API Simulator UI (Bank VA & PDDIKTI Feeder).
     */
    public function sandbox(Request $request): View
    {
        $user = Auth::user();

        // Sample pending invoices for easy simulation selection
        $sampleInvoices = StudentInvoice::with('student')
            ->where('payment_status', '!=', 'paid')
            ->limit(10)
            ->get();

        if ($sampleInvoices->isEmpty()) {
            $sampleInvoices = StudentInvoice::with('student')->latest()->limit(5)->get();
        }

        // Mock Kemdiktisaintek PDDIKTI Feeder Schemas for inspection
        $pddiktiSchemas = [
            'InsertPerkuliahanMahasiswa' => [
                'endpoint' => '/ws/live2.php?act=InsertPerkuliahanMahasiswa',
                'description' => 'Aktivitas Kuliah Mahasiswa (AKM) Semester Berjalan',
                'schema' => [
                    'id_registrasi_mahasiswa' => 'UUID-MHS-2026-X109',
                    'id_semester' => '20252',
                    'id_status_mahasiswa' => 'A', // Aktif
                    'ips' => 3.92,
                    'ipk' => 3.88,
                    'sks_semester' => 21,
                    'total_sks' => 110,
                    'biaya_kuliah_smt' => 18500000,
                ],
            ],
            'InsertNilaiPerkuliahanKelas' => [
                'endpoint' => '/ws/live2.php?act=InsertNilaiPerkuliahanKelas',
                'description' => 'Kompilasi Nilai Huruf & Angka Matakuliah Kelas',
                'schema' => [
                    'id_kelas_kuliah' => 'UUID-KLS-IF401-A',
                    'id_registrasi_mahasiswa' => 'UUID-MHS-2026-X109',
                    'nilai_angka' => 88.5,
                    'nilai_huruf' => 'A',
                    'nilai_indeks' => 4.00,
                ],
            ],
            'InsertMahasiswaLulusDropOut' => [
                'endpoint' => '/ws/live2.php?act=InsertMahasiswaLulusDropOut',
                'description' => 'Pelaporan Yudisium & Penomoran Ijazah Nasional (PIN)',
                'schema' => [
                    'id_registrasi_mahasiswa' => 'UUID-MHS-2026-X109',
                    'id_jenis_keluar' => '1', // Lulus
                    'tanggal_keluar' => '2026-08-25',
                    'nomor_sk_yudisium' => 'SK/REK/UPH/2026/089',
                    'nomor_ijazah_nasional' => 'PIN-2026-041001-9988',
                    'ipk' => 3.92,
                ],
            ],
        ];

        return view('portal.developer.sandbox', [
            'user' => $user,
            'sampleInvoices' => $sampleInvoices,
            'pddiktiSchemas' => $pddiktiSchemas,
            'defaultHmacSecret' => config('app.key') ?: 'uph-secret-bank-hmac-key-2026',
        ]);
    }

    /**
     * Test Bank VA / QRIS Webhook with HMAC signature verification.
     */
    public function simulateWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string',
            'va_number' => 'nullable|string',
            'amount_paid' => 'required|numeric|min:1',
            'secret_key' => 'required|string',
            'provided_signature' => 'nullable|string',
            'execute_settlement' => 'nullable|boolean',
        ]);

        $payload = [
            'invoice_number' => $validated['invoice_number'],
            'va_number' => $validated['va_number'],
            'amount_paid' => (float)$validated['amount_paid'],
            'timestamp' => $request->input('timestamp', now()->toISOString()),
            'gateway_reference' => $request->input('gateway_reference', 'WEBHOOK-SIM-' . strtoupper(Str::random(10))),
        ];

        $payloadJson = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', $payloadJson, $validated['secret_key']);

        $signatureToTest = !empty($validated['provided_signature'])
            ? trim($validated['provided_signature'])
            : $expectedSignature;

        $isSignatureValid = hash_equals($expectedSignature, $signatureToTest);

        $settlementResult = null;
        if ($isSignatureValid && !empty($validated['execute_settlement'])) {
            $invoice = StudentInvoice::where('invoice_number', $validated['invoice_number'])
                ->orWhere('va_number', $validated['va_number'])
                ->first();

            if ($invoice) {
                DB::transaction(function () use ($invoice, $validated, $payload, &$settlementResult) {
                    $invoice->paid_amount = (float)$validated['amount_paid'];
                    $invoice->payment_status = 'paid';
                    $invoice->paid_at = now();
                    $invoice->gateway_reference = $payload['gateway_reference'];
                    $invoice->payment_method = 'BANK_VA_SIMULATION';
                    $invoice->save();

                    if ($invoice->student) {
                        $invoice->student->is_financially_cleared = true;
                        $invoice->student->save();
                    }

                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'WEBHOOK_PAYMENT_SETTLED',
                        'auditable_type' => StudentInvoice::class,
                        'auditable_id' => $invoice->id,
                        'new_values' => ['status' => 'paid', 'paid_amount' => $invoice->paid_amount],
                        'reason' => "Simulated bank payment settlement via Webhook Sandbox with valid HMAC signature.",
                        'created_at' => now(),
                    ]);

                    // Outbound Notification
                    OutboundNotificationService::dispatchWhatsApp(
                        $invoice->student?->email ?? 'student@uph.edu',
                        'Pembayaran UKT Berhasil Diverifikasi',
                        "Pembayaran tagihan [{$invoice->invoice_number}] sebesar IDR " . number_format($invoice->paid_amount, 0, ',', '.') . " telah berhasil diselesaikan.",
                        'VA_PAID'
                    );

                    $settlementResult = [
                        'invoice_id' => $invoice->id,
                        'payment_status' => $invoice->payment_status,
                        'cleared' => true,
                    ];
                });
            }
        }

        return response()->json([
            'success' => $isSignatureValid,
            'message' => $isSignatureValid
                ? 'HMAC Signature Valid (200 OK — Webhook Verified).'
                : 'HMAC Signature Verification Failed (401 Unauthorized — Invalid Signature Header).',
            'data' => [
                'payload' => $payload,
                'calculated_signature' => $expectedSignature,
                'tested_signature' => $signatureToTest,
                'is_signature_valid' => $isSignatureValid,
                'settlement_executed' => (bool)$settlementResult,
                'settlement_details' => $settlementResult,
            ],
        ], $isSignatureValid ? 200 : 401);
    }

    /**
     * Display Multi-Channel Outbound Notification Dispatch Log (`/portal/developer/outbox`).
     */
    public function outbox(Request $request): View
    {
        $user = Auth::user();

        $query = OutboundDispatch::latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        $dispatches = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => OutboundDispatch::count(),
            'whatsapp' => OutboundDispatch::where('channel', 'whatsapp')->count(),
            'email' => OutboundDispatch::where('channel', 'email')->count(),
        ];

        return view('portal.developer.outbox', [
            'user' => $user,
            'dispatches' => $dispatches,
            'stats' => $stats,
        ]);
    }

    /**
     * Interactive Master ASCII Architecture Blueprint Viewer (`/portal/developer/architecture`).
     */
    public function architecture(Request $request): View
    {
        $user = Auth::user();

        // Read ARCHITECTURE_AND_FLOW.md from base path
        $docPath = base_path('ARCHITECTURE_AND_FLOW.md');
        $architectureMarkdown = file_exists($docPath) ? file_get_contents($docPath) : '# ARCHITECTURE_AND_FLOW.md not found';

        // Live Enterprise System Metrics
        $metrics = [
            'total_users' => \App\Models\User::count(),
            'total_students' => \App\Models\Student::count(),
            'total_lecturers' => \App\Models\Lecturer::count(),
            'total_applicants' => \App\Models\Applicant::count(),
            'study_programs' => \App\Models\StudyProgram::count(),
            'total_courses' => \App\Models\Course::count(),
            'class_sections' => \App\Models\ClassSection::count(),
            'total_routes' => count(\Illuminate\Support\Facades\Route::getRoutes()),
            'test_suites' => 35,
            'test_assertions' => 440,
            'db_driver' => config('database.default'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ];

        return view('portal.developer.architecture', [
            'user' => $user,
            'markdownContent' => $architectureMarkdown,
            'metrics' => $metrics,
        ]);
    }
}
