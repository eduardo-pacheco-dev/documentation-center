<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Plugins\Analytics\Support\AnalyticsDatabase;

return new class extends Migration
{
    public function up(): void
    {
        AnalyticsDatabase::schema()->create('analytics_nokia_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('oc', 60);
            $table->string('site_id', 100)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('regional', 100)->nullable();
            $table->string('project', 150)->nullable();
            $table->string('gap_status', 150)->nullable();
            $table->string('macro_status', 150)->nullable();
            $table->string('status_geral', 150)->nullable();
            $table->json('data');
            $table->timestamps();

            $table->index(['user_id', 'oc']);
            $table->index(['user_id', 'status_geral']);
            $table->index(['user_id', 'regional']);
        });
    }

    public function down(): void
    {
        AnalyticsDatabase::schema()->dropIfExists('analytics_nokia_records');
    }
};
