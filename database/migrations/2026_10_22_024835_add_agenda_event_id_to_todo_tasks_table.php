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
        Schema::table('todo_tasks', function (Blueprint $table) {
            $table->foreignId('agenda_event_id')
                ->nullable()
                ->after('todo_list_id')
                ->constrained('agenda_events')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todo_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agenda_event_id');
        });
    }
};
