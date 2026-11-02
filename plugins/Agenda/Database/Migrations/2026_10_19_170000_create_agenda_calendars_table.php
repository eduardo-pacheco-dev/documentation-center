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
        Schema::create('agenda_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 30)->default('blueberry');
            $table->string('timezone', 64)->default('UTC');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->json('default_reminders')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'position']);
            $table->index(['user_id', 'is_default']);
        });

        Schema::create('agenda_calendar_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_calendar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->default('reader');
            $table->timestamps();

            $table->unique(['agenda_calendar_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_calendar_shares');
        Schema::dropIfExists('agenda_calendars');
    }
};
