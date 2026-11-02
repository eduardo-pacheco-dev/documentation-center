<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Plugins\Analytics\Support\AnalyticsDatabase;

return new class extends Migration
{
    public function up(): void
    {
        AnalyticsDatabase::schema()->create('analytics_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('oc', 60);
            $table->string('os', 60);
            $table->date('created_on')->nullable();
            $table->string('supplier')->nullable();
            $table->string('audit_supplier')->nullable();
            $table->string('project_type')->nullable();
            $table->string('process_stage')->nullable();
            $table->string('element_type')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('node_name')->nullable();
            $table->string('location')->nullable();
            $table->string('operator')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'oc', 'os']);
            $table->index(['user_id', 'created_on']);
            $table->index(['user_id', 'process_stage']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        AnalyticsDatabase::schema()->dropIfExists('analytics_records');
    }
};
