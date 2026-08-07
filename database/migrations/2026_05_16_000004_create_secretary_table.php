<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretary', function (Blueprint $table) {
            $table->unsignedBigInteger('secretary_id')->primary();
            $table->enum('shift', ['morning', 'evening', 'night'])->default('morning');
            $table->string('office_number');

            $table->foreign('secretary_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretary');
    }
};
