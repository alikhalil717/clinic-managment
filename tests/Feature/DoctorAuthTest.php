<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DoctorAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/doctor/register', [
            'first_name' => 'Ali',
            'last_name' => 'Doctor',
            'email' => 'ali.doctor@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'specialization' => 'General Dentistry',
            'license_number' => 'LIC-10001',
            'years_of_experience' => 10,
            'rating' => 4.5,
            'reviews_count' => 20,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Doctor registered successfully.')
            ->assertJsonStructure(['success', 'message', 'token', 'user', 'doctor']);

        $this->assertDatabaseHas('users', [
            'email' => 'ali.doctor@example.com',
            'role' => 'Doctor',
        ]);

        $this->assertDatabaseHas('doctor', [
            'license_number' => 'LIC-10001',
        ]);
    }

    public function test_doctor_can_login_and_get_new_token(): void
    {
        $user = User::factory()->create([
            'role' => 'Doctor',
            'email' => 'login.doctor@example.com',
            'password' => Hash::make('password123'),
        ]);

        Doctor::create([
            'doctor_id' => $user->user_id,
            'specialization' => 'General Dentistry',
            'license_number' => 'LIC-10002',
            'years_of_experience' => 12,
            'rating' => 4.8,
            'reviews_count' => 25,
        ]);

        $response = $this->postJson('/api/doctor/login', [
            'email' => 'login.doctor@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Doctor logged in successfully.');

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_doctor_can_logout_with_bearer_token(): void
    {
        $user = User::factory()->create([
            'role' => 'Doctor',
        ]);

        Doctor::create([
            'doctor_id' => $user->user_id,
            'specialization' => 'Orthodontics',
            'license_number' => 'LIC-10003',
            'years_of_experience' => 8,
            'rating' => 4.2,
            'reviews_count' => 10,
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/doctor/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Doctor logged out successfully.');

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'api_token' => null,
        ]);
    }

    public function test_doctor_can_get_profile_with_bearer_token(): void
    {
        $user = User::factory()->create([
            'role' => 'Doctor',
        ]);

        Doctor::create([
            'doctor_id' => $user->user_id,
            'specialization' => 'Orthodontics',
            'license_number' => 'LIC-10004',
            'years_of_experience' => 8,
            'rating' => 4.2,
            'reviews_count' => 10,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->getJson('/api/doctor/profile');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['user', 'doctor_id', 'specialization']]);
    }

    public function test_doctor_can_update_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'Doctor',
            'first_name' => 'OldDoc',
        ]);

        Doctor::create([
            'doctor_id' => $user->user_id,
            'specialization' => 'Orthodontics',
            'license_number' => 'LIC-10005',
            'years_of_experience' => 8,
            'rating' => 4.2,
            'reviews_count' => 10,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $user->api_token)
            ->postJson('/api/doctor/update-profile', [
                'first_name' => 'NewDoc',
                'specialization' => 'Endodontics',
                'years_of_experience' => 15,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'first_name' => 'NewDoc',
        ]);

        $this->assertDatabaseHas('doctor', [
            'doctor_id' => $user->user_id,
            'specialization' => 'Endodontics',
            'years_of_experience' => 15,
        ]);
    }

    public function test_doctor_update_profile_requires_auth(): void
    {
        $response = $this->postJson('/api/doctor/update-profile', [
            'first_name' => 'Hacker',
        ]);

        $response->assertUnauthorized();
    }

    public function test_doctor_profile_requires_auth(): void
    {
        $response = $this->getJson('/api/doctor/profile');

        $response->assertUnauthorized();
    }
}
