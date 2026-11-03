<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\Stations\Models\StationAttachment;

class StoreStationAttachmentRequest extends FormRequest
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
            'category' => ['required', Rule::in([
                StationAttachment::CATEGORY_PPI_TSSR,
                StationAttachment::CATEGORY_ATTACHMENT,
            ])],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt,csv,png,jpg,jpeg,webp,kml,kmz',
            ],
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
            'files.required' => 'Selecione pelo menos um arquivo.',
            'files.max' => 'Envie no máximo 10 arquivos por vez.',
            'files.*.max' => 'Cada arquivo deve ter no máximo 20 MB.',
            'files.*.mimes' => 'Formato de arquivo não permitido.',
        ];
    }
}
