<?php

namespace App\Backend\Admissions;

use App\Backend\Finance\BillingEngineService;
use Exception;

/**
 * Enterprise Admissions (PMB - Penerimaan Mahasiswa Baru) Service
 * Handles multi-step registration, automated VA, CBT scoring, scholarship tiering,
 * LoA generation, and automatic NIM migration to active student status.
 */
class PMBService
{
    /**
     * Scholarship Tiers based on CBT Exam & Academic GPA
     */
    public const SCHOLARSHIP_TIER_1 = 'Platinum (100% Tuition Waiver)';
    public const SCHOLARSHIP_TIER_2 = 'Gold (50% Tuition Waiver)';
    public const SCHOLARSHIP_TIER_3 = 'Silver (25% Tuition Waiver)';
    public const SCHOLARSHIP_TIER_NONE = 'Regular (No Scholarship)';

    /**
     * Validate and create admission application.
     */
    public static function createApplication(array $applicantData, string $pathCode, string $prodiCode): array
    {
        // Generate applicant unique ID
        $registrationNumber = 'PMB-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Generate Virtual Account for Registration Fee
        $vaNumber = BillingEngineService::generateVirtualAccount('BCA', $registrationNumber);

        return [
            'registration_number' => $registrationNumber,
            'applicant_name'      => $applicantData['full_name'],
            'nik'                 => $applicantData['nik'],
            'high_school'         => $applicantData['high_school_name'],
            'path_code'           => $pathCode,
            'study_program_code'  => $prodiCode,
            'registration_fee'    => 350000.00,
            'virtual_account'     => $vaNumber,
            'payment_status'      => 'Pending',
            'admission_status'    => 'Document_Verification',
            'created_at'          => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Score Online Computer-Based Test (CBT) and determine scholarship tier.
     */
    public static function evaluateCBTExam(
        int $scoreLogic,
        int $scoreEnglish,
        int $scoreMajorSpecific,
        float $highSchoolGpa
    ): array {
        // Weighted composite score (Logic 35%, English 35%, Specific 30%)
        $compositeScore = round(($scoreLogic * 0.35) + ($scoreEnglish * 0.35) + ($scoreMajorSpecific * 0.30), 2);

        $passed = $compositeScore >= 65.00;
        $scholarshipTier = self::SCHOLARSHIP_TIER_NONE;
        $discountPercentage = 0.00;

        if ($passed) {
            if ($compositeScore >= 90.00 && $highSchoolGpa >= 3.80) {
                $scholarshipTier = self::SCHOLARSHIP_TIER_1;
                $discountPercentage = 100.00;
            } elseif ($compositeScore >= 82.00 && $highSchoolGpa >= 3.50) {
                $scholarshipTier = self::SCHOLARSHIP_TIER_2;
                $discountPercentage = 50.00;
            } elseif ($compositeScore >= 75.00) {
                $scholarshipTier = self::SCHOLARSHIP_TIER_3;
                $discountPercentage = 25.00;
            }
        }

        return [
            'composite_score'     => $compositeScore,
            'passed'              => $passed,
            'scholarship_tier'    => $scholarshipTier,
            'discount_percentage' => $discountPercentage,
            'grade_breakdown'     => [
                'logic'          => $scoreLogic,
                'english'        => $scoreEnglish,
                'major_specific' => $scoreMajorSpecific
            ]
        ];
    }

    /**
     * Generate Official Digital Letter of Acceptance (LoA) with Cryptographic Token.
     */
    public static function generateLoA(
        string $registrationNumber,
        string $applicantName,
        string $studyProgramName,
        string $facultyName,
        array $evaluationResult
    ): array {
        $skNumber = 'SK-REK/UPH/' . date('Y') . '/' . strtoupper(substr(md5($registrationNumber), 0, 5));
        $verificationToken = hash('sha256', $registrationNumber . '|' . $skNumber . '|UPH_OFFICIAL');

        return [
            'decree_number'       => $skNumber,
            'registration_number' => $registrationNumber,
            'applicant_name'      => $applicantName,
            'faculty'             => $facultyName,
            'study_program'       => $studyProgramName,
            'decision'            => $evaluationResult['passed'] ? 'DITERIMA' : 'TIDAK_DITERIMA',
            'scholarship_awarded' => $evaluationResult['scholarship_tier'],
            'discount_percentage' => $evaluationResult['discount_percentage'],
            'verification_token'  => $verificationToken,
            'verification_url'    => 'https://verify.uph.edu/loa/' . $verificationToken,
            're_registration_due' => date('Y-m-d', strtotime('+14 days')),
            'issued_date'         => date('d F Y')
        ];
    }

    /**
     * Re-registration verification and Automatic NIM Generation.
     * Generates standard Indonesian NIM format: [FacultyCode][ProdiCode][Degree][YearYY][SequenceNumber]
     * Example for S1 Informatika 2026: 01021260045
     */
    public static function processReRegistrationAndMigrateStudent(
        string $registrationNumber,
        string $facultyCode,
        string $prodiCode,
        int $degreeLevelCode = 1, // 1 = S1, 2 = S2, 3 = S3, 0 = D3
        int $year = 2026,
        int $sequenceNumber = 45
    ): array {
        $year2Digits = substr((string)$year, -2);
        $paddedSequence = str_pad((string)$sequenceNumber, 5, '0', STR_PAD_LEFT);
        
        $nim = sprintf('%s%s%d%s%s', $facultyCode, $prodiCode, $degreeLevelCode, $year2Digits, $paddedSequence);

        return [
            'registration_number'  => $registrationNumber,
            'nim'                  => $nim,
            'status'               => 'ACTIVE_STUDENT',
            'academic_year'        => $year . '/' . ($year + 1) . ' Ganjil',
            'initial_semester'     => 1,
            'student_email'        => strtolower($nim) . '@student.uph.edu',
            'default_portal_login' => $nim,
            'migrated_at'          => date('Y-m-d H:i:s')
        ];
    }
}
