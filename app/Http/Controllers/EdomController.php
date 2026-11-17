<?php

namespace App\Http\Controllers;

use App\Models\KrsItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EdomController extends Controller
{
    /**
     * Standardized 10 Likert-Scale Evaluation Questions for Teaching & Pedagogical Competence.
     */
    public static function getEvaluationQuestions(): array
    {
        return [
            1 => [
                'dimension' => 'Pedagogik & Kontrak Kuliah',
                'question' => 'Dosen menyampaikan Rencana Pembelajaran Semester (RPS), kontrak kuliah, dan kriteria penilaian secara jelas di awal semester.',
            ],
            2 => [
                'dimension' => 'Penguasaan Materi Kuliah',
                'question' => 'Dosen menguasai materi perkuliahan dengan mendalam serta mampu menyajikan konsep dengan runtut dan mudah dipahami.',
            ],
            3 => [
                'dimension' => 'Kedisiplinan & Pukul Belajar',
                'question' => 'Dosen hadir tepat waktu sesuai jadwal dan memenuhi kuota 16 kali pertemuan perkuliahan.',
            ],
            4 => [
                'dimension' => 'Interaktivitas & Diskusi',
                'question' => 'Dosen menciptakan suasana kelas yang kondusif, interaktif, dan memotivasi mahasiswa untuk berpikir kritis serta bertanya.',
            ],
            5 => [
                'dimension' => 'Pemanfaatan LMS & Media Digital',
                'question' => 'Dosen mengoptimalkan Learning Management System (LMS) dan sumber rujukan ilmiah mutakhir dalam proses pembelajaran.',
            ],
            6 => [
                'dimension' => 'Relevansi Tugas & Penilaian OBE',
                'question' => 'Tugas, kuis, UTS, dan UAS yang diberikan selaras dengan Capaian Pembelajaran Matakuliah (CPMK) yang telah ditetapkan.',
            ],
            7 => [
                'dimension' => 'Transparansi & Umpan Balik',
                'question' => 'Dosen memberikan umpan balik (feedback) atas tugas/evaluasi serta bersikap transparan dan objektif dalam penilaian.',
            ],
            8 => [
                'dimension' => 'Etika & Keteladanan Profesional',
                'question' => 'Dosen menunjukkan integritas moral, etika akademik, kesopanan, dan perlakuan yang adil tanpa diskriminasi kepada seluruh mahasiswa.',
            ],
            9 => [
                'dimension' => 'Aksesibilitas & Bimbingan',
                'question' => 'Dosen mudah dihubungi dan terbuka memberikan bimbingan konsultasi akademik di luar jam tatap muka kelas.',
            ],
            10 => [
                'dimension' => 'Evaluasi Kepuasan Menyeluruh',
                'question' => 'Secara umum, proses perkuliahan bersama dosen ini memberikan nilai tambah yang signifikan bagi perkembangan akademik dan profesional saya.',
            ],
        ];
    }

    /**
     * Display the EDOM Questionnaire for a specific KRS Item.
     */
    public function show(KrsItem $krsItem): View|RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        // Security check: Only enrolled student or superadmin may fill the survey
        if ($user->role === 'student' && (!$student || $krsItem->krsSubmission->student_id !== $student->id)) {
            abort(403, 'Akses ditolak: Anda tidak terdaftar pada matakuliah ini.');
        }

        // Check if already completed
        if ($krsItem->edom_completed) {
            return redirect()->route('khs.index')->with('info', 'Anda telah menyelesaikan pengisian EDOM untuk matakuliah ini.');
        }

        $krsItem->load([
            'classSection.course',
            'classSection.lecturer.user',
            'classSection.academicPeriod',
            'krsSubmission.student.user'
        ]);

        $questions = self::getEvaluationQuestions();

        return view('edom.survey', compact('krsItem', 'questions'));
    }

    /**
     * Submit EDOM Survey Responses and Unlock KHS Grades.
     */
    public function store(Request $request, KrsItem $krsItem): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if ($user->role === 'student' && (!$student || $krsItem->krsSubmission->student_id !== $student->id)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengisi evaluasi ini.');
        }

        $validated = $request->validate([
            'answers' => 'required|array|size:10',
            'answers.*' => 'required|integer|min:1|max:5',
            'feedback_positive' => 'nullable|string|max:1000',
            'feedback_constructive' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($krsItem, $student, $validated) {
            // Unlock EDOM flag on KRS Item
            $krsItem->edom_completed = true;
            $krsItem->save();

            // Record anonymized evaluation in audit trail
            $scoreAverage = round(array_sum($validated['answers']) / count($validated['answers']), 2);
            $krsItem->recordAudit(
                'EDOM_COMPLETED',
                "Student ID: {$student->nim} successfully submitted EDOM questionnaire. Average evaluation score: {$scoreAverage}/5.00."
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas partisipasi Anda dalam evaluasi dosen. Nilai matakuliah Anda kini telah terbuka.',
            ]);
        }

        return redirect()->route('khs.index')->with('success', 'Evaluasi dosen (EDOM) berhasil dikirim. Nilai matakuliah Anda telah terbuka pada Kartu Hasil Studi.');
    }
}
