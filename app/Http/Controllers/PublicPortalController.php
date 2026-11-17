<?php

namespace App\Http\Controllers;

use App\Models\AdmissionWave;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    /**
     * University Marketing Home Page (uph.edu).
     */
    public function index(): View
    {
        $data = Cache::remember('public_home_data', 3600, function () {
            $faculties = Faculty::where('is_active', true)
                ->with(['studyPrograms' => function ($q) {
                    $q->where('is_active', true)->orderBy('degree_level');
                }])
                ->orderBy('name_id')
                ->get();

            $activeWave = AdmissionWave::where('is_active', true)->first();
            $totalStudents = \App\Models\Student::count();
            $totalCourses = Course::count();

            $stats = [
                ['label' => 'Tingkat Keterserapan Kerja Lulusan', 'value' => '99%', 'icon' => 'fa-briefcase', 'sub' => 'Bekerja < 6 bulan setelah wisuda'],
                ['label' => 'Akreditasi Institusi Nasional', 'value' => 'UNGGUL', 'icon' => 'fa-award', 'sub' => 'Badan Akreditasi Nasional (BAN-PT)'],
                ['label' => 'Fakultas & Program Studi', 'value' => '40+', 'icon' => 'fa-building-columns', 'sub' => 'Jenjang S1, S2, Profesi & Doktoral'],
                ['label' => 'Jaringan Mitra Global & Industri', 'value' => '300+', 'icon' => 'fa-globe', 'sub' => 'Kerjasama Universitas Internasional'],
            ];

            $testimonials = [
                [
                    'name' => 'Jessica Tanoto, S.Kom.',
                    'title' => 'Software Engineer Lead, Global Tech Unicorn',
                    'quote' => 'Kurikulum OBE di Informatika UPH memberikan pondasi analitis dan teknis yang relevan dengan standar industri internasional.',
                    'prodi' => 'Informatika - Angkatan 2020'
                ],
                [
                    'name' => 'dr. Daniel Christian, M.Ked.',
                    'title' => 'Dokter Spesialis Bedah, Siloam Hospitals',
                    'quote' => 'Integrasi rumah sakit pendidikan Siloam dan fakultas kedokteran UPH membentuk integritas dan keunggulan klinis kami.',
                    'prodi' => 'Kedokteran - Angkatan 2018'
                ]
            ];

            return compact('faculties', 'activeWave', 'totalStudents', 'totalCourses', 'stats', 'testimonials');
        });

        return view('public.home', $data);
    }

    /**
     * Faculty & Study Program Showcase.
     */
    public function programs(Request $request): View
    {
        $degreeLevel = $request->query('level', 'S1');

        $faculties = Cache::remember("public_programs_{$degreeLevel}", 3600, function () use ($degreeLevel) {
            return Faculty::where('is_active', true)
                ->with(['studyPrograms' => function ($q) use ($degreeLevel) {
                    $q->where('is_active', true);
                    if ($degreeLevel !== 'all') {
                        $q->where('degree_level', $degreeLevel);
                    }
                }])
                ->orderBy('name_id')
                ->get();
        });

        return view('public.programs', compact('faculties', 'degreeLevel'));
    }

    /**
     * Sarjana (S1) Undergraduate Dynamic Programs Page.
     */
    public function undergraduate(Request $request): View
    {
        $faculties = Cache::remember('public_undergraduate_faculties', 3600, function () {
            return Faculty::where('is_active', true)
                ->with(['studyPrograms' => function ($q) {
                    $q->where('is_active', true)
                      ->where('degree_level', 'S1')
                      ->withCount('courses')
                      ->orderBy('name_id');
                }])
                ->orderBy('name_id')
                ->get();
        });

        $activeWave = AdmissionWave::where('is_active', true)->first();

        return view('public.undergraduate', compact('faculties', 'activeWave'));
    }

    /**
     * Pascasarjana (S2 & S3) Graduate Programs Page.
     */
    public function pascasarjana(Request $request): View
    {
        $graduatePrograms = Cache::remember('public_pascasarjana_programs', 3600, function () {
            return StudyProgram::where('is_active', true)
                ->whereIn('degree_level', ['S2', 'S3', 'Profesi'])
                ->with(['faculty', 'headOfProgram'])
                ->orderBy('degree_level')
                ->orderBy('name_id')
                ->get();
        });

        $activeWave = AdmissionWave::where('is_active', true)->first();

        return view('public.pascasarjana', compact('graduatePrograms', 'activeWave'));
    }

    /**
     * Kelas Karyawan & Professional Executive Education Page.
     */
    public function kelasKaryawan(Request $request): View
    {
        $executivePrograms = Cache::remember('public_kelas_karyawan_programs', 3600, function () {
            return StudyProgram::where('is_active', true)
                ->whereIn('code', ['IF', 'SI', 'MN', 'AK', 'HK'])
                ->with(['faculty', 'courses' => fn($q) => $q->take(5)])
                ->get();
        });

        $activeWave = AdmissionWave::where('is_active', true)->first();

        return view('public.kelas-karyawan', compact('executivePrograms', 'activeWave'));
    }

    /**
     * Events, Public Seminars, and University Calendar.
     */
    public function events(Request $request): View
    {
        $activeWave = AdmissionWave::where('is_active', true)->first();
        $academicPeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();

        $events = [
            [
                'title' => 'UPH International Innovation & AI Summit 2026',
                'category' => 'Konferensi Akademik',
                'date' => '24 Agustus 2026',
                'location' => 'Grand Chapel UPH & Live Hybrid Stream',
                'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop&q=80',
                'description' => 'Simposium tahunan teknologi informasi dan etika kecerdasan buatan bersama mitra perguruan tinggi global.'
            ],
            [
                'title' => 'Open House & Consultation Day PMB T.A. 2026/2027',
                'category' => 'Admissions & Beasiswa',
                'date' => '15 September 2026',
                'location' => 'Main Campus Lippo Village & Virtual Campus Tour',
                'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&auto=format&fit=crop&q=80',
                'description' => 'Konsultasi pemetaan minat bakat, tes bakat CBT instan, dan penyerahan beasiswa prestasi hingga 100%.'
            ],
            [
                'title' => 'Wisuda Sarjana & Pascasarjana Semester Ganjil T.A. 2025/2026',
                'category' => 'Yudisium & Kelulusan',
                'date' => '10 Oktober 2026',
                'location' => 'Plenary Hall UPH Kampus Tangerang',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&auto=format&fit=crop&q=80',
                'description' => 'Pengukuhan lulusan sarjana, magister, dan doktor berkarakter ilahi siap mengabdi untuk bangsa.'
            ],
        ];

        return view('public.events', compact('events', 'activeWave', 'academicPeriod'));
    }

    /**
     * Public Outcome-Based Education (OBE) Curriculum Preview.
     */
    public function curriculumPreview(string $prodiCode): View
    {
        $studyProgram = StudyProgram::where('code', $prodiCode)
            ->orWhere('pddikti_code', $prodiCode)
            ->with(['faculty'])
            ->firstOrFail();

        $curriculum = Curriculum::where('study_program_id', $studyProgram->id)
            ->where('is_active', true)
            ->with([
                'cpls',
                'courses' => function ($q) {
                    $q->orderBy('semester_level')->orderBy('code');
                },
                'courses.prerequisites'
            ])
            ->first();

        // Group courses by semester level (1 to 8)
        $coursesBySemester = [];
        if ($curriculum) {
            foreach ($curriculum->courses as $course) {
                $coursesBySemester[$course->semester_level][] = $course;
            }
            ksort($coursesBySemester);
        }

        return view('public.curriculum-preview', compact('studyProgram', 'curriculum', 'coursesBySemester'));
    }
}
