<?php

namespace Database\Seeders;

use App\Enums\RadioLinkPolarization;
use App\Enums\RadioLinkStatus;
use App\Models\Erb;
use App\Models\RadioLink;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RadioLinkSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The maximum number of radio links to seed per user.
     */
    private const LINK_COUNT = 12;

    /**
     * The radio equipment models used to compose the ends.
     *
     * @var list<string>
     */
    private const EQUIPMENT = [
        'Ericsson MINI-LINK 6363',
        'Cambium PTP 820',
        'Ubiquiti AirFiber 60',
        'Huawei RTN 905',
        'Ceragon IP-20C',
        'Nokia Wavence',
    ];

    /**
     * The status rotation used so the seeded links cover every state.
     *
     * @var list<RadioLinkStatus>
     */
    private const STATUSES = [
        RadioLinkStatus::Active,
        RadioLinkStatus::Active,
        RadioLinkStatus::Planned,
        RadioLinkStatus::Active,
        RadioLinkStatus::Maintenance,
        RadioLinkStatus::Inactive,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        foreach ($users as $owner) {
            $erbs = $owner->erbs()->orderBy('id')->get();

            if ($erbs->count() < 2) {
                continue;
            }

            $count = min(self::LINK_COUNT, $erbs->count());

            foreach (range(1, $count) as $index) {
                $endA = $erbs->get($index - 1);
                $endB = $erbs->get($index % $erbs->count());

                $this->seedLink($owner, $endA, $endB, $index);
            }
        }
    }

    /**
     * Seed one radio link owned by the given user.
     */
    private function seedLink(User $owner, Erb $endA, Erb $endB, int $index): void
    {
        $code = 'RL-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT);

        RadioLink::firstOrCreate(
            [
                'user_id' => $owner->getKey(),
                'code' => $code,
            ],
            [
                'erb_a_id' => $endA->getKey(),
                'erb_b_id' => $endB->getKey(),
                'equipment_a' => self::EQUIPMENT[$index % count(self::EQUIPMENT)],
                'equipment_b' => self::EQUIPMENT[($index + 1) % count(self::EQUIPMENT)],
                'frequency' => fake()->randomElement([7, 11, 15, 18, 23, 26, 32, 38]),
                'bandwidth' => fake()->randomElement([10, 20, 40, 60, 80]),
                'capacity' => fake()->randomElement([100, 250, 500, 1000]),
                'polarization' => fake()->randomElement(RadioLinkPolarization::cases()),
                'status' => self::STATUSES[($index - 1) % count(self::STATUSES)],
                'notes' => $index % 3 === 0 ? fake('pt_BR')->sentence() : null,
            ],
        );
    }
}
