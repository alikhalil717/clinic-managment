<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Directly link payments to a treatment plan (for plan_payment type).
        Schema::table('payment', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('related_session_id');
            $table->foreign('plan_id')->references('plan_id')->on('treatment_plan')->nullOnDelete();
        });

        // Directly link a clinical session to the plan that owns it.
        Schema::table('treatment_session', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('appointment_id');
            $table->foreign('plan_id')->references('plan_id')->on('treatment_plan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });

        Schema::table('treatment_session', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};
