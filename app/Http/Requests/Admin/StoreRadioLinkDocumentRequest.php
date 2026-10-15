<?php

namespace App\Http\Requests\Admin;

use App\Models\RadioLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRadioLinkDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->radioLink());
    }

    /**
     * The radio link the request acts on.
     */
    public function radioLink(): RadioLink
    {
        return $this->route('radioLink');
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
