<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    /**
     * Mark a case as done and upload the after photo.
     */
    public function finish(Request $request, int $caseId): JsonResponse
    {
        $request->validate([
            'after_photo' => 'required|image|max:10240', // 10MB max
        ]);

        $case = CaseModel::findOrFail($caseId);

        // Store the after photo
        $path = $request->file('after_photo')->store('cases/after', 'public');

        $case->update([
            'after_photo' => $path,
            'status' => 'done',
        ]);

        return response()->json([
            'message' => 'Case marked as done.',
            'case' => [
                'case_id' => $case->case_id,
                'status' => $case->status,
                'after_photo' => $case->after_photo,
            ],
        ]);
    }
}
