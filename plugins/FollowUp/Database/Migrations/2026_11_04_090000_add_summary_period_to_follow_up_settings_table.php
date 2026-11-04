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
            $table->string('summary_period', 20)->default('all')->after('email_subject_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_up_settings', function (Blueprint $table) {
            $table->dropColumn('summary_period');
        });
    }
};
