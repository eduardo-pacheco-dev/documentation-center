<?php

namespace App\Exports;

use App\Models\TodoTask;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TodoTasksExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  Builder<TodoTask>  $query
     */
    public function __construct(private readonly Builder $query) {}

    /**
     * The tasks matching the current workspace view and filters.
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * The heading row of the spreadsheet.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Tarefa',
            'Lista',
            'Status',
            'Importante',
            'Meu Dia',
            'Vencimento',
            'Lembrete',
            'Repetição',
            'Etapas',
            'Anotações',
            'Concluída em',
            'Criada em',
        ];
    }

    /**
     * The values written for each task row.
     *
     * @return array<int, string>
     */
    public function map(mixed $task): array
    {
        $steps = $task->steps;
        $stepsDone = $steps->filter(fn ($step): bool => $step->isCompleted())->count();

        return [
            $task->title,
            $task->todoList?->name ?? '',
            $task->isCompleted() ? 'Concluída' : 'Pendente',
            $task->is_important ? 'Sim' : 'Não',
            $task->isMyDay() ? 'Sim' : 'Não',
            $task->due_at?->format('d/m/Y') ?? '',
            $task->remind_at?->format('d/m/Y H:i') ?? '',
            $task->repeat_type->label(),
            $steps->isEmpty() ? '' : $stepsDone.'/'.$steps->count(),
            $task->notes ?? '',
            $task->completed_at?->format('d/m/Y H:i') ?? '',
            $task->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
