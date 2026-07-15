<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserProfileResource($this->user),
            'doctor_id' => $this->doctor_id,
            'specialization' => $this->specialization,
            'license_number' => $this->license_number,
            'years_of_experience' => $this->years_of_experience,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
        ];
    }
}
