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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('calendar_id')->nullable()->constrained('calendars')->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('work');
            $table->string('code')->nullable();
            $table->decimal('max_units', 6, 2)->default(100);
            $table->decimal('cost_per_hour', 14, 2)->default(0);
            $table->decimal('cost_per_unit', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });

        Schema::create('resource_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->decimal('units', 6, 2)->default(100);
            $table->unsignedInteger('work_minutes')->nullable();
            $table->decimal('cost', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['task_id', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_assignments');
        Schema::dropIfExists('resources');
    }
};
