<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AgendaColor;
use App\Enums\AgendaEventStatus;
use App\Enums\AgendaTransparency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveAgendaEventRequest;
use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\TodoTask;
use App\Models\User;
use App\Services\Agenda\AgendaEventExpander;
use App\Services\Agenda\AgendaEventSynchronizer;
use App\Services\Agenda\AgendaGridBuilder;
use App\Services\Agenda\AgendaOccurrence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AgendaController extends Controller
{
    /**
     * The supported grid views.
     *
     * @var list<string>
     */
    private const VIEWS = ['month', 'week', 'day', 'list'];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AgendaEventExpander $expander,
        private readonly AgendaGridBuilder $grid,
        private readonly AgendaEventSynchronizer $synchronizer,
    ) {}

    /**
     * Show the agenda grid.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $calendars = $this->calendars($user);

        if ($calendars->isEmpty()) {
            $user->agendaCalendars()->create([
                'name' => 'Minha agenda',
                'color' => AgendaColor::Blueberry->value,
                'timezone' => config('app.timezone'),
                'is_default' => true,
                'is_visible' => true,
                'position' => 1,
            ]);

            $calendars = $this->calendars($user);
        }

        $visible = $this->visibleCalendars($user, $request, $calendars);
        $view = $this->resolveView($request);
        $date = $this->resolveDate($request);
        [$rangeStart, $rangeEnd] = $this->rangeFor($view, $date);

        $occurrences = $this->occurrences($visible, $rangeStart, $rangeEnd);
        $search = trim((string) $request->input('q'));

        $agendaTasks = TodoTask::query()
            ->where('user_id', $user->getKey())
            ->incomplete()
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()])
            ->with('todoList')
            ->orderBy('due_at')
            ->get();

        $tasksByDay = $agendaTasks->groupBy(fn (TodoTask $task): string => $task->due_at->toDateString());

        if ($search !== '') {
            $occurrences = $this->filterBySearch($occurrences, $search);
        }

        $listPaginator = null;

        if ($view === 'list') {
            $allOccurrences = collect($occurrences);
            $perPage = 10;
            $page = max(1, $request->integer('page', 1));

            $listPaginator = new LengthAwarePaginator(
                $allOccurrences->forPage($page, $perPage)->values()->all(),
                $allOccurrences->count(),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ],
            );

            $occurrences = $listPaginator->items();
        }

        return view('admin.agenda.index', [
            'calendars' => $calendars,
            'visibleCalendarIds' => $visible->pluck('id')->all(),
            'activeView' => $view,
            'date' => $date,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'occurrences' => $occurrences,
            'listPaginator' => $listPaginator,
            'agendaTasks' => $agendaTasks,
            'tasksByDay' => $tasksByDay,
            'days' => $this->grid->build($occurrences, $rangeStart, $rangeEnd),
            'search' => $search,
            'canCreate' => $calendars->contains(fn (AgendaCalendar $calendar): bool => $calendar->canWrite($user)),
            'writableCalendarIds' => $calendars
                ->filter(fn (AgendaCalendar $calendar): bool => $calendar->canWrite($user))
                ->pluck('id')
                ->all(),
            'firstWeekday' => Carbon::MONDAY,
            'calendarPageSize' => AgendaCalendarController::PER_PAGE,
        ]);
    }

    /**
     * Return the occurrences of a window as JSON.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $calendars = $this->calendars($user);
        $visible = $this->visibleCalendars($user, $request, $calendars);
        $from = $this->resolveDate($request)->startOfDay();
        $to = $from->copy()->addDays((int) $request->integer('days', 31))->endOfDay();

        $occurrences = collect($this->occurrences($visible, $from, $to))
            ->map(fn (AgendaOccurrence $occurrence): array => $this->occurrencePayload($occurrence))
            ->all();

        return response()->json(['occurrences' => $occurrences]);
    }

    /**
     * Return the busy slots of the accessible calendars as JSON.
     */
    public function freeBusy(Request $request): JsonResponse
    {
        $user = $request->user();
        $calendars = $this->calendars($user);
        $visible = $this->visibleCalendars($user, $request, $calendars);
        $from = $this->resolveDate($request)->startOfDay();
        $to = $from->copy()->addDays((int) $request->integer('days', 7))->endOfDay();

        $busy = [];

        foreach ($this->occurrences($visible, $from, $to) as $occurrence) {
            if ($occurrence->status() === AgendaEventStatus::Cancelled) {
                continue;
            }

            if ($occurrence->transparency() !== AgendaTransparency::Opaque) {
                continue;
            }

            $busy[] = [
                'calendar_id' => $occurrence->event->agenda_calendar_id,
                'title' => $occurrence->title(),
                'start' => $occurrence->start->toIso8601String(),
                'end' => $occurrence->end->toIso8601String(),
            ];
        }

        return response()->json(['busy' => $busy]);
    }

    /**
     * Reschedule an event through drag and drop or resizing.
     */
    public function move(MoveAgendaEventRequest $request, AgendaEvent $agendaEvent): JsonResponse|RedirectResponse
    {
        $start = Carbon::parse($request->string('start_at')->toString());
        $end = Carbon::parse($request->string('end_at')->toString());

        if ($agendaEvent->is_all_day) {
            $start = $start->startOfDay();
            $end = $end->startOfDay();

            if ($end->lessThanOrEqualTo($start)) {
                $end = $start->copy()->addDay();
            }
        }

        $scope = $request->input('scope', 'all');

        if (! $agendaEvent->isRecurring() || $scope === 'all') {
            $agendaEvent->update([
                'start_at' => $start,
                'end_at' => $end,
                'sequence' => $agendaEvent->sequence + 1,
            ]);
        } else {
            $recurrenceId = $request->filled('recurrence_id')
                ? Carbon::parse($request->string('recurrence_id')->toString())
                : $agendaEvent->start_at->copy();

            if ($scope === 'this') {
                $this->synchronizer->applyException($agendaEvent, $recurrenceId, [
                    'start_at' => $start->toDateTimeString(),
                    'end_at' => $end->toDateTimeString(),
                ]);
            } else {
                $this->synchronizer->splitSeries($agendaEvent, $recurrenceId, $start, $end);
            }
        }

        if ($request->expectsJson()) {
            $agendaEvent->refresh();

            return response()->json([
                'status' => 'ok',
                'start_at' => $agendaEvent->start_at->toIso8601String(),
                'end_at' => $agendaEvent->end_at->toIso8601String(),
            ]);
        }

        return back()->with('status', 'Evento reagendado.');
    }

    /**
     * The calendars the user can see.
     *
     * @return Collection<int, AgendaCalendar>
     */
    private function calendars(User $user): Collection
    {
        return AgendaCalendar::query()
            ->accessibleBy($user)
            ->with(['shares.user', 'user'])
            ->ordered()
            ->get();
    }

    /**
     * Filter the calendars shown on the grid.
     *
     * @param  Collection<int, AgendaCalendar>  $calendars
     * @return Collection<int, AgendaCalendar>
     */
    private function visibleCalendars(User $user, Request $request, Collection $calendars): Collection
    {
        if ($request->has('calendars')) {
            $selected = collect((array) $request->input('calendars', []))
                ->map(fn ($id): int => (int) $id)
                ->all();

            return $calendars->whereIn('id', $selected)->values();
        }

        return $calendars
            ->filter(fn (AgendaCalendar $calendar): bool => ! $calendar->isOwnedBy($user) || $calendar->is_visible)
            ->values();
    }

    /**
     * Query and expand the occurrences of the calendars in a window.
     *
     * @param  Collection<int, AgendaCalendar>  $calendars
     * @return list<AgendaOccurrence>
     */
    private function occurrences(Collection $calendars, Carbon $from, Carbon $to): array
    {
        $events = AgendaEvent::query()
            ->whereIn('agenda_calendar_id', $calendars->pluck('id'))
            ->overlapping($from, $to)
            ->with(['calendar', 'attendees', 'reminders', 'exceptions', 'user', 'tasks'])
            ->get();

        return $this->expander->expandMany($events, $from, $to);
    }

    /**
     * Keep only the occurrences matching the search term.
     *
     * @param  list<AgendaOccurrence>  $occurrences
     * @return list<AgendaOccurrence>
     */
    private function filterBySearch(array $occurrences, string $search): array
    {
        $needle = mb_strtolower($search);

        return array_values(array_filter($occurrences, function (AgendaOccurrence $occurrence) use ($needle): bool {
            $haystack = mb_strtolower(implode(' ', array_filter([
                $occurrence->title(),
                $occurrence->location(),
                $occurrence->description(),
            ])));

            return str_contains($haystack, $needle);
        }));
    }

    /**
     * Normalize the requested view.
     */
    private function resolveView(Request $request): string
    {
        $view = (string) $request->input('view', 'month');

        return in_array($view, self::VIEWS, true) ? $view : 'month';
    }

    /**
     * Normalize the requested reference date.
     */
    private function resolveDate(Request $request): Carbon
    {
        $date = $request->input('date');

        if (is_string($date) && $date !== '') {
            try {
                return Carbon::parse($date, config('app.timezone'))->startOfDay();
            } catch (\Throwable) {
                // Fall back to today when the date cannot be parsed.
            }
        }

        return Carbon::today(config('app.timezone'));
    }

    /**
     * The inclusive window covered by a view.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rangeFor(string $view, Carbon $date): array
    {
        return match ($view) {
            'week' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'day' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'list' => [$date->copy()->startOfDay(), $date->copy()->addDays(30)->endOfDay()],
            default => [
                $date->copy()->startOfMonth()->startOfWeek(),
                $date->copy()->endOfMonth()->endOfWeek(),
            ],
        };
    }

    /**
     * The JSON payload of an occurrence.
     *
     * @return array<string, mixed>
     */
    private function occurrencePayload(AgendaOccurrence $occurrence): array
    {
        return [
            'id' => $occurrence->event->getKey(),
            'recurrence_id' => $occurrence->recurrenceId?->toIso8601String(),
            'title' => $occurrence->title(),
            'start' => $occurrence->start->toIso8601String(),
            'end' => $occurrence->end->toIso8601String(),
            'is_all_day' => $occurrence->isAllDay(),
            'is_recurring' => $occurrence->isRecurring,
            'status' => $occurrence->status()->value,
            'transparency' => $occurrence->transparency()->value,
            'color' => $occurrence->color()->hex(),
            'location' => $occurrence->location(),
            'calendar_id' => $occurrence->event->agenda_calendar_id,
        ];
    }
}
