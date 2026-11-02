<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Plugins\Analytics\Support\AnalyticsDatabase;

return new class extends Migration
{
    public function up(): void
    {
        AnalyticsDatabase::schema()->create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('captured_on');
            $table->json('metrics');
            $table->timestamps();

            $table->unique(['user_id', 'captured_on']);
            $table->index('captured_on');
        });
    }

    public function down(): void
    {
        AnalyticsDatabase::schema()->dropIfExists('analytics_snapshots');
    }
};
