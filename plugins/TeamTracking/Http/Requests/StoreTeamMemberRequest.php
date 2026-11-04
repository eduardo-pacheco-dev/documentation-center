<?php

namespace Plugins\TeamTracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in(['membro', 'lider'])],
            'joined_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $team = $this->route('team');
            $userId = (int) $this->input('user_id');

            if ($team === null || $userId === 0) {
                return;
            }

            if ($team->members()->where('users.id', $userId)->exists()) {
                $validator->errors()->add('user_id', 'Esse usuário já faz parte da equipe.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Selecione um usuário.',
            'user_id.exists' => 'Selecione um usuário válido.',
            'role.required' => 'Selecione o papel do membro.',
        ];
    }
}
