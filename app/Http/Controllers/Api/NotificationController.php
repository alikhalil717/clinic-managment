<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List the authenticated user's notifications, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->user_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
            ],
        ]);
    }

    /**
     * Get a single notification owned by the authenticated user.
     */
    public function show(Request $request, int $notification): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->user_id)
            ->findOrFail($notification);

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, int $notification): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->user_id)
            ->findOrFail($notification);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Count of unread notifications for the authenticated user.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::query()
            ->where('user_id', $request->user()->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }
}