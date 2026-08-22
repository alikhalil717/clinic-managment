<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * List-valued doctor profile fields moved from JSON columns into tables.
     * Maps the attribute name to its normalized table name.
     */
    private const LIST_FIELDS = [
        'education' => 'doctor_education',
        'certifications' => 'doctor_certification',
        'expertise' => 'doctor_expertise',
    ];

    private const DAY_ORDER = [
        'saturday' => 0,
        'sunday' => 1,
        'monday' => 2,
        'tuesday' => 3,
        'wednesday' => 4,
        'thursday' => 5,
        'friday' => 6,
    ];

    public function up(): void
    {
        foreach (self::LIST_FIELDS as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('doctor_id');
                $table->string('item');
                $table->unsignedSmallInteger('sort_order')->default(0);

                $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
                $table->index(['doctor_id', 'sort_order']);
            });
        }

        Schema::create('doctor_working_day', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->string('day', 10);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
            $table->unique(['doctor_id', 'day']);
        });

        Schema::create('doctor_working_hour', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->string('day', 10);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
            $table->unique(['doctor_id', 'day']);
        });

        // Migrate existing JSON data into the new tables.
        $doctors = DB::table('doctor')
            ->select(array_merge(['doctor_id'], array_keys(self::LIST_FIELDS), ['working_days', 'working_hours']))
            ->get();

        foreach ($doctors as $doctor) {
            foreach (self::LIST_FIELDS as $field => $tableName) {
                $items = json_decode((string) $doctor->{$field}, true) ?? [];

                foreach (array_values((array) $items) as $index => $item) {
                    if (! is_string($item) || $item === '') {
                        continue;
                    }

                    DB::table($tableName)->insert([
                        'doctor_id' => $doctor->doctor_id,
                        'item' => $item,
                        'sort_order' => $index,
                    ]);
                }
            }

            $days = json_decode((string) $doctor->working_days, true) ?? [];

            foreach (array_values((array) $days) as $index => $day) {
                $day = strtolower(trim((string) $day));

                if ($day === '') {
                    continue;
                }

                DB::table('doctor_working_day')->insert([
                    'doctor_id' => $doctor->doctor_id,
                    'day' => $day,
                    'sort_order' => self::DAY_ORDER[$day] ?? $index,
                ]);
            }

            $hours = json_decode((string) $doctor->working_hours, true) ?? [];

            foreach ((array) $hours as $day => $window) {
                if (! is_array($window)) {
                    continue;
                }

                $day = strtolower(trim((string) $day));

                DB::table('doctor_working_hour')->insert([
                    'doctor_id' => $doctor->doctor_id,
                    'day' => $day,
                    'start_time' => $this->normalizeTime($window['start'] ?? null),
                    'end_time' => $this->normalizeTime($window['end'] ?? null),
                ]);
            }
        }

        Schema::table('doctor', function (Blueprint $table) {
            $table->dropColumn(array_merge(array_keys(self::LIST_FIELDS), ['working_days', 'working_hours']));
        });
    }

    public function down(): void
    {
        Schema::table('doctor', function (Blueprint $table) {
            $table->json('education')->nullable()->after('about');
            $table->json('certifications')->nullable()->after('education');
            $table->json('expertise')->nullable()->after('certifications');
            $table->json('working_days')->nullable()->after('expertise');
            $table->json('working_hours')->nullable()->after('working_days');
        });

        $doctors = DB::table('doctor')->select('doctor_id')->get();

        foreach ($doctors as $doctor) {
            foreach (self::LIST_FIELDS as $field => $tableName) {
                $items = DB::table($tableName)
                    ->where('doctor_id', $doctor->doctor_id)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->pluck('item')
                    ->all();

                DB::table('doctor')->where('doctor_id', $doctor->doctor_id)->update([
                    $field => json_encode(array_values($items)),
                ]);
            }

            $days = DB::table('doctor_working_day')
                ->where('doctor_id', $doctor->doctor_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('day')
                ->all();

            DB::table('doctor')->where('doctor_id', $doctor->doctor_id)->update([
                'working_days' => json_encode(array_values($days)),
            ]);

            $hours = [];

            foreach (DB::table('doctor_working_hour')->where('doctor_id', $doctor->doctor_id)->get() as $row) {
                $hours[$row->day] = array_filter([
                    'start' => $row->start_time !== null ? substr($row->start_time, 0, 5) : null,
                    'end' => $row->end_time !== null ? substr($row->end_time, 0, 5) : null,
                ], fn ($value) => $value !== null);
            }

            DB::table('doctor')->where('doctor_id', $doctor->doctor_id)->update([
                'working_hours' => json_encode($hours),
            ]);
        }

        Schema::dropIfExists('doctor_working_hour');
        Schema::dropIfExists('doctor_working_day');

        foreach (self::LIST_FIELDS as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }

    /**
     * Convert "HH:MM" or "HH:MM:SS" strings into a TIME-compatible value.
     */
    private function normalizeTime(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        return date('H:i:s', strtotime($time));
    }
};
