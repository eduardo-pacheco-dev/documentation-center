<?php

namespace Database\Seeders;

use App\Enums\ErbStatus;
use App\Models\Erb;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ErbSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The number of ERBs to seed.
     */
    private const ERB_COUNT = 24;

    /**
     * The mobile operators used to compose an ERB.
     *
     * @var list<string>
     */
    private const OPERATORS = ['Vivo', 'Claro', 'TIM', 'Oi', 'Algar', 'Sercomtel'];

    /**
     * The generations supported by the ERB sites.
     *
     * @var list<string>
     */
    private const TECHNOLOGIES = ['2G', '3G', '4G', '5G', '4G/5G'];

    /**
     * The cities used to compose an ERB address.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const CITIES = [
        ['São Paulo', 'SP'], ['Rio de Janeiro', 'RJ'], ['Belo Horizonte', 'MG'],
        ['Curitiba', 'PR'], ['Porto Alegre', 'RS'], ['Florianópolis', 'SC'],
        ['Salvador', 'BA'], ['Recife', 'PE'], ['Campinas', 'SP'],
        ['Brasília', 'DF'], ['Fortaleza', 'CE'], ['Manaus', 'AM'],
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

        foreach (range(1, self::ERB_COUNT) as $index) {
            $this->seedErb($users->get($index % $users->count()), $index);
        }

        $this->linkProjects($users);
    }

    /**
     * Seed one ERB owned by the given user.
     */
    private function seedErb(User $owner, int $index): void
    {
        $position = $index - 1;
        [$city, $state] = self::CITIES[$position % count(self::CITIES)];

        Erb::create([
            'user_id' => $owner->getKey(),
            'code' => 'ERB-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
            'name' => 'ERB '.$city.' '.intdiv($position, count(self::CITIES)) + 1,
            'operator' => self::OPERATORS[$position % count(self::OPERATORS)],
            'technology' => self::TECHNOLOGIES[$position % count(self::TECHNOLOGIES)],
            'status' => match (true) {
                $index % 7 === 0 => ErbStatus::Maintenance,
                $index % 5 === 0 => ErbStatus::Inactive,
                default => ErbStatus::Active,
            },
            'street' => 'Estrada '.fake()->streetName(),
            'number' => (string) random_int(100, 9000),
            'complement' => null,
            'neighborhood' => fake()->randomElement(['Centro', 'Distrito Industrial', 'Zona Norte', 'Zona Sul', 'Jardim Aeroporto']),
            'city' => $city,
            'state' => $state,
            'zip' => fake()->numerify('#####-###'),
            'latitude' => fake()->latitude(-33.75, 5.27),
            'longitude' => fake()->longitude(-73.98, -34.79),
            'notes' => $index % 4 === 0 ? fake('pt_BR')->sentence() : null,
        ]);
    }

    /**
     * Attach the users' existing projects to their ERBs.
     *
     * @param  Collection<int, User>  $users
     */
    private function linkProjects(Collection $users): void
    {
        foreach ($users as $owner) {
            $erbs = $owner->erbs()->orderBy('id')->get();

            if ($erbs->isEmpty()) {
                continue;
            }

            foreach ($owner->projects()->orderBy('id')->get() as $position => $project) {
                $project->update(['erb_id' => $erbs->get($position % $erbs->count())->getKey()]);
            }
        }
    }
}
