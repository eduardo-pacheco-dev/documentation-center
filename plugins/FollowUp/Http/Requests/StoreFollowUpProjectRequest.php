<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFollowUpProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:follow_up_clients,id'],
            'project_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('follow_up_projects', 'name')
                    ->where(fn ($query) => $query->where('client_id', $this->integer('client_id'))),
            ],
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
            'client_id.required' => 'Selecione o cliente do projeto.',
            'client_id.exists' => 'Selecione um cliente válido.',
            'project_name.required' => 'Informe o nome do projeto.',
            'project_name.max' => 'O nome do projeto pode ter no máximo 150 caracteres.',
            'project_name.unique' => 'Este cliente já possui um projeto com este nome.',
        ];
    }
}
