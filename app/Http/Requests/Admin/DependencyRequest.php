<?php

namespace App\Http\Requests\Admin;

use App\Enums\DependencyType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class DependencyRequest extends ProjectRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->project()->getKey();
        $dependencyId = $this->route('dependency')?->getKey();

        return [
            'predecessor_id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->where('project_id', $projectId),
            ],
            'successor_id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->where('project_id', $projectId),
                Rule::notIn([$this->input('predecessor_id')]),
                Rule::unique('task_dependencies', 'successor_id')
                    ->where('predecessor_id', $this->input('predecessor_id'))
                    ->ignore($dependencyId),
            ],
            'type' => ['nullable', new Enum(DependencyType::class)],
            'lag_days' => ['nullable', 'numeric', 'min:-3650', 'max:3650'],
        ];
    }

    /**
     * The link must not close a loop inside the plan graph.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $predecessorId = (int) $this->input('predecessor_id');
                $successorId = (int) $this->input('successor_id');

                if ($predecessorId === $successorId) {
                    return;
                }

                $links = $this->project()->dependencies()->get(['predecessor_id', 'successor_id']);

                $successors = [];

                foreach ($links as $link) {
                    $successors[(int) $link->predecessor_id][] = (int) $link->successor_id;
                }

                $queue = [$successorId];
                $visited = [];

                for ($depth = 0; $queue !== [] && $depth < 100; $depth++) {
                    $node = array_pop($queue);

                    if ($node === $predecessorId) {
                        $validator->errors()->add(
                            'successor_id',
                            'A dependência formaria um ciclo entre as tarefas.',
                        );

                        return;
                    }

                    if (isset($visited[$node])) {
                        continue;
                    }

                    $visited[$node] = true;

                    array_push($queue, ...($successors[$node] ?? []));
                }
            },
        ];
    }

    /**
     * The validated lag expressed in working minutes.
     */
    public function lagMinutes(): int
    {
        $days = $this->validated('lag_days');

        if ($days === null) {
            return 0;
        }

        return (int) round((float) $days * $this->project()->defaultCalendar()->minutes_per_day);
    }
}
