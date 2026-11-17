<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define the 9 Official System Roles
        $rolesData = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Administrator',
                'level' => 100,
                'description' => 'Full IAM, System & Global Audit Control.',
            ],
            [
                'name' => 'rector',
                'display_name' => 'Rektor & Pimpinan Eksekutif',
                'level' => 90,
                'description' => 'Executive Read-All, KPI Analytics & Accreditation Oversight.',
            ],
            [
                'name' => 'dean',
                'display_name' => 'Dekan Fakultas',
                'level' => 80,
                'description' => 'Scoped to Faculty: Faculty KPI & Study Program Oversight.',
            ],
            [
                'name' => 'kaprodi',
                'display_name' => 'Ketua Program Studi (Kaprodi)',
                'level' => 70,
                'description' => 'Scoped to Study Program: OBE Curriculum, Grade Lock Override, MBKM & Yudisium.',
            ],
            [
                'name' => 'baak',
                'display_name' => 'Biro Administrasi Akademik (BAAK)',
                'level' => 60,
                'description' => 'Academic Calendar, Semester Rollover, Master Schedule & PDDIKTI Sync.',
            ],
            [
                'name' => 'bauk',
                'display_name' => 'Biro Administrasi Keuangan (BAUK)',
                'level' => 60,
                'description' => 'Tuition Invoicing, VA Payment Verification, Financial Hold Control.',
            ],
            [
                'name' => 'lecturer',
                'display_name' => 'Dosen Pengajar & Pembimbing Akademik',
                'level' => 50,
                'description' => 'Assigned Classes, QR Attendance, OBE Grading, Advisee KRS Approval.',
            ],
            [
                'name' => 'student',
                'display_name' => 'Mahasiswa Aktif',
                'level' => 20,
                'description' => 'KRS War, QR Attendance, LMS, EDOM, KHS, Thesis Logbook & MBKM.',
            ],
            [
                'name' => 'applicant',
                'display_name' => 'Calon Mahasiswa (PMB)',
                'level' => 10,
                'description' => 'Admission Form, Document Upload, CBT Exam & LoA Download.',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $data) {
            $roles[$data['name']] = Role::firstOrCreate(['name' => $data['name']], [
                'id' => (string) Str::uuid(),
                'display_name' => $data['display_name'],
                'level' => $data['level'],
                'description' => $data['description'],
            ]);
        }

        // 2. Define Granular Permission Slugs
        $permissionsData = [
            // KRS & Academic Registration
            ['slug' => 'krs.submit', 'name' => 'Pengajuan KRS Online', 'module' => 'KRS'],
            ['slug' => 'krs.approve.advisee', 'name' => 'Persetujuan KRS Bimbingan PA', 'module' => 'KRS'],
            ['slug' => 'krs.override.quota', 'name' => 'Override Batas Kuota Kelas KRS', 'module' => 'KRS'],
            
            // Attendance & Sessions
            ['slug' => 'attendance.generate_qr', 'name' => 'Generate Token QR Presensi 15s', 'module' => 'Attendance'],
            ['slug' => 'attendance.scan_qr', 'name' => 'Scan Presensi Mandiri Mahasiswa', 'module' => 'Attendance'],
            ['slug' => 'attendance.bap_fill', 'name' => 'Pengisian Berita Acara Kuliah (BAP)', 'module' => 'Attendance'],
            
            // Grading & Assessment
            ['slug' => 'grades.input.own_class', 'name' => 'Input Nilai Huruf & OBE Kelas Sendiri', 'module' => 'Grading'],
            ['slug' => 'grades.lock', 'name' => 'Kunci Nilai Akhir Semester', 'module' => 'Grading'],
            ['slug' => 'grades.unlock.prodi', 'name' => 'Buka Kunci Nilai Semester (Kaprodi)', 'module' => 'Grading'],
            ['slug' => 'grades.appeal.respond', 'name' => 'Tanggapi Sanggah Nilai Mahasiswa', 'module' => 'Grading'],
            
            // Finance & Invoicing
            ['slug' => 'finance.view.own', 'name' => 'Lihat Tagihan & Invoice Mandiri', 'module' => 'Finance'],
            ['slug' => 'finance.verify.payment', 'name' => 'Verifikasi Pelunasan Pembayaran UKT', 'module' => 'Finance'],
            ['slug' => 'finance.toggle.hold', 'name' => 'Aktif / Nonaktifkan Cekal Finansial', 'module' => 'Finance'],
            
            // Curriculum & MBKM
            ['slug' => 'curriculum.manage.prodi', 'name' => 'Kelola Matriks Kurikulum CPL & CPMK', 'module' => 'Curriculum'],
            ['slug' => 'mbkm.approve.prodi', 'name' => 'Persetujuan Konversi 20-SKS MBKM', 'module' => 'MBKM'],
            
            // Thesis & Yudisium
            ['slug' => 'thesis.supervise', 'name' => 'Bimbingan Skripsi & Verifikasi Logbook', 'module' => 'Thesis'],
            ['slug' => 'yudisium.verify.checkpoint', 'name' => 'Verifikasi Gate Yudisium & Kelulusan', 'module' => 'Yudisium'],
            ['slug' => 'pddikti.sync.execute', 'name' => 'Eksekusi Sinkronisasi PDDIKTI Feeder', 'module' => 'PDDIKTI'],
            
            // Executive & Dean
            ['slug' => 'executive.view.kpi', 'name' => 'Akses Dashboard Eksekutif Rektorat', 'module' => 'Executive'],
            ['slug' => 'dean.view.faculty', 'name' => 'Akses Monitoring Fakultas & Dekanat', 'module' => 'Executive'],
            ['slug' => 'calendar.manage', 'name' => 'Manajemen Kalender & Rollover Semester', 'module' => 'Academic Calendar'],

            // Admissions & PMB
            ['slug' => 'cbt.take.exam', 'name' => 'Mengikuti Ujian Masuk Online CBT', 'module' => 'Admissions'],
            ['slug' => 'loa.download', 'name' => 'Unduh Surat Penerimaan Resmi (LoA)', 'module' => 'Admissions'],

            // IAM & User Management
            ['slug' => 'iam.users.manage', 'name' => 'Manajemen Akun Sivitas & Status', 'module' => 'IAM'],
            ['slug' => 'iam.privileges.assign', 'name' => 'Penugasan Matriks Hak Akses & Privilege', 'module' => 'IAM'],
        ];

        $permissions = [];
        foreach ($permissionsData as $p) {
            $permissions[$p['slug']] = Permission::firstOrCreate(['slug' => $p['slug']], [
                'id' => (string) Str::uuid(),
                'name' => $p['name'],
                'module' => $p['module'],
                'description' => $p['name'],
            ]);
        }

        // 3. Map Default Granular Permissions per Role
        $rolePermissionsMap = [
            'superadmin' => array_keys($permissions), // Superadmin has all
            'rector' => [
                'executive.view.kpi', 'dean.view.faculty', 'pddikti.sync.execute',
            ],
            'dean' => [
                'dean.view.faculty', 'executive.view.kpi', 'yudisium.verify.checkpoint',
            ],
            'kaprodi' => [
                'curriculum.manage.prodi', 'grades.unlock.prodi', 'grades.lock', 'mbkm.approve.prodi',
                'krs.override.quota', 'yudisium.verify.checkpoint',
            ],
            'baak' => [
                'calendar.manage', 'krs.override.quota', 'yudisium.verify.checkpoint',
                'pddikti.sync.execute', 'iam.users.manage',
            ],
            'bauk' => [
                'finance.verify.payment', 'finance.toggle.hold', 'yudisium.verify.checkpoint',
            ],
            'lecturer' => [
                'krs.approve.advisee', 'attendance.generate_qr', 'attendance.bap_fill',
                'grades.input.own_class', 'grades.lock', 'grades.appeal.respond', 'thesis.supervise',
            ],
            'student' => [
                'krs.submit', 'attendance.scan_qr', 'finance.view.own',
            ],
            'applicant' => [
                'cbt.take.exam', 'loa.download', 'finance.view.own',
            ],
        ];

        foreach ($rolePermissionsMap as $roleName => $slugs) {
            if (isset($roles[$roleName])) {
                $permissionIds = [];
                foreach ($slugs as $slug) {
                    if (isset($permissions[$slug])) {
                        $permissionIds[] = $permissions[$slug]->id;
                    }
                }
                $roles[$roleName]->permissions()->sync($permissionIds);
            }
        }

        // 4. Update existing users with matching role_id
        foreach ($roles as $name => $roleModel) {
            User::where('role', $name)->update(['role_id' => $roleModel->id]);
        }
    }
}
