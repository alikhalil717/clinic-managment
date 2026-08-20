<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makePatientUser(): User
    {
        $user = User::factory()->create(['role' => 'patient']);

        Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        return $user;
    }

    public function test_user_can_list_own_notifications(): void
    {
        $user = $this->makePatientUser();

        Notification::create([
            'user_id' => $user->user_id,
            'title' => 'Appointment Confirmed',
            'message' => 'Your appointment is confirmed.',
            'type' => 'appointment',
            'related_id' => 5,
            'payload' => ['appointment_id' => 5],
            'is_read' => false,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->getJson('/api/notifications');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.notifications')
            ->assertJsonPath('data.notifications.0.title', 'Appointment Confirmed');
    }

    public function test_user_cannot_see_other_users_notifications(): void
    {
        $owner = $this->makePatientUser();
        $other = $this->makePatientUser();

        Notification::create([
            'user_id' => $owner->user_id,
            'title' => 'Secret',
            'message' => 'Only for owner.',
            'type' => 'system',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $other->api_token)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(0, 'data.notifications');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = $this->makePatientUser();

        $notification = Notification::create([
            'user_id' => $user->user_id,
            'title' => 'Hi',
            'message' => 'Read me.',
            'type' => 'system',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson("/api/notifications/{$notification->notification_id}/read")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notification', [
            'notification_id' => $notification->notification_id,
            'is_read' => true,
        ]);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $owner = $this->makePatientUser();
        $other = $this->makePatientUser();

        $notification = Notification::create([
            'user_id' => $owner->user_id,
            'title' => 'Hi',
            'message' => 'Not yours.',
            'type' => 'system',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $other->api_token)
            ->postJson("/api/notifications/{$notification->notification_id}/read")
            ->assertNotFound();

        $this->assertDatabaseHas('notification', [
            'notification_id' => $notification->notification_id,
            'is_read' => false,
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->makePatientUser();

        foreach (range(1, 3) as $i) {
            Notification::create([
                'user_id' => $user->user_id,
                'title' => "N{$i}",
                'message' => "M{$i}",
                'type' => 'system',
                'is_read' => false,
                'created_at' => now(),
            ]);
        }

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, Notification::query()->where('user_id', $user->user_id)->where('is_read', false)->count());
    }

    public function test_unread_count_endpoint(): void
    {
        $user = $this->makePatientUser();

        Notification::create([
            'user_id' => $user->user_id,
            'title' => 'A',
            'message' => 'A',
            'type' => 'system',
            'is_read' => false,
            'created_at' => now(),
        ]);
        Notification::create([
            'user_id' => $user->user_id,
            'title' => 'B',
            'message' => 'B',
            'type' => 'system',
            'is_read' => true,
            'created_at' => now(),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    }

    public function test_notifications_require_auth(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
    }
}