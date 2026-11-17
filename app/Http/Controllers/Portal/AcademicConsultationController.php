<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ConsultationMessage;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicConsultationController extends Controller
{
    /**
     * Retrieve consultation chat thread messages.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;

        $studentId = null;
        $lecturerId = null;

        if ($role === 'student' && $user->student) {
            $studentId = $user->student->id;
            $lecturerId = $request->input('lecturer_id', $user->student->academic_advisor_id);
        } elseif ($role === 'lecturer' && $user->lecturer) {
            $lecturerId = $user->lecturer->id;
            $studentId = $request->input('student_id');
        } elseif (in_array($role, ['superadmin', 'baak', 'kaprodi'])) {
            $studentId = $request->input('student_id');
            $lecturerId = $request->input('lecturer_id');
        }

        if (!$studentId || !$lecturerId) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi Mahasiswa dan Dosen PA belum ditentukan.',
                'data' => [],
            ], 422);
        }

        $contextType = $request->input('context_type', 'krs');

        // Mark incoming unread messages as read
        ConsultationMessage::where('student_id', $studentId)
            ->where('lecturer_id', $lecturerId)
            ->where('context_type', $contextType)
            ->where('sender_user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ConsultationMessage::with('sender')
            ->where('student_id', $studentId)
            ->where('lecturer_id', $lecturerId)
            ->where('context_type', $contextType)
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        $unreadCount = ConsultationMessage::where('student_id', $studentId)
            ->where('lecturer_id', $lecturerId)
            ->where('sender_user_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => $messages,
            'unread_count' => $unreadCount,
            'current_user_id' => $user->id,
        ]);
    }

    /**
     * Post a new consultation message.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'context_type' => 'nullable|string|in:krs,thesis,general',
            'student_id' => 'nullable|string',
            'lecturer_id' => 'nullable|string',
        ]);

        $studentId = $validated['student_id'] ?? null;
        $lecturerId = $validated['lecturer_id'] ?? null;

        if ($role === 'student' && $user->student) {
            $studentId = $user->student->id;
            $lecturerId = $lecturerId ?: $user->student->academic_advisor_id;
        } elseif ($role === 'lecturer' && $user->lecturer) {
            $lecturerId = $user->lecturer->id;
        }

        if (!$studentId || !$lecturerId) {
            return response()->json([
                'success' => false,
                'message' => 'ID Mahasiswa atau Dosen Pembimbing tidak valid.',
            ], 422);
        }

        $contextType = $validated['context_type'] ?? 'krs';

        $msg = ConsultationMessage::create([
            'student_id' => $studentId,
            'lecturer_id' => $lecturerId,
            'sender_user_id' => $user->id,
            'context_type' => $contextType,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        // Send In-App Notification to recipient
        $student = Student::find($studentId);
        $lecturer = Lecturer::find($lecturerId);

        if ($role === 'student' && $lecturer?->user) {
            NotificationService::notify(
                $lecturer->user,
                'consultation_message',
                "Pesan Konsultasi dari Mahasiswa: {$user->full_name}",
                substr($msg->message, 0, 100),
                '/portal/lecturer/advisees'
            );
        } elseif ($role === 'lecturer' && $student?->user) {
            NotificationService::notify(
                $student->user,
                'consultation_message',
                "Catatan Konsultasi dari Dosen PA: {$user->full_name}",
                substr($msg->message, 0, 100),
                '/portal/student/krs'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesan konsultasi berhasil dikirimkan.',
            'data' => $msg->load('sender'),
        ], 201);
    }
}
