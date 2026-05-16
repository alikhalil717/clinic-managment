<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->unsignedBigInteger('record_id');
            $table->string('condition_name');
            $table->text('description');
            $table->date('diagnosed_date');

            $table->foreign('record_id')->references('record_id')->on('medical_record')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_history');
    }
};