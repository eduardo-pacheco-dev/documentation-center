<?php

namespace Database\Seeders;

use App\Enums\CalendarType;
use App\Enums\DependencyType;
use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\ResourceType;
use App\Models\Calendar;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\Baseline\BaselineService;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\WbsCalculator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The number of projects to seed.
     */
    private const PROJECT_COUNT = 36;

    /**
     * The subjects used to compose a project name.
     *
     * @var list<string>
     */
    private const SUBJECTS = [
        'Construção de', 'Reforma de', 'Ampliação de', 'Implantação de', 'Modernização de', 'Obra de',
    ];

    /**
     * The objects used to compose a project name.
     *
     * @var list<string>
     */
    private const OBJECTS = [
        'edifício comercial', 'ponte urbana', 'pavilhão industrial', 'adutora', 'usina fotovoltaica',
        'galpão logístico', 'rede de fibra óptica', 'centro de distribuição',
    ];

    /**
     * The people that can be assigned to a plan, with their hourly rate range.
     *
     * @var array<string, array{0: int, 1: int}>
     */
    private const PEOPLE = [
        'Ana Beatriz Martins' => [70, 110],
        'Bruno Almeida Costa' => [60, 95],
        'Carla Nunes Ferreira' => [55, 85],
        'Diego Rocha Lima' => [65, 100],
        'Eduarda Castro Pinto' => [50, 80],
        'Fábio Moreira Gomes' => [75, 120],
        'Gabriela Santos Dias' => [45, 75],
        'Heitor Barbosa Nunes' => [60, 90],
        'Isabela Correia Rocha' => [70, 105],
        'João Vitor Mendes' => [55, 88],
        'Laura Antunes Teixeira' => [65, 98],
        'Marcos Vinícius Araújo' => [80, 130],
        'Natália Farias Cordeiro' => [50, 82],
        'Otávio Ramos Siqueira' => [72, 112],
        'Patrícia Lopes Vieira' => [58, 92],
        'Renato Carvalho Duarte' => [62, 96],
        'Sofia Brandão Nogueira' => [48, 78],
        'Tiago Moraes Cardoso' => [68, 102],
    ];

    /**
     * The equipment that can be charged to a plan, with its hourly rate range.
     *
     * @var array<string, array{0: int, 1: int}>
     */
    private const EQUIPMENT = [
        'Guindaste 30t' => [220, 320],
        'Retroescavadeira' => [130, 190],
        'Betoneira 400L' => [45, 70],
        'Andaime metálico' => [35, 60],
        'Caminhão basculante' => [150, 210],
        'Compressor de ar' => [40, 65],
    ];

    /**
     * The materials that can be bought, with their unit price range.
     *
     * @var array<string, array{0: int, 1: int}>
     */
    private const MATERIALS = [
        'Concreto usinado' => [280, 360],
        'Aço CA-50' => [90, 130],
        'Tijolos cerâmicos' => [80, 140],
        'Cimento CP-II' => [2800, 4200],
        'Cabo elétrico 10mm' => [450, 750],
        'Tinta acrílica' => [6000, 9500],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        if ($users->count() < 8) {
            User::factory()->count(8 - $users->count())->create();
            $users = User::query()->orderBy('id')->get();
        }

        foreach (range(1, self::PROJECT_COUNT) as $index) {
            $this->seedProject($users, $index);
        }
    }

    /**
     * Seed one project with a complete plan.
     *
     * @param  Collection<int, User>  $users
     */
    private function seedProject(Collection $users, int $index): void
    {
        $owner = $users->get($index % $users->count());
        $status = $this->statusFor($index);

        $startDate = now()->startOfMonth()
            ->subMonths($index % 9)
            ->addDays(($index * 3) % 20)
            ->toDateString();

        $project = Project::create([
            'user_id' => $owner->getKey(),
            'name' => $this->projectName($index),
            'description' => fake('pt_BR')->sentence(8),
            'status' => $status,
            'start_date' => $startDate,
            'priority' => random_int(100, 1000),
            'currency' => 'BRL',
            'budget' => 0,
        ]);

        Calendar::createDefault($project, CalendarType::Standard, 'Calendário padrão');

        [$leaves, $closing] = $this->createPlan($project, $index);
        $this->linkPlan($project, $leaves, $closing);
        $this->assignResources($project, $leaves);

        (new WbsCalculator)->recalculate($project);
        app(ProjectScheduler::class)->schedule($project);

        $this->recordProgress($project, $leaves, $status);
        app(ProjectScheduler::class)->schedule($project);

        $project->forceFill([
            'budget' => round($leaves->sum('budget_cost'), 2),
            'status_date' => $status === ProjectStatus::Active ? now()->toDateString() : $project->finish_date?->toDateString(),
        ])->save();

        $this->saveBaselines($project, $owner, $status);
        $this->addMembers($project, $owner, $users);
    }

    /**
     * Create the phase and task outline of the project.
     *
     * @return array{0: Collection<int, Task>, 1: ?Task} the leaves in plan order and the closing milestone
     */
    private function createPlan(Project $project, int $index): array
    {
        $phases = $this->phasesFor($index);
        $leaves = collect();
        $sortOrder = 0;
        $closing = null;

        foreach ($phases as $phaseName => $activities) {
            $phase = Task::query()->create([
                'project_id' => $project->getKey(),
                'parent_id' => null,
                'name' => $phaseName,
                'sort_order' => ++$sortOrder,
                'task_type' => 'fixed_duration',
                'scheduling_mode' => 'auto',
                'duration_minutes' => null,
                'priority' => 500,
            ]);

            foreach ($activities as $name => $days) {
                [$minDays, $maxDays] = $days;

                $leaf = Task::query()->create([
                    'project_id' => $project->getKey(),
                    'parent_id' => $phase->getKey(),
                    'name' => $name,
                    'sort_order' => ++$sortOrder,
                    'task_type' => 'fixed_duration',
                    'scheduling_mode' => 'auto',
                    'is_milestone' => false,
                    'duration_minutes' => random_int($minDays, $maxDays) * 480,
                    'priority' => random_int(100, 1000),
                    'percent_complete' => 0,
                    'budget_cost' => 0,
                ]);

                $leaves->push($leaf);
            }

            if (random_int(1, 100) <= 70) {
                $leaves->push(Task::query()->create([
                    'project_id' => $project->getKey(),
                    'parent_id' => $phase->getKey(),
                    'name' => $phaseName.' concluída',
                    'sort_order' => ++$sortOrder,
                    'task_type' => 'fixed_duration',
                    'scheduling_mode' => 'auto',
                    'is_milestone' => true,
                    'duration_minutes' => 0,
                    'priority' => 500,
                    'percent_complete' => 0,
                ]));
            }
        }

        if ($index % 3 === 0) {
            $closing = Task::query()->create([
                'project_id' => $project->getKey(),
                'parent_id' => null,
                'name' => 'Entrega final do projeto',
                'sort_order' => ++$sortOrder,
                'task_type' => 'fixed_duration',
                'scheduling_mode' => 'auto',
                'is_milestone' => true,
                'duration_minutes' => 0,
                'priority' => 500,
            ]);
        }

        return [$leaves, $closing];
    }

    /**
     * The outline of the project: phases with their activities and day ranges.
     *
     * @return array<string, array<string, array{0: int, 1: int}>>
     */
    private function phasesFor(int $index): array
    {
        $planning = [
            'Levantar requisitos e restrições' => [2, 5],
            'Elaborar escopo e cronograma' => [2, 6],
            'Detalhar orçamento' => [1, 4],
            'Contratar fornecedores e equipe' => [3, 8],
            'Aprovar plano de trabalho' => [1, 2],
        ];

        $execution = [
            'Mobilizar equipe e recursos' => [2, 5],
            'Executar as atividades principais' => [6, 15],
            'Acompanhar indicadores de desempenho' => [1, 3],
            'Gerenciar mudanças de escopo' => [2, 6],
            'Validar as entregas intermediárias' => [3, 7],
        ];

        $quality = [
            'Realizar inspeções e testes' => [2, 6],
            'Corrigir não conformidades' => [2, 5],
            'Validar entregas com o cliente' => [1, 4],
        ];

        $closure = [
            'Consolidar documentação técnica' => [2, 5],
            'Capacitar usuários ou operadores' => [2, 6],
            'Preparar laudo de conclusão' => [1, 3],
            'Desmobilizar equipes e equipamentos' => [1, 4],
        ];

        $phases = ['Planejamento' => $this->trim($planning), 'Execução' => $this->trim($execution)];

        $phases['Qualidade e controle'] = $this->trim($quality);

        if ($index % 4 !== 0) {
            $phases['Encerramento'] = $this->trim($closure);
        }

        return $phases;
    }

    /**
     * Keep only some of the activities, shortening a few of them randomly.
     *
     * @param  array<string, array{0: int, 1: int}>  $activities
     * @return array<string, array{0: int, 1: int}>
     */
    private function trim(array $activities): array
    {
        $kept = collect($activities)->take(random_int(3, count($activities)));

        return $kept->map(function (array $days): array {
            if (random_int(1, 100) > 80) {
                return [1, 3];
            }

            return $days;
        })->all();
    }

    /**
     * Link the leaf tasks into one sequential plan.
     *
     * @param  Collection<int, Task>  $leaves
     */
    private function linkPlan(Project $project, Collection $leaves, ?Task $closing): void
    {
        foreach ($leaves->slice(0, -1) as $position => $predecessor) {
            $successor = $leaves->get($position + 1);
            $this->link($project, $predecessor, $successor);
        }

        if ($closing !== null && $leaves->isNotEmpty()) {
            $this->link($project, $leaves->last(), $closing);
        }
    }

    /**
     * Link two tasks with a finish-to-start dependency and a possible lag.
     */
    private function link(Project $project, Task $predecessor, Task $successor): void
    {
        $type = match (random_int(1, 100)) {
            1 => DependencyType::StartToStart,
            2 => DependencyType::FinishToFinish,
            default => DependencyType::FinishToStart,
        };

        $lag = random_int(1, 100) <= 25 ? random_int(1, 2) * 480 : 0;

        TaskDependency::query()->create([
            'project_id' => $project->getKey(),
            'predecessor_id' => $predecessor->getKey(),
            'successor_id' => $successor->getKey(),
            'type' => $type,
            'lag_minutes' => $lag,
        ]);
    }

    /**
     * Create the resources and charge their cost to the leaf tasks.
     *
     * @param  Collection<int, Task>  $leaves
     */
    private function assignResources(Project $project, Collection $leaves): void
    {
        $resources = collect();
        $materialCount = 0;
        $people = collect(self::PEOPLE);
        $equipment = collect(self::EQUIPMENT);

        foreach ($people->keys()->shuffle()->take(random_int(3, 6)) as $name) {
            $resources->push($this->createResource($project, $name, ResourceType::Work, $people[$name]));
        }

        foreach ($equipment->keys()->shuffle()->take(random_int(0, 2)) as $name) {
            $resources->push($this->createResource($project, $name, ResourceType::Equipment, $equipment[$name]));
        }

        foreach ($leaves as $leaf) {
            if ($leaf->is_milestone) {
                continue;
            }

            $leafCost = 0;
            $leafWork = 0;

            foreach ($resources->shuffle()->take(random_int(1, 2)) as $resource) {
                $units = random_int(50, 150);
                $hours = $leaf->duration_minutes / 60;
                $cost = round($hours * $resource->cost_per_hour * $units / 100, 2);
                $workMinutes = (int) round($leaf->duration_minutes * $units / 100);

                ResourceAssignment::query()->create([
                    'task_id' => $leaf->getKey(),
                    'resource_id' => $resource->getKey(),
                    'units' => $units,
                    'work_minutes' => $workMinutes,
                    'cost' => $cost,
                ]);

                $leafCost += $cost;
                $leafWork += $workMinutes;
            }

            if (random_int(1, 100) <= 40) {
                [$costPerUnit, $quantity] = $this->materialBundle();
                $material = $this->createResource($project, $this->materialName(++$materialCount), ResourceType::Material, [0, 0]);
                $material->forceFill(['cost_per_unit' => $costPerUnit])->save();

                ResourceAssignment::query()->create([
                    'task_id' => $leaf->getKey(),
                    'resource_id' => $material->getKey(),
                    'units' => $quantity,
                    'work_minutes' => null,
                    'cost' => round($costPerUnit * $quantity, 2),
                ]);

                $leafCost += round($costPerUnit * $quantity, 2);
            }

            $leaf->forceFill([
                'budget_cost' => $leafCost,
                'work_minutes' => $leafWork,
            ])->save();
        }
    }

    /**
     * A name for the next material of the project.
     */
    private function materialName(int $count): string
    {
        return collect(self::MATERIALS)->keys()->random().' ('.$count.')';
    }

    /**
     * Create one project resource.
     *
     * @param  array{0: int, 1: int}  $rate
     */
    private function createResource(Project $project, string $name, ResourceType $type, array $rate): Resource
    {
        return Resource::query()->create([
            'project_id' => $project->getKey(),
            'name' => $name,
            'type' => $type,
            'code' => substr(mb_strtoupper(preg_replace('/\PL+/u', '', $name)), 0, 4),
            'max_units' => 100,
            'cost_per_hour' => random_int($rate[0], $rate[1]),
            'cost_per_unit' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * A plausible unit price and quantity for a material bundle.
     *
     * @return array{0: float, 1: int}
     */
    private function materialBundle(): array
    {
        $material = collect(self::MATERIALS)->keys()->random();
        [$min, $max] = self::MATERIALS[$material];

        return [random_int($min, $max) / 100, random_int(20, 400)];
    }

    /**
     * Record the progress and actual costs of the plan.
     *
     * @param  Collection<int, Task>  $leaves
     */
    private function recordProgress(Project $project, Collection $leaves, ProjectStatus $status): void
    {
        foreach ($leaves as $leaf) {
            $percent = match ($status) {
                ProjectStatus::Active => random_int(1, 100) <= 65 ? random_int(5, 90) : 0,
                ProjectStatus::Completed, ProjectStatus::Archived => 100,
            };

            if ($percent <= 0) {
                continue;
            }

            $leaf->forceFill([
                'percent_complete' => $percent,
                'actual_start_at' => $leaf->start_at ?? $project->start_date,
                'actual_finish_at' => $percent >= 100 ? ($leaf->finish_at ?? $project->finish_date) : null,
                'actual_duration_minutes' => $percent >= 100 ? $leaf->duration_minutes : null,
                'actual_cost' => round($leaf->budget_cost * $percent / 100, 2),
            ])->save();
        }
    }

    /**
     * Save one or two baselines for the project.
     */
    private function saveBaselines(Project $project, User $owner, ProjectStatus $status): void
    {
        $service = app(BaselineService::class);

        if (random_int(1, 100) <= 65) {
            $service->save($project, 'Baseline inicial', $owner);
        }

        if ($status === ProjectStatus::Completed && random_int(1, 100) <= 60) {
            $service->save($project, 'Baseline final', $owner);
        }
    }

    /**
     * Share the project with a few other users.
     *
     * @param  Collection<int, User>  $users
     */
    private function addMembers(Project $project, User $owner, Collection $users): void
    {
        if (random_int(1, 100) > 55) {
            return;
        }

        $candidates = $users->reject(fn (User $user) => $user->getKey() === $owner->getKey());

        foreach ($candidates->shuffle()->take(random_int(1, 3)) as $member) {
            ProjectMember::query()->firstOrCreate([
                'project_id' => $project->getKey(),
                'user_id' => $member->getKey(),
            ], [
                'role' => random_int(1, 100) <= 50 ? ProjectRole::Editor : ProjectRole::Viewer,
            ]);
        }
    }

    /**
     * A realistic project name that stays unique across the seeds.
     */
    private function projectName(int $index): string
    {
        $subject = self::SUBJECTS[($index - 1) % count(self::SUBJECTS)];
        $object = self::OBJECTS[($index - 1) % count(self::OBJECTS)];

        return $subject.' '.$object.' — Fase '.((intdiv($index - 1, 8)) % 4 + 1);
    }

    /**
     * The status of the project at the given index.
     */
    private function statusFor(int $index): ProjectStatus
    {
        if ($index <= 18) {
            return ProjectStatus::Active;
        }

        return $index <= 28 ? ProjectStatus::Completed : ProjectStatus::Archived;
    }
}
