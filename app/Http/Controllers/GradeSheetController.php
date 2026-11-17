<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradeSheetController extends Controller
{
    /**
     * Display Lecturer's Teaching Sections for Grading.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        $query = ClassSection::with(['course', 'academicPeriod', 'room', 'krsItems']);

        if ($lecturer && $user->role === 'lecturer') {
            $query->where('lecturer_id', $lecturer->id);
        }

        $classSections = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        return view('grading.index', compact('classSections'));
    }

    /**
     * Show Keyboard-Navigable Inline Grading Spreadsheet Grid.
     */
    public function show(ClassSection $classSection): View
    {
        $classSection->load([
            'course.curriculum',
            'lecturer.user',
            'academicPeriod',
            'room',
            'krsItems' => fn($q) => $q->orderBy('id'),
            'krsItems.krsSubmission.student.user'
        ]);

        $course = $classSection->course;
        $isLocked = $classSection->krsItems->where('is_locked', true)->count() > 0;

        return view('grading.spreadsheet', compact('classSection', 'course', 'isLocked'));
    }

    /**
     * Bulk Save Student Scores with Automated Real-Time OBE Weighted Calculation.
     */
    public function bulkSave(Request $request, ClassSection $classSection): JsonResponse
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        // Anti-IDOR Authorization: Lecturer must be assigned to this class section
        if ($user->role === 'lecturer') {
            if (!$lecturer || $classSection->lecturer_id !== $lecturer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak ditugaskan sebagai dosen pengampu untuk kelas ini.',
                ], 403);
            }
        } elseif (!in_array($user->role, ['superadmin', 'kaprodi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya dosen pengampu yang berwenang menginput nilai.',
            ], 403);
        }

        // Check if grades already locked
        $alreadyLocked = $classSection->krsItems()->where('is_locked', true)->exists();
        if ($alreadyLocked && $user->role !== 'superadmin' && $user->role !== 'kaprodi') {
            return response()->json([
                'success' => false,
                'message' => 'Nilai kelas kuliah ini telah dikunci (Locked) dan tidak dapat dimodifikasi tanpa izin Kaprodi.',
            ], 403);
        }

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.krs_item_id' => 'required|exists:krs_items,id',
            'grades.*.score_presence' => 'nullable|numeric|min:0|max:100',
            'grades.*.score_task' => 'nullable|numeric|min:0|max:100',
            'grades.*.score_uts' => 'nullable|numeric|min:0|max:100',
            'grades.*.score_uas' => 'nullable|numeric|min:0|max:100',
        ]);

        $course = $classSection->course;
        $wPresence = (float)$course->weight_presence;
        $wTask = (float)$course->weight_task;
        $wUts = (float)$course->weight_uts;
        $wUas = (float)$course->weight_uas;
        $totalWeight = $wPresence + $wTask + $wUts + $wUas ?: 100.0;

        $updatedRecords = [];

        DB::transaction(function () use ($validated, $totalWeight, $wPresence, $wTask, $wUts, $wUas, &$updatedRecords, $classSection) {
            foreach ($validated['grades'] as $g) {
                $item = KrsItem::findOrFail($g['krs_item_id']);

                $sPres = isset($g['score_presence']) ? (float)$g['score_presence'] : (float)$item->score_presence;
                $sTask = isset($g['score_task']) ? (float)$g['score_task'] : (float)$item->score_task;
                $sUts = isset($g['score_uts']) ? (float)$g['score_uts'] : (float)$item->score_uts;
                $sUas = isset($g['score_uas']) ? (float)$g['score_uas'] : (float)$item->score_uas;

                // Weighted Numeric Formula
                $finalNumeric = (
                    ($sPres * $wPresence) +
                    ($sTask * $wTask) +
                    ($sUts * $wUts) +
                    ($sUas * $wUas)
                ) / $totalWeight;

                $finalNumeric = round($finalNumeric, 2);

                // Convert to Indonesian Standard Letter Grade & Grade Point
                $conversion = KrsItem::convertToGradePoint($finalNumeric);

                $item->score_presence = $sPres;
                $item->score_task = $sTask;
                $item->score_uts = $sUts;
                $item->score_uas = $sUas;
                $item->final_numeric_score = $finalNumeric;
                $item->letter_grade = $conversion['letter'];
                $item->grade_point = $conversion['point'];
                $item->save();

                $updatedRecords[] = [
                    'krs_item_id' => $item->id,
                    'final_numeric_score' => $finalNumeric,
                    'letter_grade' => $conversion['letter'],
                    'grade_point' => $conversion['point'],
                ];
            }

            $classSection->recordAudit(
                'GRADES_BULK_SAVED',
                "Bulk saved scores for " . count($updatedRecords) . " students in {$classSection->course->code}"
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Seluruh nilai mahasiswa berhasil disimpan dan dikalkulasi otomatis.',
            'updated_items' => $updatedRecords,
        ]);
    }

    /**
     * Lock Class Section Grades (Immutable Ledger Freeze).
     */
    public function lockGrades(Request $request, ClassSection $classSection): JsonResponse
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        // Anti-IDOR Authorization: Lecturer must be assigned to this class section
        if ($user->role === 'lecturer') {
            if (!$lecturer || $classSection->lecturer_id !== $lecturer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak ditugaskan sebagai dosen pengampu untuk kelas ini.',
                ], 403);
            }
        } elseif (!in_array($user->role, ['superadmin', 'kaprodi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya dosen pengampu yang berwenang mengunci nilai.',
            ], 403);
        }

        $classSection->krsItems()->update(['is_locked' => true]);

        // Notify all enrolled students to complete EDOM
        $enrolledItems = $classSection->krsItems()->with('krsSubmission.student.user')->get();
        foreach ($enrolledItems as $item) {
            $studentUser = $item->krsSubmission?->student?->user;
            if ($studentUser) {
                \App\Services\NotificationService::notify(
                    $studentUser,
                    'edom_required',
                    "Pengisian EDOM: {$classSection->course->name_id}",
                    "Dosen telah mengunci nilai akhir matakuliah {$classSection->course->name_id}. Silakan isi kuesioner Evaluasi Dosen oleh Mahasiswa (EDOM) untuk membuka tampilan nilai pada KHS.",
                    "/portal/student/edom/{$classSection->id}"
                );
            }
        }

        $classSection->recordAudit(
            'GRADES_LOCKED',
            "Final grades officially locked for class section [{$classSection->course->code} - {$classSection->section_code}]"
        );

        return response()->json([
            'success' => true,
            'message' => 'Nilai kelas kuliah ini resmi dikunci dan disahkan ke dalam riwayat akademik.',
        ]);
    }

    /**
     * Export Spreadsheet Grading Roster to CSV.
     */
    public function exportCsv(ClassSection $classSection): Response
    {
        $classSection->load([
            'course',
            'krsItems.krsSubmission.student.user'
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"Nilai_{$classSection->course->code}_{$classSection->section_code}.csv\"",
        ];

        $callback = function () use ($classSection) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIM', 'Nama Mahasiswa', 'Presensi', 'Tugas', 'UTS', 'UAS', 'Nilai Akhir', 'Nilai Huruf', 'Bobot']);

            foreach ($classSection->krsItems as $item) {
                fputcsv($handle, [
                    $item->krsSubmission->student->nim,
                    $item->krsSubmission->student->user->full_name,
                    $item->score_presence,
                    $item->score_task,
                    $item->score_uts,
                    $item->score_uas,
                    $item->final_numeric_score ?? '0.00',
                    $item->letter_grade ?? '-',
                    $item->grade_point ?? '0.00',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
