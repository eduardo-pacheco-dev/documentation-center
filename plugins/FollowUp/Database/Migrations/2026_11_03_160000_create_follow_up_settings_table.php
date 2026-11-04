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
        Schema::create('follow_up_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('per_page')->default(15);
            $table->unsignedSmallInteger('due_soon_days')->default(7);
            $table->unsignedSmallInteger('widget_limit')->default(5);
            $table->boolean('email_summary_enabled')->default(true);
            $table->string('email_subject_prefix', 150)->default('Follow-up');
            $table->string('summary_times', 100)->default('08:00,17:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_settings');
    }
};
