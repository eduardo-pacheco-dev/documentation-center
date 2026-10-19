<?php

namespace App\Http\Requests\Admin;

use App\Enums\AgendaColor;
use App\Enums\AgendaEventStatus;
use App\Enums\AgendaEventVisibility;
use App\Enums\AgendaRecurrenceFrequency;
use App\Enums\AgendaReminderMethod;
use App\Enums\AgendaTransparency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

abstract class AgendaEventRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'agenda_calendar_id' => ['required', 'integer', Rule::exists('agenda_calendars', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:2048'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'scope' => ['nullable', Rule::in(['this', 'following', 'all'])],
            'recurrence_id' => ['nullable', 'date'],
            'is_all_day' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'timezone'],
            'status' => ['nullable', new Enum(AgendaEventStatus::class)],
            'visibility' => ['nullable', new Enum(AgendaEventVisibility::class)],
            'transparency' => ['nullable', new Enum(AgendaTransparency::class)],
            'color' => ['nullable', new Enum(AgendaColor::class)],
            'repeat_frequency' => ['nullable', new Enum(AgendaRecurrenceFrequency::class)],
            'repeat_interval' => ['nullable', 'integer', 'min:1', 'max:99'],
            'repeat_end_mode' => ['nullable', 'in:never,until,count'],
            'repeat_until' => ['nullable', 'date', 'required_if:repeat_end_mode,until'],
            'repeat_count' => ['nullable', 'integer', 'min:1', 'max:365', 'required_if:repeat_end_mode,count'],
            'repeat_weekdays' => ['nullable', 'array'],
            'repeat_weekdays.*' => ['integer', 'between:1,7'],
            'repeat_monthdays' => ['nullable', 'array'],
            'repeat_monthdays.*' => ['integer', 'between:1,31'],
            'attendees' => ['nullable', 'array'],
            'attendees.*' => ['email', 'max:255'],
            'reminders' => ['nullable', 'array'],
            'reminders.*.method' => ['required_with:reminders', new Enum(AgendaReminderMethod::class)],
            'reminders.*.minutes_before' => ['nullable', 'integer', 'min:0', 'max:40320'],
            'attendees_can_invite_others' => ['nullable', 'boolean'],
            'attendees_can_modify' => ['nullable', 'boolean'],
            'attendees_can_see_other_attendees' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.title' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.url' => ['required_with:attachments', 'url', 'max:2048'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Normalize the blank optional values before validation.
     */
    protected function prepareForValidation(): void
    {
        $nullable = [
            'description', 'location', 'meeting_url', 'timezone', 'status', 'visibility',
            'transparency', 'color', 'repeat_frequency', 'repeat_interval', 'repeat_end_mode',
            'repeat_until', 'repeat_count',
        ];

        $normalized = [];

        foreach ($nullable as $key) {
            if ($this->input($key) === '') {
                $normalized[$key] = null;
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * The event attributes ready to be persisted.
     *
     * @return array<string, mixed>
     */
    public function eventAttributes(): array
    {
        return [
            'agenda_calendar_id' => (int) $this->input('agenda_calendar_id'),
            'title' => $this->string('title')->toString(),
            'description' => $this->filled('description') ? $this->string('description')->toString() : null,
            'location' => $this->filled('location') ? $this->string('location')->toString() : null,
            'meeting_url' => $this->filled('meeting_url') ? $this->string('meeting_url')->toString() : null,
            'start_at' => $this->startAt(),
            'end_at' => $this->endAt(),
            'is_all_day' => $this->boolean('is_all_day'),
            'timezone' => $this->filled('timezone') ? $this->string('timezone')->toString() : config('app.timezone'),
            'status' => $this->input('status') ?: AgendaEventStatus::Confirmed->value,
            'visibility' => $this->input('visibility') ?: AgendaEventVisibility::Default->value,
            'transparency' => $this->input('transparency') ?: AgendaTransparency::Opaque->value,
            'color' => $this->input('color') ?: null,
            'recurrence' => $this->recurrence(),
            'recurrence_until' => $this->recurrenceUntil(),
            'attendees_can_invite_others' => $this->booleanOrDefault('attendees_can_invite_others', true),
            'attendees_can_modify' => $this->booleanOrDefault('attendees_can_modify', false),
            'attendees_can_see_other_attendees' => $this->booleanOrDefault('attendees_can_see_other_attendees', true),
            'attachments' => $this->attachments(),
        ];
    }

    /**
     * The e-mail addresses of the invited guests.
     *
     * @return list<string>
     */
    public function attendeeEmails(): array
    {
        $emails = array_filter((array) $this->input('attendees', []), fn ($email): bool => is_string($email) && $email !== '');

        return array_values(array_unique(array_map(fn (string $email): string => strtolower(trim($email)), $emails)));
    }

    /**
     * The reminders configured on the event.
     *
     * @return list<array{method: string, minutes_before: int|null}>
     */
    public function reminders(): array
    {
        $reminders = [];

        foreach ((array) $this->input('reminders', []) as $reminder) {
            if (! is_array($reminder) || ! isset($reminder['method'])) {
                continue;
            }

            $reminders[] = [
                'method' => (string) $reminder['method'],
                'minutes_before' => isset($reminder['minutes_before']) && $reminder['minutes_before'] !== ''
                    ? (int) $reminder['minutes_before']
                    : null,
            ];
        }

        return $reminders;
    }

    /**
     * The attachments configured on the event.
     *
     * @return list<array{title: string, url: string, mime_type: string|null}>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ((array) $this->input('attachments', []) as $attachment) {
            if (! is_array($attachment) || empty($attachment['title']) || empty($attachment['url'])) {
                continue;
            }

            $attachments[] = [
                'title' => (string) $attachment['title'],
                'url' => (string) $attachment['url'],
                'mime_type' => $attachment['mime_type'] ?? null,
            ];
        }

        return $attachments;
    }

    /**
     * The recurrence rule built from the flat form fields.
     *
     * @return array<string, mixed>|null
     */
    public function recurrence(): ?array
    {
        $frequency = AgendaRecurrenceFrequency::tryFrom((string) $this->input('repeat_frequency'));

        if ($frequency === null) {
            return null;
        }

        $recurrence = [
            'frequency' => $frequency->value,
            'interval' => max(1, (int) $this->input('repeat_interval', 1)),
        ];

        if ($this->input('repeat_end_mode') === 'count' && $this->filled('repeat_count')) {
            $recurrence['count'] = max(1, (int) $this->input('repeat_count'));
        }

        $weekdays = array_values(array_unique(array_map('intval', (array) $this->input('repeat_weekdays', []))));

        if ($frequency === AgendaRecurrenceFrequency::Weekly && $weekdays !== []) {
            sort($weekdays);
            $recurrence['byweekday'] = $weekdays;
        }

        $monthDays = array_values(array_unique(array_map('intval', (array) $this->input('repeat_monthdays', []))));

        if ($frequency === AgendaRecurrenceFrequency::Monthly && $monthDays !== []) {
            sort($monthDays);
            $recurrence['bymonthday'] = $monthDays;
        }

        return $recurrence;
    }

    /**
     * The end date of the recurrence, when it is bounded by a date.
     */
    public function recurrenceUntil(): ?string
    {
        if (! $this->input('repeat_frequency') || $this->input('repeat_end_mode') !== 'until') {
            return null;
        }

        return $this->filled('repeat_until')
            ? Carbon::parse($this->string('repeat_until')->toString())->toDateString()
            : null;
    }

    /**
     * The scope the change applies to on a recurring event.
     */
    public function scope(): string
    {
        $scope = (string) $this->input('scope', 'all');

        return in_array($scope, ['this', 'following', 'all'], true) ? $scope : 'all';
    }

    /**
     * The original start of the occurrence the change targets.
     */
    public function recurrenceId(): ?Carbon
    {
        return $this->filled('recurrence_id')
            ? Carbon::parse($this->string('recurrence_id')->toString())
            : null;
    }

    /**
     * The normalized start of the event.
     */
    protected function startAt(): Carbon
    {
        $start = Carbon::parse($this->string('start_at')->toString(), config('app.timezone'));

        return $this->boolean('is_all_day') ? $start->startOfDay() : $start;
    }

    /**
     * The normalized exclusive end of the event.
     */
    protected function endAt(): Carbon
    {
        $end = Carbon::parse($this->string('end_at')->toString(), config('app.timezone'));

        if (! $this->boolean('is_all_day')) {
            return $end;
        }

        $end = $end->startOfDay();

        return $end->lessThanOrEqualTo($this->startAt()) ? $this->startAt()->copy()->addDay() : $end;
    }

    /**
     * Read a boolean flag, falling back to a default when it is absent.
     */
    protected function booleanOrDefault(string $key, bool $default): bool
    {
        return $this->has($key) ? $this->boolean($key) : $default;
    }
}
