<?php

namespace App\Services\Clients;

use App\Enums\ClientStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClientBulkImporter
{
    /**
     * Import the given rows as clients for the user.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int The number of clients created.
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): int
    {
        $prepared = [];
        $errors = [];

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;

            $row = array_map(fn ($value): ?string => $this->normalize($value), $row);

            if ($this->hasValue($row['site'] ?? null) && ! preg_match('~^https?://~i', (string) $row['site'])) {
                $row['site'] = 'https://'.ltrim((string) $row['site'], '/');
            }

            if ($this->hasValue($row['uf'] ?? null)) {
                $row['uf'] = mb_strtoupper((string) $row['uf']);
            }

            $validator = Validator::make($row, $this->rules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Linha {$line}: {$message}";
                }

                continue;
            }

            $data = $validator->validated();

            $status = $this->parseStatus($data['status'] ?? null);

            if ($status === null) {
                $errors[] = "Linha {$line}: status inválido (use ativo ou inativo).";

                continue;
            }

            $prepared[] = [
                'name' => $data['nome'],
                'document' => $data['cpfcnpj'] ?? $data['cpf_cnpj'] ?? null,
                'email' => $data['e_mail'] ?? null,
                'phone' => $data['telefone'] ?? null,
                'website' => $data['site'] ?? null,
                'street' => $data['rua'] ?? null,
                'number' => $data['numero'] ?? null,
                'complement' => $data['complemento'] ?? null,
                'neighborhood' => $data['bairro'] ?? null,
                'city' => $data['cidade'] ?? null,
                'state' => $data['uf'] ?? null,
                'zip' => $data['cep'] ?? null,
                'status' => $status->value,
                'notes' => $data['observacoes'] ?? null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return DB::transaction(function () use ($user, $prepared): int {
            foreach ($prepared as $attributes) {
                $user->clients()->create($attributes);
            }

            return count($prepared);
        });
    }

    /**
     * The validation rules applied to each spreadsheet row.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    private function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpfcnpj' => ['nullable', 'string', 'max:20'],
            'cpf_cnpj' => ['nullable', 'string', 'max:20'],
            'e_mail' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'site' => ['nullable', 'url', 'max:255'],
            'rua' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:9'],
            'status' => ['nullable', 'string'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Whether the given cell contains a value.
     */
    private function hasValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    /**
     * Parse the status label or enum value from a spreadsheet cell.
     */
    private function parseStatus(mixed $value): ?ClientStatus
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return ClientStatus::Active;
        }

        return match ($value) {
            'ativo', 'active' => ClientStatus::Active,
            'inativo', 'inactive' => ClientStatus::Inactive,
            default => null,
        };
    }

    /**
     * Normalize a spreadsheet cell, trimming strings, unsetting floats that are
     * whole numbers and converting empty cells to null.
     */
    private function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) && $value === floor($value)) {
            $value = (int) $value;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
