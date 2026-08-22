<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'specialization' => ['required', 'string', 'max:255'],
            'years_of_experience' => ['required', 'integer', 'min:0'],
            'about' => ['sometimes', 'nullable', 'string'],
            'education' => ['sometimes', 'nullable', 'array'],
            'education.*' => ['string'],
            'certifications' => ['sometimes', 'nullable', 'array'],
            'certifications.*' => ['string'],
            'expertise' => ['sometimes', 'nullable', 'array'],
            'expertise.*' => ['string'],
            'working_days' => ['sometimes', 'nullable', 'array'],
            'working_days.*' => ['string', 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'working_hours' => ['sometimes', 'nullable', 'array'],
            'working_hours.*' => ['array'],
            'working_hours.*.start' => ['nullable', 'date_format:H:i,H:i:s'],
            'working_hours.*.end' => ['nullable', 'date_format:H:i,H:i:s'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $decoded = [];

        foreach (['education', 'certifications', 'expertise'] as $field) {
            $value = $this->decodedArray($field);

            if ($value !== null) {
                $decoded[$field] = $value;
            }
        }

        $this->merge($decoded);
    }

    private function decodedArray(string $field): ?array
    {
        $value = $this->input($field);

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : null;
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
