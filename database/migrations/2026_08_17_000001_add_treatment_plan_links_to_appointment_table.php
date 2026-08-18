<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->unsignedBigInteger('treatment_plan_id')->nullable()->after('doctor_id');
            $table->unsignedBigInteger('treatment_stage_id')->nullable()->after('treatment_plan_id');

            $table->foreign('treatment_plan_id')->references('plan_id')->on('treatment_plan')->nullOnDelete();
            $table->foreign('treatment_stage_id')->references('stage_id')->on('treatment_stage')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->dropForeign(['treatment_plan_id']);
            $table->dropForeign(['treatment_stage_id']);
            $table->dropColumn(['treatment_plan_id', 'treatment_stage_id']);
        });
    }
};
