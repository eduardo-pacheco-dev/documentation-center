<?php

namespace App\Http\Requests\Admin;

use App\Models\Form;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormStepsRequest extends FormRequest
{
    /**
     * The field types the builder supports.
     *
     * @var list<string>
     */
    public const TYPES = [
        'text',
        'textarea',
        'number',
        'date',
        'time',
        'radio',
        'checkbox',
        'dropdown',
        'scale',
        'image',
    ];

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
     * Decode the builder payload before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $decoded = json_decode((string) $this->input('steps_json', ''), true);

        $this->merge(['steps' => is_array($decoded) ? $decoded : []]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'steps' => ['present', 'array', 'max:50'],
            'steps.*.id' => ['required', 'string', 'max:100'],
            'steps.*.title' => ['nullable', 'string', 'max:255'],
            'steps.*.fields' => ['present', 'array', 'max:100'],
            'steps.*.fields.*.id' => ['required', 'string', 'max:100'],
            'steps.*.fields.*.type' => ['required', Rule::in(self::TYPES)],
            'steps.*.fields.*.label' => ['nullable', 'string', 'max:255'],
            'steps.*.fields.*.required' => ['sometimes', 'boolean'],
            'steps.*.fields.*.help' => ['nullable', 'string', 'max:1000'],
            'steps.*.fields.*.options' => ['sometimes', 'array', 'max:50'],
            'steps.*.fields.*.options.*' => ['nullable', 'string', 'max:255'],
            'steps.*.fields.*.scaleMin' => ['sometimes', 'integer', 'between:0,1'],
            'steps.*.fields.*.scaleMax' => ['sometimes', 'integer', 'between:2,10'],
            'steps.*.fields.*.scaleMinLabel' => ['nullable', 'string', 'max:255'],
            'steps.*.fields.*.scaleMaxLabel' => ['nullable', 'string', 'max:255'],
        ];
    }
}
