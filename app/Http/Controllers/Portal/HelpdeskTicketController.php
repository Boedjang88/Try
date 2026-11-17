<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Services\NotificationService;
use App\Services\OutboundNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HelpdeskTicketController extends Controller
{
    /**
     * Display Helpdesk Ticketing Center and SLA Dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;

        $isStaff = in_array($role, ['superadmin', 'baak', 'bauk']);

        $query = SupportTicket::with(['user', 'assignedTo'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Standard users only see their own tickets unless staff
        if (!$isStaff) {
            $query->where('user_id', $user->id);
        } elseif ($role === 'baak') {
            if (!$request->filled('all')) $query->where('department', 'baak');
        } elseif ($role === 'bauk') {
            if (!$request->filled('all')) $query->where('department', 'bauk');
        }

        $tickets = $query->paginate(15)->withQueryString();

        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        return view('portal.helpdesk.index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'user' => $user,
            'role' => $role,
            'isStaff' => $isStaff,
        ]);
    }

    /**
     * Create a new support ticket with automated SLA countdown target.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'department' => 'required|string|in:bauk,baak,it_support',
            'category' => 'required|string|max:100',
            'subject' => 'required|string|min:5|max:200',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'message' => 'required|string|min:10',
        ]);

        $slaHours = SupportTicket::getSlaHours($validated['priority']);
        $slaDueAt = now()->addHours($slaHours);
        $ticketNumber = 'TICK-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $user->id,
            'department' => $validated['department'],
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'sla_due_at' => $slaDueAt,
        ]);

        // Create opening reply/description
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_internal_note' => false,
        ]);

        // Outbound notification to user confirming ticket & SLA
        OutboundNotificationService::dispatchWhatsApp(
            $user->email,
            "Tiket Bantuan Dibuka: {$ticketNumber}",
            "Tiket Anda [{$ticket->subject}] telah resmi terdaftar pada biro [{$ticket->department}]. Target SLA penyelesaian: {$slaHours} Jam.",
            'HELPDESK_TICKET_OPENED'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tiket [{$ticketNumber}] berhasil dibuat dengan target SLA {$slaHours} jam.",
                'data' => $ticket->load('replies'),
            ], 201);
        }

        return redirect()->route('portal.helpdesk.show', $ticket)->with('success', "Tiket [{$ticketNumber}] berhasil didaftarkan.");
    }

    /**
     * View ticket details and thread conversation.
     */
    public function show(Request $request, SupportTicket $ticket): View
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;
        $isStaff = in_array($role, ['superadmin', 'baak', 'bauk']);

        // Anti-IDOR check
        if (!$isStaff && $ticket->user_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang membuka tiket pengguna lain.');
        }

        $ticket->load(['user', 'assignedTo', 'replies.user']);

        return view('portal.helpdesk.show', [
            'ticket' => $ticket,
            'user' => $user,
            'isStaff' => $isStaff,
        ]);
    }

    /**
     * Post a reply or staff internal note to the ticket.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;
        $isStaff = in_array($role, ['superadmin', 'baak', 'bauk']);

        if (!$isStaff && $ticket->user_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang membalas tiket ini.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:2',
            'is_internal_note' => 'nullable|boolean',
        ]);

        $isInternal = $isStaff && (bool)($validated['is_internal_note'] ?? false);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_internal_note' => $isInternal,
        ]);

        // Auto update open ticket to in_progress if staff replies
        if ($isStaff && $ticket->status === 'open' && !$isInternal) {
            $ticket->status = 'in_progress';
            if (!$ticket->assigned_to_user_id) {
                $ticket->assigned_to_user_id = $user->id;
            }
            $ticket->save();
        }

        // Notify customer when staff replies
        if ($isStaff && !$isInternal && $ticket->user) {
            NotificationService::notify(
                $ticket->user,
                'ticket_reply',
                "Tanggapan Baru pada Tiket {$ticket->ticket_number}",
                substr($validated['message'], 0, 100),
                "/portal/helpdesk/tickets/{$ticket->id}"
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tanggapan berhasil dikirim.',
                'data' => $reply->load('user'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Tanggapan berhasil dikirim.');
    }

    /**
     * Update ticket status (e.g. In Progress, Resolved, Closed).
     */
    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;
        $isStaff = in_array($role, ['superadmin', 'baak', 'bauk']);

        if (!$isStaff) {
            abort(403, 'Hanya staf biro atau administrator yang dapat mengubah status tiket.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,resolved,closed',
        ]);

        $ticket->status = $validated['status'];
        if ($validated['status'] === 'resolved') {
            $ticket->resolved_at = now();
        } elseif ($validated['status'] === 'in_progress') {
            if (!$ticket->assigned_to_user_id) {
                $ticket->assigned_to_user_id = $user->id;
            }
        }
        $ticket->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'HELPDESK_STATUS_UPDATED',
            'auditable_type' => SupportTicket::class,
            'auditable_id' => $ticket->id,
            'new_values' => ['status' => $ticket->status],
            'reason' => "Staff [{$user->full_name}] updated ticket [{$ticket->ticket_number}] status to [{$ticket->status}].",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($ticket->user) {
            NotificationService::notify(
                $ticket->user,
                'ticket_status',
                "Status Tiket Diperbarui: {$ticket->ticket_number}",
                "Tiket Anda kini berstatus: " . strtoupper(str_replace('_', ' ', $ticket->status)),
                "/portal/helpdesk/tickets/{$ticket->id}"
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status tiket [{$ticket->ticket_number}] berhasil diperbarui.",
                'data' => $ticket,
            ]);
        }

        return redirect()->back()->with('success', "Status tiket berhasil diperbarui.");
    }
}
