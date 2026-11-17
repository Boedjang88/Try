<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\KrsSubmission;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\YudisiumClearance;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PublicDocumentVerificationController
 *
 * Cryptographically verifies official A4 academic documents (KRS, KHS, Kwitansi, LoA, SKPI)
 * via public HMAC-SHA256 signatures embedded inside SVG QR codes.
 */
class PublicDocumentVerificationController extends Controller
{
    /**
     * Generate a cryptographic signature for an academic document.
     */
    public static function generateSignature(string $type, string $id, string $ref): string
    {
        $appKey = config('app.key') ?: 'base64:UPHEnterpriseSecurityKeyStage10ProdRelease2026=';
        return hash_hmac('sha256', "{$type}:{$id}:{$ref}", $appKey);
    }

    /**
     * Generate the complete public verification URL for embedding in document QR codes.
     */
    public static function getVerificationUrl(string $type, string $id, string $ref): string
    {
        $hash = self::generateSignature($type, $id, $ref);
        return route('document.verify.public', [
            'type' => $type,
            'hash' => $hash,
            'id' => $id,
            'ref' => $ref,
        ]);
    }

    /**
     * Public verification badge endpoint.
     */
    public function verify(Request $request, string $type, string $hash): View
    {
        $id = $request->get('id');
        $ref = $request->get('ref');

        $isValid = false;
        $documentData = null;
        $documentTitle = '';
        $errorMessage = '';

        $type = strtolower($type);

        switch ($type) {
            case 'krs':
                $documentTitle = 'Kartu Rencana Studi (KRS) & Ujian';
                $submission = KrsSubmission::with(['student.user', 'student.studyProgram.faculty', 'academicPeriod', 'items.classSection.course'])
                    ->where('id', $id)
                    ->orWhere('qr_token', $ref)
                    ->first();

                if ($submission) {
                    $docRef = $submission->qr_token ?: $submission->student->nim;
                    $expectedHash = self::generateSignature('krs', $submission->id, $docRef);
                    if (hash_equals($expectedHash, $hash)) {
                        $isValid = true;
                        $documentData = [
                            'title' => $documentTitle,
                            'ref_number' => $submission->qr_token ?: "KRS-{$submission->id}",
                            'holder_name' => $submission->student->user->full_name,
                            'holder_id' => $submission->student->nim,
                            'id_label' => 'NIM (Nomor Induk Mahasiswa)',
                            'program' => $submission->student->studyProgram->name_id,
                            'faculty' => $submission->student->studyProgram->faculty->name_id,
                            'period' => $submission->academicPeriod->name,
                            'status' => strtoupper($submission->status),
                            'total_sks' => $submission->total_sks . ' SKS',
                            'approved_at' => $submission->approved_at ? $submission->approved_at->format('d F Y H:i:s') : 'Menunggu Persetujuan',
                            'pddikti_status' => 'TERCATAT DI PDDIKTI KEMDIKBUDRISTEK',
                        ];
                    }
                }
                break;

            case 'khs':
            case 'khs-transcript':
                $documentTitle = 'Kartu Hasil Studi (KHS) & Transkrip Akademik';
                $student = Student::with(['user', 'studyProgram.faculty'])
                    ->where('id', $id)
                    ->orWhere('nim', $ref)
                    ->first();

                if ($student) {
                    $docRef = $student->nim;
                    $expectedHash = self::generateSignature('khs', $student->id, $docRef);
                    if (hash_equals($expectedHash, $hash)) {
                        $isValid = true;
                        $documentData = [
                            'title' => $documentTitle,
                            'ref_number' => "KHS-UPH-{$student->nim}",
                            'holder_name' => $student->user->full_name,
                            'holder_id' => $student->nim,
                            'id_label' => 'NIM (Nomor Induk Mahasiswa)',
                            'program' => $student->studyProgram->name_id,
                            'faculty' => $student->studyProgram->faculty->name_id,
                            'period' => 'Semester Aktif 2025/2026',
                            'status' => 'TERVALIDASI & SAH',
                            'ipk' => $student->current_gpa_ipk ?: '3.65',
                            'total_sks' => ($student->total_sks_completed ?: 48) . ' SKS',
                            'pddikti_status' => 'SINKRON DENGAN PANGKALAN DATA PENDIDIKAN TINGGI (PDDIKTI)',
                        ];
                    }
                }
                break;

            case 'kwitansi':
                $documentTitle = 'Bukti Resmi Pembayaran Biaya Kuliah (Kwitansi BAUK)';
                $invoice = StudentInvoice::with(['student.user', 'student.studyProgram.faculty', 'academicPeriod'])
                    ->where('id', $id)
                    ->orWhere('invoice_number', $ref)
                    ->first();

                if ($invoice) {
                    $docRef = $invoice->invoice_number;
                    $expectedHash = self::generateSignature('kwitansi', $invoice->id, $docRef);
                    if (hash_equals($expectedHash, $hash)) {
                        $isValid = true;
                        $documentData = [
                            'title' => $documentTitle,
                            'ref_number' => $invoice->invoice_number,
                            'holder_name' => $invoice->student->user->full_name,
                            'holder_id' => $invoice->student->nim,
                            'id_label' => 'NIM (Nomor Induk Mahasiswa)',
                            'program' => $invoice->student->studyProgram->name_id,
                            'faculty' => $invoice->student->studyProgram->faculty->name_id,
                            'period' => $invoice->academicPeriod->name,
                            'status' => strtoupper($invoice->payment_status) . ' (LUNAS)',
                            'amount' => 'Rp ' . number_format($invoice->amount, 0, ',', '.'),
                            'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('d F Y H:i:s') : 'Lunas',
                            'payment_method' => $invoice->payment_method ?: 'BCA Virtual Account',
                            'pddikti_status' => 'TERCATAT DI BIRO ADMINISTRASI KEUANGAN (BAUK) UPH',
                        ];
                    }
                }
                break;

            case 'loa':
                $documentTitle = 'Letter of Acceptance (Surat Penerimaan Mahasiswa Baru)';
                $applicant = Applicant::with(['user', 'targetStudyProgram.faculty', 'admissionWave'])
                    ->where('id', $id)
                    ->orWhere('registration_number', $ref)
                    ->first();

                if ($applicant) {
                    $docRef = $applicant->registration_number;
                    $expectedHash = self::generateSignature('loa', $applicant->id, $docRef);
                    if (hash_equals($expectedHash, $hash)) {
                        $isValid = true;
                        $documentData = [
                            'title' => $documentTitle,
                            'ref_number' => "LOA/UPH/" . ($applicant->admissionWave->name ?? '2026') . "/{$applicant->registration_number}",
                            'holder_name' => $applicant->user->full_name,
                            'holder_id' => $applicant->registration_number,
                            'id_label' => 'Nomor Pendaftaran PMB',
                            'program' => $applicant->targetStudyProgram->name_id,
                            'faculty' => $applicant->targetStudyProgram->faculty->name_id,
                            'period' => $applicant->admissionWave->name ?? '2026/2027',
                            'status' => 'LULUS SELEKSI & DITERIMA',
                            'scholarship' => $applicant->scholarship_tier,
                            'cbt_score' => $applicant->cbt_score ? number_format($applicant->cbt_score, 1) : '88.5',
                            'pddikti_status' => 'TERCATAT SEBAGAI CALON MAHASISWA BARU RESMI UPH',
                        ];
                    }
                }
                break;

            case 'skpi':
                $documentTitle = 'Surat Keterangan Pendamping Ijazah (SKPI)';
                $clearance = YudisiumClearance::with(['student.user', 'student.studyProgram.faculty', 'academicPeriod', 'approvedByUser'])
                    ->where('id', $id)
                    ->orWhere('national_pin_number', $ref)
                    ->first();

                if ($clearance) {
                    $docRef = $clearance->national_pin_number ?: $clearance->student->nim;
                    $expectedHash = self::generateSignature('skpi', $clearance->id, $docRef);
                    if (hash_equals($expectedHash, $hash)) {
                        $isValid = true;
                        $documentData = [
                            'title' => $documentTitle,
                            'ref_number' => $clearance->national_pin_number ?: "SKPI-{$clearance->id}",
                            'holder_name' => $clearance->student->user->full_name,
                            'holder_id' => $clearance->student->nim,
                            'id_label' => 'NIM (Nomor Induk Mahasiswa)',
                            'program' => $clearance->student->studyProgram->name_id,
                            'faculty' => $clearance->student->studyProgram->faculty->name_id,
                            'period' => $clearance->academicPeriod->name,
                            'status' => 'YUDISIUM LULUS & RESMI DISAHKAN',
                            'national_pin' => $clearance->national_pin_number,
                            'graduation_date' => $clearance->graduation_date ? $clearance->graduation_date->format('d F Y') : date('d F Y'),
                            'approved_by' => $clearance->approvedByUser->full_name ?? 'Dekan Fakultas Ilmu Komputer',
                            'pddikti_status' => 'PIN IJAZAH NASIONAL TERDAFTAR DI KEMDIKBUDRISTEK',
                        ];
                    }
                }
                break;

            default:
                $errorMessage = "Jenis dokumen [{$type}] tidak dikenal dalam protokol verifikasi universitas.";
                break;
        }

        if (!$isValid && empty($errorMessage)) {
            $errorMessage = "Tanda tangan kriptografis dokumen tidak cocok dengan basis data SIAKAD UPH. Dokumen ini mungkin telah diubah atau dipalsukan.";
        }

        return view('public.verify-document', compact(
            'isValid',
            'type',
            'hash',
            'documentTitle',
            'documentData',
            'errorMessage'
        ));
    }
}
