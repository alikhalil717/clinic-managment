<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication', function (Blueprint $table) {
            $table->id('medication_id');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('dosage_form')->nullable();
            $table->text('description')->nullable();
            $table->text('side_effects')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication');
    }
};
