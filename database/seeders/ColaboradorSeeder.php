<?php

namespace Database\Seeders;

use App\Enums\ColaboradorStatus;
use App\Enums\ContractRegime;
use App\Enums\Uf;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColaboradorSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The number of colaboradores to seed.
     */
    private const COLABORADOR_COUNT = 20;

    /**
     * The first names used to compose a colaborador name.
     *
     * @var list<string>
     */
    private const FIRST_NAMES = [
        'Ana', 'Bruno', 'Carla', 'Diego', 'Elisa', 'Felipe', 'Gabriela', 'Henrique', 'Isabela', 'João',
    ];

    /**
     * The last names used to compose a colaborador name.
     *
     * @var list<string>
     */
    private const LAST_NAMES = [
        'Silva', 'Souza', 'Oliveira', 'Santos', 'Pereira', 'Lima', 'Almeida', 'Rocha',
    ];

    /**
     * The roles assigned to the seeded colaboradores.
     *
     * @var list<string>
     */
    private const ROLES = [
        'Técnico N1', 'Técnico N2', 'Técnico de campo', 'Instalador', 'Supervisor',
        'Engenheiro de projetos', 'Engenheiro de telecomunicações', 'Analista de redes',
        'Coordenador de manutenção', 'Estagiário',
    ];

    /**
     * The regional assignments used for the seeded colaboradores.
     *
     * @var list<string>
     */
    private const REGIONALS = ['NO', 'NE', 'CO', 'SE', 'S'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach (range(1, self::COLABORADOR_COUNT) as $index) {
            $this->seedColaborador($users->get($index % $users->count()), $index);
        }
    }

    /**
     * Seed one colaborador owned by the given user.
     */
    private function seedColaborador(User $owner, int $index): void
    {
        $position = $index - 1;
        $firstName = self::FIRST_NAMES[$position % count(self::FIRST_NAMES)];
        $lastName = self::LAST_NAMES[intdiv($position, count(self::FIRST_NAMES)) % count(self::LAST_NAMES)];
        $uf = Uf::cases()[$position % count(Uf::cases())];
        $regimes = ContractRegime::cases();

        Colaborador::firstOrCreate(
            [
                'user_id' => $owner->getKey(),
                'document' => $this->document($index),
            ],
            [
                'name' => $firstName.' '.$lastName,
                'contract_regime' => $regimes[$position % count($regimes)]->value,
                'regional' => self::REGIONALS[$position % count(self::REGIONALS)],
                'uf' => $uf->value,
                'pis' => sprintf('%03d.%05d.%02d-%1d', 1 + $index, 10001 + $index, 10 + $position, $index % 10),
                'role' => self::ROLES[$position % count(self::ROLES)],
                'cnpj' => null,
                'rg' => (string) (100000 + $index),
                'rg_issuer' => 'SSP/'.$uf->value,
                'birth_date' => fake('pt_BR')->dateTimeBetween('-55 years', '-19 years')->format('Y-m-d'),
                'mother_name' => fake('pt_BR')->name('female'),
                'phone' => sprintf('(11) 9%04d-%04d', 1000 + $index, 2000 + $index),
                'email' => 'colaborador'.str_pad((string) $index, 3, '0', STR_PAD_LEFT).'@example.com',
                'status' => $index % 6 === 0 ? ColaboradorStatus::Inactive : ColaboradorStatus::Active,
                'notes' => $index % 4 === 0 ? fake('pt_BR')->sentence() : null,
            ],
        );
    }

    /**
     * The deterministic CPF-like document for the given index.
     */
    private function document(int $index): string
    {
        $digits = str_pad((string) $index, 11, '0', STR_PAD_LEFT);

        return substr($digits, 0, 3)
            .'.'.substr($digits, 3, 3)
            .'.'.substr($digits, 6, 3)
            .'-'.substr($digits, 9, 2);
    }
}
