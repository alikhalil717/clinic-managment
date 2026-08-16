<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordMedicationTest extends TestCase
{
    use RefreshDatabase;

    private function makePatient(): Patient
    {
        $user = User::factory()->create([
            'first_name' => 'Paul',
            'last_name' => 'Patient',
            'email' => 'patient2@example.com',
            'phone' => '4444444445',
            'role' => 'patient',
            'api_token' => 'patient-token-abc',
        ]);

        return Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => '1995-02-10',
        ]);
    }

    private function makeDoctor(): Doctor
    {
        $user = User::factory()->create([
            'first_name' => 'Dylan',
            'last_name' => 'Doctor',
            'email' => 'doctor2@example.com',
            'phone' => '3333333334',
            'role' => 'doctor',
            'api_token' => 'doctor-token-abc',
        ]);

        return Doctor::create([
            'doctor_id' => $user->user_id,
            'specialization' => 'General Dentistry',
            'license_number' => 'LIC-20001',
            'years_of_experience' => 10,
            'rating' => 4.9,
            'reviews_count' => 30,
        ]);
    }

    public function test_medication_catalog_can_be_seeded_and_queried(): void
    {
        $medication = Medication::factory()->create([
            'name' => 'Amoxicillin',
            'category' => 'antibiotic',
        ]);

        $this->assertDatabaseHas('medication', [
            'medication_id' => $medication->medication_id,
            'name' => 'Amoxicillin',
            'category' => 'antibiotic',
        ]);

        $this->assertSame('Amoxicillin', $medication->name);
        $this->assertTrue($medication->is_active);
    }

    public function test_patient_current_medication_is_linked_to_record_and_catalog(): void
    {
        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();

        $record = MedicalRecord::create([
            'patient_id' => $patient->patient_id,
            'created_at' => now(),
        ]);

        $medication = Medication::factory()->create();
        $patientMedication = PatientMedication::create([
            'record_id' => $record->record_id,
            'medication_id' => $medication->medication_id,
            'dosage' => '500mg',
            'frequency' => 'three times daily',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-15',
            'prescribed_by' => $doctor->doctor_id,
            'notes' => 'Take after meals',
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('patient_medication', [
            'patient_medication_id' => $patientMedication->patient_medication_id,
            'record_id' => $record->record_id,
            'medication_id' => $medication->medication_id,
            'prescribed_by' => $doctor->doctor_id,
            'is_current' => true,
        ]);

        // Relations resolve correctly
        $this->assertSame($record->record_id, $patientMedication->record->record_id);
        $this->assertSame($medication->medication_id, $patientMedication->medication->medication_id);
        $this->assertSame($doctor->doctor_id, $patientMedication->prescribedBy->doctor_id);
        $this->assertCount(1, $record->medications);
        $this->assertCount(1, $medication->patientMedications);
        $this->assertCount(1, $doctor->prescribedMedications);
    }

    public function test_doctor_notes_are_linked_to_record_and_doctor(): void
    {
        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();

        $record = MedicalRecord::create([
            'patient_id' => $patient->patient_id,
            'created_at' => now(),
        ]);

        $note = DoctorNote::create([
            'record_id' => $record->record_id,
            'doctor_id' => $doctor->doctor_id,
            'title' => 'Follow-up',
            'note' => 'Patient recovering well.',
            'note_type' => 'follow_up',
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('doctor_note', [
            'note_id' => $note->note_id,
            'record_id' => $record->record_id,
            'doctor_id' => $doctor->doctor_id,
            'note_type' => 'follow_up',
        ]);

        $this->assertSame($record->record_id, $note->record->record_id);
        $this->assertSame($doctor->doctor_id, $note->doctor->doctor_id);
        $this->assertCount(1, $record->doctorNotes);
        $this->assertCount(1, $doctor->doctorNotes);
        $this->assertCount(1, $patient->doctorNotes);
    }
}
