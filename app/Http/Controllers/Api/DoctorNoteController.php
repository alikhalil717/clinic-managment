<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorNoteRequest;
use App\Services\DoctorNoteService;
use Illuminate\Http\JsonResponse;

class DoctorNoteController extends Controller
{
    public function __construct(private readonly DoctorNoteService $doctorNoteService) {}

    /**
     * Store a doctor note on a patient's medical record.
     */
    public function store(StoreDoctorNoteRequest $request, int $patientId): JsonResponse
    {
        return $this->doctorNoteService->store($request, $patientId);
    }

    /**
     * List all doctor notes for a patient.
     */
    public function index(int $patientId): JsonResponse
    {
        return $this->doctorNoteService->index($patientId);
    }

    /**
     * Show a single doctor note.
     */
    public function show(int $noteId): JsonResponse
    {
        return $this->doctorNoteService->show($noteId);
    }

    /**
     * Delete a doctor note (author only).
     */
    public function destroy(Request $request, int $note): JsonResponse
    {
        return $this->doctorNoteService->destroy($note, $request->user()->user_id);
    }
}
