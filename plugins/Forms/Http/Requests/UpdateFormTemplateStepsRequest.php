<?php

namespace Plugins\Forms\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Plugins\Forms\Http\Requests\Concerns\ValidatesFormSteps;
use Plugins\Forms\Models\FormTemplate;

class UpdateFormTemplateStepsRequest extends FormRequest
{
    use ValidatesFormSteps;

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
     * Decode the builder payload before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $this->decodeStepsJson();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->stepsValidationRules();
    }
}
