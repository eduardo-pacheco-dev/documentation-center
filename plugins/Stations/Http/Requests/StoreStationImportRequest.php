<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStationImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Selecione o arquivo CSV com as estações.',
            'file.extensions' => 'Envie um arquivo CSV ou TXT.',
            'file.max' => 'O arquivo deve ter no máximo 2 MB.',
        ];
    }
}
