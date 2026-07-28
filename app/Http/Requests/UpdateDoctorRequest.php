<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $doctorId = $this->route('doctor');

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $doctorId . ',user_id'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'password' => ['sometimes', 'string', 'min:8'],
            'specialization' => ['sometimes', 'string', 'max:255'],
            'license_number' => ['sometimes', 'string', 'max:255'],
            'years_of_experience' => ['sometimes', 'integer', 'min:0'],
            'about' => ['sometimes', 'nullable', 'string'],
            'education' => ['sometimes', 'nullable', 'array'],
            'education.*' => ['string'],
            'certifications' => ['sometimes', 'nullable', 'array'],
            'certifications.*' => ['string'],
            'expertise' => ['sometimes', 'nullable', 'array'],
            'expertise.*' => ['string'],
        ];
    }
}
