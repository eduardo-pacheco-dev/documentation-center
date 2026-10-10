<?php

namespace App\Services\Colaboradores;

use App\Enums\ColaboradorStatus;
use App\Enums\ContractRegime;
use App\Enums\Uf;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

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

            $regime = $this->parseContractRegime($data['regime_de_contrato'] ?? null);

            if ($this->hasValue($data['regime_de_contrato'] ?? null) && $regime === null) {
                $errors[] = "Linha {$line}: regime de contrato inválido (use CLT, PJ, Estágio, Aprendiz ou Temporário).";

                continue;
            }

            $uf = $this->parseUf($data['uf'] ?? null);

            if ($this->hasValue($data['uf'] ?? null) && $uf === null) {
                $errors[] = "Linha {$line}: UF inválida (use a sigla com 2 letras, ex.: SP).";

                continue;
            }

            $birthDate = $this->parseDate($data['data_de_nascimento'] ?? null);

            if ($this->hasValue($data['data_de_nascimento'] ?? null) && $birthDate === null) {
                $errors[] = "Linha {$line}: data de nascimento inválida (use dd/mm/aaaa).";

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
                'contract_regime' => $regime?->value,
                'regional' => $data['regional'] ?? null,
                'uf' => $uf?->value,
                'pis' => $data['pis'] ?? null,
                'role' => $data['funcao'] ?? $data['cargo'] ?? null,
                'document' => $document === null ? null : $this->formatCpf($document),
                'cnpj' => $data['cnpj'] ?? null,
                'rg' => $data['rg'] ?? null,
                'rg_issuer' => $data['orgao_emissor'] ?? null,
                'birth_date' => $birthDate?->toDateString(),
                'mother_name' => $data['nome_da_mae'] ?? null,
                'phone' => $data['contato'] ?? $data['telefone'] ?? null,
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
            'regime_de_contrato' => ['nullable', 'string', 'max:30'],
            'regional' => ['nullable', 'string', 'max:50'],
            'uf' => ['nullable', 'string', 'max:2'],
            'pis' => ['nullable', 'string', 'max:20'],
            'funcao' => ['nullable', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:30'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'rg' => ['nullable', 'string', 'max:30'],
            'orgao_emissor' => ['nullable', 'string', 'max:50'],
            'data_de_nascimento' => ['nullable'],
            'nome_da_mae' => ['nullable', 'string', 'max:255'],
            'contato' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'e_mail' => ['nullable', 'email', 'max:255'],
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
     * Parse the contract regime label or enum value from a spreadsheet cell.
     */
    private function parseContractRegime(mixed $value): ?ContractRegime
    {
        $token = $this->token($value);

        if ($token === '') {
            return null;
        }

        foreach (ContractRegime::cases() as $case) {
            if ($this->token($case->value) === $token || $this->token($case->label()) === $token) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Parse the UF code from a spreadsheet cell.
     */
    private function parseUf(mixed $value): ?Uf
    {
        $code = mb_strtoupper(trim((string) $value));

        if ($code === '') {
            return null;
        }

        return Uf::tryFrom($code);
    }

    /**
     * Parse a date cell, accepting Excel serials and common string formats.
     */
    private function parseDate(mixed $value): ?Carbon
    {
        if (! $this->hasValue($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->startOfDay();
            } catch (Throwable) {
                return null;
            }
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (Throwable) {
                // Try the next format.
            }
        }

        return null;
    }

    /**
     * Normalize a value into a comparable, accent-free lowercase token.
     */
    private function token(mixed $value): string
    {
        return Str::slug(trim((string) $value), '');
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
