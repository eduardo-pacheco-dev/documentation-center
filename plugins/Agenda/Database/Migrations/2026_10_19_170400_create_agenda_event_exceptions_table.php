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
        Schema::create('agenda_event_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_event_id')->constrained()->cascadeOnDelete();
            $table->dateTime('recurrence_id');
            $table->boolean('is_cancelled')->default(false);
            $table->json('overrides')->nullable();
            $table->timestamps();

            $table->unique(['agenda_event_id', 'recurrence_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_event_exceptions');
    }
};
