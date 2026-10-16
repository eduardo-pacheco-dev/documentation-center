<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Deterministic accounts used by the Cypress end-to-end suite.
 *
 * Run through `npm run cypress:db` (after `migrate:fresh --seed`) so the
 * authentication specs always find the accounts they depend on. Every account
 * is created with the factory default password: `password`.
 */
class CypressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createUser('Administrador Cypress', 'cypress.admin@example.com', isAdmin: true);

        $this->createUser('Usuario Cypress', 'cypress.user@example.com');

        $this->createUser('Usuario Inativo Cypress', 'cypress.inactive@example.com', isActive: false);
    }

    /**
     * Create a single fixture user.
     */
    private function createUser(
        string $name,
        string $email,
        bool $isAdmin = false,
        bool $isActive = true,
    ): void {
        User::factory()->create([
            'name' => $name,
            'email' => $email,
            'is_admin' => $isAdmin,
            'is_active' => $isActive,
        ]);
    }
}
