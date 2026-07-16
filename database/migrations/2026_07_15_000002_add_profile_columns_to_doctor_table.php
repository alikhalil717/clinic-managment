<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor', function (Blueprint $table) {
            $table->text('about')->nullable()->after('reviews_count');
            $table->json('education')->nullable()->after('about');
            $table->json('certifications')->nullable()->after('education');
            $table->json('expertise')->nullable()->after('certifications');
        });
    }

    public function down(): void
    {
        Schema::table('doctor', function (Blueprint $table) {
            $table->dropColumn(['about', 'education', 'certifications', 'expertise']);
        });
    }
};
