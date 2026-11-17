<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\Auth\QuickSwitchAccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BkdController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CbtExamController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DemoControlController;
use App\Http\Controllers\EdomController;
use App\Http\Controllers\GradeSheetController;
use App\Http\Controllers\KhsController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PDDIKTIFeederController;
use App\Http\Controllers\Portal\AcademicConsultationController;
use App\Http\Controllers\Portal\AcademicLeaveController;
use App\Http\Controllers\Portal\AuditLogExplorerController;
use App\Http\Controllers\Portal\BaakBaukDashboardController;
use App\Http\Controllers\Portal\BaukBillingController;
use App\Http\Controllers\Portal\BulkDataController;
use App\Http\Controllers\Portal\CampusLifeController;
use App\Http\Controllers\Portal\CampusRoomOperationsController;
use App\Http\Controllers\Portal\DeveloperSandboxController;
use App\Http\Controllers\Portal\ExecutiveDashboardController;
use App\Http\Controllers\Portal\FileStorageController;
use App\Http\Controllers\Portal\GovernanceApprovalController;
use App\Http\Controllers\Portal\GradeAppealController;
use App\Http\Controllers\Portal\HelpdeskTicketController;
use App\Http\Controllers\Portal\IamAccountController;
use App\Http\Controllers\Portal\KaprodiDashboardController;
use App\Http\Controllers\Portal\KaprodiGradeLockController;
use App\Http\Controllers\Portal\LecturerDashboardController;
use App\Http\Controllers\Portal\LecturerSessionController;
use App\Http\Controllers\Portal\LmsExamController;
use App\Http\Controllers\Portal\MbkmConversionController;
use App\Http\Controllers\Portal\NotificationController;
use App\Http\Controllers\Portal\PmbDashboardController;
use App\Http\Controllers\Portal\PrintDocumentController;
use App\Http\Controllers\Portal\SecurityProfileController;
use App\Http\Controllers\Portal\SmartAdvisorController;
use App\Http\Controllers\Portal\StudentDashboardController;
use App\Http\Controllers\PublicDocumentVerificationController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\RectorDashboardController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\YudisiumController;
use App\Http\Middleware\EnsureRoleScope;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Unified University ERP, SIAKAD & LMS Platform
| Universitas Pelita Harapan (UPH Monolith) - STAGE 10 Production Release
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC MARKETING, VERIFICATION & ADMISSIONS (PMB)
// ==========================================
Route::get('/', [PublicPortalController::class, 'index'])->name('home');
Route::get('/programs', [PublicPortalController::class, 'programs'])->name('programs');
Route::get('/academic/undergraduate', [PublicPortalController::class, 'undergraduate'])->name('academic.undergraduate');
Route::get('/academic/pascasarjana', [PublicPortalController::class, 'pascasarjana'])->name('academic.pascasarjana');
Route::get('/academic/kelas-karyawan', [PublicPortalController::class, 'kelasKaryawan'])->name('academic.kelas-karyawan');
Route::get('/events', [PublicPortalController::class, 'events'])->name('events');
Route::get('/admissions/pmb', [PublicPortalController::class, 'index'])->name('admissions.pmb');
Route::get('/academic/curriculum', [PublicPortalController::class, 'programs'])->name('academic.curriculum');
Route::get('/curriculum-preview/{studyProgram}', [PublicPortalController::class, 'curriculumPreview'])->name('curriculum.preview');

// Public Cryptographic Document Verification Route (Stage 10 Requirement 1.B)
Route::get('/verify/document/{type}/{hash}', [PublicDocumentVerificationController::class, 'verify'])->name('document.verify.public');

// PMB Registration & CBT Exam Routes
Route::get('/admissions/pmb/register', [ApplicantController::class, 'showRegistrationForm'])->name('admissions.pmb.register');
Route::post('/admissions/pmb/register', [ApplicantController::class, 'storeRegistration'])->name('admissions.pmb.register.store');

