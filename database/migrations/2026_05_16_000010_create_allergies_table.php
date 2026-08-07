<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allergies', function (Blueprint $table) {
            $table->id('allergy_id');
            $table->unsignedBigInteger('record_id');
            $table->string('allergy_name');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->text('notes');

            $table->foreign('record_id')->references('record_id')->on('medical_record')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allergies');
    }
};
