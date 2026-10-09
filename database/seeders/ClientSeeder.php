<?php

namespace Database\Seeders;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The number of clients to seed.
     */
    private const CLIENT_COUNT = 36;

    /**
     * The business segments used to compose a client name.
     *
     * @var list<string>
     */
    private const SEGMENTS = [
        'Tecnologia', 'Construção', 'Alimentos', 'Logística', 'Saúde', 'Educação',
        'Vestuário', 'Farmacêutica', 'Automotiva', 'Consultoria', 'Agronegócio', 'Energia',
    ];

    /**
     * The legal entity suffixes used to compose a client name.
     *
     * @var list<string>
     */
    private const SUFFIXES = [
        'do Brasil Ltda.', 'Comércio e Serviços S/A', 'Soluções Ltda.',
        'Indústria e Comércio Ltda.', 'Grupo S/A', 'Participações Ltda.',
    ];

    /**
     * The cities used to compose a client address.
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

        foreach (range(1, self::CLIENT_COUNT) as $index) {
            $this->seedClient($users->get($index % $users->count()), $index);
        }
    }

    /**
     * Seed one client owned by the given user.
     */
    private function seedClient(User $owner, int $index): void
    {
        $position = $index - 1;
        [$city, $state] = self::CITIES[$position % count(self::CITIES)];

        $client = Client::create([
            'user_id' => $owner->getKey(),
            'name' => sprintf(
                '%s %s',
                self::SEGMENTS[$position % count(self::SEGMENTS)],
                self::SUFFIXES[intdiv($position, count(self::SEGMENTS)) % count(self::SUFFIXES)],
            ),
            'document' => $position % 2 === 0
                ? fake('pt_BR')->unique()->cpf()
                : fake('pt_BR')->unique()->cnpj(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake('pt_BR')->phoneNumber(),
            'website' => 'https://'.fake()->domainName(),
            'street' => 'Rua '.fake()->streetName(),
            'number' => (string) random_int(10, 2500),
            'complement' => $position % 3 === 0 ? 'Sala '.random_int(1, 300) : null,
            'neighborhood' => fake()->randomElement(['Centro', 'Jardim Paulista', 'Vila Nova', 'Bela Vista', 'Savassi', 'Batel', 'Meireles', 'Boa Viagem', 'Asa Norte', 'Ponta Negra']),
            'city' => $city,
            'state' => $state,
            'zip' => fake()->numerify('#####-###'),
            'notes' => $position % 4 === 0 ? fake('pt_BR')->sentence() : null,
            'status' => $index % 5 === 0 ? ClientStatus::Inactive : ClientStatus::Active,
        ]);

        $this->seedContacts($client, $position);
    }

    /**
     * Seed a few contacts for the given client.
     */
    private function seedContacts(Client $client, int $position): void
    {
        foreach (range(1, $position % 3) as $index) {
            $client->contacts()->create([
                'name' => fake()->name(),
                'position' => fake()->randomElement(['Diretor', 'Gerente', 'Comprador', 'Financeiro', 'Comercial']),
                'phone' => fake('pt_BR')->phoneNumber(),
                'email' => fake()->unique()->safeEmail(),
                'is_primary' => $index === 1,
            ]);
        }
    }
}
