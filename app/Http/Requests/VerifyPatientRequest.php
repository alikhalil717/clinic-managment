<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Allergies
            'allergies' => ['nullable', 'array'],
            'allergies.*.name' => ['required_with:allergies', 'string', 'max:255'],
            'allergies.*.severity' => ['nullable', 'string', 'in:low,medium,high'],
            'allergies.*.notes' => ['nullable', 'string'],

            // Medical history conditions
            'medical_histories' => ['nullable', 'array'],
            'medical_histories.*.condition_name' => ['required_with:medical_histories', 'string', 'max:255'],
            'medical_histories.*.description' => ['nullable', 'string'],
            'medical_histories.*.diagnosed_date' => ['nullable', 'date'],

            // Diagnoses
            'diagnoses' => ['nullable', 'array'],
            'diagnoses.*.diagnosis_name' => ['required_with:diagnoses', 'string', 'max:255'],
            'diagnoses.*.description' => ['nullable', 'string'],
            'diagnoses.*.severity' => ['nullable', 'string', 'in:low,medium,high'],
            'diagnoses.*.diagnosed_at' => ['nullable', 'date'],

            // Current medications (prescribed to the patient)
            'medications' => ['nullable', 'array'],
            'medications.*.medication_id' => ['required_with:medications', 'integer', 'exists:medication,medication_id'],
            'medications.*.dosage' => ['nullable', 'string', 'max:255'],
            'medications.*.frequency' => ['nullable', 'string', 'max:255'],
            'medications.*.start_date' => ['nullable', 'date'],
            'medications.*.end_date' => ['nullable', 'date', 'after_or_equal:medications.*.start_date'],
            'medications.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
