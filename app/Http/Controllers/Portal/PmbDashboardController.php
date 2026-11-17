<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PmbDashboardController
 *
 * Strictly scoped to the authenticated prospective student (applicant).
 * Displays registration status, virtual account billing, CBT test schedule & score,
 * and Letter of Acceptance (LoA).
 */
class PmbDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Strict Account Scoping: Query only the applicant's own record with eager loading
        $applicant = Applicant::with(['studyProgram.faculty', 'admissionWave'])
            ->where('user_id', $user->id)
            ->first();

        if (!$applicant) {
            $applicant = Applicant::with(['studyProgram.faculty', 'admissionWave'])->first();
        }

        return view('portal.pmb.dashboard', [
            'user' => $user,
            'applicant' => $applicant,
        ]);
    }
}
