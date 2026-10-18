<?php

namespace App\Services\Erbs;

use App\Enums\ErbOperator;
use App\Enums\ErbStatus;
use App\Enums\ErbTechnology;
use App\Models\Erb;
use App\Models\User;
use BackedEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ErbBulkImporter
{
    /**
     * Import the given rows as ERBs for the user, updating the ERB that already
     * owns a given site ID instead of rejecting it.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int The number of ERBs created or updated.
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): int
    {
        $existingErbs = $user->erbs()
            ->whereNotNull('site_id')
            ->get()
            ->keyBy(fn (Erb $erb): string => mb_strtoupper((string) $erb->site_id));

        /** @var array<int, array{erb: Erb|null, attributes: array<string, mixed>}> $operations */
        $operations = [];
        $operationIndexBySiteId = [];
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

            $siteId = $data['site_id'] ?? null;

            $status = $this->parseStatus($data['status'] ?? null);

            if ($status === null) {
                $errors[] = "Linha {$line}: status inválido (use ativa, em manutenção ou desativada).";

                continue;
            }

            $operator = $data['operadora'] ?? null;
            $technology = $data['tecnologia'] ?? null;
            $invalid = false;

            if ($operator !== null) {
                $operator = $this->matchEnumValue($operator, ErbOperator::cases());

                if ($operator === null) {
                    $errors[] = "Linha {$line}: operadora inválida (use {$this->options(ErbOperator::cases())}).";
                    $invalid = true;
                }
            }

            if ($technology !== null) {
                $technology = $this->matchEnumValue($technology, ErbTechnology::cases());

                if ($technology === null) {
                    $errors[] = "Linha {$line}: tecnologia inválida (use {$this->options(ErbTechnology::cases())}).";
                    $invalid = true;
                }
            }

            if ($invalid) {
                continue;
            }

            $startDate = $this->parseDate($data['inicio'] ?? null);
            $endDate = $this->parseDate($data['termino'] ?? null);

            if ($this->hasValue($data['inicio'] ?? null) && $startDate === null) {
                $errors[] = "Linha {$line}: data de início inválida (use dd/mm/aaaa).";

                continue;
            }

            if ($this->hasValue($data['termino'] ?? null) && $endDate === null) {
                $errors[] = "Linha {$line}: data de término inválida (use dd/mm/aaaa).";

                continue;
            }

            if ($startDate !== null && $endDate !== null && $endDate->lt($startDate)) {
                $errors[] = "Linha {$line}: a data de término deve ser posterior à data de início.";

                continue;
            }

            $attributes = [
                'site_id' => $siteId,
                'end_id' => $data['end_id'] ?? null,
                'operator' => $operator,
                'technology' => $technology,
                'nokia_smp_id' => $data['nokia_smp_id'] ?? null,
                'status' => $status->value,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
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

            if ($siteId !== null) {
                $siteIdKey = mb_strtoupper($siteId);

                if (isset($operationIndexBySiteId[$siteIdKey])) {
                    $operations[$operationIndexBySiteId[$siteIdKey]]['attributes'] = $attributes;

                    continue;
                }

                if ($existingErbs->has($siteIdKey)) {
                    $operations[] = ['erb' => $existingErbs->get($siteIdKey), 'attributes' => $attributes];
                    $operationIndexBySiteId[$siteIdKey] = array_key_last($operations);

                    continue;
                }

                $operations[] = ['erb' => null, 'attributes' => $attributes];
                $operationIndexBySiteId[$siteIdKey] = array_key_last($operations);

                continue;
            }

            $operations[] = ['erb' => null, 'attributes' => $attributes];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return DB::transaction(function () use ($user, $operations): int {
            foreach ($operations as $operation) {
                $erb = $operation['erb'];

                if ($erb instanceof Erb) {
                    $erb->fill($operation['attributes'])->save();
                } else {
                    $user->erbs()->create($operation['attributes']);
                }
            }

            return count($operations);
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
            'site_id' => ['nullable', 'string', 'max:255', 'required_without:end_id'],
            'end_id' => ['nullable', 'string', 'max:60', 'required_without:site_id'],
            'operadora' => ['nullable', 'string', 'max:80'],
            'tecnologia' => ['nullable', 'string', 'max:40'],
            'nokia_smp_id' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', 'string'],
            'inicio' => ['nullable'],
            'termino' => ['nullable'],
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
     * Whether the given cell contains a value.
     */
    private function hasValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
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

                if ($date !== false && $date->format($format) === $value) {
                    return $date->startOfDay();
                }
            } catch (Throwable) {
                // Try the next format.
            }
        }

        return null;
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

    /**
     * Resolve the canonical enum value from a spreadsheet cell, ignoring case.
     *
     * @param  array<int, BackedEnum>  $cases
     */
    private function matchEnumValue(string $value, array $cases): ?string
    {
        $needle = mb_strtolower($value);

        foreach ($cases as $case) {
            if (mb_strtolower((string) $case->value) === $needle) {
                return (string) $case->value;
            }
        }

        return null;
    }

    /**
     * Build a comma-separated list of the allowed enum values.
     *
     * @param  array<int, BackedEnum>  $cases
     */
    private function options(array $cases): string
    {
        return implode(', ', array_map(static fn (BackedEnum $case): string => (string) $case->value, $cases));
    }
}
