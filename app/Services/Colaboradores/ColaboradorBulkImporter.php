<?php

namespace App\Services\Colaboradores;

use App\Enums\ColaboradorStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ColaboradorBulkImporter
{
    /**
     * Import the given rows as colaboradores for the user.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int The number of colaboradores created.
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): int
    {
        $existingDocuments = $user->colaboradores()
            ->pluck('document')
            ->filter()
            ->mapWithKeys(fn ($document): array => [$this->normalizeDocument($document) => true]);

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

            $status = $this->parseStatus($data['status'] ?? null);

            if ($status === null) {
                $errors[] = "Linha {$line}: status inválido (use ativo ou inativo).";

                continue;
            }

            $document = isset($data['cpf']) ? $this->normalizeDocument($data['cpf']) : null;

            if ($document !== null && isset($existingDocuments[$document])) {
                $errors[] = "Linha {$line}: o CPF \"{$data['cpf']}\" já está em uso.";

                continue;
            }

            if ($document !== null) {
                $existingDocuments[$document] = true;
            }

            $prepared[] = [
                'name' => $data['nome'],
                'role' => $data['cargo'] ?? null,
                'document' => $document === null ? null : $this->formatCpf($document),
                'phone' => $data['telefone'] ?? null,
                'email' => $data['e_mail'] ?? null,
                'status' => $status->value,
                'notes' => $data['observacoes'] ?? null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return DB::transaction(function () use ($user, $prepared): int {
            foreach ($prepared as $attributes) {
                $user->colaboradores()->create($attributes);
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
            'cargo' => ['nullable', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'e_mail' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string'],
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
    private function parseStatus(mixed $value): ?ColaboradorStatus
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return ColaboradorStatus::Active;
        }

        return match ($value) {
            'ativo', 'active' => ColaboradorStatus::Active,
            'inativo', 'inactive' => ColaboradorStatus::Inactive,
            default => null,
        };
    }

    /**
     * The digits of the document, ignoring any mask characters.
     */
    private function normalizeDocument(mixed $value): string
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    /**
     * Format the CPF digits with the app's mask, when they form a valid CPF.
     */
    private function formatCpf(string $digits): string
    {
        if (strlen($digits) !== 11) {
            return $digits;
        }

        return substr($digits, 0, 3)
            .'.'.substr($digits, 3, 3)
            .'.'.substr($digits, 6, 3)
            .'-'.substr($digits, 9, 2);
    }
}
