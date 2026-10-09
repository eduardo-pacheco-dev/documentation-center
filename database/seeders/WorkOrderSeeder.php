<?php

namespace Database\Seeders;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class WorkOrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The number of work orders seeded per user.
     */
    private const WORK_ORDER_COUNT = 8;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        foreach ($users as $user) {
            $clients = $user->clients()->orderBy('id')->get();

            if ($clients->isEmpty()) {
                continue;
            }

            $catalogItems = $user->catalogItems()->orderBy('id')->get();

            $erbs = $user->erbs()->orderBy('id')->get();

            foreach (range(1, self::WORK_ORDER_COUNT) as $index) {
                $this->seedWorkOrder($user, $clients, $catalogItems, $erbs, $index);
            }
        }
    }

    /**
     * Seed one work order owned by the given user.
     *
     * @param  Collection<int, Client>  $clients
     * @param  Collection<int, CatalogItem>  $catalogItems
     * @param  Collection<int, Erb>  $erbs
     */
    private function seedWorkOrder(User $owner, Collection $clients, Collection $catalogItems, Collection $erbs, int $index): void
    {
        $position = $index - 1;
        $status = WorkOrderStatus::cases()[$position % count(WorkOrderStatus::cases())];
        $openedAt = fake()->dateTimeBetween('-6 months', '-1 week')->format('Y-m-d');
        $dueAt = date('Y-m-d', strtotime($openedAt.' +14 days'));

        $workOrder = WorkOrder::create([
            'user_id' => $owner->getKey(),
            'client_id' => $clients->get($position % $clients->count())->getKey(),
            'erb_id' => $erbs->isEmpty() ? null : $erbs->get($position % $erbs->count())->getKey(),
            'number' => 'OS-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'title' => fake()->randomElement([
                'Manutenção elétrica', 'Instalação hidráulica', 'Reforma de escritório',
                'Pintura predial', 'Inspeção técnica', 'Montagem de mobiliário',
                'Consultoria de conformidade', 'Terreno e acabamento',
            ]),
            'description' => ucfirst(fake('pt_BR')->sentence(14)),
            'priority' => WorkOrderPriority::cases()[$position % count(WorkOrderPriority::cases())],
            'status' => $status,
            'opened_at' => $openedAt,
            'due_at' => $dueAt,
            'completed_at' => $status === WorkOrderStatus::Completed
                ? date('Y-m-d', strtotime($openedAt.' +10 days'))
                : null,
            'notes' => $position % 3 === 0 ? ucfirst(fake('pt_BR')->sentence(8)) : null,
        ]);

        $this->seedItems($workOrder, $catalogItems, $position);

        $workOrder->update(['total' => $workOrder->items()->sum('total')]);
    }

    /**
     * Seed the line items of the given work order.
     *
     * @param  Collection<int, CatalogItem>  $catalogItems
     */
    private function seedItems(WorkOrder $workOrder, Collection $catalogItems, int $position): void
    {
        if ($catalogItems->isEmpty()) {
            $workOrder->items()->create([
                'description' => ucfirst(fake('pt_BR')->sentence(4)),
                'unit' => fake()->randomElement(['un', 'h', 'm']),
                'quantity' => 1,
                'unit_price' => fake()->randomFloat(2, 100, 900),
                'total' => 0,
            ]);

            return;
        }

        $picked = $catalogItems->random(min(3, $catalogItems->count()))->values();

        foreach ($picked as $positionItem) {
            $quantity = random_int(1, 6);
            $unitPrice = (float) $positionItem->price;

            $workOrder->items()->create([
                'catalog_item_id' => $positionItem->getKey(),
                'description' => $positionItem->name,
                'unit' => $positionItem->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => round($quantity * $unitPrice, 2),
            ]);
        }
    }
}
