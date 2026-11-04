<?php

namespace Plugins\FollowUp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Plugins\FollowUp\Models\FollowUpSetting;

class UpdateFollowUpScheduleRequest extends FormRequest
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
            'email_summary_enabled' => ['required', 'boolean'],
            'email_subject_prefix' => ['required', 'string', 'max:150'],
            'summary_times' => ['required', 'string', 'max:100'],
            'summary_recipients' => ['nullable', 'string', 'max:1000'],
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

            foreach ($this->invalidRecipients() as $recipient) {
                $validator->errors()->add('summary_recipients', "O e-mail \"{$recipient}\" não é válido.");
            }
        });
    }

    /**
     * The normalized list of general summary recipients.
     *
     * @return list<string>
     */
    public function summaryRecipients(): array
    {
        return FollowUpSetting::parseSummaryRecipients($this->string('summary_recipients')->toString());
    }

    /**
     * The recipients that are not valid e-mail addresses.
     *
     * @return list<string>
     */
    private function invalidRecipients(): array
    {
        $recipients = preg_split('/[\s,;]+/', $this->string('summary_recipients')->toString()) ?: [];

        return collect($recipients)
            ->filter(fn (string $email): bool => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            ->values()
            ->all();
    }

    /**
     * The normalized summary delivery times.
     *
     * @return list<string>
     */
    public function summaryTimes(): array
    {
        return FollowUpSetting::parseSummaryTimes($this->string('summary_times')->toString());
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'summary_times.required' => 'Informe os horários do resumo periódico.',
            'email_subject_prefix.required' => 'Informe o prefixo do assunto dos e-mails.',
        ];
    }
}
