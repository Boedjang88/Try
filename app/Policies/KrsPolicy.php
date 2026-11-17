<?php

namespace App\Policies;

use App\Models\KrsSubmission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KrsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the KRS submission.
     */
    public function view(User $user, KrsSubmission $submission): bool
    {
        if ($user->role === 'superadmin' || $user->role === 'baak' || $user->role === 'rector' || $user->role === 'dean') {
            return true;
        }

        if ($user->role === 'student') {
            return $user->student && $user->student->id === $submission->student_id;
        }

        if ($user->role === 'lecturer') {
            return $user->lecturer && $submission->student && $submission->student->academic_advisor_id === $user->lecturer->id;
        }

        if ($user->role === 'kaprodi') {
            return $user->study_program_id && $submission->student && $submission->student->study_program_id === $user->study_program_id;
        }

        return false;
    }

    /**
     * Determine whether the user can modify or enroll in this KRS submission.
     */
    public function update(User $user, KrsSubmission $submission): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'student') {
            return $user->student
                && $user->student->id === $submission->student_id
                && in_array($submission->status, ['draft', 'rejected']);
        }

        return false;
    }

    /**
     * Determine whether the user (Dosen PA) can approve this KRS submission.
     */
    public function approve(User $user, KrsSubmission $submission): bool
    {
        if ($user->role === 'superadmin' || $user->role === 'baak') {
            return true;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            return $submission->student
                && $submission->student->academic_advisor_id === $user->lecturer->id;
        }

        return false;
    }

    /**
     * Determine whether the user (Dosen PA) can reject this KRS submission.
     */
    public function reject(User $user, KrsSubmission $submission): bool
    {
        return $this->approve($user, $submission);
    }
}
