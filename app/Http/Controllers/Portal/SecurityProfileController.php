<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SecurityProfileController extends Controller
{
    /**
     * Display Security Telemetry & Personal Audit Trail.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Parse user agent
        $userAgent = $request->userAgent() ?? 'Unknown Browser';
        $browser = 'Browser Modern';
        $os = 'Linux / Unix';

        if (str_contains($userAgent, 'Chrome')) {
            $browser = 'Google Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Mozilla Firefox';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            $browser = 'Apple Safari';
        } elseif (str_contains($userAgent, 'Edg')) {
            $browser = 'Microsoft Edge';
        }

        if (str_contains($userAgent, 'Windows')) {
            $os = 'Microsoft Windows';
        } elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) {
            $os = 'Apple macOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Google Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'Apple iOS';
        }

        $sessionMetadata = [
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'browser' => $browser,
            'os' => $os,
            'user_agent_raw' => $userAgent,
            'last_active' => now()->format('d M Y H:i:s'),
            'role' => $user->role,
            'role_label' => strtoupper($user->role),
            'email' => $user->email,
            'is_two_factor_ready' => true,
        ];

        // Fetch personal activity history from audit_logs
        $auditLogs = AuditLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(25)
            ->get();

        return view('portal.account.security', compact('user', 'sessionMetadata', 'auditLogs'));
    }
}
