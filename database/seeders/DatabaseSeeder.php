<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\CaseModel;
use App\Models\DentalChart;
use App\Models\DentalChartTooth;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\DoctorPayout;
use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PatientMedication;
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
        // FDI tooth catalog (52 real teeth) + demo chart conditions
        // -----------------------------------------------------------------
        (new ToothCatalogSeeder())->run();
        // -----------------------------------------------------------------
        // Existing random seed data
        // -----------------------------------------------------------------
        $testuser2 = User::factory()->create([
            'email' => 'aliadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $testadmin = Admin::factory()->create([
            'admin_id' => $testuser2->user_id,
        ]);
        Secretary::factory()->create();

        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
            'appointment_type' => 'normal',
        ]);

        $session = TreatmentSession::factory()->create([
            'appointment_id' => $appointment->appointment_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $tooth = Tooth::query()->where('tooth_code', '16')->first() ?? Tooth::factory()->create();

        // Create a treatment plan with a single case (1-to-1)
        $plan = TreatmentPlan::factory()->create([
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        CaseModel::factory()->done()->create([
            'treatment_plan_id' => $plan->plan_id,
        ]);

        // Link the stage to the existing plan (otherwise the factory would
        // create a new orphan treatment plan with no case)
        $stage = TreatmentStage::factory()->create([
            'plan_id' => $plan->plan_id,
        ]);

        // Each treatment plan owns exactly one dental chart (Phase A)
        $chart = DentalChart::factory()->create([
            'plan_id' => $plan->plan_id,
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
        ]);
        $this->attachChartTeeth($chart);

        // The appointment + its session belong to the plan/stage above
        $appointment->update([
            'treatment_plan_id' => $plan->plan_id,
            'treatment_stage_id' => $stage->stage_id,
        ]);
        $session->update(['plan_id' => $plan->plan_id]);

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
            'role' => 'patient',
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
            'role' => 'doctor',
            'profile_image' => 'profiles/doc1.jpg',
        ]);
        $doctor1 = Doctor::factory()->create([
            'doctor_id' => $doc1User->user_id,
            'specialization' => 'Orthodontist',
            'years_of_experience' => 8,
            'rating' => 4.8,
            'reviews_count' => 120,
            'working_days' => ['saturday', 'monday', 'wednesday'],
            'working_hours' => [
                'saturday' => ['start' => '10:00', 'end' => '15:00'],
                'monday' => ['start' => '10:00', 'end' => '15:00'],
                'wednesday' => ['start' => '15:00', 'end' => '20:00'],
            ],
        ]);

        $doc2User = User::factory()->create([
            'first_name' => 'Sara',
            'last_name' => 'Hassan',
            'role' => 'doctor',
            'profile_image' => 'profiles/doc2.jpg',
        ]);
        $doctor2 = Doctor::factory()->create([
            'doctor_id' => $doc2User->user_id,
            'specialization' => 'Endodontics',
            'years_of_experience' => 12,
            'rating' => 4.6,
            'reviews_count' => 95,
            'working_days' => ['sunday', 'tuesday', 'thursday'],
            'working_hours' => [
                'sunday' => ['start' => '09:00', 'end' => '14:00'],
                'tuesday' => ['start' => '09:00', 'end' => '14:00'],
                'thursday' => ['start' => '15:00', 'end' => '20:00'],
            ],
        ]);

        // --- Upcoming Appointments ---
        // Standalone appointment (not part of any treatment plan)
        Appointment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'Teeth Cleaning',
        ]);

        $appt2 = Appointment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
            'date' => now()->addDays(7)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'status' => 'confirmed',
            'appointment_type' => 'diagnostic',
            'notes' => 'Root Canal Checkup',
        ]);

        // Past appointment (should NOT show in upcoming)
        Appointment::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'date' => now()->subDays(5)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'finished',
            'notes' => 'Initial Consultation',
        ]);

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

        // --- Treatment Stages (each plan has stages) ---
        $stage1 = TreatmentStage::factory()->create([
            'plan_id' => $plan1->plan_id,
        ]);
        $stage2 = TreatmentStage::factory()->create([
            'plan_id' => $plan2->plan_id,
        ]);

        // --- Per-plan dental charts (each treatment plan owns its chart) ---
        $chart1 = DentalChart::factory()->create([
            'plan_id' => $plan1->plan_id,
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
        ]);
        $chart2 = DentalChart::factory()->create([
            'plan_id' => $plan2->plan_id,
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
        ]);
        $this->attachChartTeeth($chart1);
        $this->attachChartTeeth($chart2);

        // --- Link appointments into plan stages ---
        // appt2 (diagnostic) becomes the kickoff appointment of plan2 stage1
        $appt2->update([
            'treatment_plan_id' => $plan2->plan_id,
            'treatment_stage_id' => $stage1->stage_id,
        ]);

        // --- Treatment Sessions (linked to a plan + its appointment) ---
        $session1 = TreatmentSession::factory()->create([
            'appointment_id' => $appt2->appointment_id,
            'plan_id' => $plan2->plan_id,
            'doctor_id' => $doctor2->doctor_id,
            'patient_id' => $testPatient->patient_id,
        ]);

        // --- Teeth (real FDI catalog) ---
        $tooth1 = Tooth::query()->where('tooth_code', '16')->first() ?? Tooth::factory()->create();
        $tooth2 = Tooth::query()->where('tooth_code', '26')->first() ?? Tooth::factory()->create();

        // --- Done Treatment Plan (completed, with a finished case) ---
        $donePlan = TreatmentPlan::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
            'title' => 'Full Braces Treatment',
            'description' => 'Completed orthodontic treatment with before/after photos.',
            'progress_percentage' => 100,
            'estimated_total_cost' => 3500.00,
            'actual_total_cost' => 3450.00,
            'created_at' => now()->subMonths(6), // started 6 months ago
        ]);

        // "done" state fills in after_photo; created_at after plan start so case_duration is computed
        CaseModel::factory()->done()->create([
            'treatment_plan_id' => $donePlan->plan_id,
            'title' => 'Full Braces Treatment',
            'patient_age' => 29,
            'created_at' => now()->subDays(3), // finished 3 days ago
        ]);

        // Completed plans also own a dental chart
        $doneChart = DentalChart::factory()->create([
            'plan_id' => $donePlan->plan_id,
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor1->doctor_id,
        ]);
        $this->attachChartTeeth($doneChart);

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
            'plan_id' => $plan2->plan_id,
            'type' => 'plan_payment',
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
            'source' => 'session',
            'description' => 'Points from teeth cleaning session',
        ]);
        PatientPoints::factory()->create([
            'patient_id' => $testPatient->patient_id,
            'points' => 50,
            'source' => 'payment',
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
            'severity' => 'medium',
        ]);
        Diagnosis::factory()->create([
            'record_id' => $testRecord->record_id,
            'patient_id' => $testPatient->patient_id,
            'doctor_id' => $doctor2->doctor_id,
            'session_id' => $session1->session_id,
            'diagnosis_name' => 'Dental Caries',
            'severity' => 'low',
        ]);

        // --- Medication catalog ---
        $amoxicillin = Medication::factory()->create([
            'name' => 'Amoxicillin',
            'category' => 'antibiotic',
            'dosage_form' => 'capsule',
            'description' => 'Broad-spectrum antibiotic for dental infections.',
            'side_effects' => 'Nausea, diarrhea, rash.',
        ]);
        $ibuprofen = Medication::factory()->create([
            'name' => 'Ibuprofen',
            'category' => 'analgesic',
            'dosage_form' => 'tablet',
            'description' => 'Pain relief and anti-inflammatory.',
            'side_effects' => 'Stomach upset, dizziness.',
        ]);
        Medication::factory()->create([
            'name' => 'Paracetamol',
            'category' => 'analgesic',
            'dosage_form' => 'tablet',
            'description' => 'Mild pain relief and fever reduction.',
            'side_effects' => 'Liver damage at high doses.',
        ]);
        Medication::factory()->create([
            'name' => 'Metronidazole',
            'category' => 'antibiotic',
            'dosage_form' => 'tablet',
            'description' => 'Antibiotic for anaerobic infections.',
            'side_effects' => 'Metallic taste, nausea.',
        ]);
        Medication::factory()->create([
            'name' => 'Chlorhexidine',
            'category' => 'antiseptic',
            'dosage_form' => 'mouthwash',
            'description' => 'Antiseptic mouthwash for oral hygiene.',
            'side_effects' => 'Temporary staining of teeth.',
        ]);

        // --- Patient current medications ---
        PatientMedication::factory()->create([
            'record_id' => $testRecord->record_id,
            'medication_id' => $amoxicillin->medication_id,
            'dosage' => '500mg',
            'frequency' => 'three times daily',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'prescribed_by' => $doctor1->doctor_id,
            'notes' => 'Take after meals.',
            'is_current' => true,
        ]);
        PatientMedication::factory()->create([
            'record_id' => $testRecord->record_id,
            'medication_id' => $ibuprofen->medication_id,
            'dosage' => '400mg',
            'frequency' => 'every 6 hours',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'prescribed_by' => $doctor1->doctor_id,
            'notes' => 'For pain after braces adjustment.',
            'is_current' => true,
        ]);

        // --- Doctor notes ---
        DoctorNote::factory()->create([
            'record_id' => $testRecord->record_id,
            'doctor_id' => $doctor1->doctor_id,
            'title' => 'Initial consultation',
            'note' => 'Patient presents with mild crowding. Recommended braces treatment. Patient informed of cost and duration.',
            'note_type' => 'general',
            'created_at' => now()->subDays(10),
        ]);
        DoctorNote::factory()->create([
            'record_id' => $testRecord->record_id,
            'doctor_id' => $doctor2->doctor_id,
            'title' => 'Root canal follow-up',
            'note' => 'Patient reported sensitivity after root canal. Advised to return in 2 weeks if pain persists.',
            'note_type' => 'follow_up',
            'created_at' => now()->subDays(3),
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

    /**
     * Attach a handful of REAL FDI catalog teeth to a plan's dental chart.
     * Uses the seeded 52-tooth catalog codes (11, 16, 26, 36, 47, ...) so the
     * patient/doctor charts render real ISOs instead of factory random ones.
     */
    private function attachChartTeeth($chart): void
    {
        $codes = ['11', '16', '26', '36', '47'];

        foreach ($codes as $code) {
            $tooth = Tooth::query()->where('tooth_code', $code)->first();
            if (! $tooth) {
                continue;
            }

            DentalChartTooth::query()->updateOrCreate(
                ['chart_id' => $chart->chart_id, 'tooth_id' => $tooth->tooth_id],
                [
                    'condition_status' => match ($code) {
                        '16' => 'damaged',
                        '26' => 'decay',
                        '36' => 'missing',
                        '47' => 'treated',
                        default => 'healthy',
                    },
                    'treatment_type' => match ($code) {
                        '16' => 'filling',
                        '26' => 'root_canal',
                        '36' => 'extraction',
                        '47' => 'root_canal',
                        default => 'cleaning',
                    },
                    'treatment_description' => 'Seeded demo condition for ' . $code,
                    'estimated_price' => match ($code) {
                        '16' => 250,
                        '26' => 900,
                        '36' => 350,
                        '47' => 900,
                        default => 120,
                    },
                    'severity_level' => match ($code) {
                        '16', '36' => 'medium',
                        '26' => 'high',
                        default => 'low',
                    },
                    'notes' => 'Demo tooth ' . $code,
                    'updated_at' => now(),
                ],
            );
        }
    }
}
