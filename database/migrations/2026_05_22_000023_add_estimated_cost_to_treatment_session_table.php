<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_session', function (Blueprint $table) {
            $table->double('estimated_cost')->nullable()->after('session_date');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_session', function (Blueprint $table) {
            $table->dropColumn('estimated_cost');
        });
    }
};
