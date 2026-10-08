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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->string('name');
            $table->string('wbs')->default('1');
            $table->unsignedTinyInteger('outline_level')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('task_type')->default('fixed_duration');
            $table->string('scheduling_mode')->default('auto');
            $table->boolean('is_milestone')->default(false);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('finish_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('work_minutes')->nullable();
            $table->string('constraint_type')->nullable();
            $table->timestamp('constraint_date')->nullable();
            $table->unsignedSmallInteger('priority')->default(500);
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_finish_at')->nullable();
            $table->unsignedInteger('actual_duration_minutes')->nullable();
            $table->decimal('actual_cost', 14, 2)->default(0);
            $table->decimal('budget_cost', 14, 2)->default(0);
            $table->boolean('critical')->default(false);
            $table->integer('total_slack_minutes')->nullable();
            $table->integer('free_slack_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'parent_id']);
            $table->index(['project_id', 'sort_order']);
            $table->index(['project_id', 'wbs']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
