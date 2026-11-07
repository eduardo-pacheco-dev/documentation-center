<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\Nokia\Models\NokiaSiteAttachment;

class StoreNokiaSiteAttachmentRequest extends FormRequest
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
            'category' => ['required', Rule::in(array_keys(NokiaSiteAttachment::categories()))],
            'files' => ['required', 'array', 'max:5'],
            'files.*' => ['file', 'max:20480', 'extensions:pdf,doc,docx,dwg,dxf,xls,xlsx,csv,txt,png,jpg,jpeg,zip'],
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
            'category.required' => 'Selecione a categoria do anexo.',
            'category.in' => 'Escolha uma categoria válida: TSSR, DOC-D ou DWG.',
            'files.required' => 'Selecione pelo menos um arquivo.',
            'files.max' => 'Envie no máximo 5 arquivos por vez.',
            'files.*.extensions' => 'Formato não suportado. Use PDF, DOC, DOCX, DWG, DXF, XLS, XLSX, CSV, TXT, PNG, JPG, JPEG ou ZIP.',
            'files.*.max' => 'Cada arquivo deve ter no máximo 20 MB.',
        ];
    }
}
