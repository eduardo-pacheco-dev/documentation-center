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
            $table->text('summary_cc_recipients')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_up_settings', function (Blueprint $table) {
            $table->dropColumn('summary_cc_recipients');
        });
    }
};
