<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosis', function (Blueprint $table) {
            $table->id('diagnosis_id');
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('session_id')->nullable();

            $table->string('diagnosis_name');
            $table->text('description')->nullable();
            $table->enum('severity', ['low', 'medium', 'high'])->nullable()->default(null);
            $table->dateTime('diagnosed_at')->nullable();

            $table->foreign('record_id')->references('record_id')->on('medical_record')->cascadeOnDelete();
            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
            $table->foreign('session_id')->references('session_id')->on('treatment_session')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosis');
    }
};
