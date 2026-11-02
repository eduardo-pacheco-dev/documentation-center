<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use ZipArchive;

class UploadPluginRequest extends FormRequest
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
                'max:20480',
                Rule::file()->types(['zip']),
                $this->archiveContainsManifest(),
            ],
        ];
    }

    /**
     * Ensure the uploaded archive holds a plugin manifest.
     */
    private function archiveContainsManifest(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! $value instanceof UploadedFile || ! $value->isValid()) {
                return;
            }

            $zip = new ZipArchive;

            if ($zip->open($value->getRealPath()) !== true) {
                $fail('O arquivo enviado não é um ZIP válido.');

                return;
            }

            $hasManifest = $zip->locateName('plugin.json') !== false;

            for ($index = 0; ! $hasManifest && $index < $zip->numFiles; $index++) {
                $hasManifest = str_ends_with((string) $zip->getNameIndex($index), '/plugin.json');
            }

            $zip->close();

            if (! $hasManifest) {
                $fail('O arquivo enviado não contém um plugin.json.');
            }
        };
    }
}
