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
        Schema::table('erbs', function (Blueprint $table) {
            $table->string('site_id', 255)->nullable()->after('name');
            $table->string('end_id', 60)->nullable()->after('site_id');

            $table->index(['user_id', 'site_id']);
            $table->index(['user_id', 'end_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erbs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'site_id']);
            $table->dropIndex(['user_id', 'end_id']);

            $table->dropColumn(['site_id', 'end_id']);
        });
    }
};
