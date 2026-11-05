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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->string('type')->default('standard');
            $table->unsignedSmallInteger('work_start_minute')->default(480);
            $table->unsignedSmallInteger('work_end_minute')->default(1080);
            $table->unsignedSmallInteger('lunch_start_minute')->nullable();
            $table->unsignedSmallInteger('lunch_end_minute')->nullable();
            $table->unsignedSmallInteger('minutes_per_day')->default(480);
            $table->unsignedSmallInteger('minutes_per_week')->default(2400);
            $table->timestamps();

            $table->index(['project_id', 'is_default']);
        });

        Schema::create('calendar_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->json('segments');
            $table->timestamps();

            $table->unique(['calendar_id', 'day_of_week']);
        });

        Schema::create('calendar_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->json('segments');
            $table->timestamps();

            $table->unique(['calendar_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_exceptions');
        Schema::dropIfExists('calendar_days');
        Schema::dropIfExists('calendars');
    }
};
