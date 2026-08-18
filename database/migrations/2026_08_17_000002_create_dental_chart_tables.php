<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each treatment plan owns exactly one dental chart.
        Schema::create('dental_chart', function (Blueprint $table) {
            $table->id('chart_id');
            $table->unsignedBigInteger('plan_id')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('created_at');

            $table->foreign('plan_id')->references('plan_id')->on('treatment_plan')->cascadeOnDelete();
            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
        });

        // The teeth belonging to a plan's dental chart.
        Schema::create('dental_chart_tooth', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('chart_id');
            $table->unsignedBigInteger('tooth_id');
            $table->enum('condition_status', ['healthy', 'decay', 'damaged', 'treated', 'missing'])->default('healthy');
            $table->enum('treatment_type', ['filling', 'extraction', 'root_canal', 'cleaning', 'whitening', 'crown'])->default('cleaning');
            $table->text('treatment_description')->nullable();
            $table->float('estimated_price')->nullable();
            $table->enum('severity_level', ['low', 'medium', 'high'])->default('low');
            $table->text('notes')->nullable();
            $table->dateTime('updated_at');

            $table->foreign('chart_id')->references('chart_id')->on('dental_chart')->cascadeOnDelete();
            $table->foreign('tooth_id')->references('tooth_id')->on('tooth')->cascadeOnDelete();

            $table->unique(['chart_id', 'tooth_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_chart_tooth');
        Schema::dropIfExists('dental_chart');
    }
};
