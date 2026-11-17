<?php

namespace App\Backend\Auth;

use Exception;

/**
 * Enterprise RBAC & ABAC Authentication and Authorization Service
 * Scoped by Faculty, Study Program (Prodi), and Academic Year.
 */
class RBACAuthService
{
    /**
     * Standard University Roles Definition
     */
    public const ROLE_SUPERADMIN      = 'superadmin';
    public const ROLE_CALON_MAHASISWA = 'calon_mahasiswa';
    public const ROLE_MAHASISWA       = 'mahasiswa';
    public const ROLE_DOSEN           = 'dosen';
    public const ROLE_DOSEN_PA        = 'dosen_pa';
    public const ROLE_KAPRODI         = 'kaprodi';
    public const ROLE_DEKAN           = 'dekan';
    public const ROLE_BAAK            = 'baak';
    public const ROLE_BAUK            = 'bauk';
    public const ROLE_REKTORAT        = 'rektorat';

    /**
     * Map of permissions per role
     */
    protected static array $rolePermissions = [
        self::ROLE_CALON_MAHASISWA => [
            'pmb.register',
            'pmb.upload_document',
            'pmb.take_cbt',
            'pmb.view_announcement',
            'pmb.download_loa',
            'pmb.pay_registration'
        ],
        self::ROLE_MAHASISWA => [
            'academic.view_dashboard',
            'krs.select_courses',
            'krs.submit',
            'krs.print_pdf',
            'lms.view_materials',
            'lms.submit_assignment',
            'lms.take_exam',
            'attendance.scan_qr',
            'khs.view',
            'khs.print_transcript',
            'finance.view_bills',
            'finance.generate_va',
            'thesis.submit_proposal',
            'thesis.bimbingan_log',
            'skpi.submit_portfolio',
            'edom.fill_survey'
        ],
        self::ROLE_DOSEN => [
            'academic.view_schedule',
            'lms.manage_session',
            'lms.upload_material',
            'lms.grade_assignment',
            'attendance.generate_qr',
            'attendance.validate_manual',
            'attendance.sign_bap',
            'grading.input_scores',
            'grading.lock_scores',
            'bkd.report_activity'
        ],
        self::ROLE_DOSEN_PA => [
            'krs.review_advisee',
            'krs.approve',
            'krs.reject',
            'academic.view_advisee_progress',
            'academic.early_warning_check'
        ],
        self::ROLE_KAPRODI => [
            'curriculum.manage_obe',
            'curriculum.map_cpl_cpmk',
            'curriculum.lock_curriculum',
            'workload.assign_lecturers',
            'class.approve_capacity',
            'grading.unlock_request_review',
            'thesis.assign_supervisors',
            'thesis.assign_examiners',
            'analytics.view_prodi'
        ],
        self::ROLE_BAAK => [
            'calendar.manage_academic_dates',
            'timetable.resolve_conflict',
            'room.allocate',
            'nim.generate_bulk',
            'student.status_management',
            'pddikti.sync_akm',
            'pddikti.sync_grades',
            'pddikti.sync_curriculum',
            'yudisium.verify_clearance',
            'pin.national_diploma_request'
        ],
        self::ROLE_BAUK => [
            'finance.set_tariff',
            'finance.generate_bills',
            'finance.approve_installment',
            'finance.scholarship_disbursement',
            'finance.reconcile_va',
            'finance.apply_academic_hold'
        ],
        self::ROLE_REKTORAT => [
            'executive.view_kpi',
            'executive.view_financial_health',
            'executive.view_retention_rate',
            'executive.view_spmi_audit',
            'executive.view_accreditation_readiness'
        ]
    ];

    /**
     * Check if a role possesses a specific permission.
     */
    public static function hasPermission(string $role, string $permission): bool
    {
        if ($role === self::ROLE_SUPERADMIN) {
            return true;
        }

        $perms = self::$rolePermissions[$role] ?? [];
        return in_array($permission, $perms, true);
    }

    /**
     * ABAC Scope Check: Verifies if user can act upon a resource scoped by Faculty or Prodi.
     */
    public static function checkResourceScope(
        array $userRoleScope,
        ?string $targetFacultyId,
        ?string $targetStudyProgramId
    ): bool {
        // Superadmin and Rektorat have university-wide bypass
        if (in_array($userRoleScope['role'], [self::ROLE_SUPERADMIN, self::ROLE_REKTORAT, self::ROLE_BAAK, self::ROLE_BAUK], true)) {
            return true;
        }

        // Dekanat scoped by Faculty
        if ($userRoleScope['role'] === self::ROLE_DEKAN) {
            return $userRoleScope['faculty_id'] === $targetFacultyId;
        }

        // Kaprodi & Dosen scoped by Study Program
        if (in_array($userRoleScope['role'], [self::ROLE_KAPRODI, self::ROLE_DOSEN, self::ROLE_DOSEN_PA], true)) {
            return $userRoleScope['study_program_id'] === $targetStudyProgramId;
        }

        return false;
    }

    /**
     * Log an enterprise audit event for critical academic/financial operations.
     */
    public static function recordAuditLog(
        string $userId,
        string $action,
        string $auditableType,
        string $auditableId,
        ?array $oldValues,
        ?array $newValues,
        ?string $reason = null,
        ?string $ipAddress = '127.0.0.1'
    ): array {
        return [
            'user_id' => $userId,
            'action' => strtoupper($action),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'reason' => $reason,
            'ip_address' => $ipAddress,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}
