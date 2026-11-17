<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicLeaveRequest;
use App\Models\Applicant;
use App\Models\GradeAppeal;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\MbkmProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStorageController extends Controller
{
    /**
     * Generate a temporary signed download URL for private documents (30 mins validity).
     */
    public static function createSignedDownloadUrl(string $path, int $minutes = 30): string
    {
        return URL::temporarySignedRoute(
            'portal.storage.signed-download',
            now()->addMinutes($minutes),
            ['path' => urlencode($path)]
        );
    }

    /**
     * Download Private Document Protected by Cryptographic Signed URL.
     */
    public function downloadSigned(Request $request): StreamedResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Akses Ditolak: Tautan unduh telah kedaluwarsa atau tanda tangan kriptografis tidak sah.');
        }

        $encodedPath = $request->query('path');
        if (!$encodedPath) {
            abort(404, 'Path dokumen tidak valid.');
        }

        $path = urldecode($encodedPath);

        // Security check against directory traversal
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(400, 'Akses file tidak diizinkan (Path Traversal Detected).');
        }

        // Search in local or public disks
        $disk = Storage::disk('local')->exists($path) ? 'local' : (Storage::disk('public')->exists($path) ? 'public' : null);

        if (!$disk) {
            abort(404, 'Dokumen privat tidak ditemukan pada repositori server.');
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Download LMS Assignment Attachment.
     */
    public function downloadAssignment(LmsAssignment $assignment): StreamedResponse
    {
        $path = $assignment->attachment_path;
        $disk = $path && Storage::disk('public')->exists($path) ? 'public' : ($path && Storage::disk('local')->exists($path) ? 'local' : null);

        if (!$disk) {
            abort(404, 'Lampiran materi atau tugas kuliah tidak ditemukan pada repositori server.');
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Download Student Assignment Submission File.
     */
    public function downloadSubmission(LmsSubmission $submission): StreamedResponse
    {
        $user = Auth::user();

        // Access check: Student owner, course lecturer, or admin
        if ($user && $user->role === 'student' && $user->student && $submission->student_id !== $user->student->id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengunduh tugas mahasiswa lain.');
        }

        $path = $submission->submission_file_path;
        $cleanPath = str_replace('/storage/', '', $path);

        $disk = Storage::disk('public')->exists($cleanPath) ? 'public' : (Storage::disk('local')->exists($cleanPath) ? 'local' : null);

        if (!$disk) {
            abort(404, 'File lembar jawaban tugas tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk($disk)->download($cleanPath);
    }

    /**
     * Download PMB Applicant Document (KTP Identity or Certificate/Rapor).
     */
    public function downloadPmbDocument(Applicant $applicant, string $type): StreamedResponse
    {
        $user = Auth::user();

        if ($user && $user->role === 'applicant' && $applicant->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $path = $type === 'identity' ? $applicant->document_identity_path : $applicant->document_certificate_path;

        $disk = $path && Storage::disk('public')->exists($path) ? 'public' : ($path && Storage::disk('local')->exists($path) ? 'local' : null);

        if (!$disk) {
            abort(404, 'Dokumen pendaftaran calon mahasiswa tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Download Academic Leave Supporting Document.
     */
    public function downloadLeaveDocument(AcademicLeaveRequest $leaveRequest): StreamedResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'student' && $user->student && $leaveRequest->student_id !== $user->student->id) {
            abort(403, 'Akses ditolak.');
        }

        $path = $leaveRequest->document_path;
        $disk = $path && Storage::disk('public')->exists($path) ? 'public' : ($path && Storage::disk('local')->exists($path) ? 'local' : null);

        if (!$disk) {
            abort(404, 'Dokumen pendukung cuti akademik tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Download MBKM Logbook Document.
     */
    public function downloadMbkmDocument(MbkmProgram $mbkmProgram): StreamedResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'student' && $user->student && $mbkmProgram->student_id !== $user->student->id) {
            abort(403, 'Akses ditolak.');
        }

        $path = $mbkmProgram->logbook_document_path;
        $disk = $path && Storage::disk('local')->exists($path) ? 'local' : ($path && Storage::disk('public')->exists($path) ? 'public' : null);

        if (!$disk) {
            abort(404, 'Dokumen logbook MBKM tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Download Grade Appeal Evidence Document.
     */
    public function downloadAppealEvidence(GradeAppeal $gradeAppeal): StreamedResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'student' && $user->student && $gradeAppeal->student_id !== $user->student->id) {
            abort(403, 'Akses ditolak.');
        }

        $path = $gradeAppeal->evidence_file_path;
        $disk = $path && Storage::disk('local')->exists($path) ? 'local' : ($path && Storage::disk('public')->exists($path) ? 'public' : null);

        if (!$disk) {
            abort(404, 'Bukti sanggah nilai tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path);
    }
}
