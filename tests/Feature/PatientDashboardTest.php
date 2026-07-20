<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientPoints;
use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $patientUser;
    private Patient $patient;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = Patient::factory()->create();
        $this->patientUser = $this->patient->user;

        $this->doctor = Doctor::factory()->create();
    }

    // -----------------------------------------------------------------
    // Dashboard
    // -----------------------------------------------------------------

    public function test_patient_can_get_dashboard_with_all_data(): void
    {
        // Arrange: upcoming appointment
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00:00',
            'status' => 'Scheduled',
            'notes' => 'Teeth Cleaning',
        ]);

        // Arrange: reward points
        PatientPoints::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'points' => 50,
        ]);
        PatientPoints::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'points' => 70,
        ]);

        // Arrange: treatment progress
        TreatmentPlan::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'progress_percentage' => 75,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/dashboard");

        $response
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('upcoming_appointment.title', 'Teeth Cleaning')
            ->assertJsonPath('upcoming_appointment.time', '09:00 AM')
            ->assertJsonPath('reward_points', 120)
            ->assertJsonPath('progress', 75)
            ->assertJsonPath('doctors.0.name', 'Dr. ' . $this->doctor->user->first_name . ' ' . $this->doctor->user->last_name)
            ->assertJsonPath('doctors.0.specialty', $this->doctor->specialization)
            ->assertJsonPath('doctors.0.experience', $this->doctor->years_of_experience . '+ Years');
    }

    public function test_dashboard_returns_null_appointment_when_no_upcoming(): void
    {
        // No appointments created for this patient

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/dashboard");

        $response
            ->assertOk()
            ->assertJsonPath('upcoming_appointment', null)
            ->assertJsonPath('reward_points', 0)
            ->assertJsonPath('progress', 0);
    }

    public function test_dashboard_excludes_past_and_cancelled_appointments(): void
    {
        // Past appointment – should be ignored
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => now()->subDay()->toDateString(),
            'start_time' => '08:00:00',
            'status' => 'Scheduled',
            'notes' => 'Old appointment',
        ]);

        // Cancelled future appointment – should be ignored
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'status' => 'cancelled',
            'notes' => 'Cancelled one',
        ]);

        // Completed future appointment – should be ignored
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '11:00:00',
            'status' => 'completed',
            'notes' => 'Completed one',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/dashboard");

        $response
            ->assertOk()
            ->assertJsonPath('upcoming_appointment', null);
    }

    // -----------------------------------------------------------------
    // Auth
    // -----------------------------------------------------------------

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->getJson("/api/patient/{$this->patient->patient_id}/dashboard");

        $response->assertUnauthorized();
    }

    // -----------------------------------------------------------------
    // Upcoming Appointments
    // -----------------------------------------------------------------

    public function test_patient_can_get_upcoming_appointments(): void
    {
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:30:00',
            'status' => 'Scheduled',
            'notes' => 'Checkup',
        ]);
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => now()->addDays(5)->toDateString(),
            'start_time' => '14:00:00',
            'status' => 'Scheduled',
            'notes' => 'Filling',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/appointments/upcoming");

        $response
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'appointments')
            ->assertJsonPath('appointments.0.title', 'Checkup')
            ->assertJsonPath('appointments.0.time', '10:30 AM')
            ->assertJsonPath('appointments.0.status', 'Scheduled')
            ->assertJsonPath('appointments.1.title', 'Filling');
    }

    public function test_upcoming_appointments_require_auth(): void
    {
        $response = $this->getJson("/api/patient/{$this->patient->patient_id}/appointments/upcoming");

        $response->assertUnauthorized();
    }

    // -----------------------------------------------------------------
    // Points
    // -----------------------------------------------------------------

    public function test_patient_can_get_points(): void
    {
        PatientPoints::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'points' => 100,
        ]);
        PatientPoints::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'points' => 50,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/points");

        $response
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('reward_points', 150);
    }

    public function test_points_returns_zero_when_none(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/points");

        $response
            ->assertOk()
            ->assertJsonPath('reward_points', 0);
    }

    public function test_points_require_auth(): void
    {
        $response = $this->getJson("/api/patient/{$this->patient->patient_id}/points");

        $response->assertUnauthorized();
    }

    // -----------------------------------------------------------------
    // Progress
    // -----------------------------------------------------------------

    public function test_patient_can_get_progress(): void
    {
        TreatmentPlan::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'progress_percentage' => 30,
        ]);
        TreatmentPlan::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'progress_percentage' => 70,
        ]);

        // Average of 30 and 70 = 50
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/progress");

        $response
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('progress', 50);
    }

    public function test_progress_returns_zero_when_no_plans(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/progress");

        $response
            ->assertOk()
            ->assertJsonPath('progress', 0);
    }

    public function test_progress_requires_auth(): void
    {
        $response = $this->getJson("/api/patient/{$this->patient->patient_id}/progress");

        $response->assertUnauthorized();
    }

    // -----------------------------------------------------------------
    // All Doctors (public)
    // -----------------------------------------------------------------

    public function test_can_get_all_doctors(): void
    {
        $doctor2 = Doctor::factory()->create();

        $response = $this->getJson('/api/doctors');

        $response
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'doctors')
            ->assertJsonPath('doctors.0.name', 'Dr. ' . $this->doctor->user->first_name . ' ' . $this->doctor->user->last_name)
            ->assertJsonPath('doctors.0.specialty', $this->doctor->specialization)
            ->assertJsonPath('doctors.0.experience', $this->doctor->years_of_experience . '+ Years')
            ->assertJsonPath('doctors.1.name', 'Dr. ' . $doctor2->user->first_name . ' ' . $doctor2->user->last_name);
    }

    // -----------------------------------------------------------------
    // Edge cases
    // -----------------------------------------------------------------

    public function test_dashboard_returns_404_for_nonexistent_patient(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson('/api/patient/99999/dashboard');

        $response->assertNotFound();
    }

    public function test_points_scoped_to_requested_patient(): void
    {
        $otherPatient = Patient::factory()->create();

        PatientPoints::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'points' => 80,
        ]);
        PatientPoints::factory()->create([
            'patient_id' => $otherPatient->patient_id,
            'points' => 200,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/{$this->patient->patient_id}/points");

        // Should only count the requested patient's points, not the other one
        $response
            ->assertOk()
            ->assertJsonPath('reward_points', 80);
    }
}
