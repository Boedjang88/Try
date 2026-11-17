<?php

namespace App\Policies;

use App\Models\Applicant;
use App\Models\KrsSubmission;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\User;
use App\Models\YudisiumClearance;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrintDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can print/view Kartu Rencana Studi (KRS).
     */
    public function viewKrs(User $user, KrsSubmission $submission): bool
    {
        if (in_array($user->role, ['superadmin', 'baak', 'rector', 'dean'])) {
            return true;
        }

        if ($user->role === 'student' && $user->student) {
            return $user->student->id === $submission->student_id;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            return $submission->student && $submission->student->academic_advisor_id === $user->lecturer->id;
        }

        if ($user->role === 'kaprodi' && $user->study_program_id) {
            return $submission->student && $submission->student->study_program_id === $user->study_program_id;
        }

        return false;
    }

    /**
     * Determine whether the user can print/view Kartu Hasil Studi (KHS) / Transcript.
     * Enforces ZERO IDOR vulnerability for student users.
     */
    public function viewKhs(User $user, Student $targetStudent): bool
    {
        if (in_array($user->role, ['superadmin', 'baak', 'rector', 'dean'])) {
            return true;
        }

        if ($user->role === 'student') {
            return $user->student && $user->student->id === $targetStudent->id;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            return $targetStudent->academic_advisor_id === $user->lecturer->id;
        }

        if ($user->role === 'kaprodi' && $user->study_program_id) {
            return $targetStudent->study_program_id === $user->study_program_id;
        }

        return false;
    }

    /**
     * Determine whether the user can print/view Kwitansi / Payment Receipt.
     */
    public function viewKwitansi(User $user, StudentInvoice $invoice): bool
    {
        if (in_array($user->role, ['superadmin', 'bauk', 'baak', 'rector'])) {
            return true;
        }

        if ($user->role === 'student') {
            return $user->student && $user->student->id === $invoice->student_id;
        }

        return false;
    }

    /**
     * Determine whether the user can print/view Letter of Acceptance (LoA).
     */
    public function viewLoa(User $user, Applicant $applicant): bool
    {
        if (in_array($user->role, ['superadmin', 'baak', 'rector', 'dean'])) {
            return true;
        }

        if ($user->role === 'applicant') {
            return $applicant->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can print/view Surat Keterangan Pendamping Ijazah (SKPI).
     */
    public function viewSkpi(User $user, YudisiumClearance $clearance): bool
    {
        if (in_array($user->role, ['superadmin', 'baak', 'dean', 'rector'])) {
            return true;
        }

        if ($user->role === 'student') {
            return $user->student && $user->student->id === $clearance->student_id;
        }

        return false;
    }
}
