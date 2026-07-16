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
            'about' => $this->about,
            'education' => $this->education,
            'certifications' => $this->certifications,
            'expertise' => $this->expertise,
            'cases' => $this->whenLoaded('treatmentPlans', function () {
                return $this->treatmentPlans->map(function ($plan) {
                    return [
                        'plan_id' => $plan->plan_id,
                        'title' => $plan->title,
                        'description' => $plan->description,
                        'estimated_total_cost' => $plan->estimated_total_cost,
                        'actual_total_cost' => $plan->actual_total_cost,
                        'progress_percentage' => $plan->progress_percentage,
                        'created_at' => $plan->created_at,
                        'cases' => $plan->cases->map(function ($case) {
                            return [
                                'case_id' => $case->case_id,
                                'before_photo' => $case->before_photo,
                                'after_photo' => $case->after_photo,
                                'status' => $case->status,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
