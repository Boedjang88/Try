<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\LectureSession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class Loop3QrAttendanceGeofenceTest extends TestCase
{
    /**
     * Operational Loop 3: Rotating Signed HMAC QR Code Token Generation
     * and Haversine Geolocation Geofencing Enforcement.
     */
    public function test_loop3_rotating_qr_token_and_geofenced_attendance_verification(): void
    {
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->firstOrFail();
        $lecturer = $lecturerUser->lecturer;

        $studentUser = User::where('email', 'student1@uph.edu')->firstOrFail();
        $student = $studentUser->student;

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        // Create or find a test room with defined GPS coordinates (UPH Lippo Village Building D)
        $room = Room::firstOrCreate([
            'code' => 'D-401-TEST',
        ], [
            'id' => (string) Str::uuid(),
            'name' => 'Lab Komputer D-401',
            'building' => 'Building D',
            'floor' => 4,
            'capacity' => 40,
            'latitude' => -6.2255000,
            'longitude' => 106.6080000,
            'geofence_radius_meters' => 50,
        ]);

        $course = Course::firstOrFail();

        $section = ClassSection::create([
            'id' => (string) Str::uuid(),
            'course_id' => $course->id,
            'academic_period_id' => $activePeriod->id,
            'lecturer_id' => $lecturer->id,
            'room_id' => $room->id,
            'section_code' => 'QR-SEC-01',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '12:30:00',
            'max_quota' => 40,
            'enrolled_count' => 1,
            'is_closed' => false,
        ]);

        // Enroll student into section with approved status
        $submission = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
        ], [
            'total_sks' => 3,
            'status' => 'approved',
        ]);
        $submission->status = 'approved';
        $submission->save();

        KrsItem::create([
            'id' => (string) Str::uuid(),
            'krs_submission_id' => $submission->id,
            'class_section_id' => $section->id,
        ]);

        // Create Lecture Session
        $session = LectureSession::create([
            'id' => (string) Str::uuid(),
            'class_section_id' => $section->id,
            'room_id' => $room->id,
            'session_number' => 1,
            'title' => 'Pengenalan Algoritma & Kompleksitas Komputasi',
            'planned_date' => now()->toDateString(),
            'session_type' => 'lecture',
        ]);

        // Step 1: Lecturer requests rotating signed HMAC QR token
        $tokenResponse = $this->actingAs($lecturerUser)->getJson("/api/attendance/session/{$session->id}/qr-token");
        $tokenResponse->assertStatus(200);
        $tokenResponse->assertJson(['success' => true, 'expires_in' => 15]);

        $tokenData = $tokenResponse->json();
        $qrToken = $tokenData['token'];
        $this->assertNotEmpty($qrToken);

        $session->refresh();
        $this->assertEquals($qrToken, $session->qr_code_token);
        $this->assertNotNull($session->qr_expires_at);

        // Step 2: Student attempts to scan from OUTSIDE geofence (> 50m, ~1.5 km away in Karawaci CBD)
        $farLatitude = -6.2355000;
        $farLongitude = 106.6180000;

        $rejectedScan = $this->actingAs($studentUser)->postJson('/api/attendance/scan', [
            'qr_token' => $qrToken,
            'latitude' => $farLatitude,
            'longitude' => $farLongitude,
        ]);

        $rejectedScan->assertStatus(422);
        $rejectedScan->assertJson([
            'success' => false,
            'code' => 'GEOFENCE_VIOLATION',
        ]);
        $this->assertGreaterThan(50, $rejectedScan->json('distance'));

        // Step 3: Student scans from INSIDE geofence (within 50m, e.g. exactly 5 meters away)
        $nearbyLatitude = -6.2255100;
        $nearbyLongitude = 106.6080100;

        $acceptedScan = $this->actingAs($studentUser)->postJson('/api/attendance/scan', [
            'qr_token' => $qrToken,
            'latitude' => $nearbyLatitude,
            'longitude' => $nearbyLongitude,
        ]);

        $acceptedScan->assertStatus(200);
        $acceptedScan->assertJson(['success' => true]);

        // Step 4: Assert database attendance record is marked 'present' via 'qr_scan'
        $attendance = Attendance::where('lecture_session_id', $session->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->assertEquals('present', $attendance->attendance_status);
        $this->assertEquals('qr_scan', $attendance->verified_method);
        $this->assertNotNull($attendance->scanned_at);
    }
}
