<?php

use App\Enums\TodoRepeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

uses(RefreshDatabase::class);

/**
 * Read the rows of a downloaded spreadsheet.
 *
 * @return array<int, array<int|string, mixed>>
 */
function todoExportWorkbookRows(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'todo-export').'.xlsx';
    file_put_contents($path, $content);

    $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray();

    @unlink($path);

    return $rows;
}

it('downloads the tasks of the current view as an excel file', function () {
    $user = User::factory()->create();
    $list = todoListFor($user, 'Trabalho');
    $task = todoTaskIn($list, [
        'title' => 'Enviar relatório',
        'due_at' => '2026-10-20',
        'is_important' => true,
        'my_day_on' => today()->toDateString(),
        'repeat_type' => TodoRepeat::Weekly,
        'notes' => 'Com anexos',
    ]);
    $task->steps()->create(['title' => 'Coletar dados', 'position' => 0, 'completed_at' => now()]);
    $task->steps()->create(['title' => 'Revisar', 'position' => 1]);

    $response = $this->actingAs($user)->get(route('admin.tasks.export', ['view' => 'my_day']));

    $response->assertOk();
    $response->assertDownload('tarefas.xlsx');

    [$heading, $row] = todoExportWorkbookRows($response->streamedContent());

    expect($heading)->toBe([
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
    ])
        ->and($row[0])->toBe('Enviar relatório')
        ->and($row[1])->toBe('Trabalho')
        ->and($row[2])->toBe('Pendente')
        ->and($row[3])->toBe('Sim')
        ->and($row[4])->toBe('Sim')
        ->and($row[5])->toBe('20/10/2026')
        ->and($row[7])->toBe(TodoRepeat::Weekly->label())
        ->and($row[8])->toBe('1/2')
        ->and($row[9])->toBe('Com anexos');
});

it('filters the export by the active list and search term', function () {
    $user = User::factory()->create();
    $list = todoListFor($user, 'Trabalho');
    $other = todoListFor($user, 'Casa');

    todoTaskIn($list, ['title' => 'Enviar relatório']);
    todoTaskIn($list, ['title' => 'Enviar relatório final']);
    todoTaskIn($list, ['title' => 'Comprar cimento']);
    todoTaskIn($other, ['title' => 'Enviar relatório de casa']);

    $rows = todoExportWorkbookRows(
        $this->actingAs($user)->get(route('admin.tasks.export', [
            'view' => 'list',
            'list' => $list->getKey(),
            'search' => 'relatório',
        ]))->streamedContent(),
    );

    expect($rows)->toHaveCount(3)
        ->and(collect($rows)->flatten()->contains('Comprar cimento'))->toBeFalse()
        ->and(collect($rows)->flatten()->contains('Enviar relatório de casa'))->toBeFalse();
});

it('exports the completed tasks of the completed view', function () {
    $user = User::factory()->create();
    $list = todoListFor($user);

    todoTaskIn($list, ['title' => 'Tarefa Finalizada', 'completed_at' => now()]);
    todoTaskIn($list, ['title' => 'Tarefa Pendente']);

    $rows = todoExportWorkbookRows(
        $this->actingAs($user)->get(route('admin.tasks.export', ['view' => 'completed']))->streamedContent(),
    );

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('Tarefa Finalizada')
        ->and($rows[1][2])->toBe('Concluída');
});

it('never exports tasks owned by another user', function () {
    $user = User::factory()->create();
    todoTaskIn(todoListFor($user), ['title' => 'Tarefa Visível']);

    $stranger = User::factory()->create();
    todoTaskIn(todoListFor($stranger), ['title' => 'Tarefa Alheia']);

    $rows = todoExportWorkbookRows(
        $this->actingAs($user)->get(route('admin.tasks.export', ['view' => 'all']))->streamedContent(),
    );

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('Tarefa Visível')
        ->and(collect($rows)->flatten()->contains('Tarefa Alheia'))->toBeFalse();
});

it('requires authentication to export tasks', function () {
    $this->get(route('admin.tasks.export'))->assertRedirect('/login');
});
