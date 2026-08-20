<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_register_device_token(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/device-token', [
                'device_token' => 'fcm-token-123',
                'platform' => 'android',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Device token registered successfully.');

        $this->assertDatabaseHas('device_token', [
            'user_id' => $user->user_id,
            'device_token' => 'fcm-token-123',
            'platform' => 'android',
        ]);
    }

    public function test_doctor_can_register_device_token(): void
    {
        $user = User::factory()->create(['role' => 'doctor']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/device-token', [
                'device_token' => 'fcm-token-doctor',
                'platform' => 'ios',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('device_token', [
            'user_id' => $user->user_id,
            'device_token' => 'fcm-token-doctor',
        ]);
    }

    public function test_registering_same_token_is_idempotent(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/device-token', ['device_token' => 'fcm-token-123'])
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/device-token', ['device_token' => 'fcm-token-123'])
            ->assertOk();

        $this->assertSame(1, DeviceToken::query()->where('device_token', 'fcm-token-123')->count());
    }

    public function test_patient_can_remove_device_token(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/device-token', ['device_token' => 'fcm-token-123'])
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->deleteJson('/api/device-token', ['device_token' => 'fcm-token-123'])
            ->assertOk();

        $this->assertDatabaseMissing('device_token', ['device_token' => 'fcm-token-123']);
    }

    public function test_device_token_requires_auth(): void
    {
        $this->postJson('/api/device-token', ['device_token' => 'x'])->assertUnauthorized();
    }

    public function test_secretary_cannot_register_device_token(): void
    {
        $user = User::factory()->create(['role' => 'secretary']);

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/device-token', ['device_token' => 'fcm-token-123'])
            ->assertForbidden();
    }
}