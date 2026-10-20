<?php

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * The number of equipments to seed.
     */
    private const EQUIPMENT_COUNT = 24;

    /**
     * The equipment catalogue used to compose the registry.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private const EQUIPMENTS = [
        ['Rádio MikroTik NetMetal 5', 'MikroTik', 'NetMetal 5 ac', 'Rádio'],
        ['Rádio Ubiquiti AirFiber 5X HD', 'Ubiquiti', 'AF-5XHD', 'Rádio'],
        ['Rádio Cambium ePMP 3000', 'Cambium', 'ePMP 3000', 'Rádio'],
        ['Rádio Mimosa C5c', 'Mimosa', 'C5c', 'Rádio'],
        ['Rádio Ubiquiti LiteBeam 5AC', 'Ubiquiti', 'LBE-5AC-Gen2', 'Rádio'],
        ['Antena Setorial 18 dBi', 'ARG', 'AS-18-5', 'Antena'],
        ['Antena Parabólica 80 cm', 'Brasilsat', 'BP-80', 'Antena'],
        ['Antena Painel 5 GHz', 'Ubiquiti', 'AM-5G20', 'Antena'],
        ['Antena Grade 5 GHz', 'Intelbras', 'ANT-GRID-5', 'Antena'],
        ['Nobreak 3 kVA', 'APC', 'SMT3000', 'Fonte'],
        ['Fonte -48V 30A', 'Eltek', 'Flatpack 1500', 'Fonte'],
        ['Injetor PoE 48V', 'Ubiquiti', 'POE-48-24W', 'Fonte'],
        ['Controlador de Carga Solar', 'Epever', 'Tracer 4210AN', 'Controlador'],
        ['Switch 24 Portas Gigabit', 'Cisco', 'SG250-24', 'Switch'],
        ['Switch 48 Portas Gigabit', 'Cisco', 'SG250-48', 'Switch'],
        ['Switch PoE 8 Portas', 'TP-Link', 'TL-SG1008P', 'Switch'],
        ['Roteador CCR2004', 'MikroTik', 'CCR2004-1G-12S+2XS', 'Roteador'],
        ['Roteador Huawei AR617VW', 'Huawei', 'AR617VW', 'Roteador'],
        ['Bateria Estacionária 220Ah', 'Moura', 'M220', 'Bateria'],
        ['Bateria 12V 100Ah', 'Heliar', 'H100', 'Bateria'],
        ['Cabo Coaxial 1/2"', 'RFS', 'LCF12-50J', 'Cabo'],
        ['Patch Cord Óptico SC/APC', 'Furukawa', 'PC-SC-3M', 'Cabo'],
        ['Conector N Macho', 'RFS', 'N-M-12', 'Conector'],
        ['DIO 24 Portas', 'Furukawa', 'DIO-24', 'Acessório'],
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

        foreach (range(1, self::EQUIPMENT_COUNT) as $index) {
            $this->seedEquipment($users->get($index % $users->count()), $index);
        }
    }

    /**
     * Seed one equipment owned by the given user.
     */
    private function seedEquipment(User $owner, int $index): void
    {
        [$name, $manufacturer, $model, $category] = self::EQUIPMENTS[($index - 1) % count(self::EQUIPMENTS)];

        $status = match (true) {
            $index % 7 === 0 => EquipmentStatus::Maintenance,
            $index % 5 === 0 => EquipmentStatus::Inactive,
            default => EquipmentStatus::Active,
        };

        Equipment::firstOrCreate(
            [
                'user_id' => $owner->getKey(),
                'name' => $name,
            ],
            [
                'manufacturer' => $manufacturer,
                'model' => $model,
                'category' => $category,
                'description' => ucfirst(fake('pt_BR')->sentence(12)),
                'notes' => $index % 4 === 0 ? ucfirst(fake('pt_BR')->sentence(8)) : null,
                'status' => $status,
            ],
        );
    }
}
