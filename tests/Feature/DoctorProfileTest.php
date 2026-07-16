<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $patientUser;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a patient user and patient
        $this->patientUser = User::factory()->create([
            'role' => 'Patient',
        ]);
        Patient::create([
            'patient_id' => $this->patientUser->user_id,
            'date_of_birth' => '1995-02-10',
        ]);

        // Create a doctor with full profile info
        $doctorUser = User::factory()->create([
            'role' => 'Doctor',
            'first_name' => 'Ahmad',
            'last_name' => 'Al-Khatib',
        ]);
        $this->doctor = Doctor::factory()->create([
            'doctor_id' => $doctorUser->user_id,
        ]);
    }

    public function test_patient_can_view_doctor_public_profile(): void
    {
        // Create a treatment plan with a single case (1-to-1)
        $plan = TreatmentPlan::factory()->create([
            'doctor_id' => $this->doctor->doctor_id,
            'patient_id' => $this->patientUser->user_id,
        ]);

        CaseModel::factory()->done()->create(['treatment_plan_id' => $plan->plan_id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->patientUser->api_token)
            ->getJson("/api/patient/doctors/{$this->doctor->doctor_id}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['first_name', 'last_name', 'email', 'phone', 'profile_image'],
                    'doctor_id',
                    'specialization',
                    'license_number',
                    'years_of_experience',
                    'rating',
                    'reviews_count',
                    'about',
                    'education',
                    'certifications',
                    'expertise',
                    'cases' => [
                        '*' => [
                            'plan_id',
                            'title',
                            'description',
                            'case' => [
                                'case_id',
                                'title',
                                'patient_age',
                                'before_photo',
                                'after_photo',
                                'case_duration',
                            ],
                        ],
                    ],
                ],
            ]);

        // Verify doctor profile info
        $this->assertEquals('Ahmad', $response->json('data.user.first_name'));
        $this->assertEquals('Al-Khatib', $response->json('data.user.last_name'));
        $this->assertNotNull($response->json('data.about'));
        $this->assertIsArray($response->json('data.education'));
        $this->assertIsArray($response->json('data.certifications'));
        $this->assertIsArray($response->json('data.expertise'));

        // Verify cases — 1:1
        $cases = $response->json('data.cases');
        $this->assertCount(1, $cases);
        $case = $cases[0]['case'];
        $this->assertNotNull($case);
        $this->assertNotNull($case['case_id']);
        $this->assertNotNull($case['before_photo']);
        $this->assertNotNull($case['after_photo']);
        $this->assertNotNull($case['case_duration']);
    }

    public function test_patient_cannot_view_doctor_profile_without_auth(): void
    {
        $response = $this->getJson("/api/patient/doctors/{$this->doctor->doctor_id}");

        $response->assertUnauthorized();
    }
}
