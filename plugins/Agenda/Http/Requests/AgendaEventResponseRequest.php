<?php

namespace Plugins\Agenda\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Plugins\Agenda\Enums\AgendaAttendeeStatus;
use Plugins\Agenda\Models\AgendaEvent;

class AgendaEventResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('agendaEvent');

        return $event instanceof AgendaEvent
            && $this->user()?->can('respond', $event) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    AgendaAttendeeStatus::Accepted->value,
                    AgendaAttendeeStatus::Declined->value,
                    AgendaAttendeeStatus::Tentative->value,
                    AgendaAttendeeStatus::NeedsAction->value,
                ]),
            ],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }
}
