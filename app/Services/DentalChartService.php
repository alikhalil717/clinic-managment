<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Tooth;
use App\Models\ToothCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DentalChartService
{
    /**
     * Maps the DB condition_status enum to the labels the Flutter
     * patient dental chart renders (see PatientDentalChartScreen.statusColors).
     */
    private const STATUS_MAP = [
        'healthy' => 'Healthy',
        'decay' => 'Caries',
        'damaged' => 'Filled',
        'treated' => 'Root Canal',
        'missing' => 'Missing',
    ];

    /**
     * Full dental chart for the authenticated patient: every FDI tooth
     * (52 codes) with its current status/notes, so the chart never shows
     * empty teeth when the catalog is seeded.
     */
    public function index(Request $request): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $conditions = ToothCondition::query()
            ->where('patient_id', $patient->patient_id)
            ->get()
            ->keyBy('tooth_id');

        $teeth = Tooth::query()
            ->orderBy('tooth_id')
            ->get()
            ->map(function (Tooth $tooth) use ($conditions) {
                $condition = $conditions->get($tooth->tooth_id);

                return [
                    'iso' => $tooth->tooth_code,
                    'tooth_id' => $tooth->tooth_id,
                    'tooth_name' => $tooth->tooth_name,
                    'status' => $condition
                        ? (self::STATUS_MAP[$condition->condition_status] ?? 'Healthy')
                        : 'Healthy',
                    'notes' => $condition?->notes,
                    'treatment_type' => $condition?->treatment_type,
                    'severity' => $condition?->severity_level,
                    'condition_id' => $condition?->condition_id,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'teeth' => $teeth,
            ],
        ]);
    }
}
