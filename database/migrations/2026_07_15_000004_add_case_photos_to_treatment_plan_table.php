<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plan', function (Blueprint $table) {
            $table->string('title')->nullable()->after('doctor_id');
            $table->text('description')->nullable()->after('title');
            $table->string('before_photo')->nullable()->after('progress_percentage');
            $table->string('after_photo')->nullable()->after('before_photo');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_plan', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'before_photo', 'after_photo']);
        });
    }
};
