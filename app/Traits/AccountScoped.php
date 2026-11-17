<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Trait AccountScoped
 *
 * Provides a unified `scopeForCurrentAccount` query scope to strictly isolate
 * records per active account/role and prevent data leaks across workspaces.
 */
trait AccountScoped
{
    /**
     * Scope query strictly to the current logged-in account and role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User|null  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCurrentAccount(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?: Auth::user();

        if (!$user) {
            // Unauthenticated: return empty set
            return $query->whereRaw('1 = 0');
        }

        $role = $user->primary_role ?? $user->role;

        // Superadmin and Rector have global read-only or administrative oversight
        if (in_array($role, ['superadmin', 'rector'], true)) {
            return $query;
        }

        $table = $this->getTable();

        // 1. STUDENT SCOPE
        if ($role === 'student') {
            $student = $user->student;
            $studentId = $student ? $student->id : null;

            if ($table === 'students') {
                return $query->where('user_id', $user->id);
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'student_id')) {
                return $query->where($table . '.student_id', $studentId);
            }

            if ($table === 'krs_items') {
                return $query->whereHas('krsSubmission', function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                });
            }

            if ($table === 'attendances') {
                return $query->where('student_id', $studentId);
            }

            if ($table === 'student_invoices') {
                return $query->where('student_id', $studentId);
            }

            if ($table === 'theses') {
                return $query->where('student_id', $studentId);
            }
        }

        // 2. LECTURER SCOPE
        if ($role === 'lecturer') {
            $lecturer = $user->lecturer;
            $lecturerId = $lecturer ? $lecturer->id : null;

            if ($table === 'lecturers') {
                return $query->where('user_id', $user->id);
            }

            if ($table === 'class_sections') {
                return $query->where($table . '.lecturer_id', $lecturerId);
            }

            if ($table === 'theses') {
                return $query->where(function ($q) use ($lecturerId) {
                    $q->where('supervisor1_lecturer_id', $lecturerId)
                      ->orWhere('supervisor2_lecturer_id', $lecturerId);
                });
            }

            if ($table === 'students') {
                return $query->where('academic_advisor_id', $lecturerId);
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'lecturer_id')) {
                return $query->where($table . '.lecturer_id', $lecturerId);
            }
        }

        // 3. KAPRODI SCOPE (Scoped by Study Program)
        if ($role === 'kaprodi') {
            $prodiId = $user->study_program_id;

            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'study_program_id')) {
                return $query->where($table . '.study_program_id', $prodiId);
            }

            if ($table === 'curricula' || $table === 'courses') {
                if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'study_program_id')) {
                    return $query->where($table . '.study_program_id', $prodiId);
                }
            }

            if ($table === 'students') {
                return $query->where('study_program_id', $prodiId);
            }
        }

        // 4. DEAN SCOPE (Scoped by Faculty)
        if ($role === 'dean') {
            $facultyId = $user->faculty_id;

            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'faculty_id')) {
                return $query->where($table . '.faculty_id', $facultyId);
            }

            if ($table === 'study_programs') {
                return $query->where('faculty_id', $facultyId);
            }

            if ($table === 'students') {
                return $query->whereHas('studyProgram', function ($q) use ($facultyId) {
                    $q->where('faculty_id', $facultyId);
                });
            }
        }

        // 5. APPLICANT SCOPE
        if ($role === 'applicant') {
            $applicant = $user->applicant;
            $applicantId = $applicant ? $applicant->id : null;

            if ($table === 'applicants') {
                return $query->where('user_id', $user->id);
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'applicant_id')) {
                return $query->where($table . '.applicant_id', $applicantId);
            }
        }

        // 6. BAAK / BAUK
        if (in_array($role, ['baak', 'bauk'], true)) {
            return $query;
        }

        return $query;
    }
}
