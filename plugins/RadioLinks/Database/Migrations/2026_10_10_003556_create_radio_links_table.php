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
        Schema::create('radio_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('erb_a_id')->constrained('erbs')->cascadeOnDelete();
            $table->foreignId('erb_b_id')->constrained('erbs')->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('equipment_a')->nullable();
            $table->string('equipment_b')->nullable();
            $table->decimal('frequency', 8, 3)->nullable();
            $table->decimal('bandwidth', 6, 2)->nullable();
            $table->decimal('capacity', 8, 2)->nullable();
            $table->string('polarization', 20)->nullable();
            $table->string('status', 20)->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'code']);
            $table->index(['user_id', 'erb_a_id']);
            $table->index(['user_id', 'erb_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_links');
    }
};
