<?php

namespace Plugins\Nokia\Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Nokia\Models\NokiaRom;

class NokiaRomSeeder extends Seeder
{
    /**
     * Seed the ROMs with a sample dataset.
     */
    public function run(): void
    {
        if (NokiaRom::query()->exists()) {
            return;
        }

        $rows = [
            [
                'first_name' => 'Rafael',
                'last_name' => 'Almeida',
                'email' => 'rafael.almeida@example.com',
                'phone' => '(11) 98888-1010',
            ],
            [
                'first_name' => 'Camila',
                'last_name' => 'Ferreira',
                'email' => 'camila.ferreira@example.com',
                'phone' => '(11) 97777-2020',
            ],
            [
                'first_name' => 'Bruno',
                'last_name' => 'Carvalho',
                'email' => 'bruno.carvalho@example.com',
                'phone' => '(21) 96666-3030',
            ],
            [
                'first_name' => 'Fernanda',
                'last_name' => 'Lima',
                'email' => 'fernanda.lima@example.com',
                'phone' => '(31) 95555-4040',
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'Martins',
                'email' => 'diego.martins@example.com',
                'phone' => '(41) 94444-5050',
            ],
        ];

        foreach ($rows as $row) {
            NokiaRom::query()->create($row);
        }
    }
}
