<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_payout', function (Blueprint $table) {
            $table->id('payout_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('session_id');
            $table->float('amount');
            $table->dateTime('payout_date');
            $table->string('status');
            $table->text('notes');

            $table->foreign('doctor_id')->references('doctor_id')->on('doctor')->cascadeOnDelete();
            $table->foreign('session_id')->references('session_id')->on('treatment_session')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_payout');
    }
};