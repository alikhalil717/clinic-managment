<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id('case_id');
            $table->unsignedBigInteger('treatment_plan_id');
            $table->string('before_photo')->nullable();
            $table->string('after_photo')->nullable();
            $table->string('status')->default('in-progress'); // 'in-progress' or 'done'
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
