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
            $table->string('checklist_fam_entrega_status', 20)->default('pending')->after('checklist_fam_entrega');
            $table->string('checklist_fam_instalacao_status', 20)->default('pending')->after('checklist_fam_instalacao');
            $table->string('checklist_fam_panoramica_status', 20)->default('pending')->after('checklist_fam_panoramica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropColumn([
                'checklist_fam_entrega_status',
                'checklist_fam_instalacao_status',
                'checklist_fam_panoramica_status',
            ]);
        });
    }
};
