<?php

declare(strict_types=1);

namespace Plugins\Analytics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Analytics\Models\AnalyticsRecord;

/**
 * @extends Factory<AnalyticsRecord>
 */
class AnalyticsRecordFactory extends Factory
{
    /**
     * The model handled by the factory.
     */
    protected $model = AnalyticsRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = [
            'Aceito pela TIM',
            'Aguardando correção de pendência impeditiva via WFM',
            'Upload Arquivo',
            'Em execução',
            'Concluída',
            'Cancelada',
        ];

        return [
            'oc' => 'OC-'.fake()->numerify('#####'),
            'os' => 'OS-'.fake()->unique()->numerify('#######'),
            'data_criacao' => fake()->dateTimeBetween('-120 days')->format('Y-m-d'),
            'fornecedor' => fake()->randomElement(['NOKIA', 'HUAWEI', 'ERICSSON', 'SIEMENS']),
            'fornecedor_fiscalizacao' => fake()->optional()->randomElement(['VANTAGE', 'AXIROS', 'TIM']),
            'tipo_projeto' => fake()->randomElement(['Acesso', 'Backbone', 'Transporte']),
            'etapa_processo' => fake()->randomElement(['Desinstalação', 'Instalação', 'Entrega', 'Fiscalização']),
            'tipo_elemento' => fake()->randomElement(['MISTO', 'TORRE', 'SHELTER', 'ANTENA']),
            'endereco_atendimento' => fake()->streetAddress(),
            'cidade' => fake()->city(),
            'estado' => fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'BA']),
            'node_name' => 'SN-'.strtoupper(fake()->bothify('????##')),
            'localizacao' => strtoupper(fake()->bothify('PABLM_####')),
            'operador' => fake()->optional()->randomElement(['TIM', 'VIVO', 'CLARO']),
            'status' => fake()->randomElement($statuses),
        ];
    }
}
