<?php

namespace Plugins\Agenda\Http\Requests;

use Plugins\Agenda\Models\AgendaEvent;

class StoreAgendaEventRequest extends AgendaEventRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AgendaEvent::class) === true;
    }
}
