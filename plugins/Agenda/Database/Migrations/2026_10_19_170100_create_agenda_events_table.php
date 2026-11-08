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
        Schema::create('agenda_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_calendar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_all_day')->default(false);
            $table->string('timezone', 64)->default('UTC');
            $table->string('status', 20)->default('confirmed');
            $table->string('visibility', 20)->default('default');
            $table->string('transparency', 20)->default('opaque');
            $table->string('color', 30)->nullable();
            $table->json('recurrence')->nullable();
            $table->date('recurrence_until')->nullable();
            $table->boolean('attendees_can_invite_others')->default(true);
            $table->boolean('attendees_can_modify')->default(false);
            $table->boolean('attendees_can_see_other_attendees')->default(true);
            $table->json('attachments')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();

            $table->index(['agenda_calendar_id', 'start_at']);
            $table->index(['user_id', 'start_at']);
            $table->index(['start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_events');
    }
};
