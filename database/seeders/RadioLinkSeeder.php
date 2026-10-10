<?php

namespace Database\Seeders;

use App\Enums\RadioLinkPolarization;
use App\Enums\RadioLinkStatus;
use App\Models\Erb;
use App\Models\RadioLink;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
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

            $count = min(self::LINK_COUNT, intdiv($erbs->count(), 2));

            foreach (range(1, $count) as $index) {
                $endA = $erbs->get($index - 1);
                $endB = $erbs->get($index % $erbs->count());

                $this->seedLink($owner, $endA, $endB, $index);
            }
        }
    }

    /**
     * Seed one radio link owned by the given user.
     *
     * @param  Collection<int, Erb>  $erbs
     */
    private function seedLink(User $owner, Erb $endA, Erb $endB, int $index): void
    {
        RadioLink::create([
            'user_id' => $owner->getKey(),
            'erb_a_id' => $endA->getKey(),
            'erb_b_id' => $endB->getKey(),
            'code' => 'RL-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
            'equipment_a' => self::EQUIPMENT[$index % count(self::EQUIPMENT)],
            'equipment_b' => self::EQUIPMENT[($index + 1) % count(self::EQUIPMENT)],
            'frequency' => fake()->randomElement([7, 11, 15, 18, 23, 26, 32, 38]),
            'bandwidth' => fake()->randomElement([10, 20, 40, 60, 80]),
            'capacity' => fake()->randomElement([100, 250, 500, 1000]),
            'polarization' => fake()->randomElement(RadioLinkPolarization::cases()),
            'status' => match (true) {
                $index % 8 === 0 => RadioLinkStatus::Maintenance,
                $index % 6 === 0 => RadioLinkStatus::Inactive,
                $index % 4 === 0 => RadioLinkStatus::Planned,
                default => RadioLinkStatus::Active,
            },
            'notes' => $index % 3 === 0 ? fake('pt_BR')->sentence() : null,
        ]);
    }
}
