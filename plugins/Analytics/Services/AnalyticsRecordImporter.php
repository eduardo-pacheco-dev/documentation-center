<?php

namespace Plugins\Analytics\Services;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Plugins\Analytics\Exceptions\AnalyticsImportCanceledException;
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
     * Start a chunked import and return its counters.
     *
     * @return array{created: int, updated: int, unchanged: int, skipped: int, processed: int, total: int, line: int, errors: array<int, string>}
     */
    public function beginImport(int $totalRows = 0): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0,
            'processed' => 0,
            'total' => max(0, $totalRows),
            'line' => 2,
            'errors' => [],
        ];
    }

    /**
     * Process one chunk of rows, keeping memory usage bounded.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array{created: int, updated: int, unchanged: int, skipped: int, processed: int, total: int, line: int, errors: array<int, string>}  $state
     * @param  (Closure(array{processed: int, total: int, created: int, updated: int, unchanged: int, skipped: int}): void)|null  $onProgress
     * @param  (Closure(): bool)|null  $shouldCancel
     *
     * @throws AnalyticsImportCanceledException
     */
    public function importChunk(
        User $user,
        array $rows,
        array &$state,
        ?Closure $onProgress = null,
        ?Closure $shouldCancel = null,
    ): void {
        if ($shouldCancel !== null && $shouldCancel()) {
            throw new AnalyticsImportCanceledException;
        }

        $operations = [];
        $operationIndexByKey = [];

        foreach (array_values($rows) as $row) {
            $line = $state['line']++;

            $row = array_map(fn ($value): ?string => $this->normalize($value), $row);

            if ($this->isEmptyRow($row) || $this->isSummaryRow($row)) {
                $state['skipped']++;

                continue;
            }

            $validator = Validator::make($row, $this->rules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $state['errors'][] = "Linha {$line}: {$message}";
                }

                continue;
            }

            $data = $validator->validated();

            $createdOn = $this->parseDate($data['data_criacao'] ?? null);

            if ($this->hasValue($data['data_criacao'] ?? null) && $createdOn === null) {
                $state['errors'][] = "Linha {$line}: data de criação inválida (use dd/mm/aaaa).";

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

        $state['processed'] += count($rows);

        $this->insertOperations($user, $operations, $state, $shouldCancel);

        $onProgress?->__invoke($this->progress($state));
    }

    /**
     * Finish a chunked import, throwing when any row was invalid.
     *
     * @param  array{created: int, updated: int, unchanged: int, skipped: int, processed: int, total: int, line: int, errors: array<int, string>}  $state
     * @return array{created: int, updated: int, unchanged: int, skipped: int, total: int}
     *
     * @throws ValidationException
     */
    public function finishImport(array $state): array
    {
        if ($state['errors'] !== []) {
            throw ValidationException::withMessages(['import' => $state['errors']]);
        }

        return [
            'created' => $state['created'],
            'updated' => $state['updated'],
            'unchanged' => $state['unchanged'],
            'skipped' => $state['skipped'],
            'total' => max($state['total'], $state['processed']),
        ];
    }

    /**
     * Import a full array of rows (small imports and tests).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  (Closure(array{processed: int, total: int, created: int, updated: int, unchanged: int, skipped: int}): void)|null  $onProgress
     * @param  (Closure(): bool)|null  $shouldCancel
     * @return array{created: int, updated: int, unchanged: int, skipped: int, total: int}
     *
     * @throws ValidationException
     * @throws AnalyticsImportCanceledException
     */
    public function import(User $user, array $rows, ?Closure $onProgress = null, ?Closure $shouldCancel = null): array
    {
        return AnalyticsDatabase::transaction(function () use ($user, $rows, $onProgress, $shouldCancel): array {
            $state = $this->beginImport(count($rows));

            $this->importChunk($user, $rows, $state, $onProgress, $shouldCancel);

            return $this->finishImport($state);
        });
    }

    /**
     * Insert the parsed operations, comparing them with the stored versions.
     *
     * @param  array<int, array<string, mixed>>  $operations
     * @param  array{created: int, updated: int, unchanged: int, skipped: int, processed: int, total: int, line: int, errors: array<int, string>}  $state
     * @param  (Closure(): bool)|null  $shouldCancel
     *
     * @throws AnalyticsImportCanceledException
     */
    private function insertOperations(User $user, array $operations, array &$state, ?Closure $shouldCancel): void
    {
        $inserts = [];

        foreach (array_chunk($operations, 250) as $chunk) {
            if ($shouldCancel !== null && $shouldCancel()) {
                throw new AnalyticsImportCanceledException;
            }

            $latestByKey = $this->latestRecordsForChunk($user, $chunk);

            foreach ($chunk as $attributes) {
                $key = mb_strtoupper($attributes['oc']).'|'.mb_strtoupper($attributes['os']);
                $latest = $latestByKey[$key] ?? null;

                if ($latest !== null && $this->isSameVersion($latest, $attributes)) {
                    $state['unchanged']++;

                    continue;
                }

                $inserts[] = [
                    ...$attributes,
                    'user_id' => $user->getKey(),
                ];

                $latest === null ? $state['created']++ : $state['updated']++;

                if (count($inserts) >= 500) {
                    $this->insertRecords($inserts);
                    $inserts = [];
                }
            }
        }

        if ($inserts !== []) {
            $this->insertRecords($inserts);
        }
    }

    /**
     * Build the progress payload for the status endpoint.
     *
     * @param  array{created: int, updated: int, unchanged: int, skipped: int, processed: int, total: int}  $state
     * @return array{processed: int, total: int, created: int, updated: int, unchanged: int, skipped: int}
     */
    private function progress(array $state): array
    {
        return [
            'processed' => $state['processed'],
            'total' => $state['total'],
            'created' => $state['created'],
            'updated' => $state['updated'],
            'unchanged' => $state['unchanged'],
            'skipped' => $state['skipped'],
        ];
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
