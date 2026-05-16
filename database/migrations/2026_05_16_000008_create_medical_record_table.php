<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record', function (Blueprint $table) {
            $table->id('record_id');
            $table->unsignedBigInteger('patient_id');
            $table->dateTime('created_at');

            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record');
    }
};