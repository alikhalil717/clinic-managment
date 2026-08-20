<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_token', function (Blueprint $table) {
            $table->id('device_token_id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_token')->unique();
            $table->enum('platform', ['android', 'ios'])->nullable();
            $table->dateTime('created_at');

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_token');
    }
};