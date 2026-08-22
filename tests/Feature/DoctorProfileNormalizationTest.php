<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorProfileNormalizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        Admin::create(['admin_id' => $this->adminUser->user_id, 'permissions' => 'manage-users']);
    }

    private function actingAsAdmin(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->adminUser->api_token);
    }

    public function test_store_doctor_accepts_array_fields_and_returns_them(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/admin/doctors', [
            'first_name' => 'Sami',
            'last_name' => 'Haddad',
            'email' => 'sami.haddad@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'specialization' => 'Orthodontics',
            'years_of_experience' => 7,
            'working_days' => ['saturday', 'monday'],
        ]);

        $response->assertCreated();

        $doctorId = User::query()->where('email', 'sami.haddad@example.com')->first()->user_id;

        $this->assertSame(['saturday', 'monday'], $response->json('data.working_days'));

        $this->assertDatabaseHas('doctor_working_day', [
            'doctor_id' => $doctorId,
            'day' => 'saturday',
        ]);
        $this->assertDatabaseHas('doctor_working_day', [
            'doctor_id' => $doctorId,
            'day' => 'monday',
        ]);
    }

    public function test_update_doctor_replaces_array_fields_and_keeps_response_shape(): void
    {
        $doctor = Doctor::factory()->create([
            'education' => ['DDS - Old University'],
            'certifications' => ['Board Certified Old'],
            'expertise' => ['Braces'],
            'working_days' => ['saturday'],
        ]);

        // NOTE: matches legacy behavior — an explicit null is ignored by
        // UpdateDoctorRequest handling (isset()), so expertise stays unchanged.
        $response = $this->actingAsAdmin()->putJson("/api/admin/doctors/{$doctor->doctor_id}", [
            'education' => ['DDS - New University', 'MSc - Other University'],
            'certifications' => [],
            'working_days' => ['sunday', 'monday', 'tuesday'],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.education.0', 'DDS - New University')
            ->assertJsonPath('data.education.1', 'MSc - Other University')
            ->assertJsonPath('data.working_days.0', 'sunday')
            ->assertJsonPath('data.working_days.1', 'monday')
            ->assertJsonPath('data.working_days.2', 'tuesday');

        $this->assertSame(
            ['DDS - New University', 'MSc - Other University'],
            array_values($response->json('data.education'))
        );
        $this->assertSame(['Braces'], array_values($response->json('data.expertise')));

        // Old rows must be replaced, not duplicated.
        $this->assertDatabaseCount('doctor_education', 2);
        $this->assertDatabaseCount('doctor_certification', 0);
        $this->assertDatabaseCount('doctor_expertise', 1);
        $this->assertDatabaseCount('doctor_working_day', 3);
        $this->assertDatabaseMissing('doctor_working_day', [
            'doctor_id' => $doctor->doctor_id,
            'day' => 'saturday',
        ]);
    }

    public function test_model_roundtrip_matches_old_json_column_semantics(): void
    {
        $doctor = Doctor::factory()->create();

        $doctor->update([
            'education' => ['BDS - A', 'BDS - B'],
            'working_hours' => [
                'sunday' => ['start' => '08:30', 'end' => '13:00'],
                'monday' => ['start' => '09:00', 'end' => '17:00'],
            ],
        ]);

        $fresh = Doctor::query()->find($doctor->doctor_id);

        $this->assertSame(['BDS - A', 'BDS - B'], $fresh->education);
        $this->assertSame(
            ['sunday' => ['start' => '08:30', 'end' => '13:00'], 'monday' => ['start' => '09:00', 'end' => '17:00']],
            $fresh->working_hours
        );
        $this->assertSame('08:30', $fresh->workingHoursForDay('sunday')['start']);
        $this->assertSame('13:00', $fresh->workingHoursForDay('sunday')['end']);

        // Clearing an array behaves like the old nullable JSON column.
        $fresh->update(['education' => null]);
        $this->assertNull(Doctor::query()->find($doctor->doctor_id)->education);
    }

    public function test_store_doctor_with_dashboard_payload_persists_normalized_tables(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/admin/doctors', [
            'first_name' => 'Lina',
            'last_name' => 'Nassar',
            'email' => 'lina.nassar@example.com',
            'phone' => '+963999888777',
            'password' => 'password123',
            'specialization' => 'Orthodontics, Smile Design',
            'years_of_experience' => 5,
            'about' => 'Passionate about aligners.',
            'education' => json_encode(['BDS - Damascus University']),
            'certifications' => json_encode([]),
            'expertise' => json_encode(['Clear Aligners', 'Braces']),
            'working_days' => ['friday', 'saturday'],
            'working_hours' => [
                'friday' => ['start' => '09:00', 'end' => '14:00'],
                'saturday' => ['start' => '10:00', 'end' => '15:00'],
            ],
        ]);

        $response->assertCreated();

        $doctorId = User::query()->where('email', 'lina.nassar@example.com')->first()->user_id;

        $this->assertSame('Passionate about aligners.', $response->json('data.about'));
        $this->assertSame(
            ['BDS - Damascus University'],
            array_values($response->json('data.education'))
        );
        $this->assertNull($response->json('data.certifications'));
        $this->assertSame(
            ['Clear Aligners', 'Braces'],
            array_values($response->json('data.expertise'))
        );
        $this->assertSame(
            ['friday', 'saturday'],
            array_values($response->json('data.working_days'))
        );
        $this->assertSame('09:00', $response->json('data.working_hours.friday.start'));
        $this->assertSame('14:00', $response->json('data.working_hours.friday.end'));
        $this->assertSame('10:00', $response->json('data.working_hours.saturday.start'));
        $this->assertSame('15:00', $response->json('data.working_hours.saturday.end'));

        $this->assertDatabaseHas('doctor_education', [
            'doctor_id' => $doctorId,
            'item' => 'BDS - Damascus University',
        ]);
        $this->assertDatabaseCount('doctor_certification', 0);
        $this->assertDatabaseHas('doctor_expertise', [
            'doctor_id' => $doctorId,
            'item' => 'Clear Aligners',
        ]);
        $this->assertDatabaseHas('doctor_working_day', [
            'doctor_id' => $doctorId,
            'day' => 'friday',
        ]);
        $this->assertDatabaseHas('doctor_working_hour', [
            'doctor_id' => $doctorId,
            'day' => 'saturday',
            'start_time' => '10:00:00',
            'end_time' => '15:00:00',
        ]);

        $show = $this->actingAsAdmin()->getJson("/api/admin/doctors/{$doctorId}");

        $show
            ->assertOk()
            ->assertJsonPath('data.working_hours.friday.start', '09:00')
            ->assertJsonPath('data.education.0', 'BDS - Damascus University');
    }

    public function test_update_doctor_accepts_json_string_arrays_and_working_hours(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->actingAsAdmin()->postJson("/api/admin/doctors/{$doctor->doctor_id}", [
            '_method' => 'PUT',
            'education' => json_encode(['DDS - Updated University']),
            'certifications' => json_encode(['Board Certified']),
            'expertise' => json_encode(['Whitening']),
            'working_days' => ['thursday'],
            'working_hours' => [
                'thursday' => ['start' => '11:00', 'end' => '16:30'],
            ],
        ]);

        $response->assertOk();

        $fresh = Doctor::query()->find($doctor->doctor_id);

        $this->assertSame(['DDS - Updated University'], array_values($fresh->education));
        $this->assertSame(['Board Certified'], array_values($fresh->certifications));
        $this->assertSame(['Whitening'], array_values($fresh->expertise));
        $this->assertSame(['thursday'], array_values($fresh->working_days));
        $this->assertSame('11:00', $fresh->workingHoursForDay('thursday')['start']);
        $this->assertSame('16:30', $fresh->workingHoursForDay('thursday')['end']);

        $this->assertSame('11:00', $response->json('data.working_hours.thursday.start'));
    }

    public function test_serialized_model_keeps_legacy_keys(): void
    {
        $doctor = Doctor::factory()->create([
            'education' => ['DDS - X'],
            'working_days' => ['wednesday'],
        ]);

        $json = $doctor->toArray();

        foreach (['education', 'certifications', 'expertise', 'working_days', 'working_hours'] as $key) {
            $this->assertArrayHasKey($key, $json);
        }

        $this->assertSame(['DDS - X'], array_values($json['education']));
        $this->assertSame(['wednesday'], array_values($json['working_days']));

        // Backing relations must not leak into the serialized payload.
        $this->assertArrayNotHasKey('educations', $json);
        $this->assertArrayNotHasKey('workingDays', $json);
    }
}
