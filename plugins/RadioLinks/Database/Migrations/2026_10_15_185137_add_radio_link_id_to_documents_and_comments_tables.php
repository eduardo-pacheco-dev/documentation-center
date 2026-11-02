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
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('radio_link_id')->nullable()->after('erb_id')->constrained()->nullOnDelete();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('radio_link_id')->nullable()->after('erb_id')->constrained()->nullOnDelete();
            $table->foreignId('erb_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('radio_link_id');
            $table->foreignId('erb_id')->nullable(false)->change();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('radio_link_id');
        });
    }
};
