<?php

namespace Plugins\Stations\Services;

use Illuminate\Support\Facades\Http;
use Plugins\Stations\Support\BrazilianStates;
use Throwable;

/**
 * Fills station address fields from a ZIP code (ViaCEP) or coordinates
 * (OpenStreetMap/Nominatim).
 */
class StationAddressLookup
{
    /**
     * Look up an address by ZIP code.
     *
     * @return array<string, string|null>|null
     */
    public function fromZip(string $zip): ?array
    {
        $digits = preg_replace('/\D/', '', $zip) ?? '';

        if (strlen($digits) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout())
                ->connectTimeout(5)
                ->acceptJson()
                ->get($this->zipUrl($digits));
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || $response->json('erro') === true) {
            return null;
        }

        return [
            'zip' => $this->formatZip($digits),
            'street' => $this->stringOrNull($response->json('logradouro')),
            'number' => null,
            'complement' => $this->stringOrNull($response->json('complemento')),
            'neighborhood' => $this->stringOrNull($response->json('bairro')),
            'city' => $this->stringOrNull($response->json('localidade')),
            'state' => BrazilianStates::code($this->stringOrNull($response->json('uf'))),
        ];
    }

    /**
     * Look up an address by coordinates.
     *
     * @return array<string, mixed>|null
     */
    public function fromCoordinates(float $latitude, float $longitude): ?array
    {
        $address = [];

        try {
            $response = Http::timeout($this->timeout())
                ->connectTimeout(5)
                ->withHeaders([
                    'User-Agent' => (string) config('stations.lookup.user_agent', config('app.name')),
                ])
                ->acceptJson()
                ->get((string) config('stations.lookup.coordinates_url'), [
                    'format' => 'jsonv2',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'addressdetails' => 1,
                    'accept-language' => 'pt-BR',
                ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || ! is_array($response->json('address'))) {
            return null;
        }

        $address = (array) $response->json('address');

        return [
            'zip' => $this->formatZip((string) ($address['postcode'] ?? '')),
            'street' => $this->stringOrNull($address['road'] ?? null),
            'number' => $this->stringOrNull($address['house_number'] ?? null),
            'complement' => $this->stringOrNull($address['suburb'] ?? null),
            'neighborhood' => $this->stringOrNull($address['neighbourhood'] ?? $address['suburb'] ?? null),
            'city' => $this->stringOrNull($address['city'] ?? $address['town'] ?? $address['village'] ?? null),
            'state' => BrazilianStates::code($this->stringOrNull($address['state_code'] ?? $address['state'] ?? null)),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    private function zipUrl(string $digits): string
    {
        return str_replace('{zip}', $digits, (string) config('stations.lookup.zip_url'));
    }

    private function timeout(): int
    {
        return (int) config('stations.lookup.timeout', 10);
    }

    private function formatZip(string $zip): ?string
    {
        $digits = preg_replace('/\D/', '', $zip) ?? '';

        if (strlen($digits) !== 8) {
            return null;
        }

        return substr($digits, 0, 5).'-'.substr($digits, 5);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
