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
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->id();
            $table->text('storage_provider');

            $table->text('aws_key')->nullable();
            $table->text('aws_secret')->nullable();
            $table->text('aws_region')->nullable();
            $table->text('aws_bucket')->nullable();
            $table->text('aws_endpoint')->nullable();
            $table->text('aws_url')->nullable();
            $table->boolean('aws_use_path_style')->default(false);

            $table->text('azure_account_name')->nullable();
            $table->text('azure_account_key')->nullable();
            $table->text('azure_container')->nullable();
            $table->text('azure_endpoint_suffix')->nullable();
            $table->text('azure_url')->nullable();

            $table->text('cloudflare_account_id')->nullable();
            $table->text('cloudflare_access_key_id')->nullable();
            $table->text('cloudflare_secret_access_key')->nullable();
            $table->text('cloudflare_bucket')->nullable();
            $table->text('cloudflare_public_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_settings');
    }
};
