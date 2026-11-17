<?php

namespace App\Backend\Finance;

use Exception;

/**
 * Enterprise Student Finance, Billing & Reconciliation Engine
 * Supports UKT & UP3 + BPP + SKS variable tuition, Installments (Cicilan),
 * Late-payment fines, Academic Hold enforcement, VA generation, and Webhook reconciliation.
 */
class BillingEngineService
{
    /**
     * Generate semester student bill based on enrolled SKS, fixed BPP, and active scholarships.
     */
    public static function generateSemesterBill(
        int $enrolledSks,
        float $bppRate = 12000000.00,
        float $sksRate = 350000.00,
        float $labFee = 1500000.00,
        float $scholarshipPercentage = 0.00
    ): array {
        $sksTotal = $enrolledSks * $sksRate;
        $subtotal = $bppRate + $sksTotal + $labFee;
        $discount = $subtotal * ($scholarshipPercentage / 100.00);
        $grandTotal = max(0.00, $subtotal - $discount);

        return [
            'bpp_amount'      => $bppRate,
            'sks_count'       => $enrolledSks,
            'sks_rate'        => $sksRate,
            'sks_amount'      => $sksTotal,
            'lab_amount'      => $labFee,
            'discount_amount' => $discount,
            'total_amount'    => $grandTotal,
            'status'          => 'Unpaid'
        ];
    }

    /**
     * Generate Bank Virtual Account (BCA, BNI, Mandiri, Permata).
     */
    public static function generateVirtualAccount(string $bank, string $identifier): string
    {
        $bankPrefix = match (strtoupper($bank)) {
            'BCA'     => '80128',
            'BNI'     => '8808',
            'MANDIRI' => '88770',
            'PERMATA' => '8522',
            default   => '9988'
        };

        // Clean numeric characters
        $cleanId = preg_replace('/\D/', '', $identifier);
        if (empty($cleanId)) {
            $cleanId = (string)crc32($identifier);
        }

        return $bankPrefix . str_pad(substr($cleanId, -8), 10, '0', STR_PAD_LEFT);
    }

    /**
     * Generate 3-Term Installment Schedule (Cicilan Kuliah).
     */
    public static function createInstallmentSchedule(float $totalAmount): array
    {
        // Standard UPH Installment: Term 1 (40% - Before KRS), Term 2 (30% - Before UTS), Term 3 (30% - Before UAS)
        $term1 = round($totalAmount * 0.40, 2);
        $term2 = round($totalAmount * 0.30, 2);
        $term3 = round($totalAmount * 0.30, 2);

        return [
            [
                'term'           => 1,
                'name'           => 'Cicilan 1 (Syarat Pembukaan KRS)',
                'amount'         => $term1,
                'due_date'       => date('Y-m-d', strtotime('+7 days')),
                'status'         => 'PAID',
                'payment_ref'    => 'TRX-' . strtoupper(substr(md5(uniqid()), 0, 8))
            ],
            [
                'term'           => 2,
                'name'           => 'Cicilan 2 (Syarat Kartu Ujian UTS)',
                'amount'         => $term2,
                'due_date'       => date('Y-m-d', strtotime('+60 days')),
                'status'         => 'UNPAID',
                'payment_ref'    => null
            ],
            [
                'term'           => 3,
                'name'           => 'Cicilan 3 (Syarat Kartu Ujian UAS)',
                'amount'         => $term3,
                'due_date'       => date('Y-m-d', strtotime('+120 days')),
                'status'         => 'UNPAID',
                'payment_ref'    => null
            ]
        ];
    }

    /**
     * Evaluate Academic Financial Hold (Blokir KRS / Kartu Ujian jika menunggak).
     */
    public static function evaluateAcademicHold(float $overdueBalance, int $daysOverdue = 0): array
    {
        $hasHold = $overdueBalance > 0 && $daysOverdue > 0;
        $latePenalty = 0.0;

        if ($hasHold) {
            // Late penalty 2% per 30 days overdue
            $latePenalty = round($overdueBalance * 0.02 * ceil($daysOverdue / 30), 2);
        }

        return [
            'has_hold'             => $hasHold,
            'overdue_balance'      => $overdueBalance,
            'late_penalty'         => $latePenalty,
            'total_payable'        => $overdueBalance + $latePenalty,
            'days_overdue'         => $daysOverdue,
            'krs_locked'           => $hasHold,
            'exam_card_locked'     => $hasHold,
            'khs_locked'           => $hasHold,
            'hold_message'         => $hasHold
                ? "Terdapat tunggakan finansial sebesar Rp " . number_format($overdueBalance, 0, ',', '.') . " terlambat {$daysOverdue} hari. Akses KRS/Ujian dibekukan sementara."
                : "Status finansial bersih (Clear). Tidak ada penahanan akademik."
        ];
    }

    /**
     * Process Gateway Webhook IPN (Instant Payment Notification).
     */
    public static function processWebhookNotification(array $payload, string $receivedSignature, string $secretKey): array
    {
        $computedSignature = hash_hmac('sha256', $payload['transaction_id'] . '|' . $payload['amount'] . '|' . $payload['va_number'], $secretKey);

        if (!hash_equals($computedSignature, $receivedSignature)) {
            throw new Exception('Invalid Webhook HMAC Signature: Security Mismatch.');
        }

        return [
            'verified'        => true,
            'transaction_id'  => $payload['transaction_id'],
            'va_number'       => $payload['va_number'],
            'amount_paid'     => (float)$payload['amount'],
            'payment_time'    => $payload['payment_time'] ?? date('Y-m-d H:i:s'),
            'reconciled_by'   => 'BAUK_AUTOMATED_SETTLEMENT',
            'official_receipt'=> 'KW-UPH/' . date('Ymd') . '/' . substr(md5($payload['transaction_id']), 0, 6)
        ];
    }
}
