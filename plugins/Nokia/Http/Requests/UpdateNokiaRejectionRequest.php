<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNokiaRejectionRequest extends FormRequest
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
            'rejection_reason.required' => 'Informe o motivo da rejeição.',
            'rejection_reason.max' => 'O motivo da rejeição deve ter no máximo 255 caracteres.',
            'rejection_return_days.required' => 'Informe o prazo de retorno.',
            'rejection_return_days.in' => 'O prazo de retorno deve ser 7, 14, 21 ou 28 dias.',
        ];
    }
}
