<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('assigned_to')->nullable();
            $table->string('name');
            $table->string('site_id', 255)->nullable();
            $table->string('end_id', 60)->nullable();
            $table->string('operator', 80)->nullable();
            $table->string('technology', 40)->nullable();
            $table->string('nokia_smp_id', 60)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('service_order_id', 60)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('street')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 9)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'code']);
            $table->index(['user_id', 'site_id']);
            $table->index(['user_id', 'end_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
