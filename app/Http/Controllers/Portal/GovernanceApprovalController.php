<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\KrsItem;
use App\Models\StudentInvoice;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\OutboundNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GovernanceApprovalController extends Controller
{
    /**
     * Display Governance Approvals Inbox (Dual-Control Maker-Checker Gate).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $userRole = $user->primary_role ?? $user->role;

        $query = ApprovalRequest::with(['maker', 'checker'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('workflow_type')) {
            $query->where('workflow_type', $request->input('workflow_type'));
        }

        // Checkers see matching queue; Makers see their created requests
        if ($userRole !== 'superadmin') {
            $query->where(function ($q) use ($user, $userRole) {
                $q->where('checker_role_required', $userRole)
                  ->orWhere('maker_user_id', $user->id);
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        $stats = [
            'pending' => ApprovalRequest::where('status', 'pending')->count(),
            'approved' => ApprovalRequest::where('status', 'approved')->count(),
            'rejected' => ApprovalRequest::where('status', 'rejected')->count(),
        ];

        return view('portal.governance.approvals', [
            'requests' => $requests,
            'stats' => $stats,
            'user' => $user,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Maker Action: Create a new pending Approval Request.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'workflow_type' => 'required|string|in:grade_change_after_lock,tuition_waiver_or_discount,krs_late_override,account_privilege_elevation',
            'checker_role_required' => 'required|string|in:kaprodi,dean,bauk,superadmin',
            'target_model_type' => 'required|string',
            'target_model_id' => 'required|string',
            'maker_reason' => 'required|string|min:5',
            'payload_diff' => 'nullable|array',
        ]);

        $requestCode = 'APR-' . date('Y') . '-' . strtoupper(Str::random(6));

        $payloadDiff = $validated['payload_diff'] ?? [];

        // If specific payload not manually passed, build standard diffs based on workflow
        if (empty($payloadDiff)) {
            if ($validated['workflow_type'] === 'grade_change_after_lock') {
                $krsItem = KrsItem::find($validated['target_model_id']);
                if ($krsItem) {
                    $payloadDiff = [
                        'before' => [
                            'score_uts' => $krsItem->score_uts,
                            'score_uas' => $krsItem->score_uas,
                            'final_numeric_score' => $krsItem->final_numeric_score,
                            'letter_grade' => $krsItem->letter_grade,
                        ],
                        'after' => [
                            'score_uts' => $request->input('proposed_score_uts', $krsItem->score_uts),
                            'score_uas' => $request->input('proposed_score_uas', 85.0),
                            'final_numeric_score' => $request->input('proposed_final_score', 85.0),
                            'letter_grade' => $request->input('proposed_letter_grade', 'A'),
                        ],
                    ];
                }
            } elseif ($validated['workflow_type'] === 'tuition_waiver_or_discount') {
                $invoice = StudentInvoice::find($validated['target_model_id']);
                if ($invoice) {
                    $discountPct = (float)$request->input('discount_percentage', 50.0);
                    $newAmount = max(0, (float)$invoice->amount * (1 - ($discountPct / 100)));
                    $payloadDiff = [
                        'before' => [
                            'amount' => $invoice->amount,
                            'waiver_applied' => false,
                        ],
                        'after' => [
                            'amount' => $newAmount,
                            'discount_percentage' => $discountPct,
                            'waiver_applied' => true,
                        ],
                    ];
                }
            }
        }

        $approval = ApprovalRequest::create([
            'request_code' => $requestCode,
            'workflow_type' => $validated['workflow_type'],
            'maker_user_id' => $user->id,
            'checker_role_required' => $validated['checker_role_required'],
            'target_model_type' => $validated['target_model_type'],
            'target_model_id' => $validated['target_model_id'],
            'payload_diff' => $payloadDiff,
            'maker_reason' => $validated['maker_reason'],
            'status' => 'pending',
        ]);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'MAKER_APPROVAL_INITIATED',
            'auditable_type' => ApprovalRequest::class,
            'auditable_id' => $approval->id,
            'new_values' => ['request_code' => $requestCode, 'workflow_type' => $approval->workflow_type],
            'reason' => "Maker initiated dual-control approval request [{$requestCode}]: {$approval->maker_reason}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Outbound Notification to Checker role
        OutboundNotificationService::dispatchWhatsApp(
            '081198765432',
            "Permohonan Otorisasi Baru: {$requestCode}",
            "Maker [{$user->full_name}] mengajukan permohonan otorisasi dual-control [{$approval->workflow_type}]. Memerlukan persetujuan peran [{$approval->checker_role_required}].",
            'APPROVAL_REQUIRED'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permohonan otorisasi [{$requestCode}] berhasil diajukan dan menunggu persetujuan Checker.",
                'data' => $approval,
            ], 201);
        }

        return redirect()->back()->with('success', "Permohonan otorisasi [{$requestCode}] berhasil diajukan.");
    }

    /**
     * Checker Action: Approve & Execute Mutation Atomically.
     */
    public function approve(Request $request, ApprovalRequest $approvalRequest): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        // 1. Dual Control Check: Maker cannot be Checker!
        if ($approvalRequest->maker_user_id === $user->id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zero-Trust Dual Control: Pembuat permohonan (Maker) dilarang menyetujui permintaannya sendiri.',
                ], 403);
            }
            abort(403, 'Zero-Trust Dual Control: Pembuat permohonan (Maker) dilarang menyetujui permintaannya sendiri.');
        }

        // 2. Checker Role Authorization Check
        if (!$approvalRequest->canBeCheckedBy($user)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Akses Ditolak: Permohonan ini hanya dapat disetujui oleh peran [{$approvalRequest->checker_role_required}].",
                ], 403);
            }
            abort(403, "Akses Ditolak: Permohonan ini hanya dapat disetujui oleh peran [{$approvalRequest->checker_role_required}].");
        }

        if ($approvalRequest->status !== 'pending') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Permohonan ini telah berstatus [{$approvalRequest->status}] sebelumnya.",
                ], 422);
            }
            return redirect()->back()->with('error', "Permohonan ini telah diproses sebelumnya.");
        }

        $checkerNotes = $request->input('checker_notes', 'Disetujui dan dieksekusi secara resmi melalui Dual-Control Governance Gate.');

        // 3. Atomic Execution inside DB Transaction
        DB::transaction(function () use ($approvalRequest, $user, $checkerNotes, $request) {
            $diff = $approvalRequest->payload_diff;
            $after = $diff['after'] ?? [];

            // Apply specific model mutation
            if ($approvalRequest->workflow_type === 'grade_change_after_lock') {
                $krsItem = KrsItem::find($approvalRequest->target_model_id);
                if ($krsItem) {
                    if (isset($after['score_uts'])) $krsItem->score_uts = $after['score_uts'];
                    if (isset($after['score_uas'])) $krsItem->score_uas = $after['score_uas'];
                    if (isset($after['final_numeric_score'])) $krsItem->final_numeric_score = $after['final_numeric_score'];
                    if (isset($after['letter_grade'])) {
                        $krsItem->letter_grade = $after['letter_grade'];
                        $pts = KrsItem::convertToGradePoint((float)($after['final_numeric_score'] ?? 85.0));
                        $krsItem->grade_point = $pts['point'];
                    }
                    $krsItem->save();
                }
            } elseif ($approvalRequest->workflow_type === 'tuition_waiver_or_discount') {
                $invoice = StudentInvoice::find($approvalRequest->target_model_id);
                if ($invoice) {
                    if (isset($after['amount'])) {
                        $invoice->amount = (float)$after['amount'];
                        if ((float)$invoice->paid_amount >= (float)$invoice->amount) {
                            $invoice->payment_status = 'paid';
                        }
                    }
                    $invoice->save();
                }
            } elseif ($approvalRequest->workflow_type === 'account_privilege_elevation') {
                $targetUser = User::find($approvalRequest->target_model_id);
                if ($targetUser && isset($after['privilege_slug'])) {
                    $targetUser->grantPrivilege($after['privilege_slug']);
                }
            }

            // Update Approval Request record
            $approvalRequest->update([
                'status' => 'approved',
                'checker_user_id' => $user->id,
                'checker_notes' => $checkerNotes,
                'resolved_at' => now(),
            ]);

            // Immutable Audit Log with both Maker and Checker IDs
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'MAKER_CHECKER_APPROVED',
                'auditable_type' => $approvalRequest->target_model_type,
                'auditable_id' => $approvalRequest->target_model_id,
                'old_values' => $diff['before'] ?? [],
                'new_values' => array_merge($diff['after'] ?? [], [
                    'maker_user_id' => $approvalRequest->maker_user_id,
                    'checker_user_id' => $user->id,
                    'request_code' => $approvalRequest->request_code,
                ]),
                'reason' => "Dual-Control Approval [{$approvalRequest->request_code}] approved by Checker [{$user->full_name}]. Notes: {$checkerNotes}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            // Notify Maker
            if ($approvalRequest->maker) {
                NotificationService::notify(
                    $approvalRequest->maker,
                    'approval_approved',
                    "Permohonan [{$approvalRequest->request_code}] Disetujui",
                    "Permohonan otorisasi Anda telah disetujui resmi oleh Checker ({$user->full_name}).",
                    '/portal/governance/approvals'
                );

                OutboundNotificationService::dispatchWhatsApp(
                    $approvalRequest->maker->email,
                    "Permohonan Disetujui: {$approvalRequest->request_code}",
                    "Permohonan [{$approvalRequest->workflow_type}] Anda telah disetujui resmi oleh Checker [{$user->full_name}]. Perubahan telah diterapkan di sistem.",
                    'APPROVAL_RESOLVED'
                );
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permohonan [{$approvalRequest->request_code}] berhasil disetujui dan mutasi data telah diterapkan secara otomatis.",
                'data' => $approvalRequest->fresh(),
            ]);
        }

        return redirect()->back()->with('success', "Permohonan [{$approvalRequest->request_code}] berhasil disetujui dan dieksekusi.");
    }

    /**
     * Checker Action: Reject Request.
     */
    public function reject(Request $request, ApprovalRequest $approvalRequest): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($approvalRequest->maker_user_id === $user->id) {
            abort(403, 'Zero-Trust Dual Control: Pembuat permohonan tidak dapat menolak permintaannya sendiri.');
        }

        if (!$approvalRequest->canBeCheckedBy($user)) {
            abort(403, "Akses Ditolak: Permohonan ini hanya dapat ditolak oleh peran [{$approvalRequest->checker_role_required}].");
        }

        $checkerNotes = $request->input('checker_notes', 'Permohonan ditolak oleh otoritas pengawas (Checker).');

        $approvalRequest->update([
            'status' => 'rejected',
            'checker_user_id' => $user->id,
            'checker_notes' => $checkerNotes,
            'resolved_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'MAKER_CHECKER_REJECTED',
            'auditable_type' => $approvalRequest->target_model_type,
            'auditable_id' => $approvalRequest->target_model_id,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'rejected', 'checker_notes' => $checkerNotes],
            'reason' => "Dual-Control Approval [{$approvalRequest->request_code}] rejected by Checker [{$user->full_name}].",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permohonan [{$approvalRequest->request_code}] telah ditolak.",
            ]);
        }

        return redirect()->back()->with('warning', "Permohonan [{$approvalRequest->request_code}] telah ditolak.");
    }
}
