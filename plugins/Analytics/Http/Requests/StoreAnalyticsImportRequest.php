<?php

declare(strict_types=1);

namespace Plugins\Analytics\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnalyticsImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:csv,txt,xlsx,xls', 'max:10240'],
        ];
    }

    /**
     * Get the validation messages in Brazilian Portuguese.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Selecione a planilha com os registros de analytics.',
            'file.extensions' => 'Envie uma planilha CSV, XLSX ou XLS.',
            'file.max' => 'O arquivo deve ter no máximo 10 MB.',
        ];
    }
}
