<?php

namespace App\Http\Requests\Admin\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesFormSteps
{
    /**
     * The field types the builder supports.
     *
     * @var list<string>
     */
    public const FIELD_TYPES = [
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
        'file',
    ];

    /**
     * Decode the builder payload into a validation-ready steps array.
     */
    protected function decodeStepsJson(): void
    {
        $decoded = json_decode((string) $this->input('steps_json', ''), true);

        $this->merge(['steps' => is_array($decoded) ? $decoded : []]);
    }

    /**
     * Get the validation rules for the builder steps structure.
     *
     * @return array<string, array<mixed>>
     */
    protected function stepsValidationRules(): array
    {
        return [
            'steps' => ['present', 'array', 'max:50'],
            'steps.*.id' => ['required', 'string', 'max:100'],
            'steps.*.title' => ['nullable', 'string', 'max:255'],
            'steps.*.fields' => ['present', 'array', 'max:100'],
            'steps.*.fields.*.id' => ['required', 'string', 'max:100'],
            'steps.*.fields.*.type' => ['required', Rule::in(self::FIELD_TYPES)],
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
