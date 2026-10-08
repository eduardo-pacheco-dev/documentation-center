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
        Schema::create('baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('baseline_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baseline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('finish_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('work_minutes')->nullable();
            $table->decimal('budget_cost', 14, 2)->default(0);
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['baseline_id', 'task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baseline_tasks');
        Schema::dropIfExists('baselines');
    }
};
