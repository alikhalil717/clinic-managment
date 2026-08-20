<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\TreatmentSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationHooksTest extends TestCase
{
    use RefreshDatabase;

    private function makePatient(): Patient
    {
        $patient = Patient::factory()->create();

        return $patient;
    }

    private function makeDoctor(): Doctor
    {
        return Doctor::factory()->create();
    }

    public function test_secretary_confirming_appointment_notifies_patient_and_doctor(): void
    {
        Queue::fake();

        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();
        $secretary = User::factory()->create(['role' => 'secretary']);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'status' => 'pending',
            'date' => '2026-09-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $secretary->api_token)
            ->putJson("/api/secretary/appointments/{$appointment->appointment_id}", [
                'status' => 'confirmed',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notification', [
            'user_id' => $patient->patient_id,
            'type' => 'appointment',
            'related_id' => $appointment->appointment_id,
        ]);

        $this->assertDatabaseHas('notification', [
            'user_id' => $doctor->doctor_id,
            'type' => 'appointment',
            'related_id' => $appointment->appointment_id,
        ]);

        $patientNotification = Notification::query()
            ->where('user_id', $patient->patient_id)
            ->first();

        $this->assertNotEmpty($patientNotification->payload);
        $this->assertSame($appointment->appointment_id, $patientNotification->payload['appointment_id'] ?? null);
    }

    public function test_doctor_creating_plan_notifies_patient(): void
    {
        Queue::fake();

        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();

        $this->withHeader('Authorization', 'Bearer ' . $doctor->user->api_token)
            ->postJson('/api/doctor/treatment-plans', [
                'patient_id' => $patient->patient_id,
                'title' => 'Root Canal',
                'description' => 'Two-visit root canal',
                'estimated_total_cost' => 500,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notification', [
            'user_id' => $patient->patient_id,
            'type' => 'treatment',
            'title' => 'New Treatment Plan',
        ]);

        $notification = Notification::query()
            ->where('user_id', $patient->patient_id)
            ->first();

        $this->assertSame('Root Canal', $notification->payload['title'] ?? null);
        $this->assertNotNull($notification->related_id);
    }

    public function test_patient_cancelling_appointment_notifies_doctor(): void
    {
        Queue::fake();

        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'status' => 'confirmed',
            'date' => '2026-09-05',
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $patient->user->api_token)
            ->postJson("/api/patient/appointments/{$appointment->appointment_id}/cancel")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notification', [
            'user_id' => $doctor->doctor_id,
            'type' => 'appointment',
            'title' => 'Appointment Cancelled',
            'related_id' => $appointment->appointment_id,
        ]);
    }

    public function test_patient_electronic_payment_notifies_patient(): void
    {
        Queue::fake();

        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();

        $session = TreatmentSession::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'estimated_cost' => 250,
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $patient->user->api_token)
            ->postJson('/api/patient/payment/submit', [
                'sessionIds' => [$session->session_id],
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.invoices_paid', 1);

        $this->assertDatabaseHas('notification', [
            'user_id' => $patient->patient_id,
            'type' => 'payment',
            'title' => 'Payment Successful',
        ]);
    }

    public function test_appointment_request_notifies_doctor(): void
    {
        Queue::fake();

        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();

        $this->withHeader('Authorization', 'Bearer ' . $patient->user->api_token)
            ->postJson('/api/patient/appointments/add', [
                'doctor_id' => $doctor->doctor_id,
                'date' => '2026-09-10',
                'time' => '10:00',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('notification', [
            'user_id' => $doctor->doctor_id,
            'type' => 'appointment',
            'title' => 'New Appointment Request',
        ]);
    }
}