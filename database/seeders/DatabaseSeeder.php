<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\CaseModel;
use App\Models\Diagnosis;
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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // -----------------------------------------------------------------
        // Existing random seed data
        // -----------------------------------------------------------------
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

        CaseModel::factory()->done()->create([
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
        Diagnosis::factory()->create([
            'record_id' => $medicalRecord->record_id,
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'session_id' => $session->session_id,
        ]);

        // -----------------------------------------------------------------
        // TEST PATIENT: ali1234@gmail.com / ali.1234
        // Full data across ALL tables for dashboard / appointments / etc.
        // -----------------------------------------------------------------
        $testUser = User::factory()->create([
            'first_name' => 'Ali',
            'last_name' => 'Alkhalil',
            'email' => 'ali1234@gmail.com',
            'password' => Hash::make('ali.1234'),
            'role' => 'Patient',
            'phone' => '0501234567',
            'api_token' => Str::random(60),
            'profile_image' => null,
        ]);

        $testPatient = Patient::factory()->create([
            'patient_id' => $testUser->user_id,
            'date_of_birth' => '1995-03-15',
        ]);

        // --- Doctors (seeded so dashboard returns doctors) ---
        $doc1User = User::factory()->create([
            'first_name' => 'Ahmad',
            'last_name' => 'Al-Khatib',
            'role' => 'Doctor',
            'profile_image' => 'profiles/doc1.jpg',
        ]);
        $doctor1 = Doctor::factory()->create([
            'doctor_id' => $doc1User->user_id,
            'specialization' => 'Orthodontist',
            'years_of_experience' => 8,
            'rating' => 4.8,
            'reviews_count' => 120,
        ]);

        $doc2User = User::factory()->create([
            'first_name' => 'Sara',
            'last_name' => 'Hassan',
            'role' => 'Doctor',
            'profile_image' => 'profiles/doc2.jpg',
        ]);
        $doctor2 = Doctor::factory()->create([
            'doctor_id' => $doc2User->user_id,
            'specialization' => 'Endodontics',
            'years_of_experience' => 12,
            'rating' => 4.6,
            'reviews_count' => 95,
        ]);

        // --- Upcoming Appointments ---
        Appointment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'Scheduled',
            'notes' => 'Teeth Cleaning',
        ]);

        $appt2 = Appointment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
            'date' => now()->addDays(7)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'status' => 'Scheduled',
            'notes' => 'Root Canal Checkup',
        ]);

        // Past appointment (should NOT show in upcoming)
        Appointment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'date' => now()->subDays(5)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'Completed',
            'notes' => 'Initial Consultation',
        ]);

        // --- Treatment Sessions (linked to appointments) ---
        $session1 = TreatmentSession::factory()->create([
            'appointment_id' => $appt2->appointment_id,
            'doctor_id' => $doctor2->doctor_id,
            'patient_id' => $testPatient->patient_id,
        ]);

        // --- Teeth ---
        $tooth1 = Tooth::factory()->create();
        $tooth2 = Tooth::factory()->create();

        // --- Treatment Plans ---
        $plan1 = TreatmentPlan::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'progress_percentage' => 75,
            'estimated_total_cost' => 2500.00,
            'actual_total_cost' => 2100.00,
        ]);

        $plan2 = TreatmentPlan::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
            'progress_percentage' => 30,
            'estimated_total_cost' => 1200.00,
            'actual_total_cost' => 800.00,
        ]);

        // --- Cases (1-to-1 with treatment plans) ---
        CaseModel::factory()->create([
            'treatment_plan_id' => $plan1->plan_id,
            'title' => 'Braces Adjustment',
            'patient_age' => 29,
        ]);
        CaseModel::factory()->create([
            'treatment_plan_id' => $plan2->plan_id,
            'title' => 'Root Canal Treatment',
            'patient_age' => 29,
        ]);

        // --- Treatment Stages ---
        TreatmentStage::factory()->create([
            'plan_id' => $plan1->plan_id,
        ]);
        TreatmentStage::factory()->create([
            'plan_id' => $plan2->plan_id,
        ]);

        // --- Treatment Details ---
        TreatmentDetails::factory()->create([
            'session_id' => $session1->session_id,
            'tooth_id' => $tooth1->tooth_id,
        ]);
        TreatmentDetails::factory()->create([
            'session_id' => $session1->session_id,
            'tooth_id' => $tooth2->tooth_id,
        ]);

        // --- Payments ---
        Payment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'related_session_id' => $session1->session_id,
        ]);

        // --- Doctor Payouts ---
        DoctorPayout::factory()->create([
            'doctor_id' => $doctor1->doctor_id,
            'session_id' => $session1->session_id,
        ]);
        DoctorPayout::factory()->create([
            'doctor_id' => $doctor2->doctor_id,
            'session_id' => $session1->session_id,
        ]);

        // --- Ratings ---
        Rating::factory()->create([
            'doctor_id' => $doctor1->doctor_id,
            'patient_id' => $testPatient->patient_id,
        ]);
        Rating::factory()->create([
            'doctor_id' => $doctor2->doctor_id,
            'patient_id' => $testPatient->patient_id,
        ]);

        // --- Patient Points ---
        PatientPoints::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'points' => 70,
            'source' => 'Session',
            'description' => 'Points from teeth cleaning session',
        ]);
        PatientPoints::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'points' => 50,
            'source' => 'Payment',
            'description' => 'Bonus points from payment',
        ]);

        // --- Notifications ---
        Notification::factory()->create([
            'user_id' => $testUser->user_id,
        ]);

        // --- Tooth Conditions ---
        ToothCondition::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'session_id' => $session1->session_id,
            'tooth_id' => $tooth1->tooth_id,
        ]);
        ToothCondition::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
            'session_id' => $session1->session_id,
            'tooth_id' => $tooth2->tooth_id,
        ]);

        // --- Medical Record ---
        $testRecord = MedicalRecord::factory()->create([
            'patient_id' => $testPatient->patient_id,
        ]);

        // --- Medical Histories ---
        MedicalHistory::factory()->create([
            'record_id' => $testRecord->record_id,
            'condition_name' => 'Asthma',
            'description' => 'Mild asthma, uses inhaler occasionally',
            'diagnosed_date' => '2010-06-01',
        ]);
        MedicalHistory::factory()->create([
            'record_id' => $testRecord->record_id,
            'condition_name' => 'Hypertension',
            'description' => 'Controlled with medication',
            'diagnosed_date' => '2018-12-10',
        ]);

        // --- Allergies ---
        Allergy::factory()->create([
            'record_id' => $testRecord->record_id,
        ]);
        Allergy::factory()->create([
            'record_id' => $testRecord->record_id,
        ]);

        // --- Diagnoses ---
        Diagnosis::factory()->create([
            'record_id' => $testRecord->record_id,
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'session_id' => $session1->session_id,
            'diagnosis_name' => 'Malocclusion',
            'severity' => 'Medium',
        ]);
        Diagnosis::factory()->create([
            'record_id' => $testRecord->record_id,
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
            'session_id' => $session1->session_id,
            'diagnosis_name' => 'Dental Caries',
            'severity' => 'Low',
        ]);

        $this->command->info('');
        $this->command->info('============================================');
        $this->command->info(' Test patient seeded successfully!');
        $this->command->info(' Email    : ali1234@gmail.com');
        $this->command->info(' Password : ali.1234');
        $this->command->info(" User ID  : {$testUser->user_id}");
        $this->command->info(" Token    : {$testUser->api_token}");
        $this->command->info('============================================');
        $this->command->info('');
    }
}
