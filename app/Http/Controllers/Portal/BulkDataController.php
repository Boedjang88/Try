<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkDataController extends Controller
{
    /**
     * Display BAAK Bulk Data Center.
     */
    public function baakIndex(): View
    {
        return view('portal.baak.bulk-data', [
            'portalRole' => 'baak',
            'title' => 'BAAK Bulk Data Command Center'
        ]);
    }

    /**
     * Display BAUK Bulk Data Center.
     */
    public function baukIndex(): View
    {
        return view('portal.baak.bulk-data', [
            'portalRole' => 'bauk',
            'title' => 'BAUK Bulk Financial Data Center'
        ]);
    }

    /**
     * Streaming CSV Export: Master Student Active Status & GPA Recap.
     */
    public function exportStudents(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-students-' . date('Ymd-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'NIM',
                'Nama Lengkap',
                'Program Studi',
                'Fakultas',
                'Angkatan',
                'Semester Berjalan',
                'Status Akademik',
                'IPK Kumulatif',
                'Total SKS Lulus',
                'Bebas Keuangan',
            ]);

            Student::with(['user', 'studyProgram.faculty'])
                ->chunk(200, function ($students) use ($handle) {
                    foreach ($students as $s) {
                        fputcsv($handle, [
                            $s->nim,
                            $s->user->full_name ?? '-',
                            $s->studyProgram->name ?? '-',
                            $s->studyProgram->faculty->name ?? '-',
                            $s->entry_year,
                            $s->current_semester,
                            strtoupper($s->academic_status),
                            number_format((float)$s->current_gpa_ipk, 2),
                            $s->total_sks_completed,
                            $s->is_financially_cleared ? 'YA' : 'TIDAK',
                        ]);
                    }
                });

            fclose($handle);
        }, 'export-students-' . date('Ymd') . '.csv', $headers);
    }

    /**
     * Streaming CSV Export: Class Section Timetables & Quotas.
     */
    public function exportSchedules(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-schedules-' . date('Ymd') . '.csv"',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Kode MK',
                'Nama Mata Kuliah',
                'SKS',
                'Kelas',
                'Dosen Pengampu',
                'Hari',
                'Waktu Mulai',
                'Waktu Selesai',
                'Ruangan',
                'Kapasitas',
                'Terisi',
            ]);

            ClassSection::with(['course', 'lecturer.user'])
                ->chunk(200, function ($sections) use ($handle) {
                    foreach ($sections as $cs) {
                        fputcsv($handle, [
                            $cs->course->code ?? '-',
                            $cs->course->name ?? '-',
                            $cs->course->sks ?? 0,
                            $cs->name,
                            $cs->lecturer?->user?->full_name ?? 'TBA',
                            $cs->day_of_week ?? '-',
                            $cs->start_time ?? '-',
                            $cs->end_time ?? '-',
                            $cs->room ?? '-',
                            $cs->capacity,
                            $cs->enrolled_count ?? 0,
                        ]);
                    }
                });

            fclose($handle);
        }, 'export-schedules-' . date('Ymd') . '.csv', $headers);
    }

    /**
     * Streaming CSV Export: Semester Tuition Billing & VA Reconciliation.
     */
    public function exportInvoices(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-invoices-' . date('Ymd') . '.csv"',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Nomor Invoice',
                'NIM',
                'Nama Mahasiswa',
                'Nomor Virtual Account',
                'Jenis Tagihan',
                'Total Nominal (IDR)',
                'Nominal Terbayar (IDR)',
                'Status Pembayaran',
                'Tanggal Jatuh Tempo',
                'Tanggal Pelunasan',
            ]);

            StudentInvoice::with(['student.user'])
                ->chunk(200, function ($invoices) use ($handle) {
                    foreach ($invoices as $inv) {
                        fputcsv($handle, [
                            $inv->invoice_number,
                            $inv->student?->nim ?? '-',
                            $inv->student?->user?->full_name ?? '-',
                            $inv->virtual_account_number ?? '-',
                            $inv->invoice_type ?? 'UKT Regular',
                            $inv->total_amount,
                            $inv->paid_amount ?? 0,
                            strtoupper($inv->payment_status),
                            $inv->due_date ? $inv->due_date->format('Y-m-d') : '-',
                            $inv->paid_at ? $inv->paid_at->format('Y-m-d H:i:s') : '-',
                        ]);
                    }
                });

            fclose($handle);
        }, 'export-invoices-' . date('Ymd') . '.csv', $headers);
    }

    /**
     * Streaming CSV Export: Lecturer Grade Sheet Template.
     */
    public function exportGradeTemplate(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-grade-template.csv"',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'krs_item_id',
                'nim',
                'nama_mahasiswa',
                'kode_mk',
                'kehadiran_10pct',
                'tugas_20pct',
                'kuis_10pct',
                'uts_25pct',
                'uas_25pct',
                'proyek_10pct',
            ]);

            // Sample rows for template
            KrsItem::with(['krsSubmission.student.user', 'classSection.course'])
                ->take(10)
                ->get()
                ->each(function ($item) use ($handle) {
                    fputcsv($handle, [
                        $item->id,
                        $item->krsSubmission?->student?->nim ?? 'NIM',
                        $item->krsSubmission?->student?->user?->full_name ?? 'Nama',
                        $item->classSection?->course?->code ?? 'MK01',
                        $item->score_presence ?? 85,
                        $item->score_task ?? 80,
                        $item->score_quiz ?? 80,
                        $item->score_uts ?? 85,
                        $item->score_uas ?? 85,
                        $item->score_project ?? 90,
                    ]);
                });

            fclose($handle);
        }, 'export-grade-template.csv', $headers);
    }

    /**
     * Bulk Data Dry-Run Validation (Preview without committing).
     */
    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'import_type' => 'required|in:grades,invoices,students',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $validRows = [];
        $invalidRows = [];
        $rowNumber = 1;

        $type = $request->input('import_type');

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (empty(array_filter($row))) continue;

            if ($type === 'grades') {
                $krsItemId = trim($row[0] ?? '');
                $presence = isset($row[4]) ? (float)$row[4] : null;
                $task = isset($row[5]) ? (float)$row[5] : null;
                $quiz = isset($row[6]) ? (float)$row[6] : null;
                $uts = isset($row[7]) ? (float)$row[7] : null;
                $uas = isset($row[8]) ? (float)$row[8] : null;
                $project = isset($row[9]) ? (float)$row[9] : null;

                $item = KrsItem::find($krsItemId);

                if (!$item) {
                    $invalidRows[] = [
                        'row' => $rowNumber,
                        'data' => $row,
                        'reason' => 'KRS Item ID tidak ditemukan dalam database.',
                    ];
                } elseif ($presence < 0 || $presence > 100 || $uts < 0 || $uts > 100) {
                    $invalidRows[] = [
                        'row' => $rowNumber,
                        'data' => $row,
                        'reason' => 'Skor nilai harus berada dalam rentang 0 - 100.',
                    ];
                } else {
                    $validRows[] = [
                        'row' => $rowNumber,
                        'krs_item_id' => $krsItemId,
                        'student_name' => $item->krsSubmission?->student?->user?->full_name ?? 'Mahasiswa',
                        'scores' => [
                            'presence' => $presence,
                            'task' => $task,
                            'quiz' => $quiz,
                            'uts' => $uts,
                            'uas' => $uas,
                            'project' => $project,
                        ],
                    ];
                }
            } elseif ($type === 'invoices') {
                $invoiceNum = trim($row[0] ?? '');
                $invoice = StudentInvoice::where('invoice_number', $invoiceNum)->first();

                if (!$invoice) {
                    $invalidRows[] = [
                        'row' => $rowNumber,
                        'data' => $row,
                        'reason' => "Invoice $invoiceNum tidak ditemukan.",
                    ];
                } else {
                    $validRows[] = [
                        'row' => $rowNumber,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoiceNum,
                        'amount' => (float)($row[5] ?? $invoice->total_amount),
                    ];
                }
            }
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'total_rows' => count($validRows) + count($invalidRows),
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
            'valid_rows' => array_slice($validRows, 0, 50),
            'invalid_rows' => $invalidRows,
        ]);
    }

    /**
     * Atomically Commit Validated Bulk Data via DB Transaction.
     */
    public function commitImport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'import_type' => 'required|in:grades,invoices',
            'items' => 'required|array|min:1',
        ]);

        $type = $validated['import_type'];
        $items = $validated['items'];
        $processedCount = 0;

        DB::beginTransaction();
        try {
            if ($type === 'grades') {
                foreach ($items as $entry) {
                    $krsItem = KrsItem::lockForUpdate()->find($entry['krs_item_id']);
                    if (!$krsItem) continue;

                    $scores = $entry['scores'] ?? [];
                    $p = (float)($scores['presence'] ?? 80);
                    $t = (float)($scores['task'] ?? 80);
                    $q = (float)($scores['quiz'] ?? 80);
                    $uts = (float)($scores['uts'] ?? 80);
                    $uas = (float)($scores['uas'] ?? 80);
                    $proj = (float)($scores['project'] ?? 80);

                    $finalNumeric = round(
                        ($p * 0.1) + ($t * 0.2) + ($q * 0.1) + ($uts * 0.25) + ($uas * 0.25) + ($proj * 0.1),
                        2
                    );

                    $letter = 'E';
                    $point = 0.00;
                    if ($finalNumeric >= 85) { $letter = 'A'; $point = 4.00; }
                    elseif ($finalNumeric >= 80) { $letter = 'A-'; $point = 3.75; }
                    elseif ($finalNumeric >= 75) { $letter = 'B+'; $point = 3.50; }
                    elseif ($finalNumeric >= 70) { $letter = 'B'; $point = 3.00; }
                    elseif ($finalNumeric >= 65) { $letter = 'B-'; $point = 2.75; }
                    elseif ($finalNumeric >= 60) { $letter = 'C+'; $point = 2.50; }
                    elseif ($finalNumeric >= 55) { $letter = 'C'; $point = 2.00; }
                    elseif ($finalNumeric >= 40) { $letter = 'D'; $point = 1.00; }

                    $krsItem->score_presence = $p;
                    $krsItem->score_task = $t;
                    $krsItem->score_quiz = $q;
                    $krsItem->score_uts = $uts;
                    $krsItem->score_uas = $uas;
                    $krsItem->score_project = $proj;
                    $krsItem->final_numeric_score = $finalNumeric;
                    $krsItem->letter_grade = $letter;
                    $krsItem->grade_point = $point;
                    $krsItem->save();

                    // Recalculate student GPA
                    $student = $krsItem->krsSubmission?->student;
                    if ($student) {
                        $student->recalculateCumulativeGpa();
                    }

                    $processedCount++;
                }
            } elseif ($type === 'invoices') {
                foreach ($items as $entry) {
                    $invoice = StudentInvoice::lockForUpdate()->find($entry['invoice_id']);
                    if (!$invoice) continue;

                    $invoice->payment_status = 'paid';
                    $invoice->paid_amount = $invoice->total_amount;
                    $invoice->paid_at = now();
                    $invoice->save();

                    $student = $invoice->student;
                    if ($student) {
                        $unpaid = StudentInvoice::where('student_id', $student->id)
                            ->where('payment_status', '!=', 'paid')
                            ->count();
                        if ($unpaid === 0) {
                            $student->is_financially_cleared = true;
                            $student->save();
                        }
                    }

                    $processedCount++;
                }
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'BULK_IMPORT_COMMIT',
                'auditable_type' => 'BulkData',
                'auditable_id' => Auth::id(),
                'old_values' => null,
                'new_values' => ['type' => $type, 'count' => $processedCount],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses $processedCount data secara atomik.",
                'processed_count' => $processedCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
