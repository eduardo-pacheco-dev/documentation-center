<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNokiaRejectionRequest extends FormRequest
{
    /**
     * The return periods (in days) accepted for a rejection.
     *
     * @var list<int>
     */
    public const RETURN_PERIODS = [7, 14, 21, 28];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fam' => ['required', 'array', 'min:1'],
            'fam.*' => ['string', Rule::in(array_keys(config('nokia.checklists', [])))],
            'rejection_reason' => ['required', 'string', 'max:255'],
            'rejection_return_days' => ['required', 'integer', Rule::in(self::RETURN_PERIODS)],
            'rejection_requires_field_return' => ['boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fam.required' => 'Selecione ao menos uma FAM rejeitada.',
            'fam.min' => 'Selecione ao menos uma FAM rejeitada.',
            'fam.*.in' => 'Selecione apenas FAMs válidas.',
            'rejection_reason.required' => 'Informe o motivo da rejeição.',
            'rejection_reason.max' => 'O motivo da rejeição deve ter no máximo 255 caracteres.',
            'rejection_return_days.required' => 'Informe o prazo de retorno.',
            'rejection_return_days.in' => 'O prazo de retorno deve ser 7, 14, 21 ou 28 dias.',
        ];
    }
}
