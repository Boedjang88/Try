<?php

namespace App\Policies;

use App\Models\Thesis;
use App\Models\ThesisGuidanceLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThesisPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the thesis details.
     */
    public function view(User $user, Thesis $thesis): bool
    {
        if (in_array($user->role, ['superadmin', 'dean', 'baak', 'rector'])) {
            return true;
        }

        if ($user->role === 'student' && $user->student) {
            return $user->student->id === $thesis->student_id;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            $lecturerId = $user->lecturer->id;
            return in_array($lecturerId, [
                $thesis->supervisor1_lecturer_id,
                $thesis->supervisor2_lecturer_id,
                $thesis->examiner1_lecturer_id,
                $thesis->examiner2_lecturer_id,
            ]);
        }

        if ($user->role === 'kaprodi' && $user->study_program_id) {
            return $thesis->student && $thesis->student->study_program_id === $user->study_program_id;
        }

        return false;
    }

    /**
     * Determine whether the user can verify a guidance log session.
     */
    public function verifyGuidance(User $user, ThesisGuidanceLog $log): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            $thesis = $log->thesis;
            return $log->lecturer_id === $user->lecturer->id
                || ($thesis && in_array($user->lecturer->id, [$thesis->supervisor1_lecturer_id, $thesis->supervisor2_lecturer_id]));
        }

        return false;
    }
}
