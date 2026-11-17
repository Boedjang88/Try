<?php

namespace App\Services;

class PmbService
{
    public const SCHOLARSHIP_TIER_PLATINUM = 'Platinum (100% Tuition Waiver)';
    public const SCHOLARSHIP_TIER_GOLD     = 'Gold (50% Tuition Waiver)';
    public const SCHOLARSHIP_TIER_SILVER   = 'Silver (25% Tuition Waiver)';
    public const SCHOLARSHIP_TIER_REGULAR  = 'Regular (No Scholarship)';

    /**
     * Calculate scholarship tier from CBT score.
     */
    public static function determineScholarshipTier(int|float $cbtScore): string
    {
        if ($cbtScore >= 85) {
            return self::SCHOLARSHIP_TIER_PLATINUM;
        } elseif ($cbtScore >= 70) {
            return self::SCHOLARSHIP_TIER_GOLD;
        } elseif ($cbtScore >= 60) {
            return self::SCHOLARSHIP_TIER_SILVER;
        }

        return self::SCHOLARSHIP_TIER_REGULAR;
    }

    /**
     * Generate standard 11-digit Student NIM.
     * Format: {AdmissionYear:4}{ProdiCodeOrId:3}{Sequence:4}
     */
    public static function generateNim(int $admissionYear, string $prodiCode, int $sequenceNumber): string
    {
        $codeMap = [
            'IF' => '082',
            'SI' => '083',
            'TE' => '084',
            'MN' => '012',
            'AK' => '013',
            'HK' => '021',
            'KD' => '031',
        ];

        $numericCode = $codeMap[strtoupper($prodiCode)] ?? '082';
        $shortYear = substr((string)$admissionYear, -2); // 26

        return sprintf('%s%s%04d', $shortYear, $numericCode, $sequenceNumber);
    }
}
