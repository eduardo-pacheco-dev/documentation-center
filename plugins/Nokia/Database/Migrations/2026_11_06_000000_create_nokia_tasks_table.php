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
        Schema::create('nokia_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('nokia_site_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nokia_site_id')->constrained('nokia_sites')->cascadeOnDelete();
            $table->foreignId('nokia_task_id')->constrained('nokia_tasks')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['nokia_site_id', 'nokia_task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nokia_site_task');
        Schema::dropIfExists('nokia_tasks');
    }
};
