<?php

namespace App\Policies;

use App\Models\ClassSection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GradeSheetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the grading sheet.
     */
    public function view(User $user, ClassSection $classSection): bool
    {
        if (in_array($user->role, ['superadmin', 'dean', 'baak', 'rector'])) {
            return true;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            return $classSection->lecturer_id === $user->lecturer->id;
        }

        if ($user->role === 'kaprodi' && $user->study_program_id) {
            $courseProdiId = $classSection->course->study_program_id 
                ?? $classSection->course->curriculum->study_program_id 
                ?? null;
            return $courseProdiId === $user->study_program_id;
        }

        return false;
    }

    /**
     * Determine whether the user can modify or input student grades.
     */
    public function update(User $user, ClassSection $classSection): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            // Must be the assigned lecturer
            if ($classSection->lecturer_id !== $user->lecturer->id) {
                return false;
            }

            // Cannot edit if grades are already locked
            $alreadyLocked = $classSection->krsItems()->where('is_locked', true)->exists();
            return !$alreadyLocked;
        }

        return false;
    }

    /**
     * Determine whether the user can freeze/lock the grades.
     */
    public function lock(User $user, ClassSection $classSection): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'lecturer' && $user->lecturer) {
            return $classSection->lecturer_id === $user->lecturer->id;
        }

        if ($user->role === 'kaprodi' && $user->study_program_id) {
            $courseProdiId = $classSection->course->study_program_id 
                ?? $classSection->course->curriculum->study_program_id 
                ?? null;
            return $courseProdiId === $user->study_program_id;
        }

        return false;
    }

    /**
     * Determine whether the user can override and unlock previously locked grades.
     * Strictly reserved for the Kaprodi overseeing that specific Study Program.
     */
    public function unlock(User $user, ClassSection $classSection): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'kaprodi' && $user->study_program_id) {
            $courseProdiId = $classSection->course->study_program_id 
                ?? $classSection->course->curriculum->study_program_id 
                ?? null;
            return $courseProdiId === $user->study_program_id;
        }

        return false;
    }
}
