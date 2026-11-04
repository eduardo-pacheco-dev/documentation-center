<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendFollowUpEmailRequest extends FormRequest
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
            'recipients' => ['required', 'string', 'max:1000'],
            'subject' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->recipients() === []) {
                $validator->errors()->add('recipients', 'Informe ao menos um e-mail válido.');

                return;
            }

            foreach ($this->recipients() as $recipient) {
                if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('recipients', "O e-mail \"{$recipient}\" não é válido.");
                }
            }
        });
    }

    /**
     * The normalized recipient list.
     *
     * @return list<string>
     */
    public function recipients(): array
    {
        $recipients = preg_split('/[\s,;]+/', $this->string('recipients')->toString()) ?: [];

        return array_values(array_unique(array_filter($recipients, fn (string $email): bool => $email !== '')));
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipients.required' => 'Informe ao menos um destinatário.',
            'recipients.max' => 'A lista de destinatários é muito longa.',
            'subject.max' => 'O assunto pode ter no máximo 150 caracteres.',
        ];
    }
}
