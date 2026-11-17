<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FacilityBooking;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\TracerStudy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampusLifeController extends Controller
{
    /**
     * E-Library & Smart Facility/Lab Booking Workspace.
     */
    public function campusLifeIndex(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        $loans = collect();
        $bookings = collect();

        if ($student) {
            $loans = LibraryLoan::where('student_id', $student->id)
                ->orderByDesc('borrowed_at')
                ->get();

            $bookings = FacilityBooking::where('student_id', $student->id)
                ->orderByDesc('start_time')
                ->get();
        } else {
            $loans = LibraryLoan::with('student.user')
                ->latest()
                ->take(30)
                ->get();

            $bookings = FacilityBooking::with(['student.user', 'user'])
                ->latest()
                ->take(30)
                ->get();
        }

        return view('portal.student.campus-life', compact('student', 'loans', 'bookings'));
    }

    /**
     * Store new Facility / Lab Booking.
     */
    public function storeFacilityBooking(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        $validated = $request->validate([
            'facility_name' => 'required|string|max:150',
            'facility_type' => 'required|in:lab,discussion_room,equipment',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'required|string|max:500',
        ]);

        $booking = FacilityBooking::create([
            'id' => (string) Str::uuid(),
            'student_id' => $student?->id,
            'user_id' => $user->id,
            'facility_name' => $validated['facility_name'],
            'facility_type' => $validated['facility_type'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'FACILITY_BOOKING_CREATE',
            'auditable_type' => 'FacilityBooking',
            'auditable_id' => $booking->id,
            'old_values' => null,
            'new_values' => $booking->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Reservasi fasilitas/lab berhasil diajukan dan sedang menunggu verifikasi.');
    }

    /**
     * Career Portal & Mandatory Alumni Tracer Study.
     */
    public function careerTracerIndex(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        $existingTracer = null;
        if ($student) {
            $existingTracer = TracerStudy::where('student_id', $student->id)->latest()->first();
        }

        // Aggregate Tracer metrics for BAN-PT / LAM Criteria 9
        $totalResponses = TracerStudy::count();
        $employedCount = TracerStudy::whereIn('employment_status', ['employed', 'entrepreneur'])->count();
        $employedPercentage = $totalResponses > 0 ? round(($employedCount / $totalResponses) * 100, 1) : 0.0;

        $avgWaitingTime = $totalResponses > 0 ? round(TracerStudy::avg('waiting_time_months'), 1) : 0.0;

        $highAlignmentCount = TracerStudy::where('job_major_alignment', 'high')->count();
        $alignmentPercentage = $totalResponses > 0 ? round(($highAlignmentCount / $totalResponses) * 100, 1) : 0.0;

        $metrics = [
            'total_responses' => $totalResponses,
            'employed_percentage' => $employedPercentage,
            'avg_waiting_time_months' => $avgWaitingTime,
            'high_alignment_percentage' => $alignmentPercentage,
        ];

        // Sample partner company job board
        $jobListings = [
            [
                'company' => 'PT Bank Central Asia Tbk (BCA)',
                'title' => 'Associate Solutions Architect',
                'type' => 'Full-Time',
                'location' => 'Menara BCA, Jakarta',
                'tags' => ['Cloud', 'Distributed Systems', 'Laravel/Go'],
                'deadline' => '30 Nov 2026',
            ],
            [
                'company' => 'Lippo Group Digital Health',
                'title' => 'Software Engineer (Frontend / Mobile)',
                'type' => 'Full-Time / Internship',
                'location' => 'Lippo Village, Karawaci',
                'tags' => ['Alpine.js', 'Tailwind', 'Flutter'],
                'deadline' => '15 Des 2026',
            ],
            [
                'company' => 'Tokopedia / GoTo',
                'title' => 'Data Analyst & ML Specialist',
                'type' => 'Full-Time',
                'location' => 'Jakarta Selatan',
                'tags' => ['Python', 'SQL', 'Tracer Analytics'],
                'deadline' => '31 Des 2026',
            ],
        ];

        return view('portal.student.career-tracer', compact(
            'student',
            'existingTracer',
            'metrics',
            'jobListings'
        ));
    }

    /**
     * Submit Tracer Study Questionnaire.
     */
    public function submitTracerStudy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses terbatas untuk mahasiswa/alumni terdaftar.');
        }

        $validated = $request->validate([
            'graduation_year' => 'required|integer|min:2020|max:2030',
            'employment_status' => 'required|in:employed,entrepreneur,continuing_study,seeking',
            'company_name' => 'nullable|string|max:150',
            'job_title' => 'nullable|string|max:150',
            'waiting_time_months' => 'required|integer|min:0|max:60',
            'monthly_income_range' => 'required|string|max:50',
            'job_major_alignment' => 'required|in:high,moderate,low',
        ]);

        $tracer = TracerStudy::updateOrCreate(
            [
                'student_id' => $student->id,
            ],
            [
                'id' => (string) Str::uuid(),
                'graduation_year' => $validated['graduation_year'],
                'employment_status' => $validated['employment_status'],
                'company_name' => $validated['company_name'],
                'job_title' => $validated['job_title'],
                'waiting_time_months' => $validated['waiting_time_months'],
                'monthly_income_range' => $validated['monthly_income_range'],
                'job_major_alignment' => $validated['job_major_alignment'],
                'submitted_at' => now(),
            ]
        );

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'TRACER_STUDY_SUBMIT',
            'auditable_type' => 'TracerStudy',
            'auditable_id' => $tracer->id,
            'old_values' => null,
            'new_values' => $tracer->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Kuesioner Tracer Study Kemdiktisaintek berhasil disimpan. Terima kasih atas kontribusi Anda.');
    }
}
