<?php

namespace Plugins\Forms\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Plugins\Forms\Models\FormTemplate;

class UpdateFormTemplateRequest extends FormRequest
{
    /**
     * The template the request acts on.
     */
    public function template(): FormTemplate
    {
        return $this->route('formTemplate');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->template()) === true;
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
        ];
    }
}
