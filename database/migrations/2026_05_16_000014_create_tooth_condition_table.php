<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tooth_condition', function (Blueprint $table) {
            $table->id('condition_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('tooth_id');
            $table->unsignedBigInteger('doctor_id');
            $table->enum('condition_status', ['healthy', 'decay', 'damaged', 'treated'])->default('healthy');
            $table->enum('treatment_type', ['filling', 'extraction', 'root_canal', 'cleaning', 'whitening', 'crown'])->default('cleaning');
            $table->text('treatment_description');
            $table->float('estimated_price');
            $table->enum('severity_level', ['low', 'medium', 'high'])->default('low');
            $table->text('notes');
            $table->unsignedBigInteger('session_id');
            $table->dateTime('updated_at');

            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
            $table->foreign('tooth_id')->references('tooth_id')->on('tooth')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
            $table->foreign('session_id')->references('session_id')->on('treatment_session')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth_condition');
    }
};
