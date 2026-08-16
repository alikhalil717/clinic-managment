<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_note', function (Blueprint $table) {
            $table->id('note_id');
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('doctor_id');
            $table->string('title')->nullable();
            $table->text('note');
            $table->enum('note_type', ['general', 'prescription', 'follow_up', 'referral'])->default('general');
            $table->dateTime('created_at');

            $table->foreign('record_id')->references('record_id')->on('medical_record')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_note');
    }
};
