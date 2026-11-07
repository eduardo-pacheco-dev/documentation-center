<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Plugins\FollowUp\Database\Seeders\FollowUpSeeder;
use Plugins\Nokia\Database\Seeders\NokiaRomSeeder;
use Plugins\Nokia\Database\Seeders\NokiaSiteSeeder;

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

        $this->call(DevDocSeeder::class);

        if (Schema::hasTable('follow_ups')) {
            $this->call(FollowUpSeeder::class);
        }

        if (Schema::hasTable('nokia_roms')) {
            $this->call(NokiaRomSeeder::class);
        }

        if (Schema::hasTable('nokia_sites')) {
            $this->call(NokiaSiteSeeder::class);
        }
    }
}
