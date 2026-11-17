<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    /**
     * Handle External Payment Gateway Webhook Callback (Midtrans / Xendit / Bank VA Settlement).
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string',
            'va_number' => 'nullable|string',
            'amount_paid' => 'required|numeric|min:1',
            'gateway_reference' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        if (empty($validated['invoice_number']) && empty($validated['va_number'])) {
            return response()->json([
                'success' => false,
                'message' => 'Harus menyertakan invoice_number atau va_number.',
            ], 422);
        }

        $result = DB::transaction(function () use ($validated) {
            $query = StudentInvoice::query()->lockForUpdate();

            if (!empty($validated['invoice_number'])) {
                $query->where('invoice_number', $validated['invoice_number']);
            } else {
                $query->where('va_number', $validated['va_number']);
            }

            $invoice = $query->first();

            if (!$invoice) {
                return ['status' => 404, 'message' => 'Tagihan kuliah tidak ditemukan pada ledger universitas.'];
            }

            if ($invoice->payment_status === 'paid') {
                return ['status' => 200, 'message' => 'Tagihan telah dibayar sebelumnya (Idempotent).', 'invoice' => $invoice];
            }

            // Update invoice state
            $invoice->paid_amount = (float)$validated['amount_paid'];
            $invoice->payment_status = 'paid';
            $invoice->paid_at = now();
            $invoice->payment_method = $validated['payment_method'] ?? ($invoice->payment_method ?? 'VIRTUAL_ACCOUNT');
            $invoice->gateway_reference = $validated['gateway_reference'] ?? ('REF-' . strtoupper(Str::random(12)));
            $invoice->save();

            // Settle financial clearance on student
            $student = Student::where('id', $invoice->student_id)->lockForUpdate()->first();
            if ($student) {
                // Check if any other unpaid invoices exist
                $unpaidCount = StudentInvoice::where('student_id', $student->id)
                    ->where('payment_status', '!=', 'paid')
                    ->where('due_date', '<=', now())
                    ->count();

                if ($unpaidCount === 0) {
                    $student->is_financially_cleared = true;
                    $student->save();

                    $student->recordAudit(
                        'FINANCIAL_CLEARED',
                        "Student financial clearance activated upon full settlement of invoice [{$invoice->invoice_number}]."
                    );
                }
            }

            $invoice->recordAudit(
                'PAYMENT_SETTLED',
                "Tuition invoice settled. Amount: IDR " . number_format($invoice->paid_amount, 0, ',', '.') . " via {$invoice->payment_method}."
            );

            return ['status' => 200, 'message' => 'Pembayaran berhasil diverifikasi secara realtime.', 'invoice' => $invoice];
        });

        return response()->json([
            'success' => $result['status'] === 200,
            'message' => $result['message'],
            'data' => $result['invoice'] ?? null,
        ], $result['status']);
    }

    /**
     * UI Simulation Endpoint: Settle Payment Instantly for Testing.
     */
    public function simulatePayment(Request $request, StudentInvoice $invoice): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($invoice) {
            $lockedInvoice = StudentInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            $lockedInvoice->paid_amount = $lockedInvoice->amount;
            $lockedInvoice->payment_status = 'paid';
            $lockedInvoice->paid_at = now();
            $lockedInvoice->gateway_reference = 'SIM-BANK-' . strtoupper(Str::random(10));
            $lockedInvoice->save();

            $student = Student::where('id', $lockedInvoice->student_id)->lockForUpdate()->first();
            if ($student) {
                $student->is_financially_cleared = true;
                $student->save();

                $student->recordAudit(
                    'FINANCIAL_CLEARED_SIM',
                    "Financial clearance granted via UI Payment Simulation for invoice [{$lockedInvoice->invoice_number}]."
                );
            }

            $lockedInvoice->recordAudit(
                'PAYMENT_SIMULATED',
                "Simulated full settlement of IDR " . number_format($lockedInvoice->amount, 0, ',', '.')
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Simulasi pelunasan pembayaran berhasil! Registrasi finansial mahasiswa kini resmi Lunas (Cleared).',
            ]);
        }

        return redirect()->back()->with('success', 'Simulasi pelunasan biaya kuliah berhasil! Status finansial mahasiswa telah aktif.');
    }
}
