<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Plugins\FollowUp\Models\FollowUpClient;

class StoreFollowUpClientRequest extends FormRequest
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
            'client_id' => ['nullable', 'string', 'max:50'],
            'client_name' => ['nullable', 'string', 'max:150'],
            'project_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Require a name for new clients and validate the selected client.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $clientId = $this->input('client_id');

            if (filled($clientId) && $clientId !== 'new' && ! ctype_digit((string) $clientId)) {
                $validator->errors()->add('client_id', 'Selecione um cliente válido.');

                return;
            }

            if ($clientId === null || $clientId === '' || $clientId === 'new') {
                if (blank($this->input('client_name'))) {
                    $validator->errors()->add('client_name', 'Informe o nome do novo cliente.');
                }

                return;
            }

            if (! FollowUpClient::query()->whereKey($clientId)->exists()) {
                $validator->errors()->add('client_id', 'Selecione um cliente válido.');
            }
        });
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_name.max' => 'O nome do cliente pode ter no máximo 150 caracteres.',
            'project_name.max' => 'O nome do projeto pode ter no máximo 150 caracteres.',
        ];
    }
}
