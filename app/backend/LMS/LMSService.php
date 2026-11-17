<?php

namespace App\Backend\LMS;

use Exception;

/**
 * Enterprise LMS & Daily Lecture Operations Service
 * Covers 16-week session timeline, dynamic rotating QR token validation,
 * GPS geofence distance calculation, BAP recording, and MBKM credit conversion.
 */
class LMSService
{
    // Default Campus Geofence: UPH Lippo Village Main Campus (Building D / Pelita Hall)
    public const CAMPUS_LATITUDE  = -6.225574;
    public const CAMPUS_LONGITUDE = 106.608316;
    public const MAX_ALLOWED_DISTANCE_METERS = 300.0; // 300m radius threshold

    /**
     * Generate standard 16-week syllabus timeline for a university course.
     */
    public static function generateCourseSessions(string $courseId, string $courseName, int $sks): array
    {
        $sessions = [];
        for ($i = 1; $i <= 16; $i++) {
            if ($i === 8) {
                $sessionType = 'UTS';
                $title = 'Ujian Tengah Semester (UTS)';
                $cpmk = 'Evaluasi CPMK 1 & 2';
            } elseif ($i === 16) {
                $sessionType = 'UAS';
                $title = 'Ujian Akhir Semester (UAS)';
                $cpmk = 'Evaluasi Komprehensif Seluruh CPMK';
            } else {
                $sessionType = 'Kuliah';
                $title = "Sesi Perkuliahan {$i}: Topik Materi Pembelajaran Pekan {$i}";
                $cpmk = "CPMK-" . (int)(($i + 3) / 4);
            }

            $sessions[] = [
                'session_number' => $i,
                'session_type'   => $sessionType,
                'title'          => $title,
                'target_cpmk'    => $cpmk,
                'materials_count'=> $sessionType === 'Kuliah' ? 2 : 0,
                'assignment_due' => in_array($i, [4, 7, 11, 14], true) ? date('Y-m-d', strtotime("+{$i} weeks")) : null,
                'is_completed'   => $i < 7
            ];
        }

        return $sessions;
    }

    /**
     * Generate Dynamic Rotating QR Token with 15-second time-step (TOTP-like).
     */
    public static function generateDynamicQRToken(string $classOfferingId, int $sessionNumber): array
    {
        $timeWindow = (int)(time() / 15);
        $secret = 'UPH_SIAKAD_SECRET_SALT_2026';
        $token = hash_hmac('sha256', "{$classOfferingId}:{$sessionNumber}:{$timeWindow}", $secret);

        return [
            'class_offering_id' => $classOfferingId,
            'session_number'    => $sessionNumber,
            'token'             => substr($token, 0, 32),
            'expires_in_seconds'=> 15 - (time() % 15),
            'generated_at'      => time()
        ];
    }

    /**
     * Verify QR Token for Student Attendance.
     */
    public static function verifyQRToken(string $classOfferingId, int $sessionNumber, string $scannedToken): bool
    {
        $currentTimeWindow = (int)(time() / 15);
        $secret = 'UPH_SIAKAD_SECRET_SALT_2026';

        // Check current and immediately preceding window (to tolerate network latency)
        for ($offset = 0; $offset <= 1; $offset++) {
            $expected = substr(hash_hmac('sha256', "{$classOfferingId}:{$sessionNumber}:" . ($currentTimeWindow - $offset), $secret), 0, 32);
            if (hash_equals($expected, $scannedToken)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate Geofence Distance using Haversine formula (in meters).
     */
    public static function calculateGeofenceDistance(float $studentLat, float $studentLon): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad(self::CAMPUS_LATITUDE);
        $lonFrom = deg2rad(self::CAMPUS_LONGITUDE);
        $latTo = deg2rad($studentLat);
        $lonTo = deg2rad($studentLon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Validate Student Presensi via GPS & QR.
     */
    public static function validateSmartPresensi(
        string $classOfferingId,
        int $sessionNumber,
        string $token,
        float $studentLat,
        float $studentLon
    ): array {
        $qrValid = self::verifyQRToken($classOfferingId, $sessionNumber, $token);
        $distance = self::calculateGeofenceDistance($studentLat, $studentLon);
        $gpsValid = $distance <= self::MAX_ALLOWED_DISTANCE_METERS;

        $status = ($qrValid && $gpsValid) ? 'HADIR' : 'INVALID';
        $reason = null;

        if (!$qrValid) {
            $reason = 'Token QR sudah kedaluwarsa atau tidak sah.';
        } elseif (!$gpsValid) {
            $reason = "Perangkat berada di luar radius kampus ({$distance} meter, batas: " . self::MAX_ALLOWED_DISTANCE_METERS . "m).";
        }

        return [
            'status'            => $status,
            'qr_valid'          => $qrValid,
            'gps_valid'         => $gpsValid,
            'distance_meters'   => $distance,
            'reason'            => $reason,
            'timestamp'         => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Sign Berita Acara Perkuliahan (BAP).
     */
    public static function createBAPRecord(
        string $classOfferingId,
        int $sessionNumber,
        string $lecturerUserId,
        string $topicDelivered,
        string $teachingMethod,
        int $studentsPresent,
        int $studentsTotal
    ): array {
        $attendancePercentage = round(($studentsPresent / max(1, $studentsTotal)) * 100, 2);
        $bapCode = 'BAP/' . date('Ymd') . '/' . substr(md5($classOfferingId . $sessionNumber), 0, 6);

        return [
            'bap_code'              => $bapCode,
            'session_number'        => $sessionNumber,
            'topic_delivered'       => $topicDelivered,
            'teaching_method'       => $teachingMethod,
            'students_present'      => $studentsPresent,
            'students_total'        => $studentsTotal,
            'attendance_percentage' => $attendancePercentage,
            'lecturer_signed_at'    => date('Y-m-d H:i:s'),
            'status'                => 'VERIFIED'
        ];
    }

    /**
     * MBKM (Merdeka Belajar Kampus Merdeka) 20 SKS Equivalence Conversion Engine.
     */
    public static function convertMBKMProgram(
        string $programType, // Magang Bersertifikat, Studi Independen, Pertukaran Mahasiswa, Riset
        string $partnerName,
        array $targetCourses
    ): array {
        $totalSks = 0;
        $conversions = [];

        foreach ($targetCourses as $course) {
            $totalSks += $course['sks'];
            $conversions[] = [
                'course_code' => $course['code'],
                'course_name' => $course['name'],
                'sks'         => $course['sks'],
                'equivalent'  => true
            ];
        }

        return [
            'program_type'        => $programType,
            'partner_name'        => $partnerName,
            'total_converted_sks' => $totalSks,
            'is_valid_20_sks'     => $totalSks === 20,
            'converted_courses'   => $conversions,
            'approval_status'     => 'APPROVED_BY_KAPRODI'
        ];
    }
}
