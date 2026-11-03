<?php

namespace Plugins\Stations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Stations\Models\Station;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = Station::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $municipio = fake()->city();

        return [
            'site_id' => 'SP'.fake()->bothify('???###?'),
            'endereco_id' => 'SPSPO_'.fake()->numberBetween(1000, 9999),
            'tipo_de_elemento' => fake()->randomElement(['MSAN', 'ENODE B', 'ERB', 'DAS']),
            'tecnologia' => fake()->randomElement(['IP', 'LTE', '5G', 'GPON']),
            'tipo_de_conexao' => fake()->randomElement(['Fibra', 'Rádio', 'Metálico']),
            'classificacao' => 'ACESSO',
            'detentor_area' => fake()->randomElement(['ELETROPAULO', 'VIVO', 'CLARO']),
            'tipo_de_infra' => fake()->randomElement(['Greenfield', 'Rooftop', 'Indoor']),
            'tipo_de_ev' => null,
            'tipo_de_logradouro' => fake()->randomElement(['RUA', 'AVENIDA', 'TRAVESSA']),
            'logradouro' => fake()->streetName(),
            'numero' => (string) fake()->buildingNumber(),
            'complemento' => null,
            'bairro' => fake()->citySuffix(),
            'municipio' => $municipio,
            'estado' => 'SP',
            'cep' => fake()->numerify('########'),
            'regional' => 'TSP',
            'latitude' => '-23.'.fake()->numerify('######'),
            'longitude' => '-46.'.fake()->numerify('######'),
            'status' => fake()->randomElement(['Aquisitado', 'Em análise', 'Planejado']),
            'tipo_da_torre' => null,
            'station_id' => 'SP'.fake()->bothify('???###?'),
        ];
    }

    /**
     * Indicate that the station has been acquired.
     */
    public function acquired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'Aquisitado',
        ]);
    }
}
