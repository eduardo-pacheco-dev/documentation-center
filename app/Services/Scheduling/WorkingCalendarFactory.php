<?php

namespace App\Services\Scheduling;

use App\Models\Project;

/**
 * Builds the working calendar used to schedule a project.
 */
class WorkingCalendarFactory
{
    /**
     * Load the default calendar of a project with its working windows.
     */
    public function for(Project $project): WorkingCalendar
    {
        $calendar = $project->calendars()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->firstOrFail();

        $calendar->load(['days', 'exceptions']);

        return new WorkingCalendar($calendar);
    }
}
