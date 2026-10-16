<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesFormSteps;
use App\Models\FormTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormTemplateRequest extends FormRequest
{
    use ValidatesFormSteps;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', FormTemplate::class) === true;
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'form_id' => [
                'nullable',
                'integer',
                Rule::exists('forms', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()?->getKey())
                    ->whereNull('deleted_at')),
            ],
            ...$this->stepsValidationRules(),
        ];
    }
}
