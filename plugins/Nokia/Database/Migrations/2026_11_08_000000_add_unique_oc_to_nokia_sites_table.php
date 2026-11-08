<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('nokia_sites')
            ->where('oc', '')
            ->update(['oc' => null]);

        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->unique('oc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropUnique(['oc']);
        });
    }
};
