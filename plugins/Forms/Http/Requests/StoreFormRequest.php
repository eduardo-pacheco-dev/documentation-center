<?php

namespace Plugins\Forms\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\Forms\Models\Form;

class StoreFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Form::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
            'allows_multiple_responses' => ['sometimes', 'boolean'],
            'template_id' => ['sometimes', 'nullable', 'integer', Rule::exists('form_templates', 'id')],
            'steps_json' => ['sometimes', 'nullable', 'json'],
            'fields_json' => ['sometimes', 'nullable', 'json'],
        ];
    }
}
