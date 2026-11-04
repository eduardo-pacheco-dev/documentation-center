<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFollowUpSettingsRequest extends FormRequest
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
            'per_page' => ['required', 'integer', 'between:5,100'],
            'due_soon_days' => ['required', 'integer', 'between:1,60'],
            'widget_limit' => ['required', 'integer', 'between:1,20'],
            'email_summary_enabled' => ['required', 'boolean'],
            'email_subject_prefix' => ['required', 'string', 'max:150'],
            'summary_times' => ['required', 'string', 'max:100'],
            'mail_enabled' => ['required', 'boolean'],
            'mail_host' => ['nullable', 'required_if:mail_enabled,1', 'string', 'max:255'],
            'mail_port' => ['nullable', 'required_if:mail_enabled,1', 'integer', 'between:1,65535'],
            'mail_encryption' => ['nullable', 'in:tls,ssl'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->summaryTimes() === []) {
                $validator->errors()->add('summary_times', 'Informe ao menos um horário no formato HH:MM.');
            }
        });
    }

    /**
     * The normalized summary delivery times.
     *
     * @return list<string>
     */
    public function summaryTimes(): array
    {
        $times = preg_split('/[\s,;]+/', $this->string('summary_times')->toString()) ?: [];

        return collect($times)
            ->filter(fn (string $time): bool => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.between' => 'A listagem deve exibir entre 5 e 100 follow-ups por página.',
            'due_soon_days.between' => 'O prazo de alerta deve ficar entre 1 e 60 dias.',
            'widget_limit.between' => 'O widget deve exibir entre 1 e 20 follow-ups.',
            'email_subject_prefix.required' => 'Informe o prefixo do assunto dos e-mails.',
            'summary_times.required' => 'Informe os horários do resumo periódico.',
            'mail_host.required_if' => 'Informe o servidor SMTP para ativar o envio.',
            'mail_port.required_if' => 'Informe a porta SMTP para ativar o envio.',
            'mail_port.between' => 'A porta SMTP deve estar entre 1 e 65535.',
            'mail_encryption.in' => 'Selecione uma criptografia válida (TLS ou SSL).',
            'mail_from_address.email' => 'Informe um e-mail de remetente válido.',
        ];
    }
}
