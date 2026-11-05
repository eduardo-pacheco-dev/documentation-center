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
        Schema::create('form_module_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('types')->nullable();
            $table->json('statuses')->nullable();
            $table->foreignId('default_template_id')->nullable()->constrained('form_templates')->nullOnDelete();
            $table->string('default_view', 20)->default('table');
            $table->unsignedSmallInteger('per_page')->default(12);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_module_settings');
    }
};
