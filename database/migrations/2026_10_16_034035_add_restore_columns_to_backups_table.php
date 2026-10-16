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
        Schema::table('backups', function (Blueprint $table) {
            $table->string('restore_status')->nullable()->after('status');
            $table->text('restore_error')->nullable()->after('restore_status');
            $table->timestamp('restore_started_at')->nullable()->after('restore_error');
            $table->timestamp('restored_at')->nullable()->after('restore_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn(['restore_status', 'restore_error', 'restore_started_at', 'restored_at']);
        });
    }
};
