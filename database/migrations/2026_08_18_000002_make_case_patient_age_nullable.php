<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase H: "Case is a treatment plan" — a plan's 1:1 case row is created
        // when the plan is created (before photo only). The rest of the case info
        // (patient_age, description, after_photo) is filled in when the doctor
        // finishes the plan, so patient_age must be nullable.
        Schema::table('cases', function (Blueprint $table) {
            $table->integer('patient_age')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->integer('patient_age')->nullable(false)->change();
        });
    }
};
