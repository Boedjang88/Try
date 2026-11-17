<?php

namespace Database\Seeders;

use App\Models\ApprovalRequest;
use App\Models\ConsultationMessage;
use App\Models\KrsItem;
use App\Models\Lecturer;
use App\Models\OutboundDispatch;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\OutboundNotificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Stage16GovernanceAndSandboxSeeder extends Seeder
{
    /**
     * Run the Stage 16 database seeds.
     */
    public function run(): void
    {
        $lecturerUser = User::where('email', 'lecturer1@uph.edu')->first();
        $studentUser = User::where('email', 'student1@uph.edu')->first();
        $studentBlockedUser = User::where('email', 'student.blocked@uph.edu')->first();
        $baakUser = User::where('email', 'baak@uph.edu')->first();

        $student = $studentUser?->student;
        $lecturer = $lecturerUser?->lecturer;

        // 1. Seed Pending Maker-Checker Dual-Control Request
        $krsItem = KrsItem::first();
        if ($krsItem && $lecturerUser) {
            ApprovalRequest::firstOrCreate(['request_code' => 'APR-2026-X8910'], [
                'id' => (string) Str::uuid(),
                'workflow_type' => 'grade_change_after_lock',
                'maker_user_id' => $lecturerUser->id,
                'checker_role_required' => 'kaprodi',
                'target_model_type' => KrsItem::class,
                'target_model_id' => (string) $krsItem->id,
                'payload_diff' => [
                    'before' => [
                        'score_uts' => 90.0,
                        'score_uas' => 92.0,
                        'final_numeric_score' => 90.9,
                        'letter_grade' => 'A',
                    ],
                    'after' => [
                        'score_uts' => 92.0,
                        'score_uas' => 96.0,
                        'final_numeric_score' => 93.8,
                        'letter_grade' => 'A',
                    ],
                ],
                'maker_reason' => 'Mahasiswa Christian Jonathan menyerahkan kelengkapan tugas komputasi awan pasca-kunci. Diusulkan koreksi nilai akhir.',
                'status' => 'pending',
            ]);
        }

        // 2. Seed Real-Time Academic Consultation Messages Thread
        if ($student && $lecturer && $studentUser && $lecturerUser) {
            ConsultationMessage::firstOrCreate([
                'student_id' => $student->id,
                'lecturer_id' => $lecturer->id,
                'context_type' => 'krs',
                'message' => 'Selamat pagi Dr. Johanes Tan, mohon izin konsultasi terkait pemilihan matakuliah pilihan Kecerdasan Buatan dan beban SKS semester ini.',
            ], [
                'id' => (string) Str::uuid(),
                'sender_user_id' => $studentUser->id,
                'is_read' => true,
                'created_at' => now()->subHours(5),
            ]);

            ConsultationMessage::firstOrCreate([
                'student_id' => $student->id,
                'lecturer_id' => $lecturer->id,
                'context_type' => 'krs',
                'message' => 'Selamat pagi Christian. Kombinasi matakuliah Anda sudah sangat baik. Pastikan mempersiapkan topik proposal skripsi dari sekarang.',
            ], [
                'id' => (string) Str::uuid(),
                'sender_user_id' => $lecturerUser->id,
                'is_read' => true,
                'created_at' => now()->subHours(4),
            ]);

            ConsultationMessage::firstOrCreate([
                'student_id' => $student->id,
                'lecturer_id' => $lecturer->id,
                'context_type' => 'krs',
                'message' => 'Baik Dok, KRS sudah saya revisi sesuai arahan Dok.',
            ], [
                'id' => (string) Str::uuid(),
                'sender_user_id' => $studentUser->id,
                'is_read' => false,
                'created_at' => now()->subHour(),
            ]);
        }

        // 3. Seed SLA Helpdesk Tickets
        if ($studentUser) {
            $t1 = SupportTicket::firstOrCreate(['ticket_number' => 'TICK-2026-0001'], [
                'id' => (string) Str::uuid(),
                'user_id' => $studentUser->id,
                'department' => 'bauk',
                'category' => 'Rekonsiliasi Pembayaran Virtual Account',
                'subject' => 'Konfirmasi Pelunasan Tagihan UKT via BCA VA',
                'priority' => 'high',
                'status' => 'open',
                'sla_due_at' => now()->addHours(24),
            ]);

            TicketReply::firstOrCreate(['ticket_id' => $t1->id], [
                'id' => (string) Str::uuid(),
                'user_id' => $studentUser->id,
                'message' => 'Selamat siang tim BAUK, saya telah melakukan transfer pelunasan tagihan melalui BCA VA pada pukul 10:00 WIB, mohon bantuannya untuk rekonsiliasi.',
                'is_internal_note' => false,
            ]);
        }

        if ($studentBlockedUser) {
            $t2 = SupportTicket::firstOrCreate(['ticket_number' => 'TICK-2026-0002'], [
                'id' => (string) Str::uuid(),
                'user_id' => $studentBlockedUser->id,
                'department' => 'baak',
                'category' => 'Administrasi KRS & Jadwal Ujian',
                'subject' => 'Permohonan Dispensasi Kehadiran Medis untuk UAS',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'sla_due_at' => now()->addHours(12),
                'assigned_to_user_id' => $baakUser?->id,
            ]);

            TicketReply::firstOrCreate(['ticket_id' => $t2->id, 'user_id' => $studentBlockedUser->id], [
                'id' => (string) Str::uuid(),
                'message' => 'Selamat pagi BAAK, saya izin mengajukan surat dispensasi kehadiran karena rawat inap medis.',
                'is_internal_note' => false,
            ]);

            if ($baakUser) {
                TicketReply::firstOrCreate(['ticket_id' => $t2->id, 'user_id' => $baakUser->id], [
                    'id' => (string) Str::uuid(),
                    'message' => 'Surat keterangan dokter telah kami terima dan sedang diverifikasi oleh Kaprodi terkait.',
                    'is_internal_note' => false,
                ]);
            }
        }

        // 4. Seed Assigned Exam Seat Numbers for KrsItems
        $items = KrsItem::all();
        $rows = ['A', 'B', 'C', 'D'];
        foreach ($items as $idx => $item) {
            $row = $rows[$idx % count($rows)];
            $seatNum = str_pad(($idx + 1), 2, '0', STR_PAD_LEFT);
            $item->exam_seat_number = "ROW-{$row} / SEAT-{$seatNum}";
            $item->save();
        }

        // 5. Seed Outbound Dispatches (WhatsApp & Email)
        OutboundNotificationService::dispatchWhatsApp(
            '081082200001',
            'Persetujuan Rencana Studi (KRS War)',
            "KRS Semester Ganjil 2025/2026 Anda telah disetujui resmi oleh Dosen Pembimbing Akademik Dr. Ir. Johanes Tan, M.Sc. Silakan cetak kartu rencana studi.",
            'KRS_APPROVED'
        );

        OutboundNotificationService::dispatchEmail(
            'student1@uph.edu',
            'Kwitansi Resmi Pelunasan Biaya Kuliah UPH',
            "<p>Halo <strong>Christian Jonathan</strong>,</p><p>Terima kasih. Pembayaran tagihan semester Anda telah diverifikasi lunas secara otomatis melalui Bank Virtual Account BCA.</p><p>Status registrasi akademik Anda kini <strong>AKTIF / CLEARED</strong>.</p>",
            'VA_PAID'
        );
    }
}
