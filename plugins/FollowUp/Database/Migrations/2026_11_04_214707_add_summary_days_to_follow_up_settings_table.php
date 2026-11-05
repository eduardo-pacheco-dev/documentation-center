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
        Schema::table('follow_up_settings', function (Blueprint $table) {
            $table->string('summary_days', 20)->default('0,1,2,3,4,5,6')->after('summary_times');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_up_settings', function (Blueprint $table) {
            $table->dropColumn('summary_days');
        });
    }
};
