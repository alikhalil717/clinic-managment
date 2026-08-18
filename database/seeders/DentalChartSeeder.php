<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Tooth;
use App\Models\ToothCondition;
use App\Models\TreatmentSession;
use Illuminate\Database\Seeder;

class DentalChartSeeder extends Seeder
{
    /**
     * Seeds the 52 FDI tooth catalog plus a few realistic tooth conditions
     * for the demo patient (Ali Alkhalil, patient_id 6) so the Flutter
     * patient dental chart shows real, non-empty data.
     *
     * Idempotent: uses updateOrCreate so it can be re-run safely.
     */
    public function run(): void
    {
        // 1) Full FDI catalog (52 teeth)
        (new ToothCatalogSeeder())->run();

        // 2) Demo conditions for patient 6 (Ali Alkhalil)
        $patient = Patient::query()->find(6);
        if (! $patient) {
            return;
        }

        $session = TreatmentSession::query()
            ->where('patient_id', $patient->patient_id)
            ->orderBy('session_id')
            ->first();

        $doctorId = $session?->doctor_id ?? 8;

        $demo = [
            '11' => ['healthy',  'cleaning',   'No issues.',                    'low'],
            '16' => ['damaged',  'filling',    'Old filling, needs replacement.', 'medium'],
            '26' => ['decay',    'root_canal', 'Deep caries approaching pulp.', 'high'],
            '36' => ['missing',  'extraction', 'Tooth extracted.',              'medium'],
            '47' => ['treated',  'root_canal', 'Root canal completed.',         'low'],
        ];

        foreach ($demo as $code => [$status, $treatment, $description, $severity]) {
            $tooth = Tooth::query()->where('tooth_code', $code)->first();
            if (! $tooth) {
                continue;
            }

            ToothCondition::query()->updateOrCreate(
                [
                    'patient_id' => $patient->patient_id,
                    'tooth_id' => $tooth->tooth_id,
                ],
                [
                    'doctor_id' => $doctorId,
                    'condition_status' => $status,
                    'treatment_type' => $treatment,
                    'treatment_description' => $description,
                    'estimated_price' => match ($treatment) {
                        'extraction' => 350,
                        'root_canal' => 900,
                        'filling' => 250,
                        default => 120,
                    },
                    'severity_level' => $severity,
                    'notes' => $description,
                    'session_id' => $session?->session_id ?? 1,
                    'updated_at' => now(),
                ],
            );
        }
    }
}
