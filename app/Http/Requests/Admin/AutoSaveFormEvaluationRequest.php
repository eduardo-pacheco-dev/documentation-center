<?php

namespace App\Http\Requests\Admin;

use App\Models\Form;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AutoSaveFormEvaluationRequest extends FormRequest
{
    /**
     * The form being evaluated.
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
        return $this->user()?->can('evaluate', $this->form()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'form_response_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['Aprovado', 'Reprovado'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'decisions' => ['sometimes', 'array'],
            'decisions.*' => ['sometimes', 'nullable', Rule::in(['aprovado', 'reprovado', 'pendente', 'nao_aplicavel'])],
            'image_decisions_json' => ['nullable', 'string', 'json'],
        ];
    }
}
