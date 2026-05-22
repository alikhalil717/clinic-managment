<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BearerRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_bearer_route_accepts_patient_token(): void
    {
        $patient = User::factory()->create([
            'role' => 'Patient',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$patient->api_token)
            ->getJson('/api/patient/me')
            ->assertOk()
            ->assertJsonPath('user_id', $patient->user_id)
            ->assertJsonPath('role', 'Patient');
    }

    public function test_doctor_bearer_route_rejects_patient_token(): void
    {
        $patient = User::factory()->create([
            'role' => 'Patient',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$patient->api_token)
            ->getJson('/api/doctor/me')
            ->assertStatus(403);
    }
}
