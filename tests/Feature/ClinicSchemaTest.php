<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PatientPoints;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\Secretary;
use App\Models\Tooth;
use App\Models\ToothCondition;
use App\Models\TreatmentDetails;
use App\Models\TreatmentPlan;
use App\Models\TreatmentSession;
use App\Models\TreatmentStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_tables_and_relationships_can_be_queried(): void
    {
        $adminUser = User::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '1111111111',
            'role' => 'Admin',
        ]);

        $secretaryUser = User::factory()->create([
            'first_name' => 'Sara',
            'last_name' => 'Secretary',
            'email' => 'secretary@example.com',
            'phone' => '2222222222',
            'role' => 'Secretary',
        ]);

        $doctorUser = User::factory()->create([
            'first_name' => 'Dylan',
            'last_name' => 'Doctor',
            'email' => 'doctor@example.com',
            'phone' => '3333333333',
            'role' => 'Doctor',
        ]);

        $patientUser = User::factory()->create([
            'first_name' => 'Paul',
            'last_name' => 'Patient',
            'email' => 'patient@example.com',
            'phone' => '4444444444',
            'role' => 'Patient',
        ]);

        $admin = Admin::create([
            'admin_id' => $adminUser->user_id,
            'permissions' => 'manage-users,manage-reports',
        ]);

        $secretary = Secretary::create([
            'secretary_id' => $secretaryUser->user_id,
            'shift' => 'Morning',
            'office_number' => 'OFF-101',
        ]);

        $doctor = Doctor::create([
            'doctor_id' => $doctorUser->user_id,
            'specialization' => 'General Dentistry',
            'license_number' => 'LIC-10001',
            'years_of_experience' => 12,
            'rating' => 4.8,
            'reviews_count' => 24,
        ]);

        $patient = Patient::create([
            'patient_id' => $patientUser->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->patient_id,
            'created_at' => now(),
        ]);

        $medicalHistory = MedicalHistory::create([
            'record_id' => $medicalRecord->record_id,
            'condition_name' => 'Hypertension',
            'description' => 'Controlled condition',
            'diagnosed_date' => '2020-01-01',
        ]);

        $allergy = Allergy::create([
            'record_id' => $medicalRecord->record_id,
            'allergy_name' => 'Penicillin',
            'severity' => 'High',
            'notes' => 'Avoid penicillin-based antibiotics',
        ]);

        $tooth = Tooth::create([
            'tooth_code' => 'T11',
            'tooth_name' => 'Upper Right Central Incisor',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'date' => '2026-05-16',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'Scheduled',
            'notes' => 'Initial consultation',
        ]);

        $treatmentPlan = TreatmentPlan::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'estimated_total_cost' => 1200.00,
            'actual_total_cost' => 1300.00,
            'progress_percentage' => 25,
            'created_at' => now(),
        ]);

        $treatmentSession = TreatmentSession::create([
            'appointment_id' => $appointment->appointment_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
            'session_date' => '2026-05-16',
            'notes' => 'Treatment session one',
        ]);

        $treatmentStage = TreatmentStage::create([
            'plan_id' => $treatmentPlan->plan_id,
            'stage_name' => 'Stage 1',
            'description' => 'Cleaning and preparation',
            'estimated_cost' => 400.00,
            'actual_cost' => 450.00,
            'status' => 'In-Progress',
            'start_date' => '2026-05-16',
            'end_date' => '2026-05-20',
        ]);

        $toothCondition = ToothCondition::create([
            'patient_id' => $patient->patient_id,
            'tooth_id' => $tooth->tooth_id,
            'doctor_id' => $doctor->doctor_id,
            'condition_status' => 'Damaged',
            'treatment_type' => 'Filling',
            'treatment_description' => 'Composite filling required',
            'estimated_price' => 250.00,
            'severity_level' => 'Medium',
            'notes' => 'Monitor sensitivity',
            'session_id' => $treatmentSession->session_id,
            'updated_at' => now(),
        ]);

        $treatmentDetails = TreatmentDetails::create([
            'session_id' => $treatmentSession->session_id,
            'tooth_id' => $tooth->tooth_id,
            'previous_condition' => 'Cavity',
            'new_condition' => 'Filled',
            'cost' => 250.00,
        ]);

        $payment = Payment::create([
            'patient_id' => $patient->patient_id,
            'amount' => 250.00,
            'method' => 'Cash',
            'date' => now(),
            'related_session_id' => $treatmentSession->session_id,
            'type' => 'SessionPayment',
            'is_income' => true,
        ]);

        $doctorPayout = DoctorPayout::create([
            'doctor_id' => $doctor->doctor_id,
            'session_id' => $treatmentSession->session_id,
            'amount' => 150.00,
            'payout_date' => now(),
            'status' => 'Pending',
            'notes' => 'Paid after insurance clearance',
        ]);

        $rating = Rating::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'rating' => 5,
            'created_at' => now(),
        ]);

        $notification = Notification::create([
            'user_id' => $patientUser->user_id,
            'title' => 'Appointment reminder',
            'message' => 'Your appointment is tomorrow at 9 AM.',
            'type' => 'Appointment',
            'related_id' => $appointment->appointment_id,
            'is_read' => false,
            'created_at' => now(),
        ]);

        $patientPoints = PatientPoints::create([
            'patient_id' => $patient->patient_id,
            'points' => 10,
            'source' => 'Payment',
            'related_id' => $payment->payment_id,
            'description' => 'Loyalty points for session payment',
            'created_at' => now(),
        ]);

        $this->assertSame('manage-users,manage-reports', $admin->user->admin->permissions);
        $this->assertSame('Morning', $secretary->user->secretary->shift);
        $this->assertSame('General Dentistry', $doctor->user->doctor->specialization);
        $this->assertSame('Paul', $patient->user->patient->user->first_name);

        $this->assertCount(1, $patient->medicalRecords);
        $this->assertSame('Hypertension', $medicalRecord->histories->first()->condition_name);
        $this->assertSame('Penicillin', $medicalRecord->allergies->first()->allergy_name);

        $this->assertSame('Dylan', $appointment->doctor->user->first_name);
        $this->assertSame('Paul', $appointment->patient->user->first_name);

        $this->assertSame('Stage 1', $treatmentPlan->stages->first()->stage_name);
        $this->assertSame('Treatment session one', $treatmentSession->notes);
        $this->assertSame('Filled', $treatmentDetails->new_condition);
        $this->assertSame('Damaged', $toothCondition->condition_status);

        $this->assertSame(250.00, $payment->amount);
        $this->assertSame('Pending', $doctorPayout->status);
        $this->assertSame(5, $rating->rating);
        $this->assertSame('Appointment reminder', $notification->title);
        $this->assertSame(10, $patientPoints->points);
    }
}