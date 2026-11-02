@php
    $checkbox = 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900';
@endphp

<div class="flex items-center gap-2" data-agenda-calendar-item data-calendar-id="{{ $calendar->getKey() }}">
    <input
        type="checkbox"
        class="{{ $checkbox }}"
        value="{{ $calendar->getKey() }}"
        data-agenda-calendar-toggle
        @checked(in_array($calendar->getKey(), $visibleCalendarIds ?? [], true))
    >
    <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $calendar->color->hex() }}"></span>
    <span class="flex-1 truncate text-sm text-gray-700 dark:text-gray-200">{{ $calendar->name }}</span>
    @unless ($calendar->isOwnedBy($user))
        <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">Compartilhado</span>
    @endunless
    @if ($calendar->canWrite($user))
        <button
            type="button"
            data-agenda-calendar-edit
            data-calendar="{{ json_encode([
                'id' => $calendar->getKey(),
                'name' => $calendar->name,
                'description' => $calendar->description,
                'color' => $calendar->color->value,
                'timezone' => $calendar->timezone,
                'is_default' => $calendar->is_default,
                'is_visible' => $calendar->is_visible,
                'is_owner' => $calendar->isOwnedBy($user),
                'can_delete' => $calendar->isOwnedBy($user),
                'default_reminders' => $calendar->defaultReminders(),
                'shares' => $calendar->shares->map(fn ($share) => [
                    'id' => $share->getKey(),
                    'email' => $share->user?->email,
                    'name' => $share->user?->full_name,
                    'role' => $share->role->value,
                ])->values()->all(),
            ]) }}"
            class="rounded-md p-1 text-gray-400 transition hover:text-indigo-600"
            title="Editar calendário"
        >
            <x-icon name="pencil-square" class="h-3.5 w-3.5" />
        </button>
    @endif
</div>
