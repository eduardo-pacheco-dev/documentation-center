<?php

namespace App\Http\Requests\Admin;

use App\Models\AgendaEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveAgendaEventRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'scope' => ['nullable', Rule::in(['this', 'following', 'all'])],
            'recurrence_id' => ['nullable', 'date'],
        ];
    }
}
