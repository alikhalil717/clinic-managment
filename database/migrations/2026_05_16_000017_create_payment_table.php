<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('patient_id');
            $table->float('amount');
            $table->enum('method', ['cash', 'card', 'transfer'])->default('cash');
            $table->dateTime('date');
            $table->unsignedBigInteger('related_session_id')->nullable();
            $table->enum('type', ['session_payment', 'plan_payment', 'deposit'])->default('session_payment');
            $table->boolean('is_income');

            $table->foreign('patient_id')->references('patient_id')->on('patient')->cascadeOnDelete();
            $table->foreign('related_session_id')->references('session_id')->on('treatment_session')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
