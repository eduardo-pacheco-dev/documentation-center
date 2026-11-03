<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The encrypted values do not fit in the original short string columns.
     *
     * @var list<string>
     */
    private array $columns = [
        'storage_provider',
        'aws_key',
        'aws_region',
        'aws_bucket',
        'aws_endpoint',
        'aws_url',
        'azure_account_name',
        'azure_container',
        'azure_endpoint_suffix',
        'azure_url',
        'cloudflare_account_id',
        'cloudflare_access_key_id',
        'cloudflare_bucket',
        'cloudflare_public_url',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('station_storage_settings', function (Blueprint $table): void {
            $table->text('storage_provider')->change();

            foreach (array_slice($this->columns, 1) as $column) {
                $table->text($column)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('station_storage_settings', function (Blueprint $table): void {
            $table->string('storage_provider', 20)->change();

            foreach (array_slice($this->columns, 1) as $column) {
                $table->string($column)->nullable()->change();
            }
        });
    }
};
