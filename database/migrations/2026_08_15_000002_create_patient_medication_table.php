<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medication', function (Blueprint $table) {
            $table->id('patient_medication_id');
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('medication_id');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('prescribed_by')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_current')->default(true);

            $table->foreign('record_id')->references('record_id')->on('medical_record')->cascadeOnDelete();
            $table->foreign('medication_id')->references('medication_id')->on('medication')->cascadeOnDelete();
            $table->foreign('prescribed_by')->references('doctor_id')->on('doctor')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medication');
    }
};
