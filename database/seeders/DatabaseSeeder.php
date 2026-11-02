<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Plugins\Agenda\Database\Seeders\AgendaSeeder;
use Plugins\Catalog\Database\Seeders\CatalogItemSeeder;
use Plugins\Clients\Database\Seeders\ClientSeeder;
use Plugins\Colaboradores\Database\Seeders\ColaboradorSeeder;
use Plugins\Equipments\Database\Seeders\EquipmentSeeder;
use Plugins\Erbs\Database\Seeders\ErbSeeder;
use Plugins\Forms\Database\Seeders\FormResponseSeeder;
use Plugins\Forms\Database\Seeders\FormSeeder;
use Plugins\Links\Database\Seeders\ShortLinkSeeder;
use Plugins\Projects\Database\Seeders\ProjectSeeder;
use Plugins\RadioLinks\Database\Seeders\RadioLinkSeeder;
use Plugins\Tasks\Database\Seeders\TodoSeeder;
use Plugins\Templates\Database\Seeders\FormTemplateSeeder;
use Plugins\WorkOrders\Database\Seeders\WorkOrderSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(ProjectSeeder::class);
        $this->call(FormSeeder::class);
        $this->call(FormTemplateSeeder::class);
        $this->call(FormResponseSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(CatalogItemSeeder::class);
        $this->call(EquipmentSeeder::class);
        $this->call(ErbSeeder::class);
        $this->call(RadioLinkSeeder::class);
        $this->call(ColaboradorSeeder::class);
        $this->call(WorkOrderSeeder::class);
        $this->call(ShortLinkSeeder::class);
        $this->call(TodoSeeder::class);
        $this->call(AgendaSeeder::class);
    }
}
