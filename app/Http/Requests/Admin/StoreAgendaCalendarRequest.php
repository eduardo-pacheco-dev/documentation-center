<?php

namespace App\Http\Requests\Admin;

use App\Enums\AgendaColor;
use App\Enums\AgendaReminderMethod;
use App\Models\AgendaCalendar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAgendaCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AgendaCalendar::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'color' => ['nullable', new Enum(AgendaColor::class)],
            'timezone' => ['nullable', 'timezone'],
            'default_reminders' => ['nullable', 'array'],
            'default_reminders.*.method' => ['required_with:default_reminders', new Enum(AgendaReminderMethod::class)],
            'default_reminders.*.minutes_before' => ['nullable', 'integer', 'min:0', 'max:40320'],
        ];
    }

    /**
     * Normalize the blank optional values before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('color') === '') {
            $this->merge(['color' => null]);
        }

        if ($this->input('description') === '') {
            $this->merge(['description' => null]);
        }

        if ($this->input('timezone') === '') {
            $this->merge(['timezone' => null]);
        }
    }
}
