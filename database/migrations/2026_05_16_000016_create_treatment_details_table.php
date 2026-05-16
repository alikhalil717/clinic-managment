<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('tooth_id');
            $table->string('previous_condition');
            $table->string('new_condition');
            $table->float('cost');

            $table->foreign('session_id')->references('session_id')->on('treatment_session')->cascadeOnDelete();
            $table->foreign('tooth_id')->references('tooth_id')->on('tooth')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_details');
    }
};