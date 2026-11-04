<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFollowUpSummaryRecipientRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'summary_name' => ['required', 'string', 'max:150'],
            'summary_email' => ['required', 'email', 'max:255', Rule::unique('follow_up_summary_recipients', 'email')],
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
            'summary_name.required' => 'Informe o nome de quem receberá as cópias.',
            'summary_name.max' => 'O nome pode ter no máximo 150 caracteres.',
            'summary_email.required' => 'Informe o e-mail que receberá as cópias.',
            'summary_email.email' => 'Informe um e-mail válido.',
            'summary_email.unique' => 'Este e-mail já está cadastrado nas cópias.',
        ];
    }
}
