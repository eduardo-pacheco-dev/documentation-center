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
            $table->string('interruption_reason', 255)->nullable()->after('os_fam_instalacao');
            $table->unsignedTinyInteger('interruption_return_days')->nullable()->after('interruption_reason');
            $table->timestamp('interruption_started_at')->nullable()->after('interruption_return_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropColumn([
                'interruption_reason',
                'interruption_return_days',
                'interruption_started_at',
            ]);
        });
    }
};
