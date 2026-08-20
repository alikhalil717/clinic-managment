<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecretaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $secretaryId = $this->route('secretary');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($secretaryId, 'user_id'),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'required', 'string', 'regex:/^\+963\d{9}$/'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_profile_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must follow the format +963xxxxxxxxx (e.g. +963912345678).',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'The password must be at least 8 characters.',
        ];
    }
}