Route::prefix('pmb')->name('pmb.')->group(function () {
    Route::get('/register', [ApplicantController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [ApplicantController::class, 'storeRegistration'])->name('register.store');
    Route::get('/applicant/{applicant}', [ApplicantController::class, 'dashboard'])->name('dashboard');
    Route::post('/applicant/{applicant}/promote', [ApplicantController::class, 'promote'])->name('promote');
    Route::post('/simulate-payment/{applicant}', [ApplicantController::class, 'simulatePayment'])->name('simulate-payment');
    Route::get('/cbt/{applicant?}', [CbtExamController::class, 'show'])->name('cbt');
    Route::post('/cbt/{applicant?}/submit', [CbtExamController::class, 'submit'])->middleware('throttle:cbt_submit')->name('cbt.submit');
    Route::get('/cbt/{applicant?}/result', [CbtExamController::class, 'result'])->name('cbt.result');
});

// ==========================================
// 2. AUTHENTICATION & SINGLE SIGN-ON (SSO)
// ==========================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
Route::get('/login/quick/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::get('/auth/switch/{accountKey}', [QuickSwitchAccountController::class, 'switch'])->name('auth.quick-switch');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/portal', [AuthController::class, 'portalRouter'])->middleware(['web', 'auth', 'active.account'])->name('portal.dashboard');

// ==========================================
// 2.1 DEDICATED ISOLATED WORKSPACE DASHBOARDS & SUB-ROUTES
// ==========================================
Route::middleware(['web', 'auth', 'active.account', 'role.privilege', EnsureRoleScope::class])->prefix('portal')->name('portal.')->group(function () {
    // Dashboards
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/lecturer/dashboard', [LecturerDashboardController::class, 'index'])->name('lecturer.dashboard');
    Route::get('/kaprodi/dashboard', [KaprodiDashboardController::class, 'index'])->name('kaprodi.dashboard');
    Route::get('/dean/dashboard', [ExecutiveDashboardController::class, 'deanDashboard'])->name('dean.dashboard');
    Route::get('/baak/dashboard', [BaakBaukDashboardController::class, 'baakDashboard'])->name('baak.dashboard');
    Route::get('/bauk/dashboard', [BaakBaukDashboardController::class, 'baukDashboard'])->name('bauk.dashboard');
    Route::get('/executive/dashboard', [ExecutiveDashboardController::class, 'rectorDashboard'])->name('executive.dashboard');
    Route::get('/pmb/dashboard', [PmbDashboardController::class, 'index'])->name('pmb.dashboard');

    // Student Sub-pages Aliases
    Route::get('/student/krs', [KrsController::class, 'index'])->name('student.krs');
    Route::get('/student/attendance', [QrAttendanceController::class, 'scanPage'])->name('student.attendance');
    Route::get('/student/lms', [LmsController::class, 'index'])->name('student.lms');
    Route::get('/student/khs', [KhsController::class, 'index'])->name('student.khs');
    Route::get('/student/finance', [BillingController::class, 'index'])->name('student.finance');
    Route::get('/student/thesis', [ThesisController::class, 'index'])->name('student.thesis');
    Route::get('/student/yudisium', [YudisiumController::class, 'index'])->name('student.yudisium');

    // Lecturer Sub-pages
    Route::get('/lecturer/sessions', [LecturerSessionController::class, 'index'])->name('lecturer.sessions');
    Route::post('/lecturer/sessions/{session}/bap', [LecturerSessionController::class, 'updateBap'])->name('lecturer.sessions.bap');
    Route::get('/lecturer/sessions/{session}/qr-token', [LecturerSessionController::class, 'rotateQr'])->name('lecturer.sessions.token');
    Route::post('/lecturer/sessions/{session}/simulate-checkin', [LecturerSessionController::class, 'simulateCheckIn'])->name('lecturer.sessions.simulate');
    Route::get('/lecturer/grading', [GradeSheetController::class, 'index'])->name('lecturer.grading');
    Route::get('/lecturer/advisees', [KrsController::class, 'advisorApprovalWorkspace'])->name('lecturer.advisees');
    Route::get('/lecturer/lms', [LmsController::class, 'index'])->name('lecturer.lms');
    Route::get('/lecturer/thesis', [ThesisController::class, 'index'])->name('lecturer.thesis');
    Route::get('/lecturer/thesis-supervision', [ThesisController::class, 'index'])->name('lecturer.thesis-supervision');
    Route::get('/lecturer/bkd', [BkdController::class, 'index'])->name('lecturer.bkd');

    // Kaprodi Sub-pages
    Route::get('/kaprodi/curriculum', [CurriculumController::class, 'index'])->name('kaprodi.curriculum');
    Route::get('/kaprodi/grade-locks', [KaprodiGradeLockController::class, 'index'])->name('kaprodi.grade-locks');
    Route::post('/kaprodi/grade-locks/{classSection}/unlock', [KaprodiGradeLockController::class, 'unlock'])->name('kaprodi.grade-locks.unlock');
    Route::post('/kaprodi/grade-locks/{classSection}/lock', [KaprodiGradeLockController::class, 'lock'])->name('kaprodi.grade-locks.lock');
    Route::get('/kaprodi/yudisium', [YudisiumController::class, 'index'])->name('kaprodi.yudisium');

    // Dean Sub-pages
    Route::get('/dean/bkd', [BkdController::class, 'index'])->name('dean.bkd');

    // BAAK Sub-pages
    Route::get('/baak/calendar', [BaakBaukDashboardController::class, 'calendar'])->name('baak.calendar');
    Route::post('/baak/calendar/period/{period}', [BaakBaukDashboardController::class, 'updatePeriodWindow'])->name('baak.calendar.update');
    Route::post('/baak/calendar/rollover', [BaakBaukDashboardController::class, 'rollover'])->name('baak.calendar.rollover');
    Route::get('/baak/pddikti', [PDDIKTIFeederController::class, 'index'])->name('baak.pddikti');
    Route::get('/baak/yudisium', [YudisiumController::class, 'index'])->name('baak.yudisium');
    Route::post('/yudisium/{clearance}/verify', [YudisiumController::class, 'verifyCheckpoint'])->name('portal.yudisium.verify');

    // BAUK Sub-pages (Loop 1)
    Route::get('/bauk/billing', [BaukBillingController::class, 'index'])->name('bauk.billing');
    Route::post('/bauk/billing/{invoice}/verify', [BaukBillingController::class, 'verifyPayment'])->name('bauk.billing.verify');
    Route::post('/bauk/billing/verify/{invoice}', [BaukBillingController::class, 'verifyPayment']);
    Route::post('/bauk/billing/{invoice}/simulate-va', [BaukBillingController::class, 'simulateVa'])->name('bauk.billing.simulate-va');
    Route::post('/bauk/billing/student/{student}/toggle-hold', [BaukBillingController::class, 'toggleHold'])->name('bauk.billing.toggle-hold');

    // PMB Applicant Sub-pages
    Route::get('/pmb/register', [ApplicantController::class, 'showRegistrationForm'])->name('pmb.register');
    Route::get('/pmb/cbt', [CbtExamController::class, 'show'])->name('pmb.cbt');

    // Stage 11 Engine 2: Academic Leave (Cuti Akademik)
    Route::get('/student/leave-request', [AcademicLeaveController::class, 'index'])->name('student.leave-request');
    Route::post('/student/leave-request', [AcademicLeaveController::class, 'store'])->name('student.leave-request.store');
    Route::post('/lecturer/leave-request/{leaveRequest}/approve', [AcademicLeaveController::class, 'advisorApprove'])->name('lecturer.leave-request.approve');
    Route::post('/kaprodi/leave-request/{leaveRequest}/approve', [AcademicLeaveController::class, 'kaprodiApprove'])->name('kaprodi.leave-request.approve');
    Route::post('/leave-request/{leaveRequest}/reject', [AcademicLeaveController::class, 'reject'])->name('leave-request.reject');

    // Stage 11 Engine 3: 75% Attendance Gate & Online Exam Runner
    Route::get('/student/lms/exam/{classSection}', [LmsExamController::class, 'showExam'])->name('student.lms.exam');
    Route::post('/student/lms/exam/{classSection}/submit', [LmsExamController::class, 'submitExam'])->name('student.lms.exam.submit');
    Route::post('/kaprodi/exam-unblock/{krsItem}', [LmsExamController::class, 'unblockExam'])->name('kaprodi.exam-unblock');

    // Stage 11 Engine 4: Real File Storage Engine Downloads & LMS Uploads
    Route::post('/lms/session/{session}/assignment', [LmsController::class, 'storeAssignment'])->name('lms.session.assignment');
    Route::post('/lms/assignment/{assignment}/submit', [LmsController::class, 'submitAssignment'])->name('lms.assignment.submit');
    Route::post('/lms/submission/{submission}/grade', [LmsController::class, 'gradeSubmission'])->name('lms.submission.grade');
    Route::get('/storage/download/assignment/{assignment}', [FileStorageController::class, 'downloadAssignment'])->name('storage.download.assignment');
    Route::get('/storage/download/submission/{submission}', [FileStorageController::class, 'downloadSubmission'])->name('storage.download.submission');
    Route::get('/storage/download/pmb/{applicant}/{type}', [FileStorageController::class, 'downloadPmbDocument'])->name('storage.download.pmb');
    Route::get('/storage/download/leave/{leaveRequest}', [FileStorageController::class, 'downloadLeaveDocument'])->name('storage.download.leave');

    // Stage 11 Engine 5: Real-Time In-App Notification Center
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Stage 11 Engine 6: Global Audit Trail Explorer
    Route::get('/executive/audit-logs', [AuditLogExplorerController::class, 'index'])->name('executive.audit-logs');

    // Stage 12 Engine 1: MBKM 20-SKS Conversion Workflow
    Route::get('/student/mbkm', [MbkmConversionController::class, 'studentIndex'])->name('student.mbkm');
    Route::post('/student/mbkm', [MbkmConversionController::class, 'storeProgram'])->name('student.mbkm.store');
    Route::get('/kaprodi/mbkm', [MbkmConversionController::class, 'kaprodiIndex'])->name('kaprodi.mbkm');
    Route::post('/kaprodi/mbkm/{mbkmProgram}/approve', [MbkmConversionController::class, 'approveConversion'])->name('kaprodi.mbkm.approve');
    Route::post('/kaprodi/mbkm/{mbkmProgram}/reject', [MbkmConversionController::class, 'rejectProgram'])->name('kaprodi.mbkm.reject');

    // Stage 12 Engine 2: KHS Score Transparency Breakdown & Grade Appeal (Sanggah Nilai)
    Route::get('/student/khs/breakdown/{krsItem}', [GradeAppealController::class, 'getBreakdown'])->name('student.khs.breakdown');
    Route::post('/student/khs/appeal/{krsItem}', [GradeAppealController::class, 'submitAppeal'])->name('student.khs.appeal');
    Route::get('/lecturer/appeals', [GradeAppealController::class, 'lecturerIndex'])->name('lecturer.appeals');
    Route::post('/lecturer/appeal/{gradeAppeal}/respond', [GradeAppealController::class, 'lecturerRespond'])->name('lecturer.appeal.respond');

    // Stage 12 AppSec: Private Signed URLs & Secure Downloads
    Route::get('/storage/signed-download', [FileStorageController::class, 'downloadSigned'])->name('storage.signed-download');
    Route::get('/storage/download/mbkm/{mbkmProgram}', [FileStorageController::class, 'downloadMbkmDocument'])->name('storage.download.mbkm');
    Route::get('/storage/download/appeal/{gradeAppeal}', [FileStorageController::class, 'downloadAppealEvidence'])->name('storage.download.appeal-evidence');

    // Stage 12: Live Demo Control Center 1-Click State Resetter
    Route::post('/demo/reset-state', [DemoControlController::class, 'resetState'])->name('demo.reset-state');

    // Stage 13 Section 1: AI Academic Copilot & GPA Simulator
    Route::get('/student/smart-advisor', [SmartAdvisorController::class, 'index'])->name('student.smart-advisor');
    Route::post('/student/smart-advisor/simulate', [SmartAdvisorController::class, 'simulate'])->name('student.smart-advisor.simulate');

    // Stage 13 Section 2: BAAK & BAUK Bulk Data Center
    Route::get('/baak/bulk-data', [BulkDataController::class, 'baakIndex'])->name('baak.bulk-data');
    Route::get('/bauk/bulk-data', [BulkDataController::class, 'baukIndex'])->name('bauk.bulk-data');
    Route::get('/bulk-data/export/students', [BulkDataController::class, 'exportStudents'])->name('bulk-data.export.students');
    Route::get('/bulk-data/export/schedules', [BulkDataController::class, 'exportSchedules'])->name('bulk-data.export.schedules');
    Route::get('/bulk-data/export/invoices', [BulkDataController::class, 'exportInvoices'])->name('bulk-data.export.invoices');
    Route::get('/bulk-data/export/grade-template', [BulkDataController::class, 'exportGradeTemplate'])->name('bulk-data.export.grade-template');
    Route::post('/bulk-data/preview', [BulkDataController::class, 'previewImport'])->name('bulk-data.preview');
    Route::post('/bulk-data/commit', [BulkDataController::class, 'commitImport'])->name('bulk-data.commit');

    // Stage 13 Section 3: Campus Life & Career / Tracer Study
    Route::get('/student/campus-life', [CampusLifeController::class, 'campusLifeIndex'])->name('student.campus-life');
    Route::post('/student/campus-life/book', [CampusLifeController::class, 'storeFacilityBooking'])->name('student.campus-life.book');
    Route::get('/student/career-tracer', [CampusLifeController::class, 'careerTracerIndex'])->name('student.career-tracer');
    Route::post('/student/career-tracer/submit', [CampusLifeController::class, 'submitTracerStudy'])->name('student.career-tracer.submit');

    // Stage 13 Section 5: Security Telemetry & Active Session Profile
    Route::get('/account/security', [SecurityProfileController::class, 'index'])->name('account.security');

    // Stage 15 Section 2: Password Management & Privilege Viewer Form
    Route::get('/account/password', [AuthController::class, 'showPasswordForm'])->name('account.password');
    Route::post('/account/password', [AuthController::class, 'updatePassword'])->name('account.password.update');

    // Stage 15 Section 4: IAM User Directory & Role-Privilege Management
    Route::prefix('iam')->name('iam.')->group(function () {
        Route::get('/users', [IamAccountController::class, 'index'])->name('users');
        Route::post('/users', [IamAccountController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{user}/toggle-status', [IamAccountController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [IamAccountController::class, 'resetUserPassword'])->name('users.reset-password');
        Route::get('/privileges', [IamAccountController::class, 'privileges'])->name('privileges');
        Route::post('/privileges/override', [IamAccountController::class, 'overridePrivilege'])->name('privileges.override');
    });

    // Stage 16 Section 1: Dual-Control Maker-Checker Approval Engine
    Route::prefix('governance')->name('governance.')->group(function () {
        Route::get('/approvals', [GovernanceApprovalController::class, 'index'])->name('approvals');
        Route::post('/approvals', [GovernanceApprovalController::class, 'store'])->name('approvals.store');
        Route::post('/approvals/{approvalRequest}/approve', [GovernanceApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{approvalRequest}/reject', [GovernanceApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // Stage 16 Section 2: Real-Time Consultation Threads & SLA Helpdesk Ticketing
    Route::prefix('consultation')->name('consultation.')->group(function () {
        Route::get('/messages', [AcademicConsultationController::class, 'index'])->name('messages');
        Route::post('/messages', [AcademicConsultationController::class, 'store'])->name('messages.store');
    });

    Route::prefix('helpdesk')->name('helpdesk.')->group(function () {
        Route::get('/', [HelpdeskTicketController::class, 'index'])->name('index');
        Route::post('/tickets', [HelpdeskTicketController::class, 'store'])->name('store');
        Route::get('/tickets/{ticket}', [HelpdeskTicketController::class, 'show'])->name('show');
        Route::post('/tickets/{ticket}/replies', [HelpdeskTicketController::class, 'reply'])->name('reply');
        Route::post('/tickets/{ticket}/status', [HelpdeskTicketController::class, 'updateStatus'])->name('status');
    });

    // Stage 16 Section 3: Smart Classroom Occupancy Map & Anti-Cheating Exam Seat Generator
    Route::get('/baak/rooms-occupancy', [CampusRoomOperationsController::class, 'occupancy'])->name('baak.rooms-occupancy');
    Route::post('/baak/exam-seats/generate', [CampusRoomOperationsController::class, 'generateExamSeats'])->name('baak.exam-seats.generate');
    Route::get('/student/exam-card', [CampusRoomOperationsController::class, 'examCard'])->name('student.exam-card');

    // Stage 16 Section 4: OpenAPI / Webhook Interoperability Sandbox & Outbox
    Route::prefix('developer')->name('developer.')->group(function () {
        Route::get('/sandbox', [DeveloperSandboxController::class, 'sandbox'])->name('sandbox');
        Route::post('/sandbox/simulate-webhook', [DeveloperSandboxController::class, 'simulateWebhook'])->name('simulate-webhook');
        Route::get('/outbox', [DeveloperSandboxController::class, 'outbox'])->name('outbox');
        Route::get('/architecture', [DeveloperSandboxController::class, 'architecture'])->name('architecture');
    });

    // Official A4 Printable Documents (Stage 9 Requirement 3)
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/krs/{krsSubmission?}', [PrintDocumentController::class, 'krs'])->name('krs');
        Route::get('/khs-transcript/{student?}/{period?}', [PrintDocumentController::class, 'khsTranscript'])->name('khs-transcript');
        Route::get('/kwitansi/{invoice?}', [PrintDocumentController::class, 'kwitansi'])->name('kwitansi');
        Route::get('/loa/{applicant?}', [PrintDocumentController::class, 'loa'])->name('loa');
        Route::get('/skpi/{clearance?}', [PrintDocumentController::class, 'skpi'])->name('skpi');
    });
});

// ==========================================
// 3. PAYMENT WEBHOOK API (Public/Gateway)
// ==========================================
Route::post('/api/payment/webhook', [PaymentWebhookController::class, 'handleWebhook'])->name('api.payment.webhook');

// ==========================================
// 4. AUTHENTICATED PORTAL MODULES & ACTION ENDPOINTS
// ==========================================
Route::middleware(['web', 'auth', 'active.account', 'role.privilege'])->group(function () {

    // Module B: OBE Curriculum & Competency Matrix
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        Route::get('/', [CurriculumController::class, 'index'])->name('index');
        Route::get('/{curriculum}/obe', [CurriculumController::class, 'obeWorkspace'])->name('obe');
        Route::post('/{curriculum}/cpl', [CurriculumController::class, 'storeCpl'])->name('cpl.store');
        Route::post('/obe/{curriculum}/cpl', [CurriculumController::class, 'storeCpl']);
        Route::post('/course/{course}/cpmk', [CurriculumController::class, 'storeCpmk'])->name('cpmk.store');
        Route::post('/courses/{course}/cpmk', [CurriculumController::class, 'storeCpmk']);
        Route::post('/course/{course}/prerequisites', [CurriculumController::class, 'updatePrerequisites'])->name('prerequisites.update');
        Route::post('/courses/{course}/prerequisites', [CurriculumController::class, 'storePrerequisite']);
        Route::post('/course/{course}/weights', [CurriculumController::class, 'updateCourseWeights'])->name('course.weights');
        Route::post('/courses/{course}/weights', [CurriculumController::class, 'updateCourseWeights']);
        Route::post('/{curriculum}/lock', [CurriculumController::class, 'lockCurriculum'])->name('lock');
        Route::post('/obe/{curriculum}/lock', [CurriculumController::class, 'lockCurriculum']);
    });

    // Module C: KRS Enrollment War & Academic Advising (Loop 2)
    Route::prefix('krs')->name('krs.')->group(function () {
        Route::get('/', [KrsController::class, 'index'])->name('index');
        Route::post('/enroll', [KrsController::class, 'addCourse'])->middleware('throttle:krs_cart')->name('enroll');
        Route::post('/add', [KrsController::class, 'addCourse'])->middleware('throttle:krs_cart')->name('add');
        Route::delete('/drop/{krsItem}', [KrsController::class, 'removeCourse'])->name('drop');
        Route::post('/remove', [KrsController::class, 'removeCourse'])->name('remove');
        Route::post('/submit', [KrsController::class, 'submitKrs'])->middleware('throttle:krs_cart')->name('submit');
        Route::get('/advisor', [KrsController::class, 'advisorApprovalWorkspace'])->name('advisor');
        Route::post('/advisor/{krsSubmission}/approve', [KrsController::class, 'approveKrs'])->name('advisor.approve');
        Route::post('/advisor/{krsSubmission}/reject', [KrsController::class, 'rejectKrs'])->name('advisor.reject');
        Route::get('/print/{krsSubmission?}', [PrintDocumentController::class, 'krs'])->name('print');
    });

    // API Routes for KRS Cart (Ajax compatibility)
    Route::post('/api/krs/add', [KrsController::class, 'addCourse'])->middleware('throttle:krs_cart')->name('api.krs.add');
    Route::post('/api/krs/remove', [KrsController::class, 'removeCourse'])->name('api.krs.remove');
    Route::post('/api/krs/approve/{krsSubmission}', [KrsController::class, 'approveKrs'])->name('api.krs.approve');
    Route::post('/api/krs/reject/{krsSubmission}', [KrsController::class, 'rejectKrs'])->name('api.krs.reject');

    // API Routes for QR Attendance (Ajax compatibility)
    Route::post('/api/attendance/scan', [QrAttendanceController::class, 'verifyScan'])->middleware('throttle:attendance_scan')->name('api.attendance.scan');
    Route::get('/api/attendance/session/{lectureSession}/qr-token', [QrAttendanceController::class, 'generateQrToken'])->name('api.attendance.token');
    Route::get('/api/attendance/session/{lectureSession}/roster', [QrAttendanceController::class, 'liveRoster'])->name('api.attendance.roster');

    // API Routes for LMS, Grading, and PMB CBT (Ajax compatibility)
    Route::post('/api/grading/{classSection}/save', [GradeSheetController::class, 'bulkSave'])->name('api.grading.save');
    Route::post('/api/grading/{classSection}/lock', [GradeSheetController::class, 'lockGrades'])->name('api.grading.lock');
    Route::post('/api/lms/submissions/{submission}/grade', [LmsController::class, 'gradeSubmission'])->name('api.lms.submissions.grade');
    Route::post('/api/lms/submission/{submission}/grade', [LmsController::class, 'gradeSubmission']);
    Route::post('/api/pmb/cbt/submit', [CbtExamController::class, 'submit'])->name('api.pmb.cbt.submit');
    Route::post('/api/pmb/cbt/{applicant?}/submit', [CbtExamController::class, 'submit']);

    // Module D.1: QR Attendance & Dynamic Geofencing (Loop 3)
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [QrAttendanceController::class, 'scanPage'])->name('index');
        Route::get('/scan/active', [QrAttendanceController::class, 'scanPage'])->name('scan.active');
        Route::get('/session/{lectureSession}', [QrAttendanceController::class, 'sessionDashboard'])->name('session');
        Route::get('/session/{lectureSession}/token', [QrAttendanceController::class, 'generateQrToken'])->name('token');
        Route::get('/session/{lectureSession}/roster', [QrAttendanceController::class, 'liveRoster'])->name('roster');
        Route::post('/session/{lectureSession}/bap', [QrAttendanceController::class, 'submitBap'])->name('bap');
        Route::get('/scan/{lectureSession}', [QrAttendanceController::class, 'scanPage'])->name('scan');
        Route::post('/scan/{lectureSession}/verify', [QrAttendanceController::class, 'verifyScan'])->middleware('throttle:attendance_scan')->name('verify');
        Route::post('/verify', [QrAttendanceController::class, 'verifyScan'])->middleware('throttle:attendance_scan')->name('verify.general');
    });

    // Module D.2: LMS (Learning Management System)
    Route::prefix('lms')->name('lms.')->group(function () {
        Route::get('/', [LmsController::class, 'index'])->name('index');
        Route::get('/session/{lectureSession}', [LmsController::class, 'sessionDetail'])->name('session');
        Route::post('/session/{lectureSession}/material', [LmsController::class, 'storeMaterial'])->name('material.store');
        Route::post('/assignment/{assignment}/submit', [LmsController::class, 'submitAssignment'])->name('assignment.submit');
        Route::post('/assignments/{assignment}/submit', [LmsController::class, 'submitAssignment']);
        Route::post('/submission/{submission}/grade', [LmsController::class, 'gradeSubmission'])->name('submission.grade');
    });

    // Module E.1: Grading & Spreadsheet Grade Sheet (Loop 4)
    Route::prefix('grading')->name('grading.')->group(function () {
        Route::get('/', [GradeSheetController::class, 'index'])->name('index');
        Route::get('/{classSection}', [GradeSheetController::class, 'show'])->name('sheet');
        Route::post('/{classSection}/save', [GradeSheetController::class, 'bulkSave'])->name('save');
        Route::post('/{classSection}/lock', [GradeSheetController::class, 'lockGrades'])->name('lock');
        Route::get('/{classSection}/export', [GradeSheetController::class, 'exportCsv'])->name('export');
    });

    // Module E.2: EDOM (Teaching Evaluation Survey Gate)
    Route::prefix('edom')->name('edom.')->group(function () {
        Route::get('/{krsItem}', [EdomController::class, 'show'])->name('show');
        Route::post('/{krsItem}', [EdomController::class, 'store'])->name('store');
    });

    // Module E.3: KHS & Cumulative Transcript
    Route::prefix('khs')->name('khs.')->group(function () {
        Route::get('/', [KhsController::class, 'index'])->name('index');
        Route::get('/transcript', [KhsController::class, 'transcript'])->name('transcript');
    });

    // Module G: Enterprise Finance, Invoices & VA Gateway
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/invoices', [BillingController::class, 'index'])->name('invoices');
        Route::post('/invoice/{invoice}/va', [BillingController::class, 'generateVA'])->name('va.generate');
        Route::get('/receipt/{invoice}', [PrintDocumentController::class, 'kwitansi'])->name('receipt');
        Route::post('/invoice/{invoice}/simulate', [PaymentWebhookController::class, 'simulatePayment'])->name('simulate');
    });

    // Module F: Undergraduate Thesis, Defense & Yudisium Clearances (Loop 5)
    Route::prefix('thesis')->name('thesis.')->group(function () {
        Route::get('/', [ThesisController::class, 'index'])->name('index');
        Route::post('/proposal', [ThesisController::class, 'storeProposal'])->name('proposal.store');
        Route::post('/{thesis}/supervisors', [ThesisController::class, 'assignSupervisors'])->name('supervisors.assign');
        Route::post('/{thesis}/guidance', [ThesisController::class, 'storeGuidanceLog'])->name('guidance.store');
        Route::post('/guidance/{log}/verify', [ThesisController::class, 'verifyGuidanceLog'])->name('guidance.verify');
        Route::post('/{thesis}/defense/schedule', [ThesisController::class, 'scheduleDefense'])->name('defense.schedule');
        Route::post('/{thesis}/defense/grade', [ThesisController::class, 'gradeDefense'])->name('defense.grade');
    });

    Route::prefix('yudisium')->name('yudisium.')->group(function () {
        Route::get('/', [YudisiumController::class, 'index'])->name('index');
        Route::post('/{clearance}/verify', [YudisiumController::class, 'verifyCheckpoint'])->name('verify');
        Route::get('/skpi/{clearance}', [PrintDocumentController::class, 'skpi'])->name('skpi');
    });

    // Module H.1: Lecturer Workload (BKD) & Tri Dharma
    Route::get('/bkd', [BkdController::class, 'index'])->name('bkd.index');

    // Module H.2: Executive Rector & Dean KPI Analytics Dashboard
    Route::get('/dashboard/rector', [RectorDashboardController::class, 'index'])->name('rector.dashboard');
    Route::get('/rector/dashboard', [RectorDashboardController::class, 'index'])->name('rector.dashboard.alias');

    // Module I: PDDIKTI Neo Feeder Synchronization Engine
    Route::prefix('pddikti')->name('pddikti.')->group(function () {
        Route::get('/', [PDDIKTIFeederController::class, 'index'])->name('index');
        Route::post('/sync/{entityType}', [PDDIKTIFeederController::class, 'sync'])->name('sync');
    });

});
