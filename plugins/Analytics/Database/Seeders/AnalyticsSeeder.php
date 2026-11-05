<?php

declare(strict_types=1);

namespace Plugins\Analytics\Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Analytics\Models\AnalyticsRecord;

class AnalyticsSeeder extends Seeder
{
    /**
     * Seed the analytics records.
     */
    public function run(): void
    {
        AnalyticsRecord::query()->firstOrCreate(
            ['os' => 'OS-2026-0001'],
            [
                'oc' => 'OC-2026-0001',
                'data_criacao' => '2026-10-20',
                'fornecedor' => 'NOKIA',
                'fornecedor_fiscalizacao' => 'VANTAGE',
                'tipo_projeto' => 'Acesso',
                'etapa_processo' => 'Instalação',
                'tipo_elemento' => 'MISTO',
                'endereco_atendimento' => 'Rua das Telecomunicações, 100',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'node_name' => 'SN-BLMOK3',
                'localizacao' => 'PABLM_0037',
                'operador' => 'TIM',
                'status' => 'Aceito pela TIM',
            ],
        );

        if (AnalyticsRecord::query()->count() < 10) {
            AnalyticsRecord::factory()->count(20)->create();
        }
    }
}
