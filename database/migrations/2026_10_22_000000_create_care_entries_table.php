<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date')->index();
            $table->time('entry_time');
            $table->string('activity');
            $table->string('status');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('systolic')->nullable();
            $table->unsignedSmallInteger('diastolic')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedTinyInteger('spo2')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->timestamps();

            $table->index(['entry_date', 'entry_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_entries');
    }
};
