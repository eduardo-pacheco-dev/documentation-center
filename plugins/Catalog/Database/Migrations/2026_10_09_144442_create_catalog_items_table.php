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
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('code', 60)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('stock_quantity', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
