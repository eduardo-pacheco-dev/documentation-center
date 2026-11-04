<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 150);
            $table->date('reference_date')->index();
            $table->string('status', 20)->default('em_dia')->index();
            $table->unsignedTinyInteger('health_score')->default(3);
            $table->text('summary');
            $table->text('next_steps')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'reference_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_check_ins');
    }
};
