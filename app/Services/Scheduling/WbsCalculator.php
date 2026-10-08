<?php

namespace App\Services\Scheduling;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Collection;

/**
 * Keeps the work breakdown structure codes in sync with the task outline.
 */
class WbsCalculator
{
    /**
     * Renumber the WBS codes and outline levels of a project.
     *
     * @return int the number of tasks that had to be corrected
     */
    public function recalculate(Project $project): int
    {
        $tasks = $project->tasks()->orderBy('sort_order')->orderBy('id')->get();
        $children = $tasks->groupBy('parent_id');

        return $this->walk($children, null, '', 1);
    }

    /**
     * Number the children of a task and recurse into them.
     *
     * @param  Collection<mixed, Collection<int, Task>>  $children
     */
    private function walk($children, $parentId, string $prefix, int $level): int
    {
        $updated = 0;

        foreach ($children->get($parentId, collect()) as $index => $task) {
            $wbs = $prefix === '' ? (string) ($index + 1) : $prefix.'.'.($index + 1);

            if ($task->wbs !== $wbs || $task->outline_level !== $level) {
                Task::query()->whereKey($task->getKey())->update([
                    'wbs' => $wbs,
                    'outline_level' => $level,
                ]);

                $updated++;
            }

            $updated += $this->walk($children, $task->getKey(), $wbs, $level + 1);
        }

        return $updated;
    }
}
