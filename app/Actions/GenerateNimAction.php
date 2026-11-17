<?php

namespace App\Actions;

use App\Models\Applicant;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Enterprise Action: Generate Unique Student Identification Number (NIM)
 * and Promote Applicant to Student Role under atomic DB locking.
 */
class GenerateNimAction
{
    /**
     * Generate sequential NIM and promote applicant to active student.
     *
     * Format: [2-digit Year][2-digit Degree Level Code][3-digit Prodi Code][4-digit Sequence]
     * Example: 26 (2026) + 01 (S1) + 102 (Informatika) + 0001 = 26011020001
     */
    public function execute(Applicant $applicant): Student
    {
        return DB::transaction(function () use ($applicant) {
            $studyProgram = StudyProgram::findOrFail($applicant->target_study_program_id);
            $entryYear = (int)date('Y');
            $yearPrefix = substr((string)$entryYear, -2); // e.g. '26'

            // Degree level code mapping
            $degreeCodeMap = [
                'D3' => '03',
                'D4' => '04',
                'S1' => '01',
                'Profesi' => '05',
                'Spesialis' => '06',
                'S2' => '02',
                'S3' => '07',
            ];
            $degreePrefix = $degreeCodeMap[$studyProgram->degree_level] ?? '01';

            // Clean 3-digit study program identifier
            $rawProdi = preg_replace('/\D/', '', $studyProgram->pddikti_code ?: $studyProgram->code);
            $prodiPrefix = str_pad(substr($rawProdi ?: '101', 0, 3), 3, '0', STR_PAD_LEFT);

            $nimBase = $yearPrefix . $degreePrefix . $prodiPrefix; // e.g. '2601102'

            // Lock the students table for this cohort using lockForUpdate() to eliminate race conditions
            $latestStudent = Student::where('study_program_id', $studyProgram->id)
                ->where('entry_year', $entryYear)
                ->where('nim', 'LIKE', $nimBase . '%')
                ->lockForUpdate()
                ->orderBy('nim', 'desc')
                ->first();

            $nextSequence = 1;
            if ($latestStudent) {
                $lastSequenceStr = substr($latestStudent->nim, strlen($nimBase));
                $nextSequence = ((int)$lastSequenceStr) + 1;
            }

            $generatedNim = $nimBase . str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);

            // 1. Promote or Update User Role to 'student'
            $user = User::findOrFail($applicant->user_id);
            $user->role = 'student';
            $user->faculty_id = $studyProgram->faculty_id;
            $user->study_program_id = $studyProgram->id;
            $user->save();

            // 2. Create Student Record
            $student = Student::firstOrNew(['user_id' => $user->id]);
            $student->study_program_id = $studyProgram->id;
            $student->nim = $generatedNim;
            $student->entry_year = $entryYear;
            $student->entry_semester = 'Ganjil';
            $student->current_semester = 1;
            $student->max_sks_allowed = 24; // Standard initial freshman SKS allowance
            $student->current_gpa_ipk = 0.00;
            $student->last_semester_gpa_ips = 0.00;
            $student->total_sks_completed = 0;
            $student->academic_status = 'active';
            $student->is_financially_cleared = true; // Auto-cleared upon re-registration
            $student->has_academic_hold = false;
            $student->save();

            // 3. Mark Applicant as Enrolled
            $applicant->admission_status = 'enrolled';
            $applicant->generated_nim = $generatedNim;
            $applicant->enrolled_at = now();
            $applicant->save();

            // 4. Audit Log
            $student->recordAudit(
                'STUDENT_NIM_PROMOTED',
                "Applicant [{$applicant->registration_number}] promoted to Student with NIM [{$generatedNim}] in Study Program [{$studyProgram->name_id}]"
            );

            return $student;
        });
    }
}
