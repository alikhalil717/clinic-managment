<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Doctor extends ClinicModel
{
    protected $table = 'doctor';

    protected $primaryKey = 'doctor_id';

    public $incrementing = false;

    /**
     * Attributes that used to live in JSON columns and are now stored in
     * normalized tables. They are exposed (read/write) through accessors
     * and a transparent sync-on-save hook below, so every consumer keeps
     * working with plain arrays.
     */
    private const NORMALIZED_ARRAY_ATTRIBUTES = [
        'education',
        'certifications',
        'expertise',
        'working_days',
        'working_hours',
    ];

    /**
     * Keep serialized JSON identical to the pre-normalization schema:
     * append the virtual array attributes and hide their backing relations.
     */
    protected $appends = [
        'education',
        'certifications',
        'expertise',
        'working_days',
        'working_hours',
    ];

    protected $hidden = [
        'educations',
        'certificationRows',
        'expertises',
        'workingDays',
        'workingHours',
    ];

    /**
     * Array values received before save(), persisted by syncNormalizedArrays().
     *
     * @var array<string, mixed>
     */
    private array $pendingArrayAttributes = [];

    public function educations(): HasMany
    {
        return $this->orderedList($this->hasMany(DoctorEducation::class, 'doctor_id', 'doctor_id'));
    }

    public function certificationRows(): HasMany
    {
        return $this->orderedList($this->hasMany(DoctorCertification::class, 'doctor_id', 'doctor_id'));
    }

    public function expertises(): HasMany
    {
        return $this->orderedList($this->hasMany(DoctorExpertise::class, 'doctor_id', 'doctor_id'));
    }

    public function workingDays(): HasMany
    {
        return $this->orderedList($this->hasMany(DoctorWorkingDay::class, 'doctor_id', 'doctor_id'));
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(DoctorWorkingHour::class, 'doctor_id', 'doctor_id')->orderBy('id');
    }

    private function orderedList(HasMany $relation): HasMany
    {
        return $relation->orderBy('sort_order')->orderBy('id');
    }

    public function getEducationAttribute(): ?array
    {
        return $this->listItems($this->getRelationValue('educations'));
    }

    public function getCertificationsAttribute(): ?array
    {
        return $this->listItems($this->getRelationValue('certificationRows'));
    }

    public function getExpertiseAttribute(): ?array
    {
        return $this->listItems($this->getRelationValue('expertises'));
    }

    public function getWorkingDaysAttribute(): ?array
    {
        $days = $this->getRelationValue('workingDays');

        if ($days === null || $days->isEmpty()) {
            return null;
        }

        return $days->pluck('day')->all();
    }

    public function getWorkingHoursAttribute(): ?array
    {
        $rows = $this->getRelationValue('workingHours');

        if ($rows === null || $rows->isEmpty()) {
            return null;
        }

        $hours = [];

        foreach ($rows as $row) {
            $entry = [];

            if ($row->start_time !== null) {
                $entry['start'] = substr($row->start_time, 0, 5);
            }

            if ($row->end_time !== null) {
                $entry['end'] = substr($row->end_time, 0, 5);
            }

            if ($entry !== []) {
                $hours[$row->day] = $entry;
            }
        }

        return $hours === [] ? null : $hours;
    }

    /**
     * @param Collection<int, mixed>|null $rows
     */
    private function listItems(?Collection $rows): ?array
    {
        if ($rows === null || $rows->isEmpty()) {
            return null;
        }

        return $rows->pluck('item')->all();
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, self::NORMALIZED_ARRAY_ATTRIBUTES, true)) {
            $this->pendingArrayAttributes[$key] = $value;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function save(array $options = [])
    {
        $saved = parent::save($options);

        if ($saved && $this->exists && $this->pendingArrayAttributes !== []) {
            $pending = $this->pendingArrayAttributes;
            $this->pendingArrayAttributes = [];

            $this->syncNormalizedArrays($pending);
        }

        return $saved;
    }

    /**
     * Persist array assignments into the normalized tables, replacing the
     * previous rows so semantics match the old single-column overwrite.
     *
     * @param array<string, mixed> $attributes
     */
    private function syncNormalizedArrays(array $attributes): void
    {
        if (array_key_exists('education', $attributes)) {
            $this->syncListRows($this->educations(), $attributes['education']);
            unset($this->relations['educations']);
        }

        if (array_key_exists('certifications', $attributes)) {
            $this->syncListRows($this->certificationRows(), $attributes['certifications']);
            unset($this->relations['certificationRows']);
        }

        if (array_key_exists('expertise', $attributes)) {
            $this->syncListRows($this->expertises(), $attributes['expertise']);
            unset($this->relations['expertises']);
        }

        if (array_key_exists('working_days', $attributes)) {
            $relation = $this->workingDays();
            $relation->delete();

            foreach (array_values((array) $attributes['working_days']) as $index => $day) {
                $day = strtolower(trim((string) $day));

                if ($day === '') {
                    continue;
                }

                $relation->create([
                    'day' => $day,
                    'sort_order' => $index,
                ]);
            }

            unset($this->relations['workingDays']);
        }

        if (array_key_exists('working_hours', $attributes)) {
            $relation = $this->workingHours();
            $relation->delete();

            foreach ((array) $attributes['working_hours'] as $day => $window) {
                if (! is_array($window)) {
                    continue;
                }

                $relation->create([
                    'day' => strtolower(trim((string) $day)),
                    'start_time' => $this->normalizeTimeValue($window['start'] ?? null),
                    'end_time' => $this->normalizeTimeValue($window['end'] ?? null),
                ]);
            }

            unset($this->relations['workingHours']);
        }
    }

    private function syncListRows(HasMany $relation, mixed $items): void
    {
        $relation->delete();

        foreach (array_values((array) $items) as $index => $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            $relation->create([
                'item' => $item,
                'sort_order' => $index,
            ]);
        }
    }

    private function normalizeTimeValue(mixed $time): ?string
    {
        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        return date('H:i:s', strtotime($time));
    }

    /**
     * Get the working hours for a given day name (e.g. "saturday").
     * Falls back to 09:00–17:00 when the doctor has no explicit schedule.
     *
     * @return array{start: string, end: string}|null
     */
    public function workingHoursForDay(string $day): ?array
    {
        $day = strtolower($day);

        if (! in_array($day, $this->working_days ?? [], true)) {
            return null;
        }

        $hours = $this->working_hours ?? [];

        if (isset($hours[$day]) && isset($hours[$day]['start']) && isset($hours[$day]['end'])) {
            return [
                'start' => $hours[$day]['start'],
                'end' => $hours[$day]['end'],
            ];
        }

        // Default working hours fallback
        return ['start' => '09:00', 'end' => '17:00'];
    }

    /**
     * Check whether the doctor works on a given date (YYYY-MM-DD).
     */
    public function isWorkingOn(string $date): bool
    {
        $day = strtolower(\Carbon\Carbon::parse($date)->format('l')); // e.g. "Saturday"

        return $this->workingHoursForDay($day) !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'doctor_id', 'doctor_id');
    }

    public function treatmentSessions(): HasMany
    {
        return $this->hasMany(TreatmentSession::class, 'doctor_id', 'doctor_id');
    }

    public function toothConditions(): HasMany
    {
        return $this->hasMany(ToothCondition::class, 'doctor_id', 'doctor_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(DoctorPayout::class, 'doctor_id', 'doctor_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'doctor_id', 'doctor_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Medications prescribed by this doctor to patients.
     */
    public function prescribedMedications(): HasMany
    {
        return $this->hasMany(PatientMedication::class, 'prescribed_by', 'doctor_id');
    }

    /**
     * Notes written by this doctor on patients' medical records.
     */
    public function doctorNotes(): HasMany
    {
        return $this->hasMany(DoctorNote::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Eager-load everything backing the normalized array attributes.
     */
    public function scopeWithProfileArrays(Builder $query): Builder
    {
        return $query->with(['educations', 'certificationRows', 'expertises', 'workingDays', 'workingHours']);
    }
}
