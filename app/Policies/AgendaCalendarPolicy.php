<?php

namespace App\Policies;

use App\Models\AgendaCalendar;
use App\Models\User;

class AgendaCalendarPolicy
{
    /**
     * Determine whether the user can list calendars.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the calendar.
     */
    public function view(User $user, AgendaCalendar $agendaCalendar): bool
    {
        return $agendaCalendar->roleFor($user) !== null;
    }

    /**
     * Determine whether the user can create calendars.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the calendar.
     */
    public function update(User $user, AgendaCalendar $agendaCalendar): bool
    {
        return $agendaCalendar->canWrite($user);
    }

    /**
     * Determine whether the user can delete the calendar.
     */
    public function delete(User $user, AgendaCalendar $agendaCalendar): bool
    {
        return $agendaCalendar->isOwnedBy($user);
    }

    /**
     * Determine whether the user can manage the calendar sharing settings.
     */
    public function share(User $user, AgendaCalendar $agendaCalendar): bool
    {
        return $agendaCalendar->isOwnedBy($user);
    }
}
