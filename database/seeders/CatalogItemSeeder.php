<?php

namespace Database\Seeders;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemType;
use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogItemSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The number of catalog items to seed.
     */
    private const ITEM_COUNT = 30;

    /**
     * The products used to compose the catalog.
     *
     * @var list<array{0: string, 1: string, 2: string}>
     */
    private const PRODUCTS = [
        ['Cimento CP-II 50kg', 'un', 'SC-1001'],
        ['Areia média lavada', 'm³', 'SC-1002'],
        ['Tijolo baiano 9 furos', 'un', 'SC-1003'],
        ['Telha de concreto', 'un', 'SC-1004'],
        ['Fio elétrico 2,5mm', 'm', 'SC-1005'],
        ['Tinta acrílica branca 18L', 'l', 'SC-1006'],
        ['Tubo PVC 100mm', 'm', 'SC-1007'],
        ['Argamassa AC-II 20kg', 'un', 'SC-1008'],
        ['Chapa de aço 1mm', 'm²', 'SC-1009'],
        ['Porcelanato 90x90', 'm²', 'SC-1010'],
        ['Madeira pinus tratada', 'm', 'SC-1011'],
        ['Impermeabilizante 18L', 'l', 'SC-1012'],
    ];

    /**
     * The services used to compose the catalog.
     *
     * @var list<array{0: string, 1: string, 2: string}>
     */
    private const SERVICES = [
        ['Projeto arquitetônico', 'h', 'SV-2001'],
        ['Consultoria estrutural', 'h', 'SV-2002'],
        ['Instalação elétrica', 'h', 'SV-2003'],
        ['Manutenção hidráulica', 'h', 'SV-2004'],
        ['Pintura predial', 'm²', 'SV-2005'],
        ['Levantamento topográfico', 'h', 'SV-2006'],
        ['Gerenciamento de obras', 'h', 'SV-2007'],
        ['Laudo técnico de vistoria', 'un', 'SV-2008'],
        ['Terraplenagem', 'm³', 'SV-2009'],
        ['Paisagismo', 'm²', 'SV-2010'],
        ['Consultoria de sustentabilidade', 'h', 'SV-2011'],
        ['Regularização de imóveis', 'un', 'SV-2012'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach (range(1, self::ITEM_COUNT) as $index) {
            $this->seedItem($users->get($index % $users->count()), $index);
        }
    }

    /**
     * Seed one catalog item owned by the given user.
     */
    private function seedItem(User $owner, int $index): void
    {
        $position = $index - 1;
        $isProduct = $position % 2 === 0;

        $catalog = $isProduct ? self::PRODUCTS : self::SERVICES;
        [$name, $unit, $code] = $catalog[intdiv($position, 2) % count($catalog)];

        $cost = fake()->randomFloat(2, 20, 3000);

        CatalogItem::create([
            'user_id' => $owner->getKey(),
            'type' => $isProduct ? CatalogItemType::Product : CatalogItemType::Service,
            'name' => $name,
            'code' => $code,
            'unit' => $unit,
            'cost' => $cost,
            'price' => round($cost * fake()->randomFloat(2, 1.2, 2.5), 2),
            'stock_quantity' => $isProduct ? random_int(0, 500) : null,
            'description' => ucfirst(fake('pt_BR')->sentence(12)),
            'notes' => $position % 4 === 0 ? ucfirst(fake('pt_BR')->sentence(8)) : null,
            'status' => $index % 5 === 0 ? CatalogItemStatus::Inactive : CatalogItemStatus::Active,
        ]);
    }
}
