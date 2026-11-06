<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNokiaRomRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('nokia_roms', 'email')->ignore($this->route('rom')),
            ],
            'phone' => ['required', 'string', 'max:30'],
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
            'first_name.required' => 'Informe o nome do ROM.',
            'last_name.required' => 'Informe o sobrenome do ROM.',
            'email.required' => 'Informe o e-mail do ROM.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe um ROM com este e-mail.',
            'phone.required' => 'Informe o telefone do ROM.',
        ];
    }
}
