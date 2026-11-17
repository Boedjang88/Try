<?php

namespace App\Services;

use App\Models\OutboundDispatch;
use Illuminate\Support\Str;

class OutboundNotificationService
{
    /**
     * Dispatch an official WhatsApp Gateway notification and log it.
     */
    public static function dispatchWhatsApp(
        string $recipient,
        string $title,
        string $message,
        string $eventType = 'SYSTEM_ALERT',
        string $status = 'sent'
    ): OutboundDispatch {
        $formattedBody = "🏛️ *UNIVERSITAS PELITA HARAPAN*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "📌 *{$title}*\n\n"
            . "{$message}\n\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "ℹ️ _Pesan otomatis sistem SIAKAD Enterprise. Jangan membalas pesan ini._";

        return self::logDispatch('whatsapp', $recipient, $title, $formattedBody, $eventType, $status);
    }

    /**
     * Dispatch an official HTML Email notification and log it.
     */
    public static function dispatchEmail(
        string $recipient,
        string $subject,
        string $htmlBody,
        string $eventType = 'SYSTEM_ALERT',
        string $status = 'sent'
    ): OutboundDispatch {
        $fullHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .email-header { background: #00205B; padding: 24px; text-align: center; border-bottom: 3px solid #D4AF37; }
        .email-header h1 { color: #ffffff; font-size: 20px; margin: 0; font-family: serif; letter-spacing: 1px; }
        .email-header p { color: #D4AF37; font-size: 11px; margin: 4px 0 0; text-transform: uppercase; font-weight: bold; }
        .email-body { padding: 32px 24px; line-height: 1.6; }
        .email-footer { background: #f1f5f9; padding: 16px 24px; font-size: 11px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; }
        .btn-uph { display: inline-block; background: #00205B; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 13px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>UNIVERSITAS PELITA HARAPAN</h1>
            <p>Official Academic Notification System</p>
        </div>
        <div class="email-body">
            {$htmlBody}
        </div>
        <div class="email-footer">
            &copy; 2026 Universitas Pelita Harapan. All rights reserved.<br>
            Jl. M.H. Thamrin Boulevard 1100, Lippo Village, Tangerang 15811.
        </div>
    </div>
</body>
</html>
HTML;

        return self::logDispatch('email', $recipient, $subject, $fullHtml, $eventType, $status);
    }

    /**
     * Store outbound dispatch record in database.
     */
    public static function logDispatch(
        string $channel,
        string $recipient,
        string $subjectOrTitle,
        string $payloadBody,
        string $eventType,
        string $status = 'sent'
    ): OutboundDispatch {
        return OutboundDispatch::create([
            'dispatch_id' => 'DSP-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'channel' => strtolower($channel),
            'recipient' => $recipient,
            'subject_or_title' => $subjectOrTitle,
            'payload_body' => $payloadBody,
            'status' => $status,
            'event_type' => strtoupper($eventType),
        ]);
    }
}
