<?php

namespace App\Services;

use App\Http\Requests\CaseFinishRequest;
use App\Models\CaseModel;
use Illuminate\Http\JsonResponse;

class CaseService
{
    /**
     * Mark a case as done and upload the after photo.
     * Updates created_at so case_duration calculates from plan creation to now.
     */
    public function finish(CaseFinishRequest $request, int $caseId): JsonResponse
    {
        $case = CaseModel::with('treatmentPlan')->findOrFail($caseId);

        $path = $request->file('after_photo')->store('cases/after', 'public');

        $case->update([
            'after_photo' => $path,
            'created_at' => now(), // timestamp used for duration calculation
        ]);

        return response()->json([
            'message' => 'Case marked as done.',
            'case' => [
                'case_id' => $case->case_id,
                'after_photo' => $case->after_photo,
                'case_duration' => $case->case_duration,
            ],
        ]);
    }
}
