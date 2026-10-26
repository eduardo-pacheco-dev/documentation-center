<?php

namespace App\Http\Requests\Admin;

use App\Models\Form;
use App\Services\Forms\FormModuleSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Form::class) === true;
    }

    /**
     * Normalize the textarea values before validating them.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'types' => FormModuleSettings::normalizeLines($this->input('types')),
            'statuses' => FormModuleSettings::normalizeLines($this->input('statuses')),
            'default_template_id' => $this->filled('default_template_id')
                ? $this->input('default_template_id')
                : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', 'string', 'max:40', 'distinct:ignore_case'],
            'default_template_id' => ['nullable', 'integer', Rule::exists('form_templates', 'id')],
            'default_view' => ['required', 'string', Rule::in(array_keys(FormModuleSettings::VIEWS))],
            'per_page' => ['required', 'integer', Rule::in(FormModuleSettings::PER_PAGE_OPTIONS)],
        ];
    }

    /**
     * Get the validation messages in Brazilian Portuguese.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'types.required' => 'Informe ao menos um tipo de formulário.',
            'types.min' => 'Informe ao menos um tipo de formulário.',
            'types.*.required' => 'Cada tipo de formulário deve ser preenchido.',
            'types.*.max' => 'O tipo de formulário deve ter no máximo :max caracteres.',
            'types.*.distinct' => 'Os tipos de formulário não podem se repetir.',
            'statuses.required' => 'Informe ao menos um status de formulário.',
            'statuses.min' => 'Informe ao menos um status de formulário.',
            'statuses.*.required' => 'Cada status de formulário deve ser preenchido.',
            'statuses.*.max' => 'O status do formulário deve ter no máximo :max caracteres.',
            'statuses.*.distinct' => 'Os status de formulário não podem se repetir.',
            'default_template_id.integer' => 'Selecione um template válido.',
            'default_template_id.exists' => 'O template selecionado não existe.',
            'default_view.required' => 'Selecione a visualização padrão.',
            'default_view.in' => 'A visualização selecionada é inválida.',
            'per_page.required' => 'Selecione a quantidade de itens por página.',
            'per_page.in' => 'A quantidade de itens por página é inválida.',
        ];
    }

    /**
     * Get the user friendly names for the validation attributes.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'types' => 'tipos de formulário',
            'statuses' => 'status de formulário',
            'default_template_id' => 'template padrão',
            'default_view' => 'visualização padrão',
            'per_page' => 'itens por página',
        ];
    }
}
