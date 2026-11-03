<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpUpdateRequest extends FormRequest
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
            'body' => ['required', 'string', 'min:2', 'max:5000'],
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
            'body.required' => 'Escreva uma atualização antes de enviar.',
            'body.min' => 'A atualização precisa ter pelo menos 2 caracteres.',
            'body.max' => 'A atualização pode ter no máximo 5000 caracteres.',
        ];
    }
}
