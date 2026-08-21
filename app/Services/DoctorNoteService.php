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
     * Update a doctor note (author only — Phase I ownership rule).
     * Accepts { title?, note, note_type? } — mirrors the store validation.
     */
    public function update(int $noteId, array $data, ?int $doctorId = null): JsonResponse
    {
        $note = DoctorNote::query()->findOrFail($noteId);

        if ($doctorId !== null && (int) $note->doctor_id !== (int) $doctorId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own notes.',
            ], 403);
        }

        $body = trim((string) ($data['note'] ?? ''));

        if ($body === '') {
            return response()->json([
                'success' => false,
                'message' => 'The note field is required.',
            ], 422);
        }

        $note->note = $body;

        if (array_key_exists('title', $data)) {
            $title = trim((string) ($data['title'] ?? ''));
            $note->title = $title !== '' ? mb_substr($title, 0, 255) : null;
        }

        if (! empty($data['note_type'])
            && in_array($data['note_type'], ['general', 'prescription', 'follow_up', 'referral'], true)) {
            $note->note_type = $data['note_type'];
        }

        $note->save();

        return response()->json([
            'success' => true,
            'message' => 'Doctor note updated successfully.',
            'data' => $note->fresh(),
        ]);
    }

    /**
     * Delete a doctor note (author only — Phase I ownership rule).
     */
    public function destroy(int $noteId, ?int $doctorId = null): JsonResponse
    {
        $note = DoctorNote::query()->findOrFail($noteId);

        // Phase I: only the authoring doctor may delete the note.
        if ($doctorId !== null && $note->doctor_id !== $doctorId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own notes.',
            ], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor note deleted successfully.',
        ]);
    }
}
