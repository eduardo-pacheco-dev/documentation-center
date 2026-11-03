<?php

namespace Plugins\Stations\Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Stations\Models\Station;

class StationSeeder extends Seeder
{
    /**
     * Seed the stations with the reference dataset.
     */
    public function run(): void
    {
        $rows = [
            [
                'site_id' => 'SPJAB051M',
                'endereco_id' => 'SPSPO_2360',
                'tipo_de_elemento' => 'MSAN',
                'tecnologia' => 'IP',
                'tipo_de_conexao' => null,
                'classificacao' => 'ACESSO',
                'detentor_area' => 'ELETROPAULO',
                'tipo_de_infra' => 'Greenfield',
                'tipo_de_ev' => null,
                'tipo_de_logradouro' => 'RUA',
                'logradouro' => 'LAS PALMAS',
                'numero' => '213',
                'complemento' => null,
                'bairro' => 'JABAQUARA',
                'municipio' => 'SAO PAULO',
                'estado' => 'SP',
                'cep' => '04339000',
                'regional' => 'TSP',
                'latitude' => '-23.665278',
                'longitude' => '-46.641111',
                'status' => 'Aquisitado',
                'tipo_da_torre' => null,
                'station_id' => 'SPJAB051M',
            ],
            [
                'site_id' => 'TESTEHOMOLOG',
                'endereco_id' => 'SPSPO_2360',
                'tipo_de_elemento' => 'ENODE B',
                'tecnologia' => 'LTE',
                'tipo_de_conexao' => null,
                'classificacao' => 'ACESSO',
                'detentor_area' => 'ELETROPAULO',
                'tipo_de_infra' => 'Greenfield',
                'tipo_de_ev' => null,
                'tipo_de_logradouro' => 'RUA',
                'logradouro' => 'LAS PALMAS',
                'numero' => '213',
                'complemento' => null,
                'bairro' => 'JABAQUARA',
                'municipio' => 'SAO PAULO',
                'estado' => 'SP',
                'cep' => '04339000',
                'regional' => 'TSP',
                'latitude' => '-23.665278',
                'longitude' => '-46.641111',
                'status' => 'Aquisitado',
                'tipo_da_torre' => null,
                'station_id' => 'SPJAB051M',
            ],
            [
                'site_id' => 'TESTEHOMOLOG',
                'endereco_id' => 'SPSPO_2360',
                'tipo_de_elemento' => 'ENODE B',
                'tecnologia' => 'LTE',
                'tipo_de_conexao' => null,
                'classificacao' => 'ACESSO',
                'detentor_area' => 'ELETROPAULO',
                'tipo_de_infra' => 'Greenfield',
                'tipo_de_ev' => null,
                'tipo_de_logradouro' => 'RUA',
                'logradouro' => 'LAS PALMAS',
                'numero' => '213',
                'complemento' => null,
                'bairro' => 'JABAQUARA',
                'municipio' => 'SAO PAULO',
                'estado' => 'SP',
                'cep' => '04339000',
                'regional' => 'TSP',
                'latitude' => '-23.665278',
                'longitude' => '-46.641111',
                'status' => 'Aquisitado',
                'tipo_da_torre' => null,
                'station_id' => 'SPJAB051M',
            ],
        ];

        if (Station::query()->exists()) {
            return;
        }

        foreach ($rows as $row) {
            Station::query()->create($row);
        }
    }
}
