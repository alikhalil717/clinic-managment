<?php

namespace App\Jobs;

use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $title,
        public string $message,
        public array $payload = [],
        public string $type = 'system'
    ) {}

    public function handle(PushNotificationService $push): void
    {
        $push->sendToUser($this->userId, $this->title, $this->message, $this->payload, $this->type);
    }
}