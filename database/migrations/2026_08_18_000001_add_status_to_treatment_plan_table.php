<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase F: give treatment plans a status so the doctor can track
        // In Progress / Finished / Cancelled, plus derived stage counters.
        Schema::table('treatment_plan', function (Blueprint $table) {
            $table->enum('status', ['in_progress', 'finished', 'cancelled'])
                ->default('in_progress')
                ->after('progress_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_plan', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
