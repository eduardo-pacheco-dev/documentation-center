<?php

namespace App\Http\Requests\Admin;

use App\Enums\AgendaCalendarRole;
use App\Models\AgendaCalendar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareAgendaCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $calendar = $this->route('agendaCalendar');

        return $calendar instanceof AgendaCalendar
            && $this->user()?->can('share', $calendar) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'role' => ['required', Rule::in(array_map(fn (AgendaCalendarRole $role): string => $role->value, AgendaCalendarRole::assignable()))],
        ];
    }
}
