<?php

namespace Plugins\TeamTracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\TeamTracking\Enums\CheckInStatus;

class StoreTeamCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'reference_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(CheckInStatus::class)],
            'health_score' => ['required', 'integer', 'between:1,5'],
            'summary' => ['required', 'string', 'max:5000'],
            'next_steps' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Informe o título do acompanhamento.',
            'reference_date.required' => 'Informe a data de referência.',
            'health_score.required' => 'Informe a nota de saúde da equipe.',
            'health_score.between' => 'A nota de saúde deve estar entre 1 e 5.',
            'summary.required' => 'Descreva o acompanhamento.',
        ];
    }
}
