<?php

namespace App\Policies;

use App\Models\AgendaEvent;
use App\Models\User;

class AgendaEventPolicy
{
    /**
     * Determine whether the user can list events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, AgendaEvent $agendaEvent): bool
    {
        return $agendaEvent->calendar->roleFor($user) !== null
            || $agendaEvent->attendeeFor($user) !== null;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, AgendaEvent $agendaEvent): bool
    {
        if ($agendaEvent->isOrganizer($user)) {
            return true;
        }

        if ($agendaEvent->calendar->canWrite($user)) {
            return true;
        }

        return $agendaEvent->attendees_can_modify
            && $agendaEvent->attendeeFor($user) !== null;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, AgendaEvent $agendaEvent): bool
    {
        return $agendaEvent->isOrganizer($user) || $agendaEvent->calendar->canWrite($user);
    }

    /**
     * Determine whether the user can answer the invitation.
     */
    public function respond(User $user, AgendaEvent $agendaEvent): bool
    {
        return $agendaEvent->attendeeFor($user) !== null;
    }
}
