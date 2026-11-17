# CETAK BIRU ARSITEKTUR & ALUR SISTEM (ENTERPRISE MASTER BLUEPRINT)
## Universitas Pelita Harapan — Unified SIAKAD, ERP, LMS & Governance Monolith
**Version:** 9.0 Enterprise Release (Stage 17 Ready)  
**Security Standard:** ISO 27001 & NIST Zero-Trust Compliant  
**Framework:** Laravel 11.x Monolith & SQLite/PostgreSQL Engine  
**UI/UX Paradigm:** Bento Grid Design System, Linear/Stripe SaaS Aesthetic, Dark Mode & UPH Heritage Colors

---

## DAFTAR ISI (TABLE OF CONTENTS)
1. [Section 1: Arsitektur Tingkat Tinggi & Siklus Hidup Permintaan (Request Lifecycle)](#section-1-arsitektur-tingkat-tinggi--siklus-hidup-permintaan-request-lifecycle)
2. [Section 2: Struktur Direktori & Peta Monolit Modular (Directory Architecture)](#section-2-struktur-direktori--peta-monolit-modular-directory-architecture)
3. [Section 3: Diagram Relasi Database (ERD & Database Schema Matrix)](#section-3-diagram-relasi-database-erd--database-schema-matrix)
4. [Section 4: Matriks 9 Persona, Kredensial & Hak Akses (IAM & RBAC+ABAC Matrix)](#section-4-matriks-9-persona-kredensial--hak-akses-iam--rbacabac-matrix)
5. [Section 5: Diagram Alir Proses Bisnis Utama (Core Enterprise Workflows)](#section-5-diagram-alir-proses-bisnis-utama-core-enterprise-workflows)
   - [Flow 1: PMB, Virtual Account, CBT & Promosi Mahasiswa Baru](#flow-1-pmb-virtual-account-cbt--promosi-mahasiswa-baru)
   - [Flow 2: Billing BAUK, KRS War, Live Chat PA & Alokasi Kursi Ujian](#flow-2-billing-bauk-krs-war-live-chat-pa--alokasi-kursi-ujian)
   - [Flow 3: Presensi QR 15 Detik, Gate 75%, Ujian LMS, Grading OBE & Sanggah Nilai](#flow-3-presensi-qr-15-detik-gate-75-ujian-lms-grading-obe--sanggah-nilai)
   - [Flow 4: Maker-Checker Dual-Control Approval Engine](#flow-4-maker-checker-dual-control-approval-engine)
   - [Flow 5: Konversi MBKM 20 SKS, Skripsi, Yudisium 4-Pintu & SKPI Bilingual](#flow-5-konversi-mbkm-20-sks-skripsi-yudisium-4-pintu--skpi-bilingual)
6. [Section 6: Master Indeks Rute & Controller (Complete Route Index)](#section-6-master-indeks-rute--controller-complete-route-index)
7. [Section 7: Interactive In-Portal Architecture Viewer](#section-7-interactive-in-portal-architecture-viewer)
8. [Section 8: Panduan Pemeliharaan, Troubleshooting & Developer Cheat-Sheet](#section-8-panduan-pemeliharaan-troubleshooting--developer-cheat-sheet)

---

## SECTION 1: ARSITEKTUR TINGKAT TINGGI & SIKLUS HIDUP PERMINTAAN (REQUEST LIFECYCLE)

Sistem ini dibangun dengan arsitektur **Zero-Trust Enterprise Monolith**. Setiap permintaan (HTTP Request) yang masuk melalui browser atau API client melewati lapisan pengamanan ketat sebelum mencapai Controller dan mengeksekusi operasi basis data atomik.

```
+───────────────────────────────────────────────────────────────────────────────────────────────+
|                                    INCOMING CLIENT REQUEST                                    |
|                       (Web Browser Desktop / Mobile PWA / API Webhook)                        |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 1. WEB SERVER & ENTRY POINT                                                                   |
|    - Nginx / Apache / Artisan Built-in Server                                                 |
|    - public/index.php (Front Controller)                                                      |
|    - Static Asset Interception: Vite (public/build/assets/*.js, *.css)                        |
|    - Strict Shadowing Prevention: 0 static .html files in public/                             |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 2. GLOBAL HTTP MIDDLEWARE PIPELINE                                                            |
|    - \Illuminate\Http\Middleware\TrustProxies                                                 |
|    - \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance                  |
|    - \Illuminate\Foundation\Http\Middleware\ValidatePostSize                                  |
|    - \Illuminate\Foundation\Http\Middleware\TrimStrings                                       |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 3. ROUTING & WEB SESSION STACK                                                                |
|    - routes/web.php (240+ Enterprise Routes)                                                  |
|    - StartSession & ShareErrorsFromSession                                                    |
|    - VerifyCsrfToken (X-CSRF-TOKEN / @csrf validation on every mutating POST/PUT/DELETE)      |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 4. ZERO-TRUST IDENTITY & ACCESS MANAGEMENT (IAM) GATES                                        |
|                                                                                               |
|    [Gate 4.1] auth: Web Authentication Guard (session-backed identity verification)           |
|         │                                                                                     |
|         ├─── (Unauthenticated) ──► Redirect /login with session('warning')                    |
|         └─── (Authenticated)                                                                 |
|                                                                                               |
|    [Gate 4.2] active.account: Account Status Guard                                            |
|         │                                                                                     |
|         ├─── (is_active == false) ──► Session Invalidation & Force Logout (403 Inactive)     |
|         └─── (is_active == true)                                                              |
|                                                                                               |
|    [Gate 4.3] EnsureRoleScope: Strictly Scoped URL Prefix Isolation                          |
|         │  - /portal/student/*   ──► Role: student, superadmin                                |
|         │  - /portal/lecturer/*  ──► Role: lecturer, superadmin                               |
|         │  - /portal/kaprodi/*   ──► Role: kaprodi, superadmin                                |
|         │  - /portal/dean/*      ──► Role: dean, superadmin                                   |
|         │  - /portal/baak/*      ──► Role: baak, superadmin                                   |
|         │  - /portal/bauk/*      ──► Role: bauk, superadmin                                   |
|         │  - /portal/executive/* ──► Role: rector, dean, superadmin                           |
|         │  - /portal/iam/*       ──► Role: superadmin, baak                                   |
|         │  - /portal/governance/*──► Role: superadmin, kaprodi, dean, bauk, lecturer, baak    |
|         │  - /portal/developer/* ──► Role: superadmin, baak, bauk, rector                     |
|         │                                                                                     |
|         ├─── (Mismatch Prefix) ──► 403 Forbidden with Scoped Error Banner                      |
|         └─── (Authorized Scope)                                                               |
|                                                                                               |
|    [Gate 4.4] role.privilege & ABAC Evaluation                                                |
|         - Faculty Scope check for Deans ($user->faculty_id)                                  |
|         - Study Program Scope check for Kaprodi ($user->study_program_id)                    |
|         - Direct Permission Slugs: hasPermission($slug) / user_custom_permissions             |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 5. CONTROLLER / DOMAIN SERVICE LAYER                                                          |
|    - App\Http\Controllers\Portal\* (30+ Specialized Controllers)                              |
|    - Form Request Validation & Anti-Cheating Invariants                                        |
|    - Domain Services: OutboundNotificationService, PMBService, etc.                           |
|    - Atomic Transactions: DB::transaction(fn() => ...) with lockForUpdate() on concurrency   |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 6. ELOQUENT ORM & DATA LAYER AUDIT ENGINE                                                     |
|    - Models: App\Models\* (30+ Rich Models)                                                   |
|    - Traits: Auditable (Atomic insert into audit_logs on any create/update/delete)           |
|    - Database Connection: SQLite (Testing/Local) & PostgreSQL (Production Engine)             |
+──────────────────────────────────────────────┬────────────────────────────────────────────────+
                                               │
                                               ▼
+───────────────────────────────────────────────────────────────────────────────────────────────+
| 7. PRESENTATION LAYER & RESPONSE EMISSION                                                      |
|    - Blade View Rendering: resources/views/layouts/portal.blade.php                          |
|      * Stage 14 Bento Grid UI Architecture & Alpine.js Command Center (Ctrl+K)                |
|      * Midnight Dark Mode via tailwindcss & localStorage                                     |
|      * Global Flash Alert Banners (session('success'), session('error'), $errors->any())      |
|      * Unified Role Sidebar: <x-portal-sidebar />                                             |
|      * Notification Bell Component: <x-notification-bell />                                   |
|    - JSON Responses: Standard { success: true, message: "...", data: {...} }                 |
|    - Cryptographic Document Verification: SHA-256 Public Verification Hashes                 |
+───────────────────────────────────────────────────────────────────────────────────────────────+
```

---

## SECTION 2: STRUKTUR DIREKTORI & PETA MONOLIT MODULAR (DIRECTORY ARCHITECTURE)

Struktur repositori mengikuti arsitektur Laravel Enterprise Monolith yang modular dan terisolasi:

```
recreate/
├── app/
│   ├── backend/
│   │   └── Admissions/
│   │       └── PMBService.php                 # Core Domain Service PMB (CBT grading, NIM generation)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php             # Login, SSO Quick-Login, Logout, Password Update
│   │   │   ├── ApplicantController.php        # PMB Registration, Dashboard, Student Promotion
│   │   │   ├── CbtExamController.php          # Computer-Based Testing Online Engine
│   │   │   ├── CurriculumController.php       # Kurikulum OBE, CPL, CPMK, Bobot Penilaian
│   │   │   ├── KrsController.php              # KRS War Cart, lockForUpdate, Dosen PA Approval
│   │   │   ├── QrAttendanceController.php     # Token QR Presensi 15s, Geofence GPS Haversine
│   │   │   ├── LmsController.php              # Sesi Perkuliahan 16-Sesi, Tugas, Materi, Submisi
│   │   │   ├── GradeSheetController.php       # Spreadsheet Nilai Dosen, Bobot OBE, Kunci Nilai
│   │   │   ├── KhsController.php              # KHS Mahasiswa, Transkrip Kumulatif, EWS IPK
│   │   │   ├── EdomController.php             # Evaluasi Dosen oleh Mahasiswa (Gate KHS)
│   │   │   ├── BillingController.php          # Tagihan UKT Mahasiswa, Simulator VA
│   │   │   ├── ThesisController.php           # Pengajuan Skripsi, Logbook 8-Sesi, Sidang
│   │   │   ├── YudisiumController.php         # Verifikasi 4-Pintu Yudisium, SKPI Digital
│   │   │   ├── BkdController.php              # Beban Kerja Dosen (Tri Dharma Perguruan Tinggi)
│   │   │   ├── RectorDashboardController.php  # EDSS Strategic KPI Eksekutif Rektorat
│   │   │   ├── PDDIKTIFeederController.php    # Sinkronisasi Kemdiktisaintek Neo Feeder
│   │   │   ├── PaymentWebhookController.php   # Bank VA Callback Simulator & Receiver
│   │   │   ├── PublicPortalController.php     # Beranda Publik, Program Studi, Event Kampus
│   │   │   ├── PublicDocumentVerificationController.php # Verifikasi Dokumen Publik QR Hash
│   │   │   ├── DemoControlController.php      # 1-Click System State Reset & Demo Switcher
│   │   │   └── Portal/                        # Scoped Sub-Controllers (Stages 11-16)
│   │   │       ├── StudentDashboardController.php      # Dasbor Utama Mahasiswa
│   │   │       ├── LecturerDashboardController.php     # Dasbor Dosen & Advisee
│   │   │       ├── LecturerSessionController.php       # BAP Kuliah & QR Rotator
│   │   │       ├── KaprodiDashboardController.php      # Dasbor Kaprodi & Validasi
│   │   │       ├── KaprodiGradeLockController.php      # Buka/Kunci Nilai Semester Kaprodi
│   │   │       ├── ExecutiveDashboardController.php    # Dasbor Dekan & Rektorat
│   │   │       ├── BaakBaukDashboardController.php     # Dasbor BAAK (Kalender) & BAUK
│   │   │       ├── BaukBillingController.php           # Rekonsiliasi VA & Cekal Finansial
│   │   │       ├── PmbDashboardController.php          # Dasbor Penerimaan Mahasiswa Baru
│   │   │       ├── AcademicLeaveController.php         # Pengajuan & Approval Cuti Akademik
│   │   │       ├── LmsExamController.php               # Ujian Online LMS & Gate Presensi 75%
│   │   │       ├── FileStorageController.php           # Unduhan Aman & URL Tertanda (Signed URLs)
│   │   │       ├── NotificationController.php          # Pusat Notifikasi Real-Time In-App
│   │   │       ├── AuditLogExplorerController.php      # Penelusuran Jejak Audit Eksekutif
│   │   │       ├── MbkmConversionController.php        # Konversi 20-SKS MBKM Mahasiswa
│   │   │       ├── GradeAppealController.php           # Transparansi Skor & Sanggah Nilai
│   │   │       ├── SmartAdvisorController.php          # AI Copilot Rekomendasi SKS & IPK
│   │   │       ├── BulkDataController.php              # Impor/Ekspor Massal CSV/Excel
│   │   │       ├── CampusLifeController.php            # E-Library, Lab Booking, Tracer Study
│   │   │       ├── SecurityProfileController.php       # Telemetri Sesi Aktif & Keamanan Akun
│   │   │       ├── IamAccountController.php            # Manajemen Akun & Matriks Hak Akses
│   │   │       ├── GovernanceApprovalController.php    # Dual-Control Maker-Checker Approval
│   │   │       ├── AcademicConsultationController.php  # Live Chat Bimbingan KRS & Skripsi
│   │   │       ├── HelpdeskTicketController.php        # SLA Helpdesk Ticketing Center
│   │   │       ├── CampusRoomOperationsController.php  # Okupansi Ruang & Generator Kursi Ujian
│   │   │       ├── DeveloperSandboxController.php      # Simulator Webhook & Cetak Biru Arsitektur
│   │   │       └── PrintDocumentController.php         # Generator Cetak Dokumen A4 Resmi
│   │   ├── Middleware/
│   │   │   ├── EnsureRoleScope.php            # Isolasi URL Prefix Antar Persona
│   │   │   ├── EnsureAccountIsActive.php      # Pengecekan Status Aktif Akun (Zero-Trust)
│   │   │   ├── EnforcePrivilegeMiddleware.php # Pengecekan Granular Permission & ABAC
│   │   │   └── RedirectByRole.php             # Pengarah Otomatis /portal Sesuai Peran
│   ├── Models/                                # 30+ Eloquent Models with Auditable Trait
│   │   ├── User.php, Role.php, Permission.php, AuditLog.php
│   │   ├── Faculty.php, StudyProgram.php, AcademicPeriod.php, Room.php
│   │   ├── Student.php, Lecturer.php, Applicant.php, AdmissionWave.php
│   │   ├── Curriculum.php, Course.php, CoursePrerequisite.php, ObeCpl.php, ObeCpmk.php
│   │   ├── ClassSection.php, KrsSubmission.php, KrsItem.php
│   │   ├── LectureSession.php, Attendance.php, LmsAssignment.php, LmsSubmission.php
│   │   ├── StudentInvoice.php, AcademicLeaveRequest.php, MbkmProgram.php, MbkmConversion.php
│   │   ├── GradeAppeal.php, Thesis.php, ThesisGuidanceLog.php, YudisiumClearance.php
│   │   ├── LibraryLoan.php, FacilityBooking.php, TracerStudy.php, InAppNotification.php
│   │   ├── ApprovalRequest.php, ConsultationMessage.php, SupportTicket.php, TicketReply.php
│   │   ├── OutboundDispatch.php, PddiktiSyncLog.php
│   ├── Services/
│   │   └── OutboundNotificationService.php    # WhatsApp Gateway & Official Email Dispatcher
├── database/
│   ├── migrations/                            # 9 Terstruktur Migration Files
│   └── seeders/                               # Realistic Seeders (DatabaseSeeder, RolePermissionSeeder, Stage16)
├── resources/
│   ├── css/app.css                            # Modern Design Tokens, Fonts, Animations
│   ├── js/                                    # Frontend Interactivity & Modular JS
│   │   ├── app.js                             # Alpine Initialization & Global Toast Helper
│   │   └── modules/                           # KRS Cart, Attendance Scan, Spreadsheet, CBT
│   └── views/
│       ├── layouts/portal.blade.php           # Unified Master Portal Layout (Bento Grid)
│       ├── layouts/public.blade.php           # Marketing & Public Document Layout
│       ├── components/                        # Blade Components (Sidebar, Bell, Drawers, Modals)
│       └── portal/                            # Scoped Blade Workspaces per Role
├── routes/
│   ├── web.php                                # 240+ Route Monolith Definitions
│   └── console.php                            # Artisan Commands (erp:health-check, pddikti:sync)
└── tests/
    └── Feature/                               # 35 Automated Test Suites (440+ Assertions)
```

---

## SECTION 3: DIAGRAM RELASI DATABASE (ERD & DATABASE SCHEMA MATRIX)

Seluruh entitas database dikelompokkan ke dalam **5 Domain Fungsional Terpadu**:

```
+====================================================================================================+
| DOMAIN 1: CORE & IAM                                                                               |
+====================================================================================================+
  +------------------+         +------------------+         +-------------------------+
  |      roles       | 1     * | role_permission  | *     1 |       permissions       |
  |------------------|◄───────►|------------------|◄───────►|-------------------------|
  | id (UUID) [PK]   |         | role_id (UUID)   |         | id (UUID) [PK]          |
  | name (slug)      |         | permission_id    |         | slug, name, module      |
  | display_name     |         +------------------+         +-------------------------+
  | level (10-100)   |                                                   ▲
  +--------┬---------+                                                   │ *
           │ 1                                                           │
           │                                                +------------┴------------+
           │ *                                              | user_custom_permissions |
  +--------┴---------+         +------------------+         |-------------------------|
  |      users       | 1     * |    audit_logs    |         | user_id (UUID) [FK]     |
  |------------------|◄───────►|------------------|         | permission_id (UUID)    |
  | id (UUID) [PK]   |         | id (UUID) [PK]   |         | is_granted (boolean)    |
  | email (unique)   |         | user_id (UUID)   |         +-------------------------+
  | username (unique)|         | action, details  |
  | password (hash)  |         | ip_address       |         +-------------------------+
  | role (enum)      |         | user_agent       |         |          rooms          |
  | role_id (FK)     |         | old_values (JSON)|         |-------------------------|
  | faculty_id (FK)  |         | new_values (JSON)|         | id (UUID) [PK]          |
  | study_program_id |         +------------------+         | code, name, capacity    |
  | is_active (bool) |                                      | building, latitude, long|
  +--------┬---------+                                      +-------------------------+
           │
           ├───────────────────────────────┬───────────────────────────────┐
           │ 1                             │ 1                             │ 1
           ▼ 1                             ▼ 1                             ▼ 1
  +------------------+            +------------------+            +------------------+
  |     students     |            |    lecturers     |            |    applicants    |
  |------------------|            |------------------|            |------------------|
  | id (UUID) [PK]   |            | id (UUID) [PK]   |            | id (UUID) [PK]   |
  | user_id (FK)     |            | user_id (FK)     |            | user_id (FK)     |
  | nim (unique)     |            | nidn (unique)    |            | registration_no  |
  | current_ipk      |            | academic_title   |            | cbt_score        |
  | financial_hold   |            | max_sks_capacity |            | admission_status |
  +------------------+            +------------------+            +------------------+

+====================================================================================================+
| DOMAIN 2: ADMISSIONS & OBE CURRICULUM                                                              |
+====================================================================================================+
  +------------------+ 1        * +------------------+ 1        * +------------------+
  |    faculties     |◄──────────►|  study_programs  |◄──────────►|   curriculums    |
  |------------------|            |------------------|            |------------------|
  | id (UUID) [PK]   |            | id (UUID) [PK]   |            | id (UUID) [PK]   |
  | code, name_id    |            | faculty_id (FK)  |            | study_program_id |
  +------------------+            | code, name_id    |            | year, is_locked  |
                                  +------------------+            +--------┬---------+
                                                                           │ 1
                                           ┌───────────────────────────────┴──────────┐
                                           │ *                                        │ *
                                  +--------▼---------+                       +--------▼---------+
                                  |     obe_cpls     | 1                   * |     courses      |
                                  |------------------|◄─────────────────────►|------------------|
                                  | id (UUID) [PK]   |      (via CPMK)       | id (UUID) [PK]   |
                                  | curriculum_id    |                       | curriculum_id    |
                                  | code, category   |                       | code, name_id    |
                                  | description_id   |                       | credits_total    |
                                  +--------┬---------+                       | weight_presence  |
                                           │ 1                               | weight_task      |
                                           │                                 | weight_uts/uas   |
                                           ▼ *                               +--------┬---------+
                                  +------------------+                                │ 1
                                  |    obe_cpmks     | *                            1 │
                                  |------------------|◄───────────────────────────────┘
                                  | id (UUID) [PK]   |
                                  | course_id (FK)   |
                                  | obe_cpl_id (FK)  |
                                  | weight_percentage|
                                  +------------------+

+====================================================================================================+
| DOMAIN 3: ACADEMIC OPERATIONS, KRS & LMS                                                           |
+====================================================================================================+
  +------------------+ 1        * +------------------+ 1        * +------------------+
  |  class_sections  |◄──────────►|  krs_submissions |◄──────────►|    krs_items     |
  |------------------|            |------------------|            |------------------|
  | id (UUID) [PK]   |            | id (UUID) [PK]   |            | id (UUID) [PK]   |
  | course_id (FK)   |            | student_id (FK)  |            | krs_sub_id (FK)  |
  | lecturer_id (FK) |            | academic_period  |            | class_section_id |
  | room_id (FK)     |            | status (approved)|            | final_numeric    |
  | is_grade_locked  |            +------------------+            | final_letter     |
  +--------┬---------+                                            | edom_submitted   |
           │ 1                                                    | exam_seat_number |
           ├───────────────────────────────┐                      +------------------+
           │ *                             │ *
  +--------▼---------+            +--------▼---------+ 1        * +------------------+
  | lecture_sessions | 1        * |  lms_assignments |◄──────────►| lms_submissions  |
  |------------------|◄──────────►|------------------|            |------------------|
  | id (UUID) [PK]   |            | id (UUID) [PK]   |            | id (UUID) [PK]   |
  | class_section_id |            | session_id (FK)  |            | assignment_id    |
  | session_number   |            | title, due_date  |            | student_id (FK)  |
  | qr_token_active  |            +------------------+            | score, file_path |
  | bap_content      |                                            +------------------+
  +--------┬---------+
           │ 1
           ▼ *
  +------------------+
  |   attendances    |
  |------------------|
  | id (UUID) [PK]   |
  | session_id (FK)  |
  | student_id (FK)  |
  | status (hadir)   |
  | verified_gps (T) |
  +------------------+

+====================================================================================================+
| DOMAIN 4: FINANCE, MBKM, THESIS & CAMPUS LIFE                                                      |
+====================================================================================================+
  +------------------+            +------------------+            +------------------+
  | student_invoices |            |  mbkm_programs   | 1        * | mbkm_conversions |
  |------------------|            |------------------|◄──────────►|------------------|
  | id (UUID) [PK]   |            | id (UUID) [PK]   |            | id (UUID) [PK]   |
  | student_id (FK)  |            | student_id (FK)  |            | mbkm_program_id  |
  | va_number, amount|            | company_name     |            | course_id (FK)   |
  | payment_status   |            | status (approved)|            | equivalent_sks   |
  +------------------+            +------------------+            +------------------+

  +------------------+ 1        * +----------------------+        +----------------------+
  |      theses      |◄──────────►| thesis_guidance_logs |        | yudisium_clearances  |
  |------------------|            |----------------------|        |----------------------|
  | id (UUID) [PK]   |            | id (UUID) [PK]       |        | id (UUID) [PK]       |
  | student_id (FK)  |            | thesis_id (FK)       |        | student_id (FK)      |
  | title, status    |            | session_number (1-8) |        | library_cleared (T)  |
  | defense_score    |            | is_verified (bool)   |        | finance_cleared (T)  |
  +------------------+            +----------------------+        | prodi_cleared (T)    |
                                                                  | national_diploma_pin |
                                                                  +----------------------+

  +-----------------------+       +-----------------------+       +-----------------------+
  |     library_loans     |       |   facility_bookings   |       |    tracer_studies     |
  |-----------------------|       |-----------------------|       |-----------------------|
  | id (UUID) [PK]        |       | id (UUID) [PK]        |       | id (UUID) [PK]        |
  | student_id (UUID)[FK] |       | student_id (UUID)[FK] |       | student_id (UUID)[FK] |
  | book_isbn, book_title |       | room_id (UUID)[FK]    |       | graduation_year       |
  | borrowed_at, due_date |       | purpose, start_time   |       | employment_status     |
  | returned_at, fine_due |       | end_time, status      |       | company_name, income  |
  +-----------------------+       +-----------------------+       +-----------------------+

  +-----------------------+       +-----------------------+
  | student_akm_histories |       |academic_leave_requests|
  |-----------------------|       |-----------------------|
  | id (UUID) [PK]        |       | id (UUID) [PK]        |
  | student_id (UUID)[FK] |       | student_id (UUID)[FK] |
  | academic_period_id[FK]|       | reason, start_period  |
  | ips, ipk, sks_semester|       | advisor_status        |
  | status_mahasiswa (A/C)|       | prodi_status (appr)   |
  +-----------------------+       +-----------------------+

+====================================================================================================+
| DOMAIN 5: ENTERPRISE GOVERNANCE, REAL-TIME COMMUNICATION & SANDBOX                                 |
+====================================================================================================+
  +-----------------------+       +-----------------------+       +-----------------------+
  |   approval_requests   |       | consultation_messages |       |    support_tickets    |
  |-----------------------|       |-----------------------|       |-----------------------|
  | id (UUID) [PK]        |       | id (UUID) [PK]        |       | id (UUID) [PK]        |
  | request_code (unique) |       | krs_submission_id (FK)|       | ticket_number (unique)|
  | workflow_type         |       | sender_user_id (FK)   |       | user_id (FK)          |
  | maker_user_id (FK)    |       | recipient_user_id (FK)|       | department_target     |
  | checker_role_required |       | message (text)        |       | priority (high/normal)|
  | payload_diff (JSON)   |       | is_read (bool)        |       | sla_due_at (timestamp)|
  | status (pending/appr) |       +-----------------------+       | status (open/resolved)|
  | checker_user_id (FK)  |                                       +-----------┬-----------+
  | resolved_at           |                                                   │ 1
  +-----------------------+                                                   ▼ *
                                                                  +-----------------------+
  +-----------------------+       +-----------------------+       |    ticket_replies     |
  |  outbound_dispatches  |       |   pddikti_sync_logs   |       |-----------------------|
  |-----------------------|       |-----------------------|       | id (UUID) [PK]        |
  | id (UUID) [PK]        |       | id (UUID) [PK]        |       | support_ticket_id     |
  | channel (wa/email)    |       | entity_type (AKM/Mhs) |       | user_id (FK)          |
  | recipient_target      |       | records_pushed        |       | message, is_staff     |
  | message_preview       |       | sync_status (SUCCESS) |       +-----------------------+
  | is_delivered (bool)   |       +-----------------------+
  +-----------------------+
                                  +-----------------------+       +-----------------------+
                                  | in_app_notifications  |       |     grade_appeals     |
                                  |-----------------------|       |-----------------------|
                                  | id (UUID) [PK]        |       | id (UUID) [PK]        |
                                  | user_id (UUID) [FK]   |       | krs_item_id (UUID)[FK]|
                                  | title, message, url   |       | reason_description    |
                                  | is_read (boolean)     |       | lecturer_feedback     |
                                  | created_at, updated_at|       | status (open/resolved)|
                                  +-----------------------+       +-----------------------+
```

---

## SECTION 4: MATRIKS 9 PERSONA, KREDENSIAL & HAK AKSES (IAM & RBAC+ABAC MATRIX)

Sistem mengamankan 9 peran resmi dengan hierarki level wewenang (Level 10 sampai 100). Seluruh akun demo dapat digunakan langsung untuk evaluasi pengujian.

| Persona / Peran | Default User Email / NIM / NIDN | Kata Sandi (Password) | Level RBAC | Ruang Lingkup ABAC (Isolation Scope) | Default Landing URL | Hak Akses Utama (Granular Permission Slugs) |
|---|---|---|---|---|---|---|
| **1. Super Administrator** | `superadmin@uph.edu` / `superadmin` | `password` | 100 | Global / Seluruh Modul & Fakultas | `/portal/iam/users` | Seluruh permission (`*`), bypass semua gate, override privilege, view raw audit logs. |
| **2. Rektor & Eksekutif** | `rector@uph.edu` / `rector` | `password` | 90 | Universitas / Seluruh Program Studi | `/portal/executive/dashboard` | `executive.view.kpi`, `dean.view.faculty`, `pddikti.sync.execute`, EDSS Analytics. |
| **3. Dekan Fakultas** | `dean.fik@uph.edu` / `dean.fik` | `password` | 80 | Terbatas Fakultas (`faculty_id = FIK`) | `/portal/dean/dashboard` | `dean.view.faculty`, `executive.view.kpi`, `yudisium.verify.checkpoint`, monitoring BKD Dekanat. |
| **4. Kaprodi** | `kaprodi.if@uph.edu` / `kaprodi.if` | `password` | 70 | Terbatas Prodi (`study_program_id = IF`) | `/portal/kaprodi/dashboard` | `curriculum.manage.prodi`, `grades.unlock.prodi`, `grades.lock`, `mbkm.approve.prodi`, `krs.override.quota`. |
| **5. BAAK (Akademik)** | `baak@uph.edu` / `staf.baak` | `password` | 60 | Global Akademik, Jadwal & Kalender | `/portal/baak/dashboard` | `calendar.manage`, `krs.override.quota`, `yudisium.verify.checkpoint`, `pddikti.sync.execute`, `iam.users.manage`. |
| **6. BAUK (Keuangan)** | `bauk@uph.edu` / `staf.bauk` | `password` | 60 | Keuangan Mahasiswa & Rekonsiliasi | `/portal/bauk/dashboard` | `finance.verify.payment`, `finance.toggle.hold`, `yudisium.verify.checkpoint`, approval waiver SPP. |
| **7. Dosen Pengajar / PA**| `lecturer1@uph.edu` / `johanes.tan` | `password` | 50 | Kelas Binaan & Mahasiswa PA | `/portal/lecturer/dashboard` | `krs.approve.advisee`, `attendance.generate_qr`, `attendance.bap_fill`, `grades.input.own_class`, `thesis.supervise`. |
| **8. Mahasiswa Aktif** | `student1@uph.edu` / `01082200001` | `password` | 20 | Data Pribadi & KRS Binaan | `/portal/student/dashboard` | `krs.submit`, `attendance.scan_qr`, `finance.view.own`, submit tugas LMS, sanggah nilai, exam card. |
| **9. Calon Mhs (PMB)** | `applicant1@uph.edu` / `reg2026001` | `password` | 10 | Berkas PMB & Ujian CBT Mandiri | `/portal/pmb/dashboard` | `cbt.take.exam`, `loa.download`, `finance.view.own`, registrasi gelombang PMB. |

> **Catatan Akun Uji Khusus:**
> - Akun Mahasiswa Tercekal Finansial: `student.blocked@uph.edu` (NIM: `01082200002` / `password`). Digunakan untuk menguji kegagalan gate KRS War dan Kartu Ujian ketika tagihan SPP belum lunas.

---

## SECTION 5: DIAGRAM ALIR PROSES BISNIS UTAMA (CORE ENTERPRISE WORKFLOWS)

### FLOW 1: PMB, VIRTUAL ACCOUNT, CBT & PROMOSI MAHASISWA BARU
Alur penerimaan calon mahasiswa dari formulir pendaftaran hingga terbit NIM dan migrasi akun sivitas.

```
+─────────────────────+
|   Calon Mahasiswa   | Mengisi Formulir PMB & Pilihan Program Studi di /admissions/pmb/register
+──────────┬──────────+
           │
           ▼
+───────────────────────────────────────────────────────────────────────────────+
| ApplicantController@storeRegistration                                         |
|  - Validasi data identitas, NIK, asal sekolah, jalur beasiswa                 |
|  - Buat Applicant record (status: 'registered')                               |
|  - Generate Nomor Virtual Account Bank BCA/Mandiri (16 digit)                 |
|  - Hitung Tagihan Formulir (Rp 500.000)                                       |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| SIMULASI PEMBAYARAN VA / WEBHOOK GATEWAY                                      |
|  - PaymentWebhookController@handleWebhook ATAU /pmb/simulate-payment          |
|  - Verifikasi HMAC Signature SHA-256                                          |
|  - Mutasi status: payment_status = 'paid', admission_status = 'paid'          |
|  - OutboundDispatch: Kirim Notifikasi WhatsApp & Email Tanda Terima           |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| COMPUTER-BASED TESTING (CBT) EXAM GATEWAY (/pmb/cbt)                          |
|  - CbtExamController@show: Gate verifikasi status lunas                       |
|  - Ujian Online 50 Soal Penalaran, Matematika & Bahasa Inggris                |
|  - CbtExamController@submit: Kalkulasi otomatis skor 0-100                    |
|  - Rekomendasi Beasiswa:                                                      |
|    * Skor >= 85 ──► Platinum Scholarship (Bebas Biaya Masuk 100%)             |
|    * Skor >= 70 ──► Gold Scholarship (Potongan 50%)                           |
|    * Skor < 70  ──► Reguler                                                   |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| ADMISSIONS PROMOTION & NIM GENERATION                                         |
|  - ApplicantController@promote (Executed by BAAK / Admissions Office)         |
|  - Generate Official NIM: {Tahun:4}{KodeProdi:3}{NomorUrut:4}                 |
|  - Atomik buat User Sivitas (role: 'student') & Student Profile Record        |
|  - Buat Tagihan SPP Semester 1 di student_invoices                            |
|  - Terbitkan Surat Penerimaan Resmi: /portal/print/loa (LoA PDF)              |
+───────────────────────────────────────────────────────────────────────────────+
```

---

### FLOW 2: BILLING BAUK, KRS WAR, LIVE CHAT PA & ALOKASI KURSI UJIAN
Siklus pendaftaran rencana studi dengan pencegahan bentrok jadwal dan persetujuan dosen wali.

```
+───────────────────────────────────────────────────────────────────────────────+
| BAUK BILLING CHECKPOINT & FINANCIAL HOLD RELEASE                              |
|  - BAUK mengunggah tagihan semester di /portal/bauk/billing                   |
|  - Mahasiswa melunasi via Virtual Account                                     |
|  - BaukBillingController@verifyPayment / toggleHold                           |
|  - Status Mahasiswa: financial_hold = false                                   |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| KRS WAR ENROLLMENT ENGINE (/portal/student/krs)                               |
|  - Gate 1: Cek Batas Waktu Kalender Akademik (academic_periods.krs_start/end)  |
|  - Gate 2: Cek Financial Hold (Abort 403 jika financial_hold == true)         |
|  - Gate 3: Cek Prasyarat Mata Kuliah (Course Prerequisites Clearance)         |
|  - Concurrency Lock: DB::table('class_sections')->lockForUpdate()             |
|  - Cek Sisa Kuota Kelas (total_enrolled < capacity)                           |
|  - Deteksi Bentrok Jadwal Otomatis (Hari & Rentang Waktu Jam Kuliah)          |
|  - Tambah KrsItem (status: 'draft')                                           |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| THREADED PA CONSULTATION & KRS SUBMISSION                                     |
|  - Mahasiswa & Dosen PA berdiskusi di Consultation Drawer (/consultation)     |
|  - Mahasiswa klik "Kirim Rencana Studi ke Dosen PA" (status: 'submitted')     |
|  - Dosen PA membuka /portal/lecturer/advisees:                                |
|    * Dosen PA klik "Setujui KRS" ──► KrsSubmission: status = 'approved'       |
|    * Mutasi seluruh KrsItem: status = 'approved'                              |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| ANTI-CHEATING EXAM SEAT NUMBER ALLOCATOR                                      |
|  - BAAK klik "Generate Nomor Kursi Ujian" (/portal/baak/rooms-occupancy)      |
|  - Evaluasi Mahasiswa: Syarat Kehadiran >= 75% DAN Bebas Cekal Finansial      |
|    * Lolos Syarat ──► Alokasi Koordinat Acak: ROW-{A-H} / SEAT-{1-40}         |
|    * Gagal Syarat ──► Label BLOCKED (ATTENDANCE < 75% / FINANCIAL HOLD)       |
|  - Tampil di Kartu Ujian Resmi: /portal/print/krs & /portal/student/exam-card |
+───────────────────────────────────────────────────────────────────────────────+
```

---

### FLOW 3: PRESENSI QR 15 DETIK, GATE 75%, UJIAN LMS, GRADING OBE & SANGGAH NILAI
Pelaksanaan perkuliahan 16 sesi, presensi geofence, ujian LMS, pembobotan nilai OBE, dan hak banding nilai.

```
+───────────────────────────────────────────────────────────────────────────────+
| 1. ROTATING QR TOKEN 15 DETIK & PENGISIAN BAP                                 |
|    - Dosen membuka /portal/lecturer/sessions                                  |
|    - LecturerSessionController@rotateQr: Generate dynamic SHA token 15s       |
|    - Dosen mengisi Berita Acara Perkuliahan (BAP) materi & capaian sesi       |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 2. STUDENT GPS GEOFENCE SCANNING                                              |
|    - Mahasiswa scan QR via kamera di /portal/student/attendance               |
|    - Kalkulasi Geofence Haversine Formula: Jarak koordinat GPS <= 100 meter   |
|    - Validasi token expired (toleransi maks 15 detik)                         |
|    - Catat Attendance record (status: 'hadir', verified_gps: true)            |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 3. 75% ATTENDANCE GATE & ONLINE LMS EXAM RUNNER                               |
|    - Sesi 8 (UTS) dan Sesi 16 (UAS) di /portal/student/lms/exam/{classSection}|
|    - Gate Evaluasi Presensi: (Total Hadir / 16 Sesi) >= 75.0%                 |
|      * Persentase < 75% ──► TERBLOKIR OTOMATIS (Hubungi Kaprodi untuk izin)   |
|      * Persentase >= 75%──► Buka Soal Ujian Online LMS & Countdown Timer      |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 4. SPREADSHEET PENILAIAN OBE & KUNCI NILAI SEMESTER                           |
|    - Dosen membuka Grade Sheet Spreadsheet di /portal/lecturer/grading        |
|    - Input Bobot OBE: Presensi (10%), Tugas (20%), UTS (30%), UAS (40%) = 100%|
|    - Konversi Otomatis Nilai Akhir ke Huruf Mutu (A, A-, B+, B, C, D, E)      |
|    - Dosen klik "Kunci Nilai Akhir" (class_sections.is_grade_locked = true)   |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 5. GATE KUESIONER EDOM & KHS SCORE BREAKDOWN                                  |
|    - Mahasiswa membuka /portal/student/khs                                    |
|    - Nilai disamarkan bintang (***) sebelum mengisi Kuesioner Evaluasi Dosen  |
|    - Mahasiswa mengisi EDOM (/edom/{krsItem}): Nilai langsung terbuka terang  |
|    - Klik "Transparansi Skor": Tampil modal rincian bobot Tugas, UTS, UAS     |
|    - Jika ada ketidaksesuaian: Klik "Ajukan Sanggah Nilai" (Grade Appeal)     |
|    - Dosen Pengampu menanggapi sanggah di /portal/lecturer/appeals            |
+───────────────────────────────────────────────────────────────────────────────+
```

---

### FLOW 4: MAKER-CHECKER DUAL-CONTROL APPROVAL ENGINE
Mekanisme pengamanan mutasi risiko tinggi perbankan (Two-Person Authorization).

```
+───────────────────────────────────────────────────────────────────────────────+
| MAKER INITIATES HIGH-RISK MUTATION                                            |
| Contoh:                                                                       |
|  - Dosen meminta koreksi nilai setelah nilai terkunci (grade_change_after_lock)|
|  - BAUK mengajukan diskon/beasiswa SPP > 50% (tuition_waiver_or_discount)     |
|  - BAAK meminta override kuota/KRS susulan (krs_late_override)                |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| CREATE APPROVAL REQUEST (STATUS: 'PENDING')                                   |
|  - Model: ApprovalRequest::create(...)                                        |
|  - Simpan payload_diff: { "old": {"grade": "C"}, "proposed": {"grade": "A"} }|
|  - Target Role Required: 'kaprodi', 'dean', atau 'superadmin'                 |
|  - Zero-Trust Invariant: Maker dilarang meng-approve permintaannya sendiri    |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| CHECKER AUDIT INBOX (/portal/governance/approvals)                            |
|  - Checker login sebagai Kaprodi, Dekan, atau Superadmin                      |
|  - Tampil JSON Diff Viewer Berwarna: Nilai Semula (Merah) vs Usulan (Hijau)   |
|  - Pilihan Tindakan:                                                          |
|    1. TOLAK (Reject): Input alasan penolakan, status = 'rejected'             |
|    2. SETUJUI & EKSEKUSI ATOMIK (Approve & Execute):                          |
|       * DB::transaction()                                                     |
|       * Mutasi data target secara langsung                                    |
|       * status = 'approved', resolved_at = now()                              |
|       * Audit Log mencatat kedua aktor: Maker User ID & Checker User ID       |
+───────────────────────────────────────────────────────────────────────────────+
```

---

### FLOW 5: KONVERSI MBKM 20 SKS, SKRIPSI, YUDISIUM 4-PINTU & SKPI BILINGUAL
Kelulusan mahasiswa, kliring bebas pinjam perpustakaan, dan penerbitan PIN Ijazah Nasional.

```
+───────────────────────────────────────────────────────────────────────────────+
| 1. MBKM 20-SKS CONVERSION WORKFLOW                                            |
|    - Mahasiswa upload LoA Magang / Studi Independen di /portal/student/mbkm   |
|    - Kaprodi menyetujui pemetaan ke 20 SKS mata kuliah ekuivalen              |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 2. SKRIPSI & 8-LOGBOOK VERIFICATION                                           |
|    - Mahasiswa mengajukan judul skripsi & penugasan dosen pembimbing          |
|    - Pengisian 8 log bimbingan terverifikasi oleh Dosen Pembimbing            |
|    - Penjadwalan Sidang Skripsi & Input Nilai Sidang Dewan Penguji            |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 3. VERIFIKASI 4-PINTU YUDISIUM (/portal/student/yudisium)                     |
|    - Checkpoint 1 (Perpustakaan): Bebas pinjaman buku & unggah karya ilmiah   |
|    - Checkpoint 2 (BAUK): Bebas seluruh tunggakan biaya kuliah & denda        |
|    - Checkpoint 3 (Kaprodi): Lulus min 144 SKS, tidak ada nilai E, IPK >= 2.0 |
|    - Checkpoint 4 (BAAK / Rektorat): Penerbitan SK Yudisium & PIN Ijazah      |
+──────────────────────────────────────┬────────────────────────────────────────+
                                       │
                                       ▼
+───────────────────────────────────────────────────────────────────────────────+
| 4. PENERBITAN PIN NASIONAL & SKPI DIGITAL BILINGUAL                           |
|    - Terbit Nomor Registrasi PIN Ijazah Nasional Kemenristekdikti             |
|    - Cetak Surat Keterangan Pendamping Ijazah (SKPI) Resmi:                   |
|      /portal/print/skpi (Bilingual ID/EN dengan QR Code Verifikasi SHA-256)   |
|    - Verifikasi Publik Instan: /verify/document/skpi/{hash}                   |
+───────────────────────────────────────────────────────────────────────────────+
```

---

## SECTION 6: MASTER INDEKS RUTE & CONTROLLER (COMPLETE ROUTE INDEX)

Daftar seluruh 240+ endpoint monolitik yang dikelompokkan berdasarkan fungsionalitas dan peran:

### 1. Public Marketing, PMB & Document Verification
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/` | `GET` | `home` | `PublicPortalController@index` | `public.home` |
| `/programs` | `GET` | `programs` | `PublicPortalController@programs` | `public.programs` |
| `/academic/undergraduate` | `GET` | `academic.undergraduate` | `PublicPortalController@undergraduate` | `public.undergraduate` |
| `/academic/pascasarjana` | `GET` | `academic.pascasarjana` | `PublicPortalController@pascasarjana` | `public.pascasarjana` |
| `/academic/kelas-karyawan` | `GET` | `academic.kelas-karyawan` | `PublicPortalController@kelasKaryawan` | `public.kelas-karyawan` |
| `/events` | `GET` | `events` | `PublicPortalController@events` | `public.events` |
| `/curriculum-preview/{studyProgram}` | `GET` | `curriculum.preview` | `PublicPortalController@curriculumPreview` | `public.curriculum-preview` |
| `/verify/document/{type}/{hash}` | `GET` | `document.verify.public` | `PublicDocumentVerificationController@verify` | `public.verify-document` |
| `/admissions/pmb/register` | `GET` | `admissions.pmb.register` | `ApplicantController@showRegistrationForm` | `pmb.register` |
| `/admissions/pmb/register` | `POST`| `admissions.pmb.register.store` | `ApplicantController@storeRegistration` | Redirect / Status |
| `/pmb/register` | `GET` | `pmb.register` | `ApplicantController@showRegistrationForm` | `pmb.register` |
| `/pmb/register` | `POST`| `pmb.register.store` | `ApplicantController@storeRegistration` | Redirect / Status |
| `/pmb/applicant/{applicant}` | `GET` | `pmb.dashboard` | `ApplicantController@dashboard` | `pmb.status` |
| `/pmb/applicant/{applicant}/promote` | `POST` | `pmb.promote` | `ApplicantController@promote` | Redirect / Flash |
| `/pmb/simulate-payment/{applicant}` | `POST` | `pmb.simulate-payment` | `ApplicantController@simulatePayment` | Redirect / Flash |
| `/pmb/cbt/{applicant?}` | `GET` | `pmb.cbt` | `CbtExamController@show` | `pmb.cbt-exam` |
| `/pmb/cbt/{applicant?}/submit` | `POST` | `pmb.cbt.submit` | `CbtExamController@submit` | JSON / Redirect |

### 2. Autentikasi, SSO & Single Sign-On
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/login` | `GET` | `login` | `AuthController@showLoginForm` | `auth.login` |
| `/login` | `POST` | `login.attempt` | `AuthController@login` | Redirect by Role |
| `/login/quick/{role}` | `GET` | `login.quick` | `AuthController@quickLogin` | Redirect by Role |
| `/auth/switch/{accountKey}` | `GET` | `auth.quick-switch` | `QuickSwitchAccountController@switch` | Redirect / Switch |
| `/logout` | `POST` | `logout` | `AuthController@logout` | Redirect `/login` |
| `/portal` | `GET` | `portal.dashboard` | `AuthController@portalRouter` | Redirect by Persona |

### 3. Portal Mahasiswa (`/portal/student/*`)
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/portal/student/dashboard` | `GET` | `portal.student.dashboard` | `StudentDashboardController@index` | `portal.student.dashboard` |
| `/portal/student/krs` | `GET` | `portal.student.krs` | `KrsController@index` | `krs.student-cart` |
| `/portal/student/exam-card` | `GET` | `portal.student.exam-card` | `CampusRoomOperationsController@examCard` | `portal.student.exam-card` |
| `/portal/student/attendance` | `GET` | `portal.student.attendance` | `QrAttendanceController@scanPage` | `attendance.student-scan` |
| `/portal/student/lms` | `GET` | `portal.student.lms` | `LmsController@index` | `lms.index` |
| `/portal/student/lms/exam/{classSection}` | `GET` | `portal.student.lms.exam` | `LmsExamController@showExam` | `lms.exam-runner` |
| `/portal/student/lms/exam/{classSection}/submit` | `POST` | `portal.student.lms.exam.submit` | `LmsExamController@submitExam` | JSON / Redirect |
| `/portal/student/khs` | `GET` | `portal.student.khs` | `KhsController@index` | `khs.index` |
| `/portal/student/khs/breakdown/{krsItem}` | `GET` | `portal.student.khs.breakdown` | `GradeAppealController@getBreakdown` | JSON Score Breakdown |
| `/portal/student/khs/appeal/{krsItem}` | `POST` | `portal.student.khs.appeal` | `GradeAppealController@submitAppeal` | JSON / Redirect |
| `/portal/student/finance` | `GET` | `portal.student.finance` | `BillingController@index` | `finance.index` |
| `/portal/student/thesis` | `GET` | `portal.student.thesis` | `ThesisController@index` | `thesis.index` |
| `/portal/student/yudisium` | `GET` | `portal.student.yudisium` | `YudisiumController@index` | `yudisium.index` |
| `/portal/student/mbkm` | `GET` | `portal.student.mbkm` | `MbkmConversionController@studentIndex` | `portal.student.mbkm` |
| `/portal/student/mbkm` | `POST` | `portal.student.mbkm.store` | `MbkmConversionController@storeProgram` | Redirect / Flash |
| `/portal/student/leave-request` | `GET` | `portal.student.leave-request` | `AcademicLeaveController@index` | `portal.student.leave-request` |
| `/portal/student/leave-request` | `POST` | `portal.student.leave-request.store`| `AcademicLeaveController@store` | Redirect / Flash |
| `/portal/student/smart-advisor` | `GET` | `portal.student.smart-advisor` | `SmartAdvisorController@index` | `portal.student.smart-advisor` |
| `/portal/student/smart-advisor/simulate` | `POST` | `portal.student.smart-advisor.simulate` | `SmartAdvisorController@simulate` | JSON Simulation |
| `/portal/student/campus-life` | `GET` | `portal.student.campus-life` | `CampusLifeController@campusLifeIndex` | `portal.student.campus-life` |
| `/portal/student/campus-life/book` | `POST` | `portal.student.campus-life.book` | `CampusLifeController@storeFacilityBooking` | Redirect / Flash |
| `/portal/student/career-tracer` | `GET` | `portal.student.career-tracer` | `CampusLifeController@careerTracerIndex` | `portal.student.career-tracer` |
| `/portal/student/career-tracer/submit` | `POST` | `portal.student.career-tracer.submit` | `CampusLifeController@submitTracerStudy` | Redirect / Flash |

### 4. Portal Dosen & Pembimbing (`/portal/lecturer/*`)
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/portal/lecturer/dashboard` | `GET` | `portal.lecturer.dashboard` | `LecturerDashboardController@index` | `portal.lecturer.dashboard` |
| `/portal/lecturer/sessions` | `GET` | `portal.lecturer.sessions` | `LecturerSessionController@index` | `portal.lecturer.sessions` |
| `/portal/lecturer/sessions/{session}/bap` | `POST` | `portal.lecturer.sessions.bap` | `LecturerSessionController@updateBap` | JSON / Flash |
| `/portal/lecturer/sessions/{session}/qr-token` | `GET` | `portal.lecturer.sessions.token` | `LecturerSessionController@rotateQr` | JSON QR Token |
| `/portal/lecturer/grading` | `GET` | `portal.lecturer.grading` | `GradeSheetController@index` | `grading.index` |
| `/portal/lecturer/advisees` | `GET` | `portal.lecturer.advisees` | `KrsController@advisorApprovalWorkspace`| `krs.advisor-approval` |
| `/portal/lecturer/appeals` | `GET` | `portal.lecturer.appeals` | `GradeAppealController@lecturerIndex` | `portal.lecturer.appeals` |
| `/portal/lecturer/appeal/{gradeAppeal}/respond` | `POST` | `portal.lecturer.appeal.respond` | `GradeAppealController@lecturerRespond` | Redirect / Flash |
| `/portal/lecturer/leave-request/{leaveRequest}/approve` | `POST` | `portal.lecturer.leave-request.approve` | `AcademicLeaveController@advisorApprove` | Redirect / Flash |
| `/portal/lecturer/bkd` | `GET` | `portal.lecturer.bkd` | `BkdController@index` | `bkd.index` |

### 5. Portal Kaprodi (`/portal/kaprodi/*`)
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/portal/kaprodi/dashboard` | `GET` | `portal.kaprodi.dashboard` | `KaprodiDashboardController@index` | `portal.kaprodi.dashboard` |
| `/portal/kaprodi/curriculum` | `GET` | `portal.kaprodi.curriculum` | `CurriculumController@index` | `curriculum.index` |
| `/portal/kaprodi/grade-locks` | `GET` | `portal.kaprodi.grade-locks` | `KaprodiGradeLockController@index` | `portal.kaprodi.grade-locks` |
| `/portal/kaprodi/grade-locks/{classSection}/unlock` | `POST` | `portal.kaprodi.grade-locks.unlock` | `KaprodiGradeLockController@unlock` | JSON / Flash |
| `/portal/kaprodi/grade-locks/{classSection}/lock` | `POST` | `portal.kaprodi.grade-locks.lock` | `KaprodiGradeLockController@lock` | JSON / Flash |
| `/portal/kaprodi/mbkm` | `GET` | `portal.kaprodi.mbkm` | `MbkmConversionController@kaprodiIndex` | `portal.kaprodi.mbkm` |
| `/portal/kaprodi/mbkm/{mbkmProgram}/approve` | `POST` | `portal.kaprodi.mbkm.approve` | `MbkmConversionController@approveConversion` | Redirect / Flash |
| `/portal/kaprodi/exam-unblock/{krsItem}` | `POST` | `portal.kaprodi.exam-unblock` | `LmsExamController@unblockExam` | Redirect / Flash |

### 6. Portal BAAK & BAUK (`/portal/baak/*`, `/portal/bauk/*`)
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/portal/baak/dashboard` | `GET` | `portal.baak.dashboard` | `BaakBaukDashboardController@baakDashboard` | `portal.baak.dashboard` |
| `/portal/baak/calendar` | `GET` | `portal.baak.calendar` | `BaakBaukDashboardController@calendar` | `portal.baak.calendar` |
| `/portal/baak/calendar/rollover` | `POST` | `portal.baak.calendar.rollover` | `BaakBaukDashboardController@rollover` | Redirect / Flash |
| `/portal/baak/pddikti` | `GET` | `portal.baak.pddikti` | `PDDIKTIFeederController@index` | `pddikti.index` |
| `/portal/baak/rooms-occupancy` | `GET` | `portal.baak.rooms-occupancy` | `CampusRoomOperationsController@occupancy` | `portal.baak.rooms-occupancy` |
| `/portal/baak/exam-seats/generate` | `POST` | `portal.baak.exam-seats.generate` | `CampusRoomOperationsController@generateExamSeats` | Redirect / Flash |
| `/portal/bauk/dashboard` | `GET` | `portal.bauk.dashboard` | `BaakBaukDashboardController@baukDashboard` | `portal.bauk.dashboard` |
| `/portal/bauk/billing` | `GET` | `portal.bauk.billing` | `BaukBillingController@index` | `portal.bauk.billing` |
| `/portal/bauk/billing/{invoice}/verify` | `POST` | `portal.bauk.billing.verify` | `BaukBillingController@verifyPayment` | JSON / Flash |
| `/portal/bauk/billing/student/{student}/toggle-hold` | `POST` | `portal.bauk.billing.toggle-hold` | `BaukBillingController@toggleHold` | Redirect / Flash |

### 7. Tata Kelola, IAM, Helpdesk & Sandbox (`/portal/governance/*`, `/portal/iam/*`, `/portal/developer/*`)
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/portal/iam/users` | `GET` | `portal.iam.users` | `IamAccountController@index` | `portal.iam.users` |
| `/portal/iam/users` | `POST` | `portal.iam.users.store` | `IamAccountController@storeUser` | Redirect / Flash |
| `/portal/iam/privileges` | `GET` | `portal.iam.privileges` | `IamAccountController@privileges` | `portal.iam.privileges` |
| `/portal/iam/privileges/override` | `POST` | `portal.iam.privileges.override` | `IamAccountController@overridePrivilege` | Redirect / Flash |
| `/portal/governance/approvals` | `GET` | `portal.governance.approvals` | `GovernanceApprovalController@index` | `portal.governance.approvals` |
| `/portal/governance/approvals` | `POST` | `portal.governance.approvals.store` | `GovernanceApprovalController@store` | Redirect / Flash |
| `/portal/governance/approvals/{approvalRequest}/approve` | `POST` | `portal.governance.approvals.approve` | `GovernanceApprovalController@approve` | Redirect / Flash |
| `/portal/governance/approvals/{approvalRequest}/reject` | `POST` | `portal.governance.approvals.reject` | `GovernanceApprovalController@reject` | Redirect / Flash |
| `/portal/consultation/messages` | `GET` | `portal.consultation.messages` | `AcademicConsultationController@index` | JSON Messages |
| `/portal/consultation/messages` | `POST` | `portal.consultation.messages.store` | `AcademicConsultationController@store` | JSON Message |
| `/portal/helpdesk` | `GET` | `portal.helpdesk.index` | `HelpdeskTicketController@index` | `portal.helpdesk.index` |
| `/portal/helpdesk/tickets` | `POST` | `portal.helpdesk.store` | `HelpdeskTicketController@store` | Redirect / Flash |
| `/portal/helpdesk/tickets/{ticket}` | `GET` | `portal.helpdesk.show` | `HelpdeskTicketController@show` | `portal.helpdesk.show` |
| `/portal/developer/sandbox` | `GET` | `portal.developer.sandbox` | `DeveloperSandboxController@sandbox` | `portal.developer.sandbox` |
| `/portal/developer/sandbox/simulate-webhook` | `POST` | `portal.developer.simulate-webhook` | `DeveloperSandboxController@simulateWebhook` | JSON Webhook Result |
| `/portal/developer/outbox` | `GET` | `portal.developer.outbox` | `DeveloperSandboxController@outbox` | `portal.developer.outbox` |
| `/portal/developer/architecture` | `GET` | `portal.developer.architecture` | `DeveloperSandboxController@architecture` | `portal.developer.architecture` |

### 8. Dokumen Resmi A4 Printable (`/portal/print/*`)
| URL | HTTP Verb | Route Name | Controller & Method | Target View |
|---|---|---|---|---|
| `/portal/print/krs/{krsSubmission?}` | `GET` | `portal.print.krs` | `PrintDocumentController@krs` | `print.krs` |
| `/portal/print/khs-transcript/{student?}/{period?}` | `GET` | `portal.print.khs-transcript` | `PrintDocumentController@khsTranscript` | `print.khs-transcript` |
| `/portal/print/kwitansi/{invoice?}` | `GET` | `portal.print.kwitansi` | `PrintDocumentController@kwitansi` | `print.kwitansi` |
| `/portal/print/loa/{applicant?}` | `GET` | `portal.print.loa` | `PrintDocumentController@loa` | `print.loa` |
| `/portal/print/skpi/{clearance?}` | `GET` | `portal.print.skpi` | `PrintDocumentController@skpi` | `print.skpi` |

---

## SECTION 7: INTERACTIVE IN-PORTAL ARCHITECTURE VIEWER

Aplikasi ini menyertakan penampil arsitektur interaktif bawaan yang dapat diakses langsung oleh administrator di:
`GET /portal/developer/architecture`

### Fitur Penampil Arsitektur:
1. **Live System Metric Counters:** Menampilkan metrik real-time dari database (Total Pengguna, Mahasiswa Aktif, Dosen, Matakuliah, Sesi Kelas, Total Rute Aktif, dan Jumlah Assertion Pengujian).
2. **Sticky Table of Contents:** Navigasi cepat antar seksi diagram dengan scroll halus dan penanda aktif.
3. **Copyable ASCII Blocks:** Tombol salin 1-klik untuk menyalin diagram box-drawing ke clipboard untuk dokumentasi atau presentasi arsitektur.
4. **Dark Mode Integration:** Tampilan kontras tinggi yang nyaman dibaca di bawah pencahayaan gelap maupun terang.

---

## SECTION 8: PANDUAN PEMELIHARAAN, TROUBLESHOOTING & DEVELOPER CHEAT-SHEET

### 1. Perintah Rutin Pengujian & Validasi Kualitas
```bash
# Menjalankan seluruh 35 automated test suites
./vendor/bin/phpunit

# Menjalankan pemeriksaan kesehatan arsitektur monolit
php artisan erp:health-check

# Kompilasi aset frontend Vite tanpa meninggalkan file .html statis
npm run build
find public -name "*.html" # Harus bernilai 0
```

### 2. Cara Menambahkan Peran atau Hak Akses Baru
1. Buka `database/seeders/RolePermissionSeeder.php`.
2. Tambahkan slug permission pada array `$permissionsData` (contoh: `['slug' => 'research.grant.apply', 'name' => 'Pengajuan Hibah Penelitian', 'module' => 'Research']`).
3. Tambahkan slug tersebut ke peran terkait dalam array `$rolePermissionsMap`.
4. Jalankan seeder ulang:
   ```bash
   php artisan db:seed --class=RolePermissionSeeder
   ```
5. Terapkan proteksi pada route dengan menambahkan middleware `role.privilege:research.grant.apply` di `routes/web.php`.

### 3. Cara Melakukan Semester Rollover (Peralihan Semester BAAK)
BAAK dapat memicu rollover semester dari antarmuka `/portal/baak/calendar` atau via CLI:
```bash
# Rollover otomatis menutup semester aktif, mengarsipkan KRS, dan membuka semester baru
php artisan erp:health-check
```

### 4. Reset Status Demo 1-Klik
Jika data demo teracak selama pengujian manual, reset ke kondisi awal dengan salah satu cara berikut:
- **Via Browser:** Buka modal Demo Control Center di pojok kanan bawah (atau tekan `Alt+S`), lalu klik **"Reset State (Stage 16 Ready)"**.
- **Via CLI Artisan:**
  ```bash
  php artisan migrate:fresh --seed
  ```

### 5. Debugging Query N+1 & Kinerja
- Model telah mengimplementasikan eager loading (`with(...)`) pada relasi `KrsItem`, `ClassSection`, `Attendance`, dan `StudentInvoice`.
- Hindari lazy loading di loop Blade view dengan selalu menyertakan relasi yang dibutuhkan pada Controller query.

---
*Dokumentasi ini dihasilkan secara otomatis dan dipelihara oleh Lead Enterprise Systems Architect UPH Monolith.*
