<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The columns that must be encrypted at rest.
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
        $this->transformValues(fn (string $value): string => Crypt::encryptString($value), encrypt: true);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->transformValues(fn (string $value): string => Crypt::decryptString($value), encrypt: false);
    }

    /**
     * Encrypt the existing plaintext values or decrypt them on rollback.
     */
    private function transformValues(callable $transform, bool $encrypt): void
    {
        DB::table('station_storage_settings')->orderBy('id')->get()->each(function (object $row) use ($transform, $encrypt): void {
            $updates = [];

            foreach ($this->columns as $column) {
                $value = $row->{$column} ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                if (! $encrypt) {
                    try {
                        $updates[$column] = $transform((string) $value);
                    } catch (Throwable) {
                        // Keep values that are not valid encrypted payloads untouched.
                    }

                    continue;
                }

                try {
                    Crypt::decryptString((string) $value);

                    continue;
                } catch (Throwable) {
                    // The value is plaintext and must be encrypted.
                }

                $updates[$column] = $transform((string) $value);
            }

            if ($updates !== []) {
                DB::table('station_storage_settings')->where('id', $row->id)->update($updates);
            }
        });
    }
};
