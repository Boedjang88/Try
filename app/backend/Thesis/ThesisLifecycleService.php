<?php

namespace App\Backend\Thesis;

use Exception;

/**
 * Enterprise Thesis Lifecycle, Yudisium, and Alumni Service
 * Covers Proposal screening, Dual Supervisor assignment, Guidance Logbook validation,
 * Defense scoring rubrics, Yudisium clearance, PIN generation, and SKPI portfolio.
 */
class ThesisLifecycleService
{
    public const MINIMUM_BIMBINGAN_SESSIONS = 8;
    public const MAX_PLAGIARISM_PERCENTAGE   = 20.00;

    /**
     * Submit and screen thesis proposal for plagiarism/similarity.
     */
    public static function screenProposal(string $title, string $abstract, float $similarityPercentage): array
    {
        $passedScreening = $similarityPercentage <= self::MAX_PLAGIARISM_PERCENTAGE;

        return [
            'title'                 => $title,
            'similarity_percentage' => $similarityPercentage,
            'threshold_percentage'  => self::MAX_PLAGIARISM_PERCENTAGE,
            'passed_screening'      => $passedScreening,
            'status'                => $passedScreening ? 'PROPOSAL_ACCEPTED_FOR_REVIEW' : 'REJECTED_PLAGIARISM_EXCEEDED',
            'screened_at'           => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Verify whether a student meets the minimum bimbingan requirement for Sidang Akhir.
     */
    public static function validateDefenseEligibility(array $bimbinganLogs): array
    {
        $approvedLogs = array_filter($bimbinganLogs, fn($log) => $log['status'] === 'APPROVED_BY_SUPERVISOR');
        $approvedCount = count($approvedLogs);
        $isEligible = $approvedCount >= self::MINIMUM_BIMBINGAN_SESSIONS;

        return [
            'is_eligible'           => $isEligible,
            'approved_logs_count'   => $approvedCount,
            'minimum_required'      => self::MINIMUM_BIMBINGAN_SESSIONS,
            'remaining_sessions'    => max(0, self::MINIMUM_BIMBINGAN_SESSIONS - $approvedCount),
            'message'               => $isEligible
                ? 'Mahasiswa telah memenuhi syarat minimal 8 kali bimbingan dan siap mendaftar Sidang Skripsi.'
                : "Bimbingan baru disetujui {$approvedCount} kali. Diperlukan minimal " . self::MINIMUM_BIMBINGAN_SESSIONS . " kali bimbingan."
        ];
    }

    /**
     * Calculate Final Defense Score from Rubric (3 Examiners).
     */
    public static function calculateDefenseScore(
        array $examinerScores // array of ['presentation' => 85, 'content' => 90, 'defense' => 88]
    ): array {
        $totalComposite = 0;
        $count = count($examinerScores);

        foreach ($examinerScores as $score) {
            // Weights: Presentation 30%, Content/Methodology 40%, Defense/Q&A 30%
            $examinerComposite = ($score['presentation'] * 0.30)
                               + ($score['content'] * 0.40)
                               + ($score['defense'] * 0.30);
            $totalComposite += $examinerComposite;
        }

        $finalScore = $count > 0 ? round($totalComposite / $count, 2) : 0.00;
        $passed = $finalScore >= 70.00; // Minimal B

        return [
            'final_defense_score' => $finalScore,
            'passed'              => $passed,
            'grade_letter'        => $finalScore >= 85 ? 'A' : ($finalScore >= 80 ? 'A-' : ($finalScore >= 75 ? 'B+' : ($finalScore >= 70 ? 'B' : 'C'))),
            'examiner_count'      => $count,
            'status'              => $passed ? 'LULUS_SIDANG' : 'MENGULANG_SIDANG'
        ];
    }

    /**
     * Verify Yudisium Graduation Clearance Checklist.
     */
    public static function verifyYudisiumClearance(
        bool $financeZeroBalance,
        bool $libraryFreeLoan,
        bool $repositoryPaperUploaded,
        bool $toeflThresholdPassed, // e.g. >= 500
        int $completedSks,
        int $requiredSks = 144
    ): array {
        $checklist = [
            'finance_clearance'    => ['name' => 'Bebas Tunggakan Keuangan (BAUK)', 'cleared' => $financeZeroBalance],
            'library_clearance'    => ['name' => 'Bebas Pinjam Perpustakaan (SLiMS)', 'cleared' => $libraryFreeLoan],
            'repository_clearance' => ['name' => 'Upload Naskah Publikasi / Jurnal', 'cleared' => $repositoryPaperUploaded],
            'toefl_clearance'      => ['name' => 'Sertifikat TOEFL UPH (>= 500)', 'cleared' => $toeflThresholdPassed],
            'sks_clearance'        => ['name' => "Beban SKS Kelulusan ({$completedSks}/{$requiredSks} SKS)", 'cleared' => $completedSks >= $requiredSks]
        ];

        $allCleared = true;
        foreach ($checklist as $item) {
            if (!$item['cleared']) {
                $allCleared = false;
                break;
            }
        }

        return [
            'all_cleared' => $allCleared,
            'checklist'   => $checklist,
            'status'      => $allCleared ? 'ELIGIBLE_FOR_YUDISIUM' : 'CLEARANCE_PENDING'
        ];
    }

    /**
     * Generate National Diploma Number (PIN - Penomoran Ijazah Nasional) & SKPI.
     */
    public static function generateNationalDiplomaData(
        string $nim,
        string $studentName,
        string $prodiPddiktiCode,
        float $ipk,
        array $skpiActivities
    ): array {
        // PIN Format Dikti: [KodeProdi][Tahun][Bulan][Sequence]
        $pin = sprintf('%s%s%s%s', $prodiPddiktiCode, date('Ym'), '01', substr(preg_replace('/\D/', '', $nim), -4));

        return [
            'nim'                     => $nim,
            'student_name'            => $studentName,
            'national_diploma_number' => $pin,
            'ijazah_issued_date'      => date('d F Y'),
            'ipk'                     => $ipk,
            'skpi_document_id'        => 'SKPI/UPH/' . date('Y') . '/' . substr(md5($nim), 0, 8),
            'skpi_activities'         => $skpiActivities,
            'verification_qr_url'     => "https://pddikti.kemdikbud.go.id/pin-verify/{$pin}"
        ];
    }
}
