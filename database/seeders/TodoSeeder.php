<?php

namespace Database\Seeders;

use App\Enums\TodoColor;
use App\Enums\TodoIcon;
use App\Enums\TodoRepeat;
use App\Models\TodoList;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The lists seeded for every user.
     *
     * @var list<array{name: string, color: TodoColor, icon: TodoIcon, tasks: list<array{title: string, due?: string, important?: bool, my_day?: bool, repeat?: TodoRepeat, notes?: string, completed?: bool, steps?: list<string>}>}>
     */
    private const LISTS = [
        [
            'name' => 'Tarefas',
            'color' => TodoColor::Indigo,
            'icon' => TodoIcon::ClipboardDocumentList,
            'tasks' => [
                ['title' => 'Revisar agenda da semana', 'due' => 'today', 'my_day' => true, 'steps' => ['Conferir compromissos', 'Confirmar reuniões']],
                ['title' => 'Responder e-mails pendentes', 'due' => 'today', 'my_day' => true],
                ['title' => 'Enviar relatório mensal', 'due' => 'tomorrow', 'important' => true],
                ['title' => 'Organizar a área de trabalho', 'due' => 'week'],
                ['title' => 'Atualizar senhas', 'repeat' => TodoRepeat::Monthly, 'notes' => 'Renovar as credenciais dos sistemas internos.'],
                ['title' => 'Planejar a próxima semana', 'due' => 'week', 'repeat' => TodoRepeat::Weekly],
            ],
        ],
        [
            'name' => 'Trabalho',
            'color' => TodoColor::Blue,
            'icon' => TodoIcon::Briefcase,
            'tasks' => [
                ['title' => 'Reunião de alinhamento com a equipe', 'due' => 'today', 'repeat' => TodoRepeat::Weekdays, 'my_day' => true],
                ['title' => 'Preparar apresentação do projeto', 'due' => 'tomorrow', 'important' => true, 'steps' => ['Coletar métricas', 'Montar slides', 'Revisar com o time']],
                ['title' => 'Revisar contrato do cliente', 'due' => 'week', 'important' => true],
                ['title' => 'Fechar apontamento de horas', 'due' => 'tomorrow', 'repeat' => TodoRepeat::Weekly],
                ['title' => 'Atualizar cronograma', 'due' => 'yesterday', 'notes' => 'Atrasado — ajustar as datas das entregas.'],
                ['title' => 'Enviar proposta comercial', 'completed' => true],
            ],
        ],
        [
            'name' => 'Casa',
            'color' => TodoColor::Teal,
            'icon' => TodoIcon::Home,
            'tasks' => [
                ['title' => 'Pagar as contas do mês', 'due' => 'today', 'repeat' => TodoRepeat::Monthly, 'my_day' => true],
                ['title' => 'Trocar o filtro da água', 'due' => 'month'],
                ['title' => 'Levar o cachorro para passear', 'due' => 'today', 'repeat' => TodoRepeat::Daily, 'my_day' => true],
                ['title' => 'Limpar a garagem', 'due' => 'week'],
                ['title' => 'Regar as plantas', 'completed' => true],
            ],
        ],
        [
            'name' => 'Compras',
            'color' => TodoColor::Amber,
            'icon' => TodoIcon::Cube,
            'tasks' => [
                ['title' => 'Comprar café e leite', 'due' => 'today', 'my_day' => true, 'steps' => ['Café', 'Leite', 'Pão']],
                ['title' => 'Comprar material de escritório', 'due' => 'week'],
                ['title' => 'Encomendar toners de impressora', 'due' => 'month'],
            ],
        ],
        [
            'name' => 'Projetos',
            'color' => TodoColor::Violet,
            'icon' => TodoIcon::Folder,
            'tasks' => [
                ['title' => 'Definir escopo do novo módulo', 'due' => 'tomorrow', 'important' => true, 'steps' => ['Levantar requisitos', 'Estimar esforço', 'Validar com o cliente']],
                ['title' => 'Desenhar o fluxo das telas', 'due' => 'week'],
                ['title' => 'Configurar o ambiente de homologação', 'due' => 'month'],
                ['title' => 'Escrever a documentação técnica', 'due' => 'month', 'repeat' => TodoRepeat::None],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        foreach ($users as $user) {
            $this->seedUser($user);
        }
    }

    /**
     * Seed the lists and tasks of the given user.
     */
    private function seedUser(User $user): void
    {
        foreach (self::LISTS as $position => $definition) {
            $list = TodoList::firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'name' => $definition['name'],
                ],
                [
                    'color' => $definition['color'],
                    'icon' => $definition['icon'],
                    'position' => $position,
                ],
            );

            foreach ($definition['tasks'] as $taskPosition => $task) {
                $this->seedTask($list, $task, $taskPosition);
            }
        }
    }

    /**
     * Seed one task of the given list.
     *
     * @param  array{title: string, due?: string, important?: bool, my_day?: bool, repeat?: TodoRepeat, notes?: string, completed?: bool, steps?: list<string>}  $definition
     */
    private function seedTask(TodoList $list, array $definition, int $position): void
    {
        $completed = $definition['completed'] ?? false;

        $task = TodoTask::firstOrCreate(
            [
                'todo_list_id' => $list->getKey(),
                'title' => $definition['title'],
            ],
            [
                'user_id' => $list->user_id,
                'notes' => $definition['notes'] ?? null,
                'due_at' => $this->resolveDue($definition['due'] ?? null),
                'repeat_type' => $definition['repeat'] ?? TodoRepeat::None,
                'is_important' => $definition['important'] ?? false,
                'my_day_on' => ($definition['my_day'] ?? false) ? today()->toDateString() : null,
                'completed_at' => $completed ? now() : null,
                'position' => $position,
            ],
        );

        foreach ($definition['steps'] ?? [] as $stepPosition => $title) {
            $task->steps()->firstOrCreate(
                ['title' => $title],
                ['position' => $stepPosition],
            );
        }
    }

    /**
     * Resolve a due date keyword into a stored date.
     */
    private function resolveDue(?string $due): ?string
    {
        return match ($due) {
            'yesterday' => today()->subDay()->toDateString(),
            'today' => today()->toDateString(),
            'tomorrow' => today()->addDay()->toDateString(),
            'week' => today()->addDays(5)->toDateString(),
            'month' => today()->addDays(20)->toDateString(),
            default => null,
        };
    }
}
