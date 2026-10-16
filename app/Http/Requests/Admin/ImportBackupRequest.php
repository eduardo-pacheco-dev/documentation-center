<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use ZipArchive;

class ImportBackupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:512000',
                Rule::file()->types(['zip']),
                $this->archiveContainsDatabaseDump(),
            ],
        ];
    }

    /**
     * Ensure the uploaded archive holds a restorable database dump.
     */
    private function archiveContainsDatabaseDump(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! $value instanceof UploadedFile || ! $value->isValid()) {
                return;
            }

            $zip = new ZipArchive;

            if ($zip->open($value->getRealPath()) !== true) {
                $fail('O arquivo enviado não é um backup válido.');

                return;
            }

            $hasDump = $zip->locateName('database/database.sql') !== false;

            $zip->close();

            if (! $hasDump) {
                $fail('O arquivo enviado não contém o dump do banco de dados (database/database.sql).');
            }
        };
    }
}
