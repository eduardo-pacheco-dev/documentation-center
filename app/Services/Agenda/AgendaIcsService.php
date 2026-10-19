<?php

namespace App\Services\Agenda;

use App\Enums\AgendaAttendeeStatus;
use App\Enums\AgendaRecurrenceFrequency;
use App\Models\AgendaEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Serializes agenda events to the iCalendar format and parses incoming files.
 */
class AgendaIcsService
{
    /**
     * The RFC 5545 weekday codes indexed by ISO weekday number.
     *
     * @var array<int, string>
     */
    private const WEEKDAY_CODES = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];

    /**
     * The RFC 5545 participant status of each attendee status.
     *
     * @var array<string, string>
     */
    private const PARTICIPANT_STATUS = [
        'accepted' => 'ACCEPTED',
        'declined' => 'DECLINED',
        'tentative' => 'TENTATIVE',
        'needs_action' => 'NEEDS-ACTION',
    ];

    /**
     * Build an iCalendar document for the given events.
     *
     * @param  Collection<int, AgendaEvent>  $events
     */
    public function export(Collection $events, string $calendarName = 'Agenda'): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Documentation Center//Agenda//PT-BR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape($calendarName),
        ];

        foreach ($events as $event) {
            $event->loadMissing(['attendees', 'reminders', 'exceptions', 'user']);

            foreach ($this->eventLines($event) as $line) {
                $lines[] = $line;
            }
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map(fn (string $line): string => $this->fold($line), $lines))."\r\n";
    }

    /**
     * Parse an iCalendar document into normalized event arrays.
     *
     * @return list<array<string, mixed>>
     */
    public function parse(string $contents): array
    {
        $events = [];
        $current = null;
        $inAlarm = false;

        foreach ($this->unfold($contents) as $rawLine) {
            $line = trim($rawLine);

            if ($line === '') {
                continue;
            }

            if ($line === 'BEGIN:VEVENT') {
                $current = ['attendees' => [], 'exdates' => []];
                $inAlarm = false;

                continue;
            }

            if ($line === 'END:VEVENT') {
                if ($current !== null) {
                    $events[] = $this->normalize($current);
                }

                $current = null;

                continue;
            }

            if ($current === null) {
                continue;
            }

            if ($line === 'BEGIN:VALARM') {
                $inAlarm = true;

                continue;
            }

            if ($line === 'END:VALARM') {
                $inAlarm = false;

                continue;
            }

            [$name, $params, $value] = $this->splitLine($line);

            if ($inAlarm) {
                if ($name === 'TRIGGER') {
                    $current['alarm_trigger'] = $value;
                }

                continue;
            }

            match ($name) {
                'SUMMARY' => $current['title'] = $this->unescape($value),
                'DESCRIPTION' => $current['description'] = $this->unescape($value),
                'LOCATION' => $current['location'] = $this->unescape($value),
                'DTSTART' => $current['start'] = $this->parseDate($value, $params),
                'DTEND' => $current['end'] = $this->parseDate($value, $params),
                'RRULE' => $current['rrule'] = $value,
                'EXDATE' => $current['exdates'][] = $value,
                'ATTENDEE' => $current['attendees'][] = $this->parseAttendee($params, $value),
                default => null,
            };
        }

        return $events;
    }

    /**
     * Build the VEVENT lines of a single event.
     *
     * @return list<string>
     */
    private function eventLines(AgendaEvent $event): array
    {
        $lines = ['BEGIN:VEVENT'];
        $lines[] = 'UID:agenda-event-'.$event->getKey().'@documentation-center';
        $lines[] = 'DTSTAMP:'.$this->formatUtc(now());

        if ($event->is_all_day) {
            $lines[] = 'DTSTART;VALUE=DATE:'.$event->start_at->format('Ymd');
            $lines[] = 'DTEND;VALUE=DATE:'.$event->end_at->format('Ymd');
        } else {
            $lines[] = 'DTSTART:'.$this->formatUtc($event->start_at);
            $lines[] = 'DTEND:'.$this->formatUtc($event->end_at);
        }

        $lines[] = 'SUMMARY:'.$this->escape($event->title);
        $lines[] = 'STATUS:'.strtoupper($event->status->value);
        $lines[] = 'TRANSP:'.strtoupper($event->transparency->value);
        $lines[] = 'CLASS:'.strtoupper($event->visibility->value);

        if ($event->description) {
            $lines[] = 'DESCRIPTION:'.$this->escape($event->description);
        }

        if ($event->location) {
            $lines[] = 'LOCATION:'.$this->escape($event->location);
        }

        if ($event->meeting_url) {
            $lines[] = 'URL:'.$this->escape($event->meeting_url);
        }

        if ($event->color) {
            $lines[] = 'X-COLOR:'.$event->color->hex();
        }

        if ($rule = $this->rrule($event)) {
            $lines[] = 'RRULE:'.$rule;
        }

        $cancelled = $event->exceptions->where('is_cancelled', true);

        if ($cancelled->isNotEmpty()) {
            $exdates = $cancelled
                ->map(fn ($exception): string => $this->formatUtc($exception->recurrence_id))
                ->implode(',');

            $lines[] = 'EXDATE:'.$exdates;
        }

        $organizer = $event->user;
        $lines[] = 'ORGANIZER;CN='.$this->escape($organizer->name).':mailto:'.$organizer->email;

        foreach ($event->attendees as $attendee) {
            $lines[] = 'ATTENDEE;CN='.$this->escape($attendee->name ?? $attendee->email)
                .';PARTSTAT='.self::PARTICIPANT_STATUS[$attendee->status->value]
                .';ROLE='.($attendee->is_optional ? 'OPT-PARTICIPANT' : 'REQ-PARTICIPANT')
                .':mailto:'.$attendee->email;
        }

        foreach ($event->reminders as $reminder) {
            if ($reminder->minutes_before === null) {
                continue;
            }

            $lines[] = 'BEGIN:VALARM';
            $lines[] = 'ACTION:'.($reminder->method->value === 'email' ? 'EMAIL' : 'DISPLAY');
            $lines[] = 'DESCRIPTION:'.$this->escape($event->title);
            $lines[] = 'TRIGGER:-PT'.max(0, $reminder->minutes_before).'M';
            $lines[] = 'END:VALARM';
        }

        $lines[] = 'END:VEVENT';

        return $lines;
    }

    /**
     * Build the RRULE value of a recurring event.
     */
    private function rrule(AgendaEvent $event): ?string
    {
        $recurrence = $event->recurrence;

        if (! is_array($recurrence) || ! isset($recurrence['frequency'])) {
            return null;
        }

        $frequency = AgendaRecurrenceFrequency::tryFrom($recurrence['frequency']);

        if ($frequency === null) {
            return null;
        }

        $parts = ['FREQ='.$frequency->rruleName()];

        if ((int) ($recurrence['interval'] ?? 1) > 1) {
            $parts[] = 'INTERVAL='.(int) $recurrence['interval'];
        }

        if (! empty($recurrence['byweekday'])) {
            $days = array_map(fn (int $day): string => self::WEEKDAY_CODES[$day] ?? 'MO', $recurrence['byweekday']);
            $parts[] = 'BYDAY='.implode(',', $days);
        }

        if (! empty($recurrence['bymonthday'])) {
            $parts[] = 'BYMONTHDAY='.implode(',', array_map('intval', $recurrence['bymonthday']));
        }

        if (! empty($recurrence['count'])) {
            $parts[] = 'COUNT='.(int) $recurrence['count'];
        } elseif ($event->recurrence_until !== null) {
            $parts[] = 'UNTIL='.$this->formatUtc($event->recurrence_until->copy()->endOfDay());
        }

        return implode(';', $parts);
    }

    /**
     * Normalize a parsed VEVENT block into the shape used by the importer.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normalize(array $raw): array
    {
        $start = $raw['start'] ?? null;

        if ($start === null) {
            $start = ['value' => Carbon::now(), 'is_all_day' => false];
        }

        $end = $raw['end'] ?? null;
        $isAllDay = (bool) ($start['is_all_day'] ?? false);
        $startAt = $start['value'];
        $endAt = $end !== null && $end['value'] !== null
            ? $end['value']
            : ($isAllDay ? $startAt->copy()->addDay() : $startAt->copy()->addHour());

        if ($isAllDay) {
            $startAt = $startAt->copy()->startOfDay();
            $endAt = $endAt->copy()->startOfDay();
        }

        $attendees = $raw['attendees'] ?? [];

        return [
            'title' => $raw['title'] ?? 'Evento importado',
            'description' => $raw['description'] ?? null,
            'location' => $raw['location'] ?? null,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_all_day' => $isAllDay,
            'recurrence' => isset($raw['rrule']) ? $this->parseRrule((string) $raw['rrule']) : null,
            'attendees' => $attendees,
            'reminder_minutes' => $this->parseAlarmMinutes($raw['alarm_trigger'] ?? null),
        ];
    }

    /**
     * Convert an RRULE value into the recurrence array stored on the event.
     *
     * @return array<string, mixed>|null
     */
    private function parseRrule(string $rrule): ?array
    {
        $parts = [];

        foreach (explode(';', $rrule) as $segment) {
            $pair = explode('=', $segment, 2);

            if (count($pair) !== 2) {
                continue;
            }

            $parts[strtoupper($pair[0])] = $pair[1];
        }

        $frequency = isset($parts['FREQ'])
            ? AgendaRecurrenceFrequency::tryFrom(strtolower($parts['FREQ']))
            : null;

        if ($frequency === null) {
            return null;
        }

        $recurrence = [
            'frequency' => $frequency->value,
            'interval' => max(1, (int) ($parts['INTERVAL'] ?? 1)),
        ];

        if (isset($parts['COUNT'])) {
            $recurrence['count'] = max(1, (int) $parts['COUNT']);
        }

        if (isset($parts['UNTIL'])) {
            $recurrence['until'] = $this->parseRruleDate($parts['UNTIL'])?->toDateString();
        }

        if (isset($parts['BYDAY'])) {
            $codes = array_flip(self::WEEKDAY_CODES);
            $days = [];

            foreach (explode(',', $parts['BYDAY']) as $day) {
                $normalized = strtoupper(substr(trim($day), -2));

                if (isset($codes[$normalized])) {
                    $days[] = $codes[$normalized];
                }
            }

            sort($days);
            $recurrence['byweekday'] = $days;
        }

        if (isset($parts['BYMONTHDAY'])) {
            $recurrence['bymonthday'] = array_values(array_map('intval', explode(',', $parts['BYMONTHDAY'])));
        }

        return $recurrence;
    }

    /**
     * Convert a TRIGGER value into minutes before the event.
     */
    private function parseAlarmMinutes(?string $trigger): ?int
    {
        if ($trigger === null || ! preg_match('/^-?P(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?)?/', trim($trigger), $matches)) {
            return null;
        }

        $days = (int) ($matches[1] ?? 0);
        $hours = (int) ($matches[2] ?? 0);
        $minutes = (int) ($matches[3] ?? 0);
        $total = ($days * 24 * 60) + ($hours * 60) + $minutes;

        return str_starts_with(trim($trigger), '-') ? $total : -$total;
    }

    /**
     * Split a content line into its name, parameters and value.
     *
     * @return array{0: string, 1: array<string, string>, 2: string}
     */
    private function splitLine(string $line): array
    {
        $separator = $this->findValueSeparator($line);
        $head = substr($line, 0, $separator);
        $value = substr($line, $separator + 1);

        $segments = explode(';', $head);
        $name = strtoupper(array_shift($segments));
        $params = [];

        foreach ($segments as $segment) {
            $pair = explode('=', $segment, 2);

            if (count($pair) === 2) {
                $params[strtoupper($pair[0])] = trim($pair[1], '"');
            }
        }

        return [$name, $params, $value];
    }

    /**
     * Locate the `:` that separates the property head from its value.
     */
    private function findValueSeparator(string $line): int
    {
        $inQuotes = false;
        $length = strlen($line);

        for ($index = 0; $index < $length; $index++) {
            $character = $line[$index];

            if ($character === '"') {
                $inQuotes = ! $inQuotes;
            } elseif ($character === ':' && ! $inQuotes) {
                return $index;
            }
        }

        return $length;
    }

    /**
     * Parse a DATE or DATE-TIME property value.
     *
     * @param  array<string, string>  $params
     * @return array{value: Carbon, is_all_day: bool}|null
     */
    private function parseDate(string $value, array $params): ?array
    {
        $value = trim($value);
        $isDate = strtoupper($params['VALUE'] ?? '') === 'DATE' || preg_match('/^\d{8}$/', $value) === 1;

        if ($isDate) {
            return ['value' => Carbon::createFromFormat('Ymd', $value)->startOfDay(), 'is_all_day' => true];
        }

        $format = str_ends_with($value, 'Z') ? 'Ymd\THis\Z' : 'Ymd\THis';

        try {
            $date = Carbon::createFromFormat($format, $value, 'UTC');
        } catch (\Throwable) {
            return null;
        }

        return ['value' => $date, 'is_all_day' => false];
    }

    /**
     * Parse the UNTIL value of a recurrence rule.
     */
    private function parseRruleDate(string $value): ?Carbon
    {
        $parsed = $this->parseDate($value, []);

        return $parsed['value'] ?? null;
    }

    /**
     * Parse an ATTENDEE property.
     *
     * @param  array<string, string>  $params
     * @return array{email: string, name: string|null, status: string, is_optional: bool}
     */
    private function parseAttendee(array $params, string $value): array
    {
        $email = str_ireplace('mailto:', '', trim($value));
        $status = AgendaAttendeeStatus::NeedsAction->value;

        foreach (self::PARTICIPANT_STATUS as $key => $rfcStatus) {
            if (strtoupper($params['PARTSTAT'] ?? '') === $rfcStatus) {
                $status = $key;

                break;
            }
        }

        return [
            'email' => $email,
            'name' => $params['CN'] ?? null,
            'status' => $status,
            'is_optional' => strtoupper($params['ROLE'] ?? '') === 'OPT-PARTICIPANT',
        ];
    }

    /**
     * Unfold the content lines of an iCalendar document.
     *
     * @return list<string>
     */
    private function unfold(string $contents): array
    {
        $contents = str_replace(["\r\n", "\r"], "\n", $contents);
        $lines = explode("\n", $contents);
        $unfolded = [];

        foreach ($lines as $line) {
            if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t") && $unfolded !== []) {
                $unfolded[array_key_last($unfolded)] .= substr($line, 1);

                continue;
            }

            $unfolded[] = $line;
        }

        return $unfolded;
    }

    /**
     * Fold a content line to the RFC 5545 length limit.
     */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $chunks = [];
        $remaining = $line;

        while (strlen($remaining) > 75) {
            $chunks[] = substr($remaining, 0, 75);
            $remaining = substr($remaining, 75);
        }

        $chunks[] = $remaining;

        return implode("\r\n ", $chunks);
    }

    /**
     * Escape a text value for the iCalendar format.
     */
    private function escape(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\r", "\n", ';', ','],
            ['\\\\', '\\n', '\\n', '\\n', '\\;', '\\,'],
            $value,
        );
    }

    /**
     * Reverse the iCalendar text escaping.
     */
    private function unescape(string $value): string
    {
        return str_replace(
            ['\\n', '\\N', '\\,', '\\;', '\\\\'],
            ["\n", "\n", ',', ';', '\\'],
            $value,
        );
    }

    /**
     * Format a date-time as a UTC iCalendar timestamp.
     */
    private function formatUtc(CarbonInterface $date): string
    {
        return $date->copy()->utc()->format('Ymd\THis\Z');
    }
}
