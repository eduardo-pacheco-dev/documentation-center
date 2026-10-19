<?php

namespace Database\Seeders;

use App\Enums\AgendaColor;
use App\Enums\WorkOrderStatus;
use App\Models\AgendaCalendar;
use App\Models\AgendaCalendarShare;
use App\Models\AgendaEvent;
use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Colaborador;
use App\Models\Document;
use App\Models\Erb;
use App\Models\Form;
use App\Models\Project;
use App\Models\RadioLink;
use App\Models\ShortLink;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

/**
 * Deterministic fixtures used by the Cypress end-to-end suite.
 *
 * Run through `npm run cypress:db` (after `migrate:fresh --seed`) so the specs
 * always find the records they depend on. Every account is created with the
 * factory default password: `password`.
 *
 * The dashboard counts are scoped to the signed-in user, so the records are
 * split between the administrator and the member: the administrator owns the
 * full dashboard data set and the member owns a single record per kind, which
 * keeps the scoping assertions unambiguous. Keep the numbers in sync with
 * `cypress/e2e/admin/dashboard.cy.js`.
 */
class CypressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrator = $this->createUser('Administrador Cypress', 'cypress.admin@example.com', isAdmin: true);

        $member = $this->createUser('Usuario Cypress', 'cypress.user@example.com');

        $this->createUser('Usuario Inativo Cypress', 'cypress.inactive@example.com', isActive: false);

        $this->seedAdministratorDashboard($administrator);

        $this->seedMemberDashboard($member);

        $this->seedWorkOrderClientOrders($administrator);

        $this->seedAgenda($administrator, $member);
    }

    /**
     * Populate the administrator dashboard with a known data set.
     */
    private function seedAdministratorDashboard(User $administrator): void
    {
        $clients = Client::factory()->count(2)->create(['user_id' => $administrator->getKey()]);

        // Its work order keeps the soft deleted client id, so the dashboard has
        // to fall back to the "Sem cliente" label.
        $trashedClient = Client::factory()->create(['user_id' => $administrator->getKey()]);
        $trashedClient->delete();

        $this->seedWorkOrders($administrator, $clients, $trashedClient);

        // Only the active project of the administrator is counted.
        $activeProject = Project::factory()->create(['user_id' => $administrator->getKey()]);
        Project::factory()->completed()->create(['user_id' => $administrator->getKey()]);

        Form::factory()->forProject($activeProject)->create();

        $erbs = Erb::factory()->count(4)->create(['user_id' => $administrator->getKey()]);

        RadioLink::factory()->count(2)->create([
            'user_id' => $administrator->getKey(),
            'erb_a_id' => $erbs->first()->getKey(),
            'erb_b_id' => $erbs->last()->getKey(),
        ]);

        CatalogItem::factory()->count(5)->create(['user_id' => $administrator->getKey()]);
        Colaborador::factory()->count(2)->create(['user_id' => $administrator->getKey()]);
        ShortLink::factory()->count(3)->create(['user_id' => $administrator->getKey()]);

        $this->seedDocuments($administrator);
    }

    /**
     * Create the administrator work orders.
     *
     * The records are spread over the last days so the "recent" panel has a
     * deterministic order: the newest work order is `OS-1007`.
     *
     * @param  Collection<int, Client>  $clients
     */
    private function seedWorkOrders(User $administrator, Collection $clients, Client $trashedClient): void
    {
        /** @var list<array{number: string, status: WorkOrderStatus, client: Client, daysAgo: int}> $fixtures */
        $fixtures = [
            ['number' => 'OS-1001', 'status' => WorkOrderStatus::Open, 'client' => $clients[0], 'daysAgo' => 7],
            ['number' => 'OS-1002', 'status' => WorkOrderStatus::Open, 'client' => $clients[1], 'daysAgo' => 6],
            ['number' => 'OS-1003', 'status' => WorkOrderStatus::InProgress, 'client' => $clients[0], 'daysAgo' => 5],
            ['number' => 'OS-1004', 'status' => WorkOrderStatus::Completed, 'client' => $clients[1], 'daysAgo' => 4],
            ['number' => 'OS-1005', 'status' => WorkOrderStatus::Completed, 'client' => $clients[0], 'daysAgo' => 3],
            ['number' => 'OS-1006', 'status' => WorkOrderStatus::Completed, 'client' => $trashedClient, 'daysAgo' => 2],
            ['number' => 'OS-1007', 'status' => WorkOrderStatus::Cancelled, 'client' => $clients[1], 'daysAgo' => 1],
        ];

        foreach ($fixtures as $fixture) {
            $workOrder = WorkOrder::factory()->create([
                'user_id' => $administrator->getKey(),
                'client_id' => $fixture['client']->getKey(),
                'number' => $fixture['number'],
                'status' => $fixture['status'],
                'total' => $fixture['daysAgo'] * 100,
            ]);

            $workOrder->forceFill(['created_at' => now()->subDays($fixture['daysAgo'])])->saveQuietly();
        }
    }

    /**
     * Create the administrator files.
     *
     * The dashboard lists the five most recent files, so the oldest fixture
     * (`arquivo-1.pdf`) must stay out of the panel.
     */
    private function seedDocuments(User $administrator): void
    {
        foreach (range(1, 6) as $index) {
            $document = Document::factory()->create([
                'user_id' => $administrator->getKey(),
                'original_name' => "arquivo-{$index}.pdf",
                'size' => $index * 1_000_000,
            ]);

            $document->forceFill(['created_at' => now()->subDays(7 - $index)])->saveQuietly();
        }
    }

    /**
     * Give the member a single record per dashboard counter.
     */
    private function seedMemberDashboard(User $member): void
    {
        $client = Client::factory()->create(['user_id' => $member->getKey()]);

        WorkOrder::factory()->create([
            'user_id' => $member->getKey(),
            'client_id' => $client->getKey(),
            'number' => 'OS-2001',
            'status' => WorkOrderStatus::Open,
            'total' => 500,
        ]);

        Document::factory()->create([
            'user_id' => $member->getKey(),
            'original_name' => 'arquivo-do-membro.pdf',
        ]);

        Erb::factory()->create(['user_id' => $member->getKey()]);

        ShortLink::factory()->create(['user_id' => $member->getKey()]);
    }

    /**
     * Attach the optional client OS fixture to the newest administrator work order.
     */
    private function seedWorkOrderClientOrders(User $administrator): void
    {
        $workOrder = WorkOrder::query()
            ->where('user_id', $administrator->getKey())
            ->where('number', 'OS-1007')
            ->first();

        if ($workOrder === null) {
            return;
        }

        $clientOrder = $workOrder->clientOrders()->create([
            'number' => 'OS-CLI-1007',
            'description' => 'OS do cliente vinculada ao item da OS.',
        ]);

        $workOrder->items()->create([
            'work_order_client_order_id' => $clientOrder->getKey(),
            'description' => 'Item com OS do cliente',
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
        ]);

        $workOrder->update([
            'client_os' => 'CLI-1007',
            'oc' => 'OC-1007',
        ]);
    }

    /**
     * Create the agenda fixtures used by the end-to-end specs.
     *
     * The administrator owns twelve calendars so the sidebar list has to load
     * its second page while scrolling. The member receives a read-only share of
     * the administrator main calendar.
     */
    private function seedAgenda(User $administrator, User $member): void
    {
        $calendars = [];

        foreach (range(1, 12) as $position) {
            $calendars[] = AgendaCalendar::factory()->create([
                'user_id' => $administrator->getKey(),
                'name' => $position === 1
                    ? 'Minha agenda'
                    : 'Calendário '.str_pad((string) $position, 2, '0', STR_PAD_LEFT),
                'description' => 'Calendário criado para os testes end-to-end.',
                'color' => AgendaColor::cases()[($position - 1) % count(AgendaColor::cases())],
                'is_default' => $position === 1,
                'is_visible' => true,
                'position' => $position,
            ]);
        }

        $mainCalendar = $calendars[0];

        AgendaEvent::factory()->forCalendar($mainCalendar)->create([
            'title' => 'Reunião Cypress',
            'description' => 'Evento determinístico usado pelos testes da agenda.',
            'location' => 'Sala Cypress',
            'start_at' => now()->setTime(10, 0),
            'end_at' => now()->setTime(11, 0),
        ]);

        $memberCalendar = AgendaCalendar::factory()->create([
            'user_id' => $member->getKey(),
            'name' => 'Agenda do membro',
            'description' => 'Agenda pessoal do usuário comum.',
            'color' => AgendaColor::Sage,
            'is_default' => true,
            'is_visible' => true,
            'position' => 1,
        ]);

        AgendaCalendarShare::factory()
            ->for($mainCalendar, 'calendar')
            ->for($member)
            ->create();

        AgendaEvent::factory()->forCalendar($memberCalendar)->create([
            'title' => 'Compromisso do membro',
            'start_at' => now()->setTime(14, 0),
            'end_at' => now()->setTime(15, 0),
        ]);
    }

    /**
     * Create a single fixture user.
     */
    private function createUser(
        string $name,
        string $email,
        bool $isAdmin = false,
        bool $isActive = true,
    ): User {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'is_admin' => $isAdmin,
            'is_active' => $isActive,
        ]);
    }
}
