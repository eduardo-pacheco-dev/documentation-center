<?php

namespace Plugins\TeamTracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\TeamTracking\Enums\TeamStatus;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $team = $this->route('team');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('teams', 'name')->ignore($team?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'area' => ['nullable', 'string', 'max:100'],
            'leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::enum(TeamStatus::class)],
            'started_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome da equipe.',
            'name.unique' => 'Já existe uma equipe com esse nome.',
            'status.required' => 'Selecione o status da equipe.',
        ];
    }
}
