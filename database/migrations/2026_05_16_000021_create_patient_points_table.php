<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_points', function (Blueprint $table) {
            $table->id('point_id');
            $table->unsignedBigInteger('patient_id');
            $table->integer('points');
            $table->string('source');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('description');
            $table->dateTime('created_at');

            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_points');
    }
};