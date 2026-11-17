<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications & count for authenticated user.
     */
    public function getUnread(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        $notifications = InAppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = InAppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(Request $request, InAppNotification $notification): JsonResponse
    {
        $user = Auth::user();
        if ($notification->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai telah dibaca.',
        ]);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        InAppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Seluruh notifikasi telah ditandai dibaca.',
        ]);
    }
}
