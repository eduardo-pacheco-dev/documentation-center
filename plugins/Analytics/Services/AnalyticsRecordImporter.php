<?php

namespace Plugins\Analytics\Services;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Plugins\Analytics\Models\AnalyticsRecord;
use Plugins\Analytics\Support\AnalyticsDatabase;
use Throwable;

/**
 * Imports the FAMs spreadsheet (OC, OS, data de criação, fornecedor, tipo de
 * projeto, etapa, elemento, endereço, node name, localização, operador...).
 *
 * The import is append-only: when a row differs from the latest version of the
 * same OC + OS, a new record is created so the change history is preserved.
 * Rows identical to the latest version are ignored.
 */
class AnalyticsRecordImporter
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created: int, updated: int, unchanged: int, skipped: int}
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): array
    {
        $operations = [];
        $operationIndexByKey = [];
        $errors = [];
        $skipped = 0;

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;

            $row = array_map(fn ($value): ?string => $this->normalize($value), $row);

            if ($this->isEmptyRow($row) || $this->isSummaryRow($row)) {
                $skipped++;

                continue;
            }

            $validator = Validator::make($row, $this->rules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Linha {$line}: {$message}";
                }

                continue;
            }

            $data = $validator->validated();

            $createdOn = $this->parseDate($data['data_criacao'] ?? null);

            if ($this->hasValue($data['data_criacao'] ?? null) && $createdOn === null) {
                $errors[] = "Linha {$line}: data de criação inválida (use dd/mm/aaaa).";

                continue;
            }

            $attributes = [
                'oc' => trim((string) $data['oc']),
                'os' => trim((string) $data['os']),
                'created_on' => $createdOn?->toDateString(),
                'supplier' => $data['fornecedor'] ?? null,
                'audit_supplier' => $data['fornecedor_fiscalizacao'] ?? null,
                'project_type' => $data['tipo_projeto'] ?? null,
                'process_stage' => $data['etapa_processo'] ?? null,
                'element_type' => $data['tipo_elemento'] ?? null,
                'address' => $data['endereco_de_atendimento'] ?? null,
                'city' => $data['cidade'] ?? null,
                'state' => isset($data['estado']) ? mb_strtoupper((string) $data['estado']) : null,
                'node_name' => $data['node_name'] ?? null,
                'location' => $data['localizacao'] ?? null,
                'operator' => $data['operador'] ?? null,
                'status' => $data['status'] ?? null,
            ];

            $key = mb_strtoupper($attributes['oc']).'|'.mb_strtoupper($attributes['os']);

            if (isset($operationIndexByKey[$key])) {
                $operations[$operationIndexByKey[$key]] = $attributes;

                continue;
            }

            $operations[] = $attributes;
            $operationIndexByKey[$key] = array_key_last($operations);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return AnalyticsDatabase::transaction(function () use ($user, $operations, $skipped): array {
            $created = 0;
            $updated = 0;
            $unchanged = 0;
            $inserts = [];

            foreach (array_chunk($operations, 250) as $chunk) {
                $latestByKey = $this->latestRecordsForChunk($user, $chunk);

                foreach ($chunk as $attributes) {
                    $key = mb_strtoupper($attributes['oc']).'|'.mb_strtoupper($attributes['os']);
                    $latest = $latestByKey[$key] ?? null;

                    if ($latest !== null && $this->isSameVersion($latest, $attributes)) {
                        $unchanged++;

                        continue;
                    }

                    $inserts[] = [
                        ...$attributes,
                        'user_id' => $user->getKey(),
                    ];

                    $latest === null ? $created++ : $updated++;

                    if (count($inserts) >= 500) {
                        $this->insertRecords($inserts);
                        $inserts = [];
                    }
                }
            }

            if ($inserts !== []) {
                $this->insertRecords($inserts);
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Fetch the latest stored version for each OC + OS pair in a chunk.
     *
     * @param  array<int, array<string, mixed>>  $operations
     * @return array<string, AnalyticsRecord>
     */
    private function latestRecordsForChunk(User $user, array $operations): array
    {
        $query = AnalyticsRecord::query()->where('user_id', $user->getKey());

        $query->where(function (Builder $query) use ($operations): void {
            foreach ($operations as $attributes) {
                $query->orWhere(function (Builder $query) use ($attributes): void {
                    $query->where('oc', $attributes['oc'])->where('os', $attributes['os']);
                });
            }
        });

        return $query
            ->orderBy('id')
            ->get()
            ->keyBy(fn (AnalyticsRecord $record): string => mb_strtoupper($record->oc).'|'.mb_strtoupper($record->os))
            ->all();
    }

    /**
     * Insert a batch of records, adding the timestamps manually.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    private function insertRecords(array $records): void
    {
        $timestamp = now();

        foreach ($records as &$record) {
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
        }

        unset($record);

        AnalyticsRecord::query()->insert($records);
    }

    /**
     * Whether the row has no value in any column.
     *
     * @param  array<string, ?string>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the row is an export summary/footer instead of a record.
     *
     * @param  array<string, ?string>  $row
     */
    private function isSummaryRow(array $row): bool
    {
        $status = trim((string) ($row['status'] ?? ''));

        return (bool) preg_match('/^(total|p[áa]gina)\b/iu', $status);
    }

    /**
     * Whether the imported attributes match the latest stored version.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function isSameVersion(AnalyticsRecord $latest, array $attributes): bool
    {
        $current = [
            'created_on' => $latest->created_on?->toDateString(),
            'supplier' => $latest->supplier,
            'audit_supplier' => $latest->audit_supplier,
            'project_type' => $latest->project_type,
            'process_stage' => $latest->process_stage,
            'element_type' => $latest->element_type,
            'address' => $latest->address,
            'city' => $latest->city,
            'state' => $latest->state,
            'node_name' => $latest->node_name,
            'location' => $latest->location,
            'operator' => $latest->operator,
            'status' => $latest->status,
        ];

        foreach ($current as $key => $value) {
            if (($attributes[$key] ?? null) !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * The validation rules applied to each spreadsheet row.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    private function rules(): array
    {
        return [
            'oc' => ['required', 'string', 'max:60'],
            'os' => ['required', 'string', 'max:60'],
            'data_criacao' => ['nullable'],
            'fornecedor' => ['nullable', 'string', 'max:255'],
            'fornecedor_fiscalizacao' => ['nullable', 'string', 'max:255'],
            'tipo_projeto' => ['nullable', 'string', 'max:255'],
            'etapa_processo' => ['nullable', 'string', 'max:255'],
            'tipo_elemento' => ['nullable', 'string', 'max:255'],
            'endereco_de_atendimento' => ['nullable', 'string', 'max:500'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'size:2'],
            'node_name' => ['nullable', 'string', 'max:255'],
            'localizacao' => ['nullable', 'string', 'max:255'],
            'operador' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
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
     * Normalize a spreadsheet cell, trimming strings, converting whole number
     * floats and turning empty cells into null.
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
