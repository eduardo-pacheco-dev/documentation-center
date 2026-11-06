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
            $table->boolean('rejection_requires_field_return')
                ->default(false)
                ->after('rejection_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropColumn('rejection_requires_field_return');
        });
    }
};
