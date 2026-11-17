<?php

namespace App\Http\Controllers;

use App\Actions\GenerateNimAction;
use App\Models\Applicant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CbtExamController extends Controller
{
    /**
     * CBT Questions Bank (Math, Logic, English, Analytical).
     */
    protected static array $questionBank = [
        [
            'id' => 1,
            'category' => 'Logika & Matematika',
            'question' => 'Jika deret bilangan adalah 3, 7, 15, 31, 63, ..., berapakah angka berikutnya?',
            'options' => [
                'A' => '125',
                'B' => '127',
                'C' => '129',
                'D' => '131'
            ],
            'correct' => 'B'
        ],
        [
            'id' => 2,
            'category' => 'Logika & Matematika',
            'question' => 'Sebuah server memproses 120 request per detik. Jika kapasitas ditambah 25%, berapa request yang diproses dalam 1 menit?',
            'options' => [
                'A' => '7.500 request',
                'B' => '8.000 request',
                'C' => '9.000 request',
                'D' => '10.500 request'
            ],
            'correct' => 'C'
        ],
        [
            'id' => 3,
            'category' => 'Bahasa Inggris Akademik',
            'question' => 'Select the most appropriate academic word to complete: "The findings of the research _____ the initial hypothesis formulated by the committee."',
            'options' => [
                'A' => 'substantiate',
                'B' => 'deteriorate',
                'C' => 'procrastinate',
                'D' => 'alienate'
            ],
            'correct' => 'A'
        ],
        [
            'id' => 4,
            'category' => 'Bahasa Inggris Akademik',
            'question' => '"Despite the economic downturn, the institution maintained its _____ to educational excellence and student welfare."',
            'options' => [
                'A' => 'compromise',
                'B' => 'commitment',
                'C' => 'negligence',
                'D' => 'reluctance'
            ],
            'correct' => 'B'
        ],
        [
            'id' => 5,
            'category' => 'Penalaran Analitis & Karakter',
            'question' => 'Semua inovator berkarakter tekun. Sebagian mahasiswa UPH adalah inovator. Kesimpulan yang sah adalah:',
            'options' => [
                'A' => 'Semua mahasiswa UPH berkarakter tekun',
                'B' => 'Sebagian mahasiswa UPH berkarakter tekun',
                'C' => 'Tidak ada mahasiswa UPH yang tidak tekun',
                'D' => 'Semua yang berkarakter tekun adalah mahasiswa UPH'
            ],
            'correct' => 'B'
        ],
        [
            'id' => 6,
            'category' => 'Penalaran Analitis & Karakter',
            'question' => 'Dalam prinsip Servant Leadership, prioritas utama seorang pemimpin adalah:',
            'options' => [
                'A' => 'Mempertahankan kekuasaan dan hierarki organisasi',
                'B' => 'Melayani kebutuhan dan pertumbuhan orang lain demi kebaikan bersama',
                'C' => 'Mencapai keuntungan finansial tanpa memperhatikan etika kerja',
                'D' => 'Menghindari seluruh bentuk tanggung jawab kolektif'
            ],
            'correct' => 'B'
        ],
        [
            'id' => 7,
            'category' => 'Logika & Matematika',
            'question' => 'Nilai dari ekspresi logika: (TRUE AND FALSE) OR (NOT FALSE AND TRUE) adalah:',
            'options' => [
                'A' => 'TRUE',
                'B' => 'FALSE',
                'C' => 'NULL',
                'D' => 'UNDEFINED'
            ],
            'correct' => 'A'
        ],
        [
            'id' => 8,
            'category' => 'Logika & Matematika',
            'question' => 'Jika x + 2y = 10 dan 3x - y = 9, maka nilai dari x * y adalah:',
            'options' => [
                'A' => '8',
                'B' => '12',
                'C' => '15',
                'D' => '18'
            ],
            'correct' => 'B'
        ],
        [
            'id' => 9,
            'category' => 'Bahasa Inggris Akademik',
            'question' => 'Choose the synonym for "PRESTIGIOUS":',
            'options' => [
                'A' => 'Reputable',
                'B' => 'Mediocre',
                'C' => 'Obscure',
                'D' => 'Precarious'
            ],
            'correct' => 'A'
        ],
        [
            'id' => 10,
            'category' => 'Penalaran Analitis & Karakter',
            'question' => 'Nilai inti UPH mencakup Faith in Christ, Knowledge, and Godly Character. Manifestasi Godly Character dalam kehidupan akademik adalah:',
            'options' => [
                'A' => 'Integritas akademik mutlak dan pantang melakukan plagiarisme',
                'B' => 'Mengejar nilai tinggi dengan menghalalkan segala cara',
                'C' => 'Mengisolasi diri dari kegiatan pengabdian masyarakat',
                'D' => 'Menyerahkan tugas setelah tenggat waktu terlewati'
            ],
            'correct' => 'A'
        ]
    ];

    /**
     * Show Online CBT Examination Workspace.
     */
    public function show(?Applicant $applicant = null): View|RedirectResponse
    {
        if (!$applicant || !$applicant->exists) {
            $applicant = Applicant::where('user_id', Auth::id())
                ->with('targetStudyProgram')
                ->latest()
                ->first();

            if (!$applicant) {
                $applicant = Applicant::with('targetStudyProgram')->first();
            }
        }

        if (!$applicant) {
            return redirect('/portal/pmb/dashboard')->with('error', 'Data pendaftaran calon mahasiswa tidak ditemukan.');
        }

        if ($applicant->payment_status !== 'paid') {
            return redirect('/portal/pmb/dashboard')->with('error', 'Silakan selesaikan pembayaran formulir pendaftaran untuk membuka sesi Ujian CBT.');
        }

        if ($applicant->cbt_score !== null) {
            return redirect('/portal/pmb/dashboard')->with('info', 'Anda telah menyelesaikan ujian CBT. Skor dan Letter of Acceptance (LoA) tersedia pada ringkasan pendaftaran.');
        }

        $questions = self::$questionBank;
        $durationSeconds = 1800; // 30 minutes for 10 high-impact questions

        return view('pmb.cbt-exam', compact('applicant', 'questions', 'durationSeconds'));
    }

    /**
     * Submit and Auto-Grade CBT Exam.
     */
    public function submit(Request $request, ?Applicant $applicant = null, ?GenerateNimAction $generateNimAction = null): JsonResponse
    {
        if (!$applicant || !$applicant->exists) {
            $applicant = Applicant::where('user_id', Auth::id())->latest()->first();
            if (!$applicant) {
                $applicant = Applicant::firstOrFail();
            }
        }

        if ($applicant->cbt_score !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian ini sudah pernah disubmit sebelumnya.',
            ], 422);
        }

        $submittedAnswers = $request->input('answers', []);
        $totalQuestions = count(self::$questionBank);
        $correctCount = 0;

        foreach (self::$questionBank as $q) {
            $qId = (string)$q['id'];
            if (isset($submittedAnswers[$qId]) && strtoupper($submittedAnswers[$qId]) === $q['correct']) {
                $correctCount++;
            }
        }

        $score = round(($correctCount / $totalQuestions) * 100, 2);

        // Determine Scholarship Tier
        $tier = 'Regular (0%)';
        if ($score >= 90.0) {
            $tier = 'Tier 1 (100%)';
        } elseif ($score >= 80.0) {
            $tier = 'Tier 2 (75%)';
        } elseif ($score >= 70.0) {
            $tier = 'Tier 3 (50%)';
        } elseif ($score >= 60.0) {
            $tier = 'Tier 4 (25%)';
        }

        $applicant->cbt_score = $score;
        $applicant->scholarship_tier = $tier;
        $applicant->admission_status = $score >= 50.0 ? 'passed' : 'rejected';
        $applicant->save();

        $applicant->recordAudit('CBT_EXAM_SUBMITTED', "CBT Exam scored {$score}/100 with scholarship tier [{$tier}]");

        // Automatically promote to active Student NIM if passed
        $generatedNim = null;
        if ($applicant->admission_status === 'passed') {
            $student = $generateNimAction->execute($applicant);
            $generatedNim = $student->nim;
        }

        return response()->json([
            'success' => true,
            'score' => $score,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
            'scholarship_tier' => $tier,
            'status' => $applicant->admission_status,
            'generated_nim' => $generatedNim,
            'message' => $score >= 50.0
                ? "Selamat! Anda dinyatakan LULUS dengan skor {$score} dan mendapatkan beasiswa {$tier}. NIM resmi Anda: {$generatedNim}."
                : "Ujian telah selesai. Skor Anda {$score}. Terima kasih atas partisipasi Anda.",
            'redirect_url' => '/pmb/status',
        ]);
    }

    /**
     * Display CBT Exam Result.
     */
    public function result(Request $request, ?Applicant $applicant = null): View
    {
        if (!$applicant || !$applicant->exists) {
            $applicant = Applicant::where('user_id', Auth::id())->latest()->first();
            if (!$applicant) {
                $applicant = Applicant::firstOrFail();
            }
        }

        return view('pmb.status', compact('applicant'));
    }
}
