<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditLogExplorerController extends Controller
{
    /**
     * Executive Global Audit Trail Explorer.
     * Accessible to Rector, BAAK, BAUK, and Superadmin.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!in_array($user->role, ['rector', 'baak', 'bauk', 'superadmin'])) {
            abort(403, 'Akses ditolak: Hanya Rektorat dan Pimpinan Biro yang berwenang meninjau Global Audit Trail.');
        }

        $query = AuditLog::with('user')->latest();

        // Search filter
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQ) use ($search) {
                      $userQ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Action type filter
        if ($action = $request->query('action_type')) {
            $query->where('action', $action);
        }

        // Date range filters
        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(25)->withQueryString();

        $actionTypes = AuditLog::select('action')->distinct()->pluck('action');

        return view('portal.executive.audit-logs', compact('auditLogs', 'actionTypes', 'user'));
    }
}
