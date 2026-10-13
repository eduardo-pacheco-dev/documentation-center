<?php

namespace App\Services\Erbs;

use App\Enums\Uf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ErbAddressLookup
{
    /**
     * Resolve an address from a Brazilian ZIP code using ViaCEP.
     *
     * @return array{zip: ?string, street: ?string, number: null, complement: ?string, neighborhood: ?string, city: ?string, state: ?string}|null
     */
    public function fromZip(string $zip): ?array
    {
        $digits = $this->digits($zip);

        if (strlen($digits) !== 8) {
            return null;
        }

        $response = Http::connectTimeout(5)
            ->timeout(10)
            ->accept('application/json')
            ->get("https://viacep.com.br/ws/{$digits}/json/");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data) || filter_var($data['erro'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        return [
            'zip' => $this->formatZip($data['cep'] ?? $digits),
            'street' => $this->value($data['logradouro'] ?? null),
            'number' => null,
            'complement' => $this->value($data['complemento'] ?? null),
            'neighborhood' => $this->value($data['bairro'] ?? null),
            'city' => $this->value($data['localidade'] ?? null),
            'state' => $this->value($data['uf'] ?? null),
        ];
    }

    /**
     * Resolve an address from coordinates using Nominatim (OpenStreetMap).
     *
     * @return array{zip: ?string, street: ?string, number: ?string, complement: null, neighborhood: ?string, city: ?string, state: ?string, latitude: float, longitude: float}|null
     */
    public function fromCoordinates(float $latitude, float $longitude): ?array
    {
        $response = Http::connectTimeout(5)
            ->timeout(10)
            ->accept('application/json')
            ->withHeaders(['User-Agent' => $this->userAgent()])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => 1,
                'accept-language' => 'pt-BR',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $address = is_array($data) ? ($data['address'] ?? null) : null;

        if (! is_array($address)) {
            return null;
        }

        return [
            'zip' => $this->formatZip($address['postcode'] ?? null),
            'street' => $this->value($address['road'] ?? $address['pedestrian'] ?? null),
            'number' => $this->value($address['house_number'] ?? null),
            'complement' => null,
            'neighborhood' => $this->value($address['suburb'] ?? $address['neighbourhood'] ?? $address['city_district'] ?? null),
            'city' => $this->value($address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null),
            'state' => $this->resolveState($address['state'] ?? null, $address['ISO3166-2-lvl4'] ?? null),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Resolve the two-letter state code from a state name or ISO 3166-2 code.
     */
    private function resolveState(?string $state, ?string $isoCode): ?string
    {
        if (is_string($isoCode) && preg_match('/-([A-Za-z]{2})$/', $isoCode, $matches) === 1) {
            return mb_strtoupper($matches[1]);
        }

        if (is_string($state) && $state !== '') {
            if (strlen($state) === 2) {
                return mb_strtoupper($state);
            }

            $normalized = Str::lower(Str::ascii($state));

            foreach (Uf::cases() as $uf) {
                if (Str::lower(Str::ascii($uf->label())) === $normalized) {
                    return $uf->value;
                }
            }
        }

        return null;
    }

    /**
     * Format a ZIP code as 00000-000.
     */
    private function formatZip(?string $zip): ?string
    {
        $digits = $this->digits((string) $zip);

        if (strlen($digits) !== 8) {
            return $this->value($zip);
        }

        return substr($digits, 0, 5).'-'.substr($digits, 5);
    }

    /**
     * Return the digits of the given value.
     */
    private function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * Trim a nullable value, converting empty strings to null.
     */
    private function value(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return ($value === null || $value === '') ? null : $value;
    }

    /**
     * The User-Agent header required by the Nominatim usage policy.
     */
    private function userAgent(): string
    {
        $configured = config('services.nominatim.user_agent');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return trim((string) config('app.name').' ('.(string) config('app.url').')');
    }
}
