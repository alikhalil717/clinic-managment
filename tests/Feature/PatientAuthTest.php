<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/patient/register', [
            'first_name' => 'Sara',
            'last_name' => 'Patient',
            'email' => 'sara.patient@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '1995-02-10',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Patient registered successfully.')
            ->assertJsonStructure(['message', 'token', 'user', 'patient']);

        $this->assertDatabaseHas('users', [
            'email' => 'sara.patient@example.com',
            'role' => 'Patient',
        ]);

        $this->assertDatabaseHas('patient', [
            'date_of_birth' => '1995-02-10',
        ]);
    }

    public function test_patient_can_login_and_get_new_token(): void
    {
        $user = User::factory()->create([
            'role' => 'Patient',
            'email' => 'login.patient@example.com',
            'password' => Hash::make('password123'),
        ]);

        Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        $response = $this->postJson('/api/patient/login', [
            'email' => 'login.patient@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Patient logged in successfully.');

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_patient_can_logout_with_bearer_token(): void
    {
        $user = User::factory()->create([
            'role' => 'Patient',
        ]);

        Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$user->api_token)
            ->postJson('/api/patient/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Patient logged out successfully.');

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'api_token' => null,
        ]);
    }
}
