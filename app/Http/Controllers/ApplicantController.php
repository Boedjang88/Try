<?php

namespace App\Http\Controllers;

use App\Models\AdmissionWave;
use App\Models\Applicant;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicantController extends Controller
{
    /**
     * Show PMB Registration Form.
     */
    public function registerForm(Request $request): View
    {
        $studyPrograms = StudyProgram::where('is_active', true)->with('faculty')->orderBy('name_id')->get();
        $activeWave = AdmissionWave::where('is_active', true)->first() ?: AdmissionWave::first();
        $selectedProdiId = $request->query('prodi');

        return view('pmb.register', compact('studyPrograms', 'activeWave', 'selectedProdiId'));
    }

    /**
     * Handle PMB Registration Submission & Virtual Account Generation.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|max:128',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'target_study_program_id' => 'required|exists:study_programs,id',
            'gender' => 'required|in:L,P',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $wave = AdmissionWave::where('is_active', true)->first();
            if (!$wave) {
                $wave = AdmissionWave::firstOrCreate([
                    'name' => 'Gelombang 1 - T.A. 2026/2027',
                ], [
                    'academic_period_id' => \App\Models\AcademicPeriod::first()->id ?? Str::uuid()->toString(),
                    'wave_number' => 1,
                    'start_date' => now()->startOfYear(),
                    'end_date' => now()->endOfYear(),
                    'registration_fee' => 500000.00,
                    'is_active' => true,
                ]);
            }

            // Create or update applicant user account
            $user = User::where('email', $validated['email'])->first();
            if ($user && $user->role !== 'applicant') {
                return back()->withErrors(['email' => 'Email ini sudah terdaftar sebagai akun civitas akademika UPH. Silakan login.']);
            }

            if (!$user) {
                $username = Str::slug(explode('@', $validated['email'])[0]) . '_' . mt_rand(100, 999);
                $user = User::create([
                    'username' => $username,
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'full_name' => $validated['full_name'],
                    'role' => 'applicant',
                    'phone_number' => $validated['phone_number'],
                    'gender' => $validated['gender'],
                    'birth_date' => $validated['birth_date'] ?? null,
                    'birth_place' => $validated['birth_place'] ?? null,
                    'study_program_id' => $validated['target_study_program_id'],
                    'is_active' => true,
                ]);
            }

            // Generate unique Registration Number and 16-digit Virtual Account
            $regNumber = 'PMB-' . date('Y') . '-' . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $vaNumber = '88019' . date('ym') . str_pad((string)mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            $ktpPath = null;
            if ($request->hasFile('document_identity')) {
                $ktpPath = $request->file('document_identity')->store('pmb/identity', 'public');
            }

            $certPath = null;
            if ($request->hasFile('document_certificate')) {
                $certPath = $request->file('document_certificate')->store('pmb/certificate', 'public');
            }

            $applicant = Applicant::create([
                'user_id' => $user->id,
                'admission_wave_id' => $wave->id,
                'target_study_program_id' => $validated['target_study_program_id'],
                'registration_number' => $regNumber,
                'va_number' => $vaNumber,
                'payment_status' => 'unpaid',
                'payment_amount' => $wave->registration_fee,
                'scholarship_tier' => 'Regular (0%)',
                'admission_status' => 'registered',
                'document_identity_path' => $ktpPath,
                'document_certificate_path' => $certPath,
            ]);

            Auth::login($user);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran PMB berhasil disimpan. Silakan lakukan pembayaran formulir.',
                    'applicant' => $applicant,
                    'redirect_url' => '/pmb/status',
                ]);
            }

            return redirect('/pmb/status')->with('success', 'Pendaftaran berhasil. Silakan selesaikan pembayaran formulir PMB.');
        });
    }

    /**
     * Show PMB Applicant Lifecycle Status Tracker.
     */
    public function status(): View
    {
        $user = Auth::user();
        $applicant = Applicant::where('user_id', $user->id)
            ->with(['admissionWave', 'targetStudyProgram.faculty'])
            ->latest()
            ->firstOrFail();

        return view('pmb.status', compact('applicant'));
    }

    /**
     * Instant Payment Simulation (For Demo / Webhook Testing).
     */
    public function simulatePayment(Applicant $applicant): RedirectResponse|JsonResponse
    {
        $applicant->payment_status = 'paid';
        $applicant->paid_at = now();
        $applicant->admission_status = 'paid';
        $applicant->save();

        $applicant->recordAudit('PMB_FEE_PAID', "Registration fee paid for applicant [{$applicant->registration_number}]");

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran VA formulir PMB berhasil diverifikasi. Ujian CBT telah dibuka.',
            ]);
        }

        return back()->with('success', 'Pembayaran Virtual Account berhasil disimulasikan lunas. Anda dapat langsung memulai Ujian CBT!');
    }

    /**
     * Alias for showRegistrationForm.
     */
    public function showRegistrationForm(Request $request): View
    {
        return $this->registerForm($request);
    }

    /**
     * Alias for storeRegistration.
     */
    public function storeRegistration(Request $request): JsonResponse|RedirectResponse
    {
        return $this->store($request);
    }

    /**
     * Show Applicant Dashboard.
     */
    public function dashboard(Applicant $applicant): View
    {
        $applicant->load(['admissionWave', 'targetStudyProgram.faculty', 'user']);
        return view('pmb.status', compact('applicant'));
    }

    /**
     * Promote passed Applicant to active Student.
     */
    public function promote(Applicant $applicant, \App\Actions\GenerateNimAction $generateNimAction): RedirectResponse|JsonResponse
    {
        $student = $generateNimAction->execute($applicant);
        $msg = "Calon mahasiswa {$applicant->registration_number} berhasil dipromosikan menjadi mahasiswa aktif dengan NIM: {$student->nim}.";
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'nim' => $student->nim]);
        }
        return back()->with('success', $msg);
    }
}
