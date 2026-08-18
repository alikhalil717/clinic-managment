<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Flutter patient chart supports a "Missing" status; add it to
        // the tooth_condition enum so missing teeth can be charted.
        Schema::table('tooth_condition', function (Blueprint $table) {
            $table->enum('condition_status', ['healthy', 'decay', 'damaged', 'treated', 'missing'])
                ->default('healthy')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tooth_condition', function (Blueprint $table) {
            $table->enum('condition_status', ['healthy', 'decay', 'damaged', 'treated'])
                ->default('healthy')
                ->change();
        });
    }
};
