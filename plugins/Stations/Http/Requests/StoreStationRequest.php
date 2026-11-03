<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
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
            'latitude' => ['nullable', 'string', 'max:30'],
            'longitude' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'max:50'],
            'tipo_da_torre' => ['nullable', 'string', 'max:100'],
            'station_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
