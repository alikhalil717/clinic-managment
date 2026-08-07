<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_stage', function (Blueprint $table) {
            $table->id('stage_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('stage_name');
            $table->text('description');
            $table->float('estimated_cost');
            $table->float('actual_cost');
            $table->enum('status', ['upcoming', 'in_progress', 'completed', 'canceled'])->default('upcoming');
            $table->date('start_date');
            $table->date('end_date');

            $table->foreign('plan_id')->references('plan_id')->on('treatment_plan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_stage');
    }
};
