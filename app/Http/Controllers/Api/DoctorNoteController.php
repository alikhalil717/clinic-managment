<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorNoteRequest;
use App\Models\DoctorNote;
use App\Models\Patient;
use App\Services\DoctorNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorNoteController extends Controller
{
    public function __construct(private readonly DoctorNoteService $doctorNoteService) {}

    /**
     * Store a doctor note on a patient's medical record.
     */
    public function store(StoreDoctorNoteRequest $request, Patient $patient): JsonResponse
    {
        return $this->doctorNoteService->store($request, $patient->patient_id);
    }

    /**
     * List all doctor notes for a patient.
     */
    public function index(Patient $patient): JsonResponse
    {
        return $this->doctorNoteService->index($patient->patient_id);
    }

    /**
     * Show a single doctor note.
     */
    public function show(DoctorNote $note): JsonResponse
    {
        return $this->doctorNoteService->show($note->note_id);
    }

    /**
     * Delete a doctor note (author only).
     */
    public function destroy(Request $request, DoctorNote $note): JsonResponse
    {
        return $this->doctorNoteService->destroy($note->note_id, $request->user()->user_id);
    }
}
