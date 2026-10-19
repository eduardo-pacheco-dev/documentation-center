<?php

namespace App\Http\Requests\Admin;

use App\Models\AgendaEvent;

class UpdateAgendaEventRequest extends AgendaEventRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('agendaEvent');

        return $event instanceof AgendaEvent
            && $this->user()?->can('update', $event) === true;
    }
}
