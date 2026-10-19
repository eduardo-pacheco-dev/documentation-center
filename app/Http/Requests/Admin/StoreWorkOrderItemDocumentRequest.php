<?php

namespace App\Http\Requests\Admin;

use App\Models\WorkOrderItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkOrderItemDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->workOrderItem()->workOrder);
    }

    /**
     * The work order item the request acts on.
     */
    public function workOrderItem(): WorkOrderItem
    {
        return $this->route('item');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*' => [
                'required',
                'file',
                Rule::file()->types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'ods', 'jpg', 'jpeg', 'png']),
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
            'documents.*.max' => 'Cada arquivo deve ter no máximo 100 MB.',
        ];
    }
}
