<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->enum('appointment_type', ['diagnostic', 'normal'])->default('normal')->after('notes');
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->dropColumn('appointment_type');
            $table->unsignedBigInteger('doctor_id')->nullable(false)->change();
        });
    }
};
