<?php

namespace App\Services;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminPatientService
{
    /**
     * Get paginated list of all patients.
     */
    public function index(): JsonResponse
    {
        $patients = Patient::with('user')
            ->get()
            ->map(function ($patient) {
                return [
                    'patient_id' => $patient->patient_id,
                    'first_name' => $patient->user->first_name,
                    'last_name' => $patient->user->last_name,
                    'email' => $patient->user->email,
                    'phone' => $patient->user->phone,
                    'date_of_birth' => $patient->date_of_birth,
                    'profile_image' => $patient->user->profile_image
                        ? asset('storage/' . $patient->user->profile_image)
                        : null,
                    'created_at' => $patient->user->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    /**
     * Store a newly created patient.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'Patient',
            'api_token' => Str::random(60),
        ]);

        $patient = Patient::query()->create([
            'patient_id' => $user->user_id,
            'date_of_birth' => $data['date_of_birth'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully.',
            'data' => [
                'patient_id' => $patient->patient_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $patient->date_of_birth,
            ],
        ], 201);
    }

    /**
     * Display the specified patient.
     */
    public function show(int $patientId): JsonResponse
    {
        $patient = Patient::with(['user', 'appointments', 'treatmentPlans', 'payments'])
            ->findOrFail($patientId);

        return response()->json([
            'success' => true,
            'data' => [
                'patient_id' => $patient->patient_id,
                'first_name' => $patient->user->first_name,
                'last_name' => $patient->user->last_name,
                'email' => $patient->user->email,
                'phone' => $patient->user->phone,
                'date_of_birth' => $patient->date_of_birth,
                'profile_image' => $patient->user->profile_image
                    ? asset('storage/' . $patient->user->profile_image)
                    : null,
                'appointments_count' => $patient->appointments->count(),
                'treatment_plans_count' => $patient->treatmentPlans->count(),
                'total_payments' => $patient->payments->sum('amount'),
            ],
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(UpdatePatientRequest $request, int $patientId): JsonResponse
    {
        $patient = Patient::with('user')->findOrFail($patientId);
        $data = $request->validated();

        $userData = [];
        if (isset($data['first_name'])) {
            $userData['first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $userData['last_name'] = $data['last_name'];
        }
        if (isset($data['email'])) {
            $userData['email'] = $data['email'];
        }
        if (isset($data['phone'])) {
            $userData['phone'] = $data['phone'];
        }
        if (isset($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        if (!empty($userData)) {
            $patient->user->update($userData);
        }

        if (isset($data['date_of_birth'])) {
            $patient->update(['date_of_birth' => $data['date_of_birth']]);
        }

        $patient->refresh()->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully.',
            'data' => [
                'patient_id' => $patient->patient_id,
                'first_name' => $patient->user->first_name,
                'last_name' => $patient->user->last_name,
                'email' => $patient->user->email,
                'phone' => $patient->user->phone,
                'date_of_birth' => $patient->date_of_birth,
            ],
        ]);
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(int $patientId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($patientId);
        $patient->user()->delete();
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully.',
        ]);
    }
}
