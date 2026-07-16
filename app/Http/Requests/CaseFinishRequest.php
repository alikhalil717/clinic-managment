<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseFinishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'after_photo' => ['required', 'image', 'max:10240'],
        ];
    }
}
