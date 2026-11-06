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
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->string('rejection_reason', 255)->nullable()->after('interruption_started_at');
            $table->unsignedTinyInteger('rejection_return_days')->nullable()->after('rejection_reason');
            $table->timestamp('rejection_started_at')->nullable()->after('rejection_return_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'rejection_return_days',
                'rejection_started_at',
            ]);
        });
    }
};
