<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AgendaColor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShareAgendaCalendarRequest;
use App\Http\Requests\Admin\StoreAgendaCalendarRequest;
use App\Http\Requests\Admin\UpdateAgendaCalendarRequest;
use App\Models\AgendaCalendar;
use App\Models\AgendaCalendarShare;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaCalendarController extends Controller
{
    /**
     * The number of calendars loaded per page in the sidebar list.
     */
    public const PER_PAGE = 10;

    /**
     * Return the next page of calendars shown in the sidebar.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(max($request->integer('per_page', self::PER_PAGE), 1), 50);
        $page = max($request->integer('page', 1), 1);

        $calendars = AgendaCalendar::query()
            ->accessibleBy($user)
            ->with(['shares.user', 'user'])
            ->ordered()
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $calendars->getCollection()
            ->map(fn (AgendaCalendar $calendar): array => [
                'id' => $calendar->getKey(),
                'html' => view('admin.agenda.partials.calendar-item', [
                    'calendar' => $calendar,
                    'user' => $user,
                ])->render(),
            ])
            ->values()
            ->all();

        return response()->json([
            'items' => $items,
            'current_page' => $calendars->currentPage(),
            'last_page' => $calendars->lastPage(),
            'total' => $calendars->total(),
        ]);
    }

    /**
     * Create a calendar owned by the authenticated user.
     */
    public function store(StoreAgendaCalendarRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->agendaCalendars()->create([
            'name' => $request->string('name')->toString(),
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
            'color' => $request->input('color') ?: AgendaColor::Blueberry->value,
            'timezone' => $request->filled('timezone') ? $request->string('timezone')->toString() : config('app.timezone'),
            'is_default' => ! $user->agendaCalendars()->exists(),
            'is_visible' => true,
            'position' => (int) $user->agendaCalendars()->max('position') + 1,
            'default_reminders' => $this->reminders($request),
        ]);

        return back()->with('status', 'Calendário criado.');
    }

    /**
     * Update the calendar details.
     */
    public function update(UpdateAgendaCalendarRequest $request, AgendaCalendar $agendaCalendar): RedirectResponse
    {
        $agendaCalendar->update([
            'name' => $request->string('name')->toString(),
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
            'color' => $request->input('color') ?: $agendaCalendar->color->value,
            'timezone' => $request->filled('timezone') ? $request->string('timezone')->toString() : $agendaCalendar->timezone,
            'default_reminders' => $this->reminders($request),
        ]);

        return back()->with('status', 'Calendário atualizado.');
    }

    /**
     * Delete the calendar and all of its events.
     */
    public function destroy(AgendaCalendar $agendaCalendar): RedirectResponse
    {
        $this->authorize('delete', $agendaCalendar);

        $agendaCalendar->delete();

        return back()->with('status', 'Calendário excluído.');
    }

    /**
     * Toggle the default visibility of the calendar.
     */
    public function visibility(Request $request, AgendaCalendar $agendaCalendar): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $agendaCalendar);

        $agendaCalendar->update(['is_visible' => $request->boolean('is_visible')]);

        if ($request->expectsJson()) {
            return response()->json(['is_visible' => $agendaCalendar->is_visible]);
        }

        return back()->with('status', 'Visibilidade atualizada.');
    }

    /**
     * Make the calendar the user's default one.
     */
    public function makeDefault(AgendaCalendar $agendaCalendar): RedirectResponse
    {
        $this->authorize('update', $agendaCalendar);

        DB::transaction(function () use ($agendaCalendar): void {
            AgendaCalendar::query()
                ->where('user_id', $agendaCalendar->user_id)
                ->whereKeyNot($agendaCalendar->getKey())
                ->update(['is_default' => false]);

            $agendaCalendar->update(['is_default' => true]);
        });

        return back()->with('status', 'Calendário padrão atualizado.');
    }

    /**
     * Share the calendar with another user.
     */
    public function share(ShareAgendaCalendarRequest $request, AgendaCalendar $agendaCalendar): RedirectResponse
    {
        $user = User::query()->where('email', $request->string('email')->toString())->firstOrFail();

        abort_if($user->is($agendaCalendar->user), 422, 'O calendário já pertence a este usuário.');

        $agendaCalendar->shares()->updateOrCreate(
            ['user_id' => $user->getKey()],
            ['role' => $request->string('role')->toString()],
        );

        return back()->with('status', 'Calendário compartilhado com '.$user->email.'.');
    }

    /**
     * Revoke a share granted on the calendar.
     */
    public function unshare(AgendaCalendar $agendaCalendar, AgendaCalendarShare $share): RedirectResponse
    {
        $this->authorize('share', $agendaCalendar);

        abort_unless($share->agenda_calendar_id === $agendaCalendar->getKey(), 404);

        $share->delete();

        return back()->with('status', 'Compartilhamento removido.');
    }

    /**
     * Build the default reminder list from the request.
     *
     * @return list<array{method: string, minutes_before: int|null}>
     */
    private function reminders(Request $request): array
    {
        $reminders = [];

        foreach ((array) $request->input('default_reminders', []) as $reminder) {
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
}
