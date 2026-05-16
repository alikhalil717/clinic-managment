<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->primary();
            $table->string('specialization');
            $table->string('license_number');
            $table->integer('years_of_experience');
            $table->float('rating');
            $table->integer('reviews_count');

            $table->foreign('doctor_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor');
    }
};