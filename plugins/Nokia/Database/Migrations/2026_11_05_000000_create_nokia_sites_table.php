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
        Schema::create('nokia_sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_id', 100)->index();
            $table->string('end_id', 100)->nullable()->index();
            $table->string('oc', 100)->nullable();
            $table->string('os_fam_entrega', 100)->nullable();
            $table->string('os_fam_instalacao', 100)->nullable();
            $table->boolean('checklist_tarefas_enviadas')->default(false);
            $table->boolean('checklist_fam_entrega')->default(false);
            $table->boolean('checklist_fam_instalacao')->default(false);
            $table->boolean('checklist_fam_panoramica')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nokia_sites');
    }
};
