<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;

class StoreFollowUpRequest extends FormRequest
{
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
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(FollowUpStatus::class)],
            'priority' => ['required', Rule::enum(FollowUpPriority::class)],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
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
            'title.required' => 'Informe o título do follow-up.',
            'title.max' => 'O título pode ter no máximo 150 caracteres.',
            'status.required' => 'Selecione o status.',
            'status.enum' => 'Selecione um status válido.',
            'priority.required' => 'Selecione a prioridade.',
            'priority.enum' => 'Selecione uma prioridade válida.',
            'due_date.date' => 'Informe uma data de prazo válida.',
            'assigned_to.exists' => 'Selecione um responsável válido.',
        ];
    }
}
