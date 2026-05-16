<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_session', function (Blueprint $table) {
            $table->id('session_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id');
            $table->date('session_date');
            $table->text('notes');

            $table->foreign('appointment_id')->references('appointment_id')->on('appointment')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_session');
    }
};