<?php

namespace App\Http\Requests\Admin;

use App\Models\Erb;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreErbDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->erb());
    }

    /**
     * The ERB the request acts on.
     */
    public function erb(): Erb
    {
        return $this->route('erb');
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
                'max:20480',
            ],
        ];
    }
}
