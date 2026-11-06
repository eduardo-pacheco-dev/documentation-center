<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNokiaInterruptionRequest extends FormRequest
{
    /**
     * The return periods (in days) accepted for a work interruption.
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
            'reason' => ['required', 'string', 'max:255'],
            'return_days' => ['required', 'integer', Rule::in(self::RETURN_PERIODS)],
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
            'reason.required' => 'Informe o motivo da parada da obra.',
            'reason.max' => 'O motivo da parada deve ter no máximo 255 caracteres.',
            'return_days.required' => 'Informe o prazo de retorno.',
            'return_days.in' => 'O prazo de retorno deve ser 7, 14, 21 ou 28 dias.',
        ];
    }
}
