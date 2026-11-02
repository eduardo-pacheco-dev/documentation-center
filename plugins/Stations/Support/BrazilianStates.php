<?php

namespace Plugins\Stations\Support;

class BrazilianStates
{
    /**
     * State names and ISO codes keyed by the two letter code.
     *
     * @var array<string, list<string>>
     */
    private const STATES = [
        'AC' => ['Acre'],
        'AL' => ['Alagoas'],
        'AP' => ['Amapá'],
        'AM' => ['Amazonas'],
        'BA' => ['Bahia'],
        'CE' => ['Ceará'],
        'DF' => ['Distrito Federal'],
        'ES' => ['Espírito Santo'],
        'GO' => ['Goiás'],
        'MA' => ['Maranhão'],
        'MT' => ['Mato Grosso'],
        'MS' => ['Mato Grosso do Sul'],
        'MG' => ['Minas Gerais'],
        'PA' => ['Pará'],
        'PB' => ['Paraíba'],
        'PR' => ['Paraná'],
        'PE' => ['Pernambuco'],
        'PI' => ['Piauí'],
        'RJ' => ['Rio de Janeiro'],
        'RN' => ['Rio Grande do Norte'],
        'RS' => ['Rio Grande do Sul'],
        'RO' => ['Rondônia'],
        'RR' => ['Roraima'],
        'SC' => ['Santa Catarina'],
        'SP' => ['São Paulo'],
        'SE' => ['Sergipe'],
        'TO' => ['Tocantins'],
    ];

    /**
     * Resolve the two letter code from a state name or ISO code.
     */
    public static function code(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtoupper(trim($value));

        if (isset(self::STATES[$normalized])) {
            return $normalized;
        }

        foreach (self::STATES as $code => $names) {
            foreach ($names as $name) {
                if (mb_strtoupper($name) === $normalized) {
                    return $code;
                }
            }
        }

        return null;
    }

    /**
     * The available codes.
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::STATES);
    }
}
