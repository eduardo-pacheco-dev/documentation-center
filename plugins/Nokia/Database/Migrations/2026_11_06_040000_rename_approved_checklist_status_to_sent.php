<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['fam_entrega', 'fam_instalacao', 'fam_panoramica'] as $item) {
            DB::table('nokia_sites')
                ->where('checklist_'.$item.'_status', 'approved')
                ->update(['checklist_'.$item.'_status' => 'sent']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['fam_entrega', 'fam_instalacao', 'fam_panoramica'] as $item) {
            DB::table('nokia_sites')
                ->where('checklist_'.$item.'_status', 'sent')
                ->update(['checklist_'.$item.'_status' => 'approved']);
        }
    }
};
