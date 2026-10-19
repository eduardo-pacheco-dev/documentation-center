<?php

namespace App\Http\Requests\Admin;

use App\Models\WorkOrder;
use Illuminate\Contracts\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkOrderDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->workOrder());
    }

    /**
     * The work order the request acts on.
     */
    public function workOrder(): WorkOrder
    {
        return $this->route('workOrder');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => [
                'nullable',
                'integer',
                Rule::exists('work_order_items', 'id')->where('work_order_id', $this->workOrder()->getKey()),
            ],
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*' => [
                'required',
                'file',
                Rule::file()->types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'ods', 'jpg', 'jpeg', 'png', 'zip', 'rar']),
                'max:102400',
            ],
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'documents.required' => 'Selecione ao menos um arquivo para anexar.',
            'documents.min' => 'Selecione ao menos um arquivo para anexar.',
            'documents.max' => 'Você pode anexar no máximo 10 arquivos por envio.',
            'documents.*.mimes' => 'O arquivo deve ser PDF, Office, imagem, texto, ZIP ou RAR.',
            'documents.*.max' => 'Cada arquivo deve ter no máximo 100 MB.',
        ];
    }
}
