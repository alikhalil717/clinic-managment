<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'profile_image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'date_of_birth' => ['sometimes', 'date'],
            'specialization' => ['sometimes', 'string', 'max:255'],
            'license_number' => ['sometimes', 'string', 'max:100'],
            'years_of_experience' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
