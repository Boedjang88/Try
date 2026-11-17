<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\AdmissionWave;
use App\Models\Applicant;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Faculty;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Lecturer;
use App\Models\LectureSession;
use App\Models\ObeCpl;
use App\Models\ObeCpmk;
use App\Models\PddiktiSyncLog;
use App\Models\Room;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\StudyProgram;
use App\Models\Thesis;
use App\Models\ThesisGuidanceLog;
use App\Models\User;
use App\Models\YudisiumClearance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the master database with realistic UPH university data across all 9 personas.
     */
    public function run(): void
    {
        // 1. FACULTIES
        $fik = Faculty::firstOrCreate(['code' => 'FIK'], [
            'id' => (string) Str::uuid(),
            'name_id' => 'Fakultas Ilmu Komputer',
            'name_en' => 'Faculty of Computer Science',
            'description' => 'Fakultas keunggulan teknologi digital, rekayasa kecerdasan buatan, dan komputasi awan bertaraf internasional.',
        ]);

        $feb = Faculty::firstOrCreate(['code' => 'FEB'], [
            'id' => (string) Str::uuid(),
            'name_id' => 'Fakultas Ekonomi dan Bisnis',
            'name_en' => 'Faculty of Economics and Business',
            'description' => 'Mencetak pemimpin bisnis transformasional berkarakter ilahi dan berwawasan global.',
        ]);

        $fh = Faculty::firstOrCreate(['code' => 'FH'], [
            'id' => (string) Str::uuid(),
            'name_id' => 'Fakultas Hukum',
            'name_en' => 'Faculty of Law',
            'description' => 'Pusat pendidikan hukum terkemuka dengan komitmen integritas moral dan keadilan.',
        ]);

        // 2. STUDY PROGRAMS
        $prodiIf = StudyProgram::firstOrCreate(['code' => 'IF'], [
            'id' => (string) Str::uuid(),
            'faculty_id' => $fik->id,
            'pddikti_code' => '55201',
            'name_id' => 'Informatika',
            'name_en' => 'Informatics',
            'degree_level' => 'S1',
            'title_abbr' => 'S.Kom.',
            'accreditation_grade' => 'Unggul',
        ]);

        $prodiSi = StudyProgram::firstOrCreate(['code' => 'SI'], [
            'id' => (string) Str::uuid(),
            'faculty_id' => $fik->id,
            'pddikti_code' => '55202',
            'name_id' => 'Sistem Informasi',
            'name_en' => 'Information Systems',
            'degree_level' => 'S1',
            'title_abbr' => 'S.Kom.',
            'accreditation_grade' => 'Unggul',
        ]);

        $prodiMgt = StudyProgram::firstOrCreate(['code' => 'MGT'], [
            'id' => (string) Str::uuid(),
            'faculty_id' => $feb->id,
            'pddikti_code' => '61201',
            'name_id' => 'Manajemen',
            'name_en' => 'Management',
            'degree_level' => 'S1',
            'title_abbr' => 'S.M.',
            'accreditation_grade' => 'Unggul',
        ]);

        // 3. ACADEMIC PERIODS
        $periodActive = AcademicPeriod::firstOrCreate(['name' => '2025/2026 Ganjil'], [
            'id' => (string) Str::uuid(),
            'code' => '20251',
            'semester_type' => 'Ganjil',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'krs_start_date' => now()->subDays(15),
            'krs_end_date' => now()->addDays(15),
            'grade_input_start_date' => now()->subDays(5),
            'grade_input_end_date' => now()->addMonths(5),
            'is_active' => true,
            'is_krs_active' => true,
            'is_grading_active' => true,
        ]);

        AcademicPeriod::firstOrCreate(['name' => '2024/2025 Genap'], [
            'id' => (string) Str::uuid(),
            'code' => '20242',
            'semester_type' => 'Genap',
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->subMonths(2)->toDateString(),
            'krs_start_date' => now()->subMonths(7),
            'krs_end_date' => now()->subMonths(6),
            'grade_input_start_date' => now()->subMonths(2),
            'grade_input_end_date' => now()->subMonths(1),
            'is_active' => false,
            'is_krs_active' => false,
            'is_grading_active' => false,
        ]);

        // 4. ROOMS (Classrooms & Labs)
        $roomD401 = Room::firstOrCreate(['code' => 'D-401'], [
            'id' => (string) Str::uuid(),
            'name' => 'Gedung D - Ruang 401',
            'building' => 'Building D Lippo Karawaci',
            'floor' => 4,
            'capacity' => 45,
            'room_type' => 'classroom',
            'latitude' => -6.22680000,
            'longitude' => 106.60950000,
            'geofence_radius_meters' => 50,
        ]);

        $roomLabAI = Room::firstOrCreate(['code' => 'LAB-AI'], [
            'id' => (string) Str::uuid(),
            'name' => 'Laboratorium AI & Software Engineering (E-201)',
            'building' => 'Building E Tech Center',
            'floor' => 2,
            'capacity' => 40,
            'room_type' => 'laboratory',
            'latitude' => -6.22680000,
            'longitude' => 106.60950000,
            'geofence_radius_meters' => 50,
        ]);

        // 5. MASTER USERS FOR ALL 9 PERSONAS (Password: password)
        $superadminUser = User::firstOrCreate(['email' => 'superadmin@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'superadmin',
            'full_name' => 'Super Administrator UPH',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $rectorUser = User::firstOrCreate(['email' => 'rector@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'rector',
            'full_name' => 'Dr. (Hon.) Jonathan L. Parapak, M.Eng.Sc.',
            'password' => Hash::make('password'),
            'role' => 'rector',
            'is_active' => true,
        ]);

        $deanUser = User::firstOrCreate(['email' => 'dean.fik@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'dean.fik',
            'full_name' => 'Prof. Dr. Ir. Samuel H. Tirtamulia, M.Sc.',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'faculty_id' => $fik->id,
            'is_active' => true,
        ]);

        $kaprodiUser = User::firstOrCreate(['email' => 'kaprodi.if@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'kaprodi.if',
            'full_name' => 'Dr. Veronica Siregar, M.Kom.',
            'password' => Hash::make('password'),
            'role' => 'kaprodi',
            'study_program_id' => $prodiIf->id,
            'faculty_id' => $fik->id,
            'is_active' => true,
        ]);

        $lecturerUser1 = User::firstOrCreate(['email' => 'lecturer1@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'johanes.tan',
            'full_name' => 'Dr. Ir. Johanes Tan, M.Sc., Ph.D.',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
            'study_program_id' => $prodiIf->id,
            'is_active' => true,
        ]);

        $lecturerUser2 = User::firstOrCreate(['email' => 'lecturer2@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'budi.santoso',
            'full_name' => 'Budi Santoso, S.Kom., M.T.',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
            'study_program_id' => $prodiSi->id,
            'is_active' => true,
        ]);

        $studentUser1 = User::firstOrCreate(['email' => 'student1@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => '01082200001',
            'full_name' => 'Christian Jonathan',
            'password' => Hash::make('password'),
            'role' => 'student',
            'study_program_id' => $prodiIf->id,
            'is_active' => true,
        ]);

        $studentBlockedUser = User::firstOrCreate(['email' => 'student.blocked@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => '01082200002',
            'full_name' => 'Jessica Aurelia Tan',
            'password' => Hash::make('password'),
            'role' => 'student',
            'study_program_id' => $prodiIf->id,
            'is_active' => true,
        ]);

        $applicantUser1 = User::firstOrCreate(['email' => 'applicant1@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'reg2026001',
            'full_name' => 'Alexander Michael Kusuma',
            'password' => Hash::make('password'),
            'role' => 'applicant',
            'study_program_id' => $prodiIf->id,
            'is_active' => true,
        ]);

        $baakUser = User::firstOrCreate(['email' => 'baak@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'staf.baak',
            'full_name' => 'Biro Administrasi Akademik (BAAK)',
            'password' => Hash::make('password'),
            'role' => 'baak',
            'is_active' => true,
        ]);

        $baukUser = User::firstOrCreate(['email' => 'bauk@uph.edu'], [
            'id' => (string) Str::uuid(),
            'username' => 'staf.bauk',
            'full_name' => 'Biro Administrasi Keuangan (BAUK)',
            'password' => Hash::make('password'),
            'role' => 'bauk',
            'is_active' => true,
        ]);

        // 6. LECTURER RECORDS
        $lecturer1 = Lecturer::firstOrCreate(['user_id' => $lecturerUser1->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'nidn' => '0412057801',
            'employment_status' => 'Tetap',
            'academic_rank' => 'Lektor Kepala',
        ]);

        $lecturer2 = Lecturer::firstOrCreate(['user_id' => $lecturerUser2->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiSi->id,
            'nidn' => '0412057802',
            'employment_status' => 'Tetap',
            'academic_rank' => 'Asisten Ahli',
        ]);

        $lecturerKaprodi = Lecturer::firstOrCreate(['user_id' => $kaprodiUser->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'nidn' => '0422088202',
            'employment_status' => 'Tetap',
            'academic_rank' => 'Lektor',
        ]);

        $lecturerDean = Lecturer::firstOrCreate(['user_id' => $deanUser->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'nidn' => '0415036901',
            'employment_status' => 'Tetap',
            'academic_rank' => 'Guru Besar',
        ]);

        // 7. OBE CURRICULUM
        $curriculum = Curriculum::firstOrCreate(['code' => 'KUR-IF-2024'], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'name' => 'Kurikulum OBE Informatika 2024',
            'start_year' => 2024,
            'total_sks' => 144,
            'is_obe_compliant' => true,
            'is_active' => true,
        ]);

        // 8. OBE CPL (Capaian Pembelajaran Lulusan)
        $cpl1 = ObeCpl::firstOrCreate(['code' => 'CPL-01', 'curriculum_id' => $curriculum->id], [
            'id' => (string) Str::uuid(),
            'category' => 'Sikap',
            'description_id' => 'Menjunjung tinggi nilai moral, etika akademik, dan kepemimpinan berkarakter ilahi (Godly Character).',
            'description_en' => 'Demonstrate high moral integrity, academic ethics, and transformational leadership.',
            'order_sequence' => 1,
        ]);

        $cpl2 = ObeCpl::firstOrCreate(['code' => 'CPL-02', 'curriculum_id' => $curriculum->id], [
            'id' => (string) Str::uuid(),
            'category' => 'Keterampilan Khusus',
            'description_id' => 'Mampu merancang dan membangun arsitektur perangkat lunak skala enterprise teruji.',
            'description_en' => 'Ability to design and engineer enterprise-scale, production-ready software systems.',
            'order_sequence' => 2,
        ]);

        // 9. COURSES
        $courseAlgo = Course::firstOrCreate(['code' => 'IF101', 'curriculum_id' => $curriculum->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'name_id' => 'Algoritma dan Pemrograman',
            'name_en' => 'Algorithms and Programming',
            'sks' => 3,
            'semester_level' => 1,
            'is_mandatory' => true,
            'weight_presence' => 10.00,
            'weight_task' => 20.00,
            'weight_uts' => 30.00,
            'weight_uas' => 40.00,
        ]);

        $courseWeb = Course::firstOrCreate(['code' => 'IF202', 'curriculum_id' => $curriculum->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'name_id' => 'Pemrograman Web & Arsitektur Monolith Enterprise',
            'name_en' => 'Enterprise Web Programming & Monolith Architecture',
            'sks' => 4,
            'semester_level' => 3,
            'is_mandatory' => true,
            'weight_presence' => 10.00,
            'weight_task' => 30.00,
            'weight_uts' => 30.00,
            'weight_uas' => 30.00,
        ]);

        $courseAi = Course::firstOrCreate(['code' => 'IF301', 'curriculum_id' => $curriculum->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'name_id' => 'Kecerdasan Buatan & Machine Learning',
            'name_en' => 'Artificial Intelligence & Machine Learning',
            'sks' => 3,
            'semester_level' => 5,
            'is_mandatory' => true,
            'weight_presence' => 10.00,
            'weight_task' => 25.00,
            'weight_uts' => 30.00,
            'weight_uas' => 35.00,
        ]);

        $courseThesis = Course::firstOrCreate(['code' => 'IF402', 'curriculum_id' => $curriculum->id], [
            'id' => (string) Str::uuid(),
            'study_program_id' => $prodiIf->id,
            'name_id' => 'Tugas Akhir / Skripsi Sarjana',
            'name_en' => 'Undergraduate Thesis',
            'sks' => 6,
            'semester_level' => 8,
            'is_mandatory' => true,
            'weight_presence' => 0.00,
            'weight_task' => 0.00,
            'weight_uts' => 0.00,
            'weight_uas' => 100.00,
        ]);

        // 10. CLASS SECTIONS
        $sectionWeb = ClassSection::firstOrCreate(['section_code' => 'A', 'course_id' => $courseWeb->id], [
            'id' => (string) Str::uuid(),
            'academic_period_id' => $periodActive->id,
            'lecturer_id' => $lecturer1->id,
            'room_id' => $roomLabAI->id,
            'day_of_week' => 1, // Senin
            'start_time' => '08:00:00',
            'end_time' => '11:40:00',
            'max_quota' => 40,
            'enrolled_count' => 1,
            'is_closed' => false,
        ]);

        $sectionAi = ClassSection::firstOrCreate(['section_code' => 'A', 'course_id' => $courseAi->id], [
            'id' => (string) Str::uuid(),
            'academic_period_id' => $periodActive->id,
            'lecturer_id' => $lecturer1->id,
            'room_id' => $roomD401->id,
            'day_of_week' => 3, // Rabu
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'max_quota' => 45,
            'enrolled_count' => 1,
            'is_closed' => false,
        ]);

        // Conflicting section for Loop 2 schedule conflict test (Senin 09:00 - 11:00 overlaps with sectionWeb)
        $sectionConflict = ClassSection::firstOrCreate(['section_code' => 'B', 'course_id' => $courseAlgo->id], [
            'id' => (string) Str::uuid(),
            'academic_period_id' => $periodActive->id,
            'lecturer_id' => $lecturer2->id,
            'room_id' => $roomD401->id,
            'day_of_week' => 1, // Senin
            'start_time' => '09:00:00',
            'end_time' => '11:30:00',
            'max_quota' => 30,
            'enrolled_count' => 0,
            'is_closed' => false,
        ]);

        // 11. STUDENT RECORDS (Cleared & Blocked)
        $student = Student::firstOrCreate(['nim' => '01082200001'], [
            'id' => (string) Str::uuid(),
            'user_id' => $studentUser1->id,
            'study_program_id' => $prodiIf->id,
            'academic_advisor_id' => $lecturer1->id,
            'entry_year' => 2024,
            'entry_semester' => 'Ganjil',
            'current_semester' => 3,
            'academic_status' => 'active',
            'is_financially_cleared' => true,
            'has_academic_hold' => false,
            'current_gpa_ipk' => 3.65,
            'last_semester_gpa_ips' => 3.70,
            'total_sks_completed' => 48,
            'max_sks_allowed' => 24,
        ]);

        $studentBlocked = Student::firstOrCreate(['nim' => '01082200002'], [
            'id' => (string) Str::uuid(),
            'user_id' => $studentBlockedUser->id,
            'study_program_id' => $prodiIf->id,
            'academic_advisor_id' => $lecturer1->id,
            'entry_year' => 2023,
            'entry_semester' => 'Ganjil',
            'current_semester' => 5,
            'academic_status' => 'active',
            'is_financially_cleared' => false,
            'has_academic_hold' => true,
            'current_gpa_ipk' => 3.20,
            'last_semester_gpa_ips' => 3.10,
            'total_sks_completed' => 76,
            'max_sks_allowed' => 20,
        ]);

        // 12. KRS SUBMISSION & KRS ITEMS
        $krsSubmission = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $periodActive->id,
        ], [
            'id' => (string) Str::uuid(),
            'status' => 'approved',
            'total_sks' => 7,
            'approved_at' => now()->subDays(5),
            'approved_by_lecturer_id' => $lecturer1->id,
            'qr_token' => 'KRS-TOKEN-' . Str::upper(Str::random(12)),
        ]);

        $krsItemWeb = KrsItem::firstOrCreate([
            'krs_submission_id' => $krsSubmission->id,
            'class_section_id' => $sectionWeb->id,
        ], [
            'id' => (string) Str::uuid(),
            'score_presence' => 95.00,
            'score_task' => 88.00,
            'score_uts' => 90.00,
            'score_uas' => 92.00,
            'final_numeric_score' => 90.90,
            'letter_grade' => 'A',
            'grade_point' => 4.00,
            'is_locked' => false,
            'edom_completed' => true,
        ]);

        $krsItemAi = KrsItem::firstOrCreate([
            'krs_submission_id' => $krsSubmission->id,
            'class_section_id' => $sectionAi->id,
        ], [
            'id' => (string) Str::uuid(),
            'score_presence' => 90.00,
            'score_task' => 85.00,
            'score_uts' => 88.00,
            'score_uas' => 87.00,
            'final_numeric_score' => 87.45,
            'letter_grade' => 'A',
            'grade_point' => 4.00,
            'is_locked' => false,
            'edom_completed' => true,
        ]);

        // 13. LECTURE SESSIONS & ATTENDANCE
        $session1 = LectureSession::firstOrCreate([
            'class_section_id' => $sectionWeb->id,
            'session_number' => 1,
        ], [
            'id' => (string) Str::uuid(),
            'title' => 'Pengenalan Arsitektur Monolith Modern & Laravel 11 Ecosystem',
            'session_type' => 'lecture',
            'planned_date' => now()->toDateString(),
            'actual_date' => now()->toDateString(),
            'room_id' => $roomLabAI->id,
            'qr_code_token' => 'QR-INITIAL-SESSION-1',
            'qr_expires_at' => now()->addMinutes(15),
            'bap_summary' => 'Perkuliahan sesi 1 pengenalan arsitektur monolith Laravel 11.',
        ]);

        // 14. STUDENT INVOICES (Student 1: Paid, Student 2: Unpaid Overdue)
        $invoice = StudentInvoice::firstOrCreate(['invoice_number' => 'INV/UPH/2025/GANJIL/01082200001'], [
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'academic_period_id' => $periodActive->id,
            'va_number' => '9887701082200001',
            'title' => 'Biaya Kuliah (BPP Pokok + 7 SKS) Semester Ganjil 2025/2026',
            'amount' => 17150000.00,
            'paid_amount' => 17150000.00,
            'payment_status' => 'paid',
            'due_date' => now()->addDays(30)->toDateString(),
            'paid_at' => now()->subDays(6),
            'payment_method' => 'BCA_VA',
            'gateway_reference' => 'BCA-SETTLED-01082200001',
        ]);

        $invoiceBlocked = StudentInvoice::firstOrCreate(['invoice_number' => 'INV/UPH/2025/GANJIL/01082200002'], [
            'id' => (string) Str::uuid(),
            'student_id' => $studentBlocked->id,
            'academic_period_id' => $periodActive->id,
            'va_number' => '9887701082200002',
            'title' => 'Biaya Kuliah (BPP Pokok + 20 SKS) Semester Ganjil 2025/2026',
            'amount' => 16500000.00,
            'paid_amount' => 0.00,
            'payment_status' => 'unpaid',
            'due_date' => now()->subDays(5)->toDateString(),
            'payment_method' => 'BCA_VA',
        ]);

        // 15. THESIS & GUIDANCE LOGS
        $thesis = Thesis::firstOrCreate(['student_id' => $student->id], [
            'id' => (string) Str::uuid(),
            'title' => 'Rancang Bangun Sistem Informasi Manajemen Rumah Sakit Berbasis Cloud Menggunakan Arsitektur Modular Monolith',
            'abstract' => 'Penelitian ini merancang sistem informasi rumah sakit komprehensif berstandar HL7 FHIR untuk efisiensi operasional rekam medis digital.',
            'supervisor1_lecturer_id' => $lecturer1->id,
            'supervisor2_lecturer_id' => $lecturerKaprodi->id,
            'examiner1_lecturer_id' => $lecturerDean->id,
            'examiner2_lecturer_id' => $lecturerKaprodi->id,
            'proposal_status' => 'approved',
            'defense_status' => 'scheduled',
            'defense_date' => now()->addDays(7),
        ]);

        // 8 Verified Guidance Sessions
        for ($i = 1; $i <= 8; $i++) {
            ThesisGuidanceLog::firstOrCreate([
                'thesis_id' => $thesis->id,
                'guidance_date' => now()->subDays(60 - ($i * 7))->toDateString(),
            ], [
                'id' => (string) Str::uuid(),
                'lecturer_id' => $lecturer1->id,
                'discussion_summary' => "Sesi Bimbingan #{$i}: Pembahasan progres penelitian dan perancangan arsitektur modul.",
                'student_notes' => "Revisi bagian Bab {$i} sesuai masukan dosen pembimbing.",
                'supervisor_feedback' => 'Penjelasan komprehensif, dapat dilanjutkan ke bab berikutnya.',
                'is_verified' => true,
                'verified_at' => now()->subDays(59 - ($i * 7)),
            ]);
        }

        // 16. YUDISIUM CLEARANCE
        YudisiumClearance::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $periodActive->id,
        ], [
            'id' => (string) Str::uuid(),
            'finance_cleared' => true,
            'library_cleared' => true,
            'repository_cleared' => true,
            'faculty_cleared' => true,
            'national_pin_number' => 'PIN-UPH/2026/IF/89472109',
            'graduation_date' => now()->toDateString(),
            'status' => 'approved',
            'approved_by_user_id' => $deanUser->id,
        ]);

        // 17. ADMISSION WAVE & APPLICANT
        $wave = AdmissionWave::firstOrCreate(['name' => 'Gelombang 1 Reguler 2026/2027'], [
            'id' => (string) Str::uuid(),
            'academic_period_id' => $periodActive->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'registration_fee' => 250000.00,
            'is_active' => true,
        ]);

        Applicant::firstOrCreate(['user_id' => $applicantUser1->id], [
            'id' => (string) Str::uuid(),
            'admission_wave_id' => $wave->id,
            'target_study_program_id' => $prodiIf->id,
            'registration_number' => 'REG202600001',
            'va_number' => '98877REG202600001',
            'cbt_score' => 88.50,
            'scholarship_tier' => 'Tier 3 (50%)',
            'admission_status' => 'passed',
            'payment_status' => 'paid',
            'payment_amount' => 250000.00,
            'paid_at' => now()->subDays(10),
        ]);

        // 18. PDDIKTI SYNC LOGS
        $entities = ['Mahasiswa', 'Dosen', 'KelasKuliah', 'Nilai', 'AKM'];
        foreach ($entities as $idx => $ent) {
            PddiktiSyncLog::firstOrCreate(['entity_type' => $ent], [
                'batch_reference' => 'BATCH-PDDIKTI-INIT-' . date('Ymd') . '-' . strtoupper($ent),
                'total_records' => 150 + ($idx * 35),
                'success_count' => 150 + ($idx * 35),
                'failed_count' => 0,
                'status' => 'completed',
                'synced_by_user_id' => $baakUser->id,
                'created_at' => now()->subHours(12 - $idx),
            ]);
        }

        // 19. STAGE 15: ROLES & GRANULAR PERMISSIONS MATRIX
        $this->call(RolePermissionSeeder::class);

        // 20. STAGE 16: DUAL-CONTROL, HELPDESK, & SANDBOX SEEDER
        $this->call(Stage16GovernanceAndSandboxSeeder::class);
    }
}
