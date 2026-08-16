<?php

namespace App\Services;

use App\Http\Requests\StoreDoctorNoteRequest;
use App\Models\DoctorNote;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class DoctorNoteService
{
    /**
     * Create a doctor note on a patient's medical record.
     */
    public function store(StoreDoctorNoteRequest $request, int $patientId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($patientId);

        $record = MedicalRecord::firstOrCreate(
            ['patient_id' => $patient->patient_id],
            ['created_at' => now()]
        );

        $doctorId = $request->user()->user_id;

        $note = DoctorNote::create([
            'record_id' => $record->record_id,
            'doctor_id' => $doctorId,
            'title' => $request->validated('title'),
            'note' => $request->validated('note'),
            'note_type' => $request->validated('note_type') ?? 'general',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor note added successfully.',
            'data' => $note,
        ], 201);
    }

    /**
     * List all doctor notes for a patient's record (doctor view).
     */
    public function index(int $patientId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($patientId);

        $record = MedicalRecord::where('patient_id', $patient->patient_id)->first();

        $notes = $record
            ? $record->doctorNotes()->with('doctor.user')->orderByDesc('created_at')->get()
            : collect();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    /**
     * Show a single doctor note.
     */
    public function show(int $noteId): JsonResponse
    {
        $note = DoctorNote::with('doctor.user', 'record.patient.user')
            ->findOrFail($noteId);

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    /**
     * Delete a doctor note (author only).
     */
    public function destroy(int $noteId): JsonResponse
    {
        $note = DoctorNote::query()->findOrFail($noteId);

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor note deleted successfully.',
        ]);
    }
}
