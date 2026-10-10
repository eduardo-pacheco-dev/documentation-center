<?php

namespace App\Services\Erbs;

use App\Enums\ErbStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ErbBulkImporter
{
    /**
     * Import the given rows as ERBs for the user.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int The number of ERBs created.
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): int
    {
        $existingCodes = $user->erbs()
            ->pluck('code')
            ->mapWithKeys(fn ($code): array => [mb_strtoupper(trim((string) $code)) => true]);

        $prepared = [];
        $errors = [];

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;

            $row = array_map(fn ($value): ?string => $this->normalize($value), $row);

            $validator = Validator::make($row, $this->rules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Linha {$line}: {$message}";
                }

                continue;
            }

            $data = $validator->validated();

            $code = mb_strtoupper(trim((string) $data['codigo']));

            if (isset($existingCodes[$code])) {
                $errors[] = "Linha {$line}: o código \"{$code}\" já está em uso.";

                continue;
            }

            $status = $this->parseStatus($data['status'] ?? null);

            if ($status === null) {
                $errors[] = "Linha {$line}: status inválido (use ativa, em manutenção ou desativada).";

                continue;
            }

            $existingCodes[$code] = true;

            $prepared[] = [
                'code' => $code,
                'name' => $data['nome'],
                'operator' => $data['operadora'] ?? null,
                'technology' => $data['tecnologia'] ?? null,
                'status' => $status->value,
                'street' => $data['logradouro'] ?? null,
                'number' => $data['numero'] ?? null,
                'complement' => $data['complemento'] ?? null,
                'neighborhood' => $data['bairro'] ?? null,
                'city' => $data['cidade'] ?? null,
                'state' => isset($data['estado']) ? mb_strtoupper((string) $data['estado']) : null,
                'zip' => $data['cep'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'notes' => $data['observacoes'] ?? null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return DB::transaction(function () use ($user, $prepared): int {
            foreach ($prepared as $attributes) {
                $user->erbs()->create($attributes);
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
            'codigo' => ['required', 'string', 'max:60'],
            'nome' => ['required', 'string', 'max:255'],
            'operadora' => ['nullable', 'string', 'max:80'],
            'tecnologia' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:9'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
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

    /**
     * Parse the status label or enum value from a spreadsheet cell.
     */
    private function parseStatus(mixed $value): ?ErbStatus
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return ErbStatus::Active;
        }

        return match ($value) {
            'ativa', 'ativo', 'active' => ErbStatus::Active,
            'em manutenção', 'em manutencao', 'maintenance' => ErbStatus::Maintenance,
            'desativada', 'desativado', 'inactive' => ErbStatus::Inactive,
            default => null,
        };
    }
}
