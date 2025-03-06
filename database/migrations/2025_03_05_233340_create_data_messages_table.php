<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_messages', function (Blueprint $table) {
            $table->id();
            $table->string('callback_query')->nullable();
            $table->string('callback_data')->nullable();
            $table->string('text')->nullable();
            $table->string('type')->nullable();
            $table->string('reply_markup')->nullable();
            $table->string('texts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_messages');
    }
};
