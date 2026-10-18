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
                'required',
                'integer',
                Rule::exists('work_order_items', 'id')->where('work_order_id', $this->workOrder()->getKey()),
            ],
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*' => [
                'required',
                'file',
                Rule::file()->types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'ods', 'jpg', 'jpeg', 'png']),
                'max:20480',
            ],
        ];
    }
}
