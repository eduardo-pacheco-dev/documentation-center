<?php

namespace App\Http\Requests\Admin;

use App\Models\AgendaEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportAgendaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AgendaEvent::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'agenda_calendar_id' => ['required', 'integer', Rule::exists('agenda_calendars', 'id')],
            'file' => ['required', 'file', 'max:2048', 'mimes:ics,ical,text/calendar,txt'],
        ];
    }
}
