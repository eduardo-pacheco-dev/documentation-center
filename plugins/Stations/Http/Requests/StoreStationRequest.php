<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Plugins\Stations\Support\CoordinateParser;

class StoreStationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalize the CEP and coordinates before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(self::normalizeValues($this->all()));
    }

    /**
     * Normalize CEP and coordinate values.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function normalizeValues(array $values): array
    {
        if (filled($values['cep'] ?? null)) {
            $values['cep'] = preg_replace('/\D/', '', (string) $values['cep']);
        }

        foreach (['latitude', 'longitude'] as $coordinate) {
            if (! filled($values[$coordinate] ?? null)) {
                continue;
            }

            $decimal = CoordinateParser::toDecimal((string) $values[$coordinate]);

            if ($decimal !== null) {
                $values[$coordinate] = number_format($decimal, 6, '.', '');
            }
        }

        return $values;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::fieldRules();
    }

    /**
     * The validation rules shared with the bulk import.
     *
     * @return array<string, mixed>
     */
    public static function fieldRules(): array
    {
        return [
            'site_id' => ['required', 'string', 'max:100'],
            'endereco_id' => ['nullable', 'string', 'max:100'],
            'tipo_de_elemento' => ['nullable', 'string', 'max:100'],
            'tecnologia' => ['nullable', 'string', 'max:50'],
            'tipo_de_conexao' => ['nullable', 'string', 'max:100'],
            'classificacao' => ['nullable', 'string', 'max:100'],
            'detentor_area' => ['nullable', 'string', 'max:150'],
            'tipo_de_infra' => ['nullable', 'string', 'max:100'],
            'tipo_de_ev' => ['nullable', 'string', 'max:100'],
            'tipo_de_logradouro' => ['nullable', 'string', 'max:50'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:30'],
            'complemento' => ['nullable', 'string', 'max:150'],
            'bairro' => ['nullable', 'string', 'max:150'],
            'municipio' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:20'],
            'regional' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['nullable', 'string', 'max:50'],
            'tipo_da_torre' => ['nullable', 'string', 'max:100'],
            'station_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom validation messages for coordinates.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::fieldMessages();
    }

    /**
     * The custom validation messages shared with the bulk import.
     *
     * @return array<string, string>
     */
    public static function fieldMessages(): array
    {
        return [
            'latitude.numeric' => 'A latitude deve estar em graus. Use graus decimais (ex.: -23.665278) ou graus, minutos e segundos (ex.: 23°39\'55"S).',
            'latitude.between' => 'A latitude deve estar entre -90 e 90 graus.',
            'longitude.numeric' => 'A longitude deve estar em graus. Use graus decimais (ex.: -46.641111) ou graus, minutos e segundos (ex.: 46°38\'28"W).',
            'longitude.between' => 'A longitude deve estar entre -180 e 180 graus.',
        ];
    }
}
