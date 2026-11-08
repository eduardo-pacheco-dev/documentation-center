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
        Schema::create('agenda_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('status', 20)->default('needs_action');
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_organizer')->default(false);
            $table->boolean('is_resource')->default(false);
            $table->string('comment')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['agenda_event_id', 'email']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_event_attendees');
    }
};
