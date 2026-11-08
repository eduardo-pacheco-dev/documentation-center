<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The task agenda link was removed so the plugin no longer depends on an
     * agenda plugin. Databases created before that change are cleaned up here.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('todo_tasks', 'agenda_event_id')) {
            return;
        }

        Schema::table('todo_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agenda_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The column is intentionally not restored: the plugin is standalone.
    }
};
