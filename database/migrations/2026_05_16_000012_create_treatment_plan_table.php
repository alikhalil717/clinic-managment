<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plan', function (Blueprint $table) {
            $table->id('plan_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->float('estimated_total_cost');
            $table->float('actual_total_cost');
            $table->integer('progress_percentage');
            $table->dateTime('created_at');

            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan');
    }
};