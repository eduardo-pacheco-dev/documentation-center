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
        Schema::create('nokia_site_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nokia_site_id')->constrained('nokia_sites')->cascadeOnDelete();
            $table->string('category', 20);
            $table->string('original_name');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime_type', 150)->nullable();
            $table->timestamps();

            $table->index(['nokia_site_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nokia_site_attachments');
    }
};
