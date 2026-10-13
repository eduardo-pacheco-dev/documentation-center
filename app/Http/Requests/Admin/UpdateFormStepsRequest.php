<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesFormSteps;
use App\Models\Form;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFormStepsRequest extends FormRequest
{
    use ValidatesFormSteps;

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
