<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Plugins\Analytics\Support\AnalyticsDatabase;

return new class extends Migration
{
    public function up(): void
    {
        AnalyticsDatabase::schema()->create('analytics_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 20);
            $table->string('status', 20)->default('pending')->index();
            $table->string('original_name')->nullable();
            $table->string('path');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('unchanged_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        AnalyticsDatabase::schema()->dropIfExists('analytics_imports');
    }
};
