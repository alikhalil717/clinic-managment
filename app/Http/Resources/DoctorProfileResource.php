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
            'profile_image' => $this->user?->profile_image
                ? asset('storage/' . $this->user->profile_image)
                : null,
            'specialization' => $this->specialization,
            'license_number' => $this->license_number,
            'years_of_experience' => $this->years_of_experience,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'about' => $this->about,
            'education' => $this->education,
            'certifications' => $this->certifications,
            'expertise' => $this->expertise,
            'working_days' => $this->working_days,
            'working_hours' => $this->working_hours,
            'cases' => $this->whenLoaded('treatmentPlans', function () {
                return $this->treatmentPlans->map(function ($plan) {
                    $caseData = null;
                    if ($plan->relationLoaded('case') && $plan->case) {
                        $caseData = [
                            'case_id' => $plan->case->case_id,
                            'title' => $plan->case->title,
                            'patient_age' => $plan->case->patient_age,
                            'before_photo' => $plan->case->before_photo
                                ? asset('storage/' . $plan->case->before_photo)
                                : null,
                            'after_photo' => $plan->case->after_photo
                                ? asset('storage/' . $plan->case->after_photo)
                                : null,
                            'case_duration' => $plan->case->case_duration,
                        ];
                    }

                    return [
                        'plan_id' => $plan->plan_id,
                        'title' => $plan->title,
                        'description' => $plan->description,
                        'estimated_total_cost' => $plan->estimated_total_cost,
                        'actual_total_cost' => $plan->actual_total_cost,
                        'progress_percentage' => $plan->progress_percentage,
                        'created_at' => $plan->created_at,
                        'case' => $caseData,
                    ];
                });
            }),
        ];
    }
}
