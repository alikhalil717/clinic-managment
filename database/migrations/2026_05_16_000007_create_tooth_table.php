<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tooth', function (Blueprint $table) {
            $table->id('tooth_id');
            $table->string('tooth_code');
            $table->string('tooth_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth');
    }
};