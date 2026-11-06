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
            $table->date('mos_plan_at')->nullable()->after('os_fam_instalacao');
            $table->date('mos_real_at')->nullable()->after('mos_plan_at');
            $table->date('instalacao_plan_at')->nullable()->after('mos_real_at');
            $table->date('instalacao_real_at')->nullable()->after('instalacao_plan_at');
            $table->date('integracao_plan_at')->nullable()->after('instalacao_real_at');
            $table->date('integracao_real_at')->nullable()->after('integracao_plan_at');
            $table->date('rfa_plan_at')->nullable()->after('integracao_real_at');
            $table->date('rfa_real_at')->nullable()->after('rfa_plan_at');
            $table->date('fam_entrega_approved_at')->nullable()->after('rfa_real_at');
            $table->date('fam_instalacao_approved_at')->nullable()->after('fam_entrega_approved_at');
            $table->date('fam_panoramica_approved_at')->nullable()->after('fam_instalacao_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropColumn([
                'mos_plan_at',
                'mos_real_at',
                'instalacao_plan_at',
                'instalacao_real_at',
                'integracao_plan_at',
                'integracao_real_at',
                'rfa_plan_at',
                'rfa_real_at',
                'fam_entrega_approved_at',
                'fam_instalacao_approved_at',
                'fam_panoramica_approved_at',
            ]);
        });
    }
};
