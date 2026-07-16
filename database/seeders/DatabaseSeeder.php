<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorPayout;
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
use App\Models\MedicalRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::factory()->create();
        Secretary::factory()->create();

        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $session = TreatmentSession::factory()->create([
            'appointment_id' => $appointment->appointment_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $tooth = Tooth::factory()->create();

        // Create a treatment plan with a single case (1-to-1)
        $plan = TreatmentPlan::factory()->create([
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        \App\Models\CaseModel::factory()->done()->create([
            'treatment_plan_id' => $plan->plan_id,
        ]);

        TreatmentStage::factory()->create();
        TreatmentDetails::factory()->create([
            'session_id' => $session->session_id,
            'tooth_id' => $tooth->tooth_id,
        ]);

        Payment::factory()->create([
            'patient_id' => $patient->patient_id,
            'related_session_id' => $session->session_id,
        ]);

        DoctorPayout::factory()->create([
            'doctor_id' => $doctor->doctor_id,
            'session_id' => $session->session_id,
        ]);

        Rating::factory()->create([
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        Notification::factory()->create();
        PatientPoints::factory()->create([
            'patient_id' => $patient->patient_id,
        ]);

        ToothCondition::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'session_id' => $session->session_id,
            'tooth_id' => $tooth->tooth_id,
        ]);

        // ensure a medical record exists for the patient
        $medicalRecord = MedicalRecord::factory()->create([
            'patient_id' => $patient->patient_id,
        ]);

        // seed a diagnosis linked to the record/session
        \App\Models\Diagnosis::factory()->create([
            'record_id' => $medicalRecord->record_id,
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'session_id' => $session->session_id,
        ]);
    }
}
