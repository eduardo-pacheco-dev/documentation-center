<?php

namespace App\Http\Requests\Admin;

use App\Enums\SchedulingMode;
use App\Enums\TaskConstraint;
use App\Enums\TaskType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class TaskRequest extends ProjectRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => $this->parentRules(),
            'task_type' => ['nullable', new Enum(TaskType::class)],
            'scheduling_mode' => ['nullable', new Enum(SchedulingMode::class)],
            'duration_days' => ['nullable', 'numeric', 'min:0', 'max:3650'],
            'start_date' => ['nullable', 'date'],
            'is_milestone' => ['boolean'],
            'constraint_type' => ['nullable', new Enum(TaskConstraint::class)],
            'constraint_date' => ['nullable', 'date', Rule::requiredIf($this->constraintNeedsDate())],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'percent_complete' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'budget_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * The task must be nested inside a task of the same project and never inside itself.
     *
     * @return list<string|object>
     */
    protected function parentRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('tasks', 'id')->where('project_id', $this->project()->getKey()),
            Rule::notIn([$this->route('task')?->getKey()]),
        ];
    }

    /**
     * Whether the chosen constraint pins the task to a date.
     */
    protected function constraintNeedsDate(): bool
    {
        return in_array($this->input('constraint_type'), [
            TaskConstraint::MustStartOn->value,
            TaskConstraint::MustFinishOn->value,
            TaskConstraint::StartNoEarlierThan->value,
            TaskConstraint::StartNoLaterThan->value,
            TaskConstraint::FinishNoEarlierThan->value,
            TaskConstraint::FinishNoLaterThan->value,
        ], true);
    }

    /**
     * The validated duration expressed in working minutes.
     */
    public function durationMinutes(): ?int
    {
        $days = $this->validated('duration_days');

        if ($days === null) {
            return null;
        }

        return (int) round((float) $days * $this->project()->defaultCalendar()->minutes_per_day);
    }

    /**
     * The rules applied after the field validation.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $task = $this->route('task');
                $parentId = $this->input('parent_id');

                if ($task === null || $parentId === null) {
                    return;
                }

                $parent = $task->project->tasks()->find($parentId);

                if ($parent !== null && $task->isDescendantOf($parent)) {
                    $validator->errors()->add('parent_id', 'A tarefa pai não pode ser um descendente da própria tarefa.');
                }
            },
        ];
    }
}
