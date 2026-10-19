<?php

namespace App\Http\Requests\Admin;

use App\Models\AgendaCalendar;

class UpdateAgendaCalendarRequest extends StoreAgendaCalendarRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $calendar = $this->route('agendaCalendar');

        return $calendar instanceof AgendaCalendar
            && $this->user()?->can('update', $calendar) === true;
    }
}
