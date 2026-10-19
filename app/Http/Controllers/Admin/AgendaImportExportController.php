<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AgendaReminderMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportAgendaRequest;
use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Services\Agenda\AgendaEventSynchronizer;
use App\Services\Agenda\AgendaIcsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgendaImportExportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AgendaIcsService $ics,
        private readonly AgendaEventSynchronizer $synchronizer,
    ) {}

    /**
     * Download the accessible events as an iCalendar file.
     */
    public function export(Request $request): Response
    {
        $user = $request->user();
        $calendarIds = AgendaCalendar::query()->accessibleBy($user)->pluck('id');

        $calendar = $request->filled('calendar')
            ? AgendaCalendar::query()->accessibleBy($user)->findOrFail($request->integer('calendar'))
            : null;

        $events = AgendaEvent::query()
            ->whereIn('agenda_calendar_id', $calendar !== null ? [$calendar->getKey()] : $calendarIds)
            ->with(['attendees', 'reminders', 'exceptions', 'user'])
            ->orderBy('start_at')
            ->get();

        $contents = $this->ics->export($events, $calendar?->name ?? 'Agenda');
        $filename = 'agenda-'.now()->format('Ymd-His').'.ics';

        return response($contents, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Import events from an uploaded iCalendar file.
     */
    public function import(ImportAgendaRequest $request): RedirectResponse
    {
        $calendar = AgendaCalendar::findOrFail($request->integer('agenda_calendar_id'));
        $this->authorize('update', $calendar);

        $contents = (string) $request->file('file')->get();
        $parsed = $this->ics->parse($contents);
        $imported = 0;

        foreach ($parsed as $data) {
            $event = $calendar->events()->create([
                'user_id' => $request->user()->getKey(),
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
                'is_all_day' => $data['is_all_day'],
                'recurrence' => $data['recurrence'],
                'timezone' => config('app.timezone'),
            ]);

            $emails = array_map(fn (array $attendee): string => $attendee['email'], $data['attendees']);
            $this->synchronizer->syncAttendees($event, $emails);

            if ($data['reminder_minutes'] !== null) {
                $this->synchronizer->syncReminders($event, [[
                    'method' => AgendaReminderMethod::Popup->value,
                    'minutes_before' => abs((int) $data['reminder_minutes']),
                    'trigger_at' => null,
                ]]);
            }

            $imported++;
        }

        return back()->with('status', "{$imported} evento(s) importado(s).");
    }
}
