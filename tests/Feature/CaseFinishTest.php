<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CaseFinishTest extends TestCase
{
    use RefreshDatabase;

    private User $doctorUser;
    private CaseModel $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctorUser = User::factory()->create([
            'role' => 'doctor',
        ]);
        Doctor::factory()->create([
            'doctor_id' => $this->doctorUser->user_id,
        ]);

        $plan = TreatmentPlan::factory()->create([
            'doctor_id' => $this->doctorUser->user_id,
        ]);

        $this->case = CaseModel::factory()->create([
            'treatment_plan_id' => $plan->plan_id,
        ]);

        Storage::fake('public');
    }

    public function test_doctor_can_finish_case_and_upload_after_photo(): void
    {
        $file = UploadedFile::fake()->create('after-photo.jpg', 1024, 'image/jpeg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->doctorUser->api_token)
            ->postJson("/api/doctor/cases/{$this->case->case_id}/finish", [
                'after_photo' => $file,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Case marked as done.')
            ->assertJsonPath('case.case_id', $this->case->case_id);

        $this->assertNotNull($response->json('case.after_photo'));
        $this->assertNotNull($response->json('case.case_duration'));

        // Verify database was updated
        $updatedCase = CaseModel::find($this->case->case_id);
        $this->assertNotNull($updatedCase->after_photo);
        $this->assertNotNull($updatedCase->case_duration);
    }

    public function test_finish_case_requires_after_photo(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->doctorUser->api_token)
            ->postJson("/api/doctor/cases/{$this->case->case_id}/finish", []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['after_photo']);
    }

    public function test_finish_case_requires_auth(): void
    {
        $file = UploadedFile::fake()->create('after-photo.jpg', 1024, 'image/jpeg');

        $response = $this->postJson("/api/doctor/cases/{$this->case->case_id}/finish", [
            'after_photo' => $file,
        ]);

        $response->assertUnauthorized();
    }
}
