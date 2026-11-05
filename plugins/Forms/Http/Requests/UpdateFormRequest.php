<?php

namespace Plugins\Forms\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Plugins\Forms\Models\Form;

class UpdateFormRequest extends FormRequest
{
    /**
     * The form the request acts on.
     */
    public function form(): Form
    {
        return $this->route('form');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->form()) === true;
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
            'type' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:40'],
            'allows_multiple_responses' => ['sometimes', 'boolean'],
            'steps_json' => ['sometimes', 'nullable', 'json'],
            'fields_json' => ['sometimes', 'nullable', 'json'],
        ];
    }
}
