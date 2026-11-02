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
            $table->string('assigned_to')->nullable()->after('code');
            $table->string('nokia_smp_id', 60)->nullable()->after('technology');
            $table->date('start_date')->nullable()->after('nokia_smp_id');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('service_order_id', 60)->nullable()->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erbs', function (Blueprint $table) {
            $table->dropColumn(['assigned_to', 'nokia_smp_id', 'start_date', 'end_date', 'service_order_id']);
        });
    }
};
