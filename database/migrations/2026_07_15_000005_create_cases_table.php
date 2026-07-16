<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old cases table first (if exists from previous migration)
        Schema::dropIfExists('cases');

        // Recreate with 1-to-1 relationship with treatment_plan
        Schema::create('cases', function (Blueprint $table) {
            $table->id('case_id');
            $table->unsignedBigInteger('treatment_plan_id')->unique();
            $table->string('title');
            $table->integer('patient_age');
            $table->string('before_photo')->nullable();
            $table->string('after_photo')->nullable();
            $table->dateTime('created_at');

            $table->foreign('treatment_plan_id')
                ->references('plan_id')
                ->on('treatment_plan')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
