<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BkdController extends Controller
{
    /**
     * Display Lecturer Workload (Beban Kinerja Dosen / BKD) & Tri Dharma Compliance Report.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Resolve target lecturer
        if ($user->role === 'lecturer' && $user->lecturer) {
            $lecturer = $user->lecturer;
        } else {
            $lecturerId = $request->get('lecturer_id');
            if ($lecturerId) {
                $lecturer = Lecturer::with(['user', 'studyProgram.faculty'])->find($lecturerId);
            } else {
                $lecturer = Lecturer::with(['user', 'studyProgram.faculty'])->first();
            }
        }

        if (!$lecturer) {
            abort(404, 'Data dosen tidak ditemukan.');
        }

        $lecturer->load(['user', 'studyProgram.faculty']);

        $activePeriod = AcademicPeriod::where('is_active', true)->first() 
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        // 1. Dharma Pendidikan & Pengajaran
        $classSections = ClassSection::with(['course', 'room', 'krsItems'])
            ->where('lecturer_id', $lecturer->id)
            ->where('academic_period_id', $activePeriod?->id)
            ->get();

        $teachingSks = $classSections->sum(fn($cs) => $cs->course->sks ?? 0);
        $totalStudentsTaught = $classSections->sum(fn($cs) => $cs->krsItems->count());

        // Thesis Advisees (Bimbingan Skripsi)
        $thesisAdviseesCount = Thesis::where(function ($q) use ($lecturer) {
            $q->where('supervisor1_lecturer_id', $lecturer->id)
              ->orWhere('supervisor2_lecturer_id', $lecturer->id);
        })
        ->where('proposal_status', 'approved')
        ->count();

        // 4 Thesis Advisees equivalent to 1 SKS BKD
        $thesisBkdSks = round($thesisAdviseesCount * 0.25, 2);

        // Academic Advising (Dosen PA)
        $paStudentCount = Student::where('academic_advisor_lecturer_id', $lecturer->id)->count();
        $paBkdSks = round($paStudentCount * 0.05, 2);

        $totalPendidikanSks = $teachingSks + $thesisBkdSks + $paBkdSks;

        // 2. Dharma Penelitian (Research & Publications)
        // Standard UPH/DIKTI: Active research projects & scopus/sinta journal publications
        $researchProjects = [
            [
                'title' => 'Pengembangan Sistem Pembelajaran Adaptif Berbasis Model Pembelajaran Mesin pada Lingkungan Cloud UPH',
                'source' => 'Hibah Penelitian Fundamental Kemendikbudristek',
                'role' => 'Ketua Peneliti',
                'sks' => 2.50,
            ],
            [
                'title' => 'Analisis Kinerja Algoritma Kriptografi Pascakuantum pada Protokol Komunikasi IoT Kampus Cerdas',
                'source' => 'Jurnal Nasional Terakreditasi SINTA 2',
                'role' => 'Penulis Pertama',
                'sks' => 1.50,
            ],
        ];
        $totalPenelitianSks = array_sum(array_column($researchProjects, 'sks'));

        // 3. Dharma Pengabdian kepada Masyarakat (PkM)
        $pkmProjects = [
            [
                'title' => 'Pelatihan Literasi Keamanan Siber dan Privasi Data bagi Siswa SMA di Wilayah Tangerang & Jabodetabek',
                'partner' => 'Dinas Pendidikan Provinsi Banten',
                'role' => 'Narasumber Utama',
                'sks' => 1.00,
            ],
        ];
        $totalPkmSks = array_sum(array_column($pkmProjects, 'sks'));

        // 4. Dharma Penunjang (Supporting Services)
        $penunjangItems = [
            [
                'activity' => 'Anggota Tim Penjaminan Mutu Akademik Internal (SPMI) Fakultas Ilmu Komputer',
                'level' => 'Tingkat Fakultas',
                'sks' => 1.00,
            ],
            [
                'activity' => 'Anggota Asosiasi Pendidikan Tinggi Informatika & Ilmu Komputer (APTIKOM)',
                'level' => 'Organisasi Profesi Nasional',
                'sks' => 0.50,
            ],
        ];
        $totalPenunjangSks = array_sum(array_column($penunjangItems, 'sks'));

        // Cumulative BKD SKS
        $totalBkdSks = $totalPendidikanSks + $totalPenelitianSks + $totalPkmSks + $totalPenunjangSks;

        // DIKTI Serdos Evaluation: 12.00 <= SKS <= 16.00
        if ($totalBkdSks >= 12.00 && $totalBkdSks <= 16.00) {
            $bkdStatus = [
                'status' => 'passed',
                'badge' => 'MEMENUHI KETENTUAN BKD',
                'color' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                'message' => 'Beban kerja dosen berada pada rentang ideal standar nasional (12 - 16 SKS). Berhak menerima Tunjangan Profesi / Kehormatan Dosen.',
            ];
        } elseif ($totalBkdSks < 12.00) {
            $bkdStatus = [
                'status' => 'underload',
                'badge' => 'BELUM MEMENUHI BEBAN MINIMAL',
                'color' => 'bg-amber-50 text-amber-800 border-amber-300',
                'message' => 'Total beban kerja kurang dari 12 SKS. Diperlukan penambahan beban tridharma (penelitian/pengabdian) untuk memenuhi syarat sertifikasi.',
            ];
        } else {
            $bkdStatus = [
                'status' => 'overload',
                'badge' => 'MELEBIHI BEBAN MAKSIMAL (>16 SKS)',
                'color' => 'bg-blue-50 text-blue-800 border-blue-300',
                'message' => 'Total beban kerja melebihi 16 SKS. Beban berlebih dialihkan sebagai kelebihan jam mengajar insentif universitas.',
            ];
        }

        return view('bkd.index', compact(
            'lecturer',
            'activePeriod',
            'classSections',
            'teachingSks',
            'totalStudentsTaught',
            'thesisAdviseesCount',
            'thesisBkdSks',
            'paStudentCount',
            'paBkdSks',
            'totalPendidikanSks',
            'researchProjects',
            'totalPenelitianSks',
            'pkmProjects',
            'totalPkmSks',
            'penunjangItems',
            'totalPenunjangSks',
            'totalBkdSks',
            'bkdStatus'
        ));
    }
}
