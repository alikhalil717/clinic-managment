<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Flutter appointment cards (patient "appointments" page + doctor
        // "next appointment" box) read a `room` value.
        Schema::table('appointment', function (Blueprint $table) {
            $table->string('room')->nullable()->after('notes');
        });

        // Flutter treatment-plan screens read the case description.
        Schema::table('cases', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });

        // Flutter "Medical History" page reads/writes these free-text fields
        // via GET/PUT patient/medical-history.
        Schema::table('medical_record', function (Blueprint $table) {
            $table->text('allergies')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->text('medications')->nullable();
            $table->string('doctor_email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->dropColumn('room');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('medical_record', function (Blueprint $table) {
            $table->dropColumn(['allergies', 'chronic_diseases', 'medications', 'doctor_email']);
        });
    }
};
