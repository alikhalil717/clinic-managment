<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;
    private Doctor $doctor;
    private string $saturdayDate;
    private string $sundayDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = Patient::factory()->create();

        // Next Saturday that is still in the future (validation requires today or later)
        $saturday = Carbon::today()->next(Carbon::SATURDAY);
        $this->saturdayDate = $saturday->toDateString();
        $this->sundayDate = $saturday->copy()->addDay()->toDateString();

        // Doctor who works Saturday 10:00–15:00
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $this->doctor = Doctor::factory()->create([
            'doctor_id' => $doctorUser->user_id,
            'working_days' => ['saturday'],
            'working_hours' => [
                'saturday' => ['start' => '10:00', 'end' => '15:00'],
            ],
        ]);
    }

    public function test_doctor_availability_returns_busy_slots_per_day(): void
    {
        // Book two slots on a specific date
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'slot 1',
        ]);
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate,
            'start_time' => '11:30:00',
            'end_time' => '12:00:00',
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'slot 2',
        ]);

        $response = $this->getJson("/api/doctors/{$this->doctor->doctor_id}/availability");

        $response
            ->assertOk()
            ->assertJsonPath('doctor_id', $this->doctor->doctor_id)
            ->assertJsonStructure(['availability' => [['date', 'busy_slots']]])
            ->assertJsonCount(31, 'availability');

        // Find the entry for the booked Saturday
        $entry = collect($response->json('availability'))
            ->firstWhere('date', $this->saturdayDate);

        $this->assertSame(['10:00', '11:30'], $entry['busy_slots']);
    }

    public function test_doctor_busy_slots_returns_booked_times(): void
    {
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'busy test',
        ]);

        $response = $this->getJson("/api/doctors/{$this->doctor->doctor_id}/busy?date={$this->saturdayDate}");

        $response
            ->assertOk()
            ->assertJsonPath('busy_slots', ['10:00']);
    }

    public function test_diagnostic_busy_slots_returns_booked_times(): void
    {
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => null,
            'date' => $this->saturdayDate,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'diagnostic',
            'notes' => 'busy test',
        ]);

        $response = $this->getJson("/api/appointments/diagnostic/busy?date={$this->saturdayDate}");

        $response
            ->assertOk()
            ->assertJsonPath('busy_slots', ['09:00']);
    }

    public function test_doctor_busy_slots_expand_long_appointments(): void
    {
        // 09:30–10:30 spans two 30-minute slots: 09:30 and 10:00
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate,
            'start_time' => '09:30:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'long appointment',
        ]);

        $response = $this->getJson("/api/doctors/{$this->doctor->doctor_id}/busy?date={$this->saturdayDate}");

        $response
            ->assertOk()
            ->assertJsonPath('busy_slots', ['09:30', '10:00']);
    }

    public function test_create_normal_rejects_slot_overlapping_busy_appointment(): void
    {
        // 09:30–10:30 blocks both 09:30 and 10:00
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate,
            'start_time' => '09:30:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'long appointment',
        ]);

        $response = $this->postJson('/api/appointments/normal', [
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate,
            'time' => '10:00', // overlaps the existing 09:30–10:30 appointment
        ]);

        $response->assertStatus(409);
    }

    public function test_create_diagnostic_appointment(): void
    {
        $response = $this->postJson('/api/appointments/diagnostic', [
            'patient_id' => $this->patient->patient_id,
            'date' => $this->saturdayDate,
            'time' => '10:00',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('appointment_type', 'diagnostic')
            ->assertJsonPath('date', $this->saturdayDate)
            ->assertJsonPath('time', '10:00')
            ->assertJsonPath('message', "We'll send you a reminder.");
    }

    public function test_create_diagnostic_rejects_duplicate_slot(): void
    {
        $this->postJson('/api/appointments/diagnostic', [
            'patient_id' => $this->patient->patient_id,
            'date' => $this->saturdayDate,
            'time' => '10:00',
        ])->assertCreated();

        $response = $this->postJson('/api/appointments/diagnostic', [
            'patient_id' => $this->patient->patient_id,
            'date' => $this->saturdayDate,
            'time' => '10:00',
        ]);

        $response->assertStatus(409);
    }

    public function test_create_diagnostic_rejects_outside_hours(): void
    {
        $response = $this->postJson('/api/appointments/diagnostic', [
            'patient_id' => $this->patient->patient_id,
            'date' => $this->saturdayDate,
            'time' => '17:00',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_normal_appointment(): void
    {
        // Standalone bookings require a prior diagnostic appointment.
        Appointment::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => null,
            'date' => $this->saturdayDate,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'diagnostic',
            'notes' => 'prior diagnostic',
        ]);

        $response = $this->postJson('/api/appointments/normal', [
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate, // Saturday
            'time' => '10:00',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('appointment_type', 'normal')
            ->assertJsonPath('doctor.id', $this->doctor->doctor_id);
    }

    public function test_create_normal_rejects_off_day(): void
    {
        $response = $this->postJson('/api/appointments/normal', [
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->sundayDate, // Sunday - doctor doesn't work
            'time' => '10:00',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_normal_rejects_outside_hours(): void
    {
        $response = $this->postJson('/api/appointments/normal', [
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->doctor_id,
            'date' => $this->saturdayDate, // Saturday 10:00-15:00
            'time' => '18:00',
        ]);

        $response->assertStatus(422);
    }
}
