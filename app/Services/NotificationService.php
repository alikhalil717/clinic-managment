<?php

namespace App\Services;

use App\Jobs\SendPushNotificationJob;
use App\Models\Notification;

class NotificationService
{
    /**
     * Store an in-app notification and dispatch a push notification for the user.
     */
    public function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        array $payload = []
    ): void {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_id' => $relatedId,
            'payload' => $payload,
            'is_read' => false,
            'created_at' => now(),
        ]);

        SendPushNotificationJob::dispatch($userId, $title, $message, $payload, $type);
    }

    /**
     * Notify multiple users about the same event.
     */
    public function notifyUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        array $payload = []
    ): void {
        foreach (array_unique($userIds) as $userId) {
            $this->notify((int) $userId, $type, $title, $message, $relatedId, $payload);
        }
    }
}