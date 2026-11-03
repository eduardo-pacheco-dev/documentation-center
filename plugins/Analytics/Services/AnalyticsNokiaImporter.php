<?php

namespace Plugins\Analytics\Services;

use App\Models\User;
use Closure;
use Illuminate\Validation\ValidationException;
use Plugins\Analytics\Exceptions\AnalyticsImportCanceledException;
use Plugins\Analytics\Models\AnalyticsNokiaRecord;
use Plugins\Analytics\Support\AnalyticsDatabase;

/**
 * Imports the Nokia spreadsheet using its heading row.
 *
 * The complete layout is stored as JSON, while the columns used by the
 * dashboard are also extracted for filtering and indexing.
 */
class AnalyticsNokiaImporter
{
    /**
     * Columns extracted from the heading row for the dashboard.
     *
     * @var array<string, string>
     */
    private const COLUMNS = [
        'oc' => 'oc',
        'site_id' => 'site_id',
        'city' => 'cidade',
        'uf' => 'uf',
        'regional' => 'regional',
        'project' => 'projeto',
        'gap_status' => 'gap_status_automatico',
        'macro_status' => 'macro_status',
        'status_geral' => 'status_geral',
    ];

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

        foreach (array_values($rows) as $row) {
            $line = $state['line']++;
            $data = [];

            foreach ($row as $key => $value) {
                $data[(string) $key] = $this->normalize($value);
            }

            if ($this->rowIsEmpty($data)) {
                $state['skipped']++;

                continue;
            }

            $oc = $this->truncate($data['oc'] ?? null, 60);

            if ($oc === null) {
                $state['errors'][] = "Linha {$line}: OC é obrigatório.";

                continue;
            }

            $attributes = ['oc' => $oc, 'data' => $data];

            foreach (self::COLUMNS as $column => $key) {
                if ($column === 'oc') {
                    continue;
                }

                $attributes[$column] = $this->valueFor($column, $data[$key] ?? null);
            }

            $operations[$oc] = $attributes;
        }

        $state['processed'] += count($rows);

        $this->insertOperations($user, array_values($operations), $state, $shouldCancel);

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

            $latestByCode = $this->latestRecordsForChunk($user, $chunk);

            foreach ($chunk as $attributes) {
                $latest = $latestByCode[$attributes['oc']] ?? null;

                if ($latest !== null && $latest->data == $attributes['data']) {
                    $state['unchanged']++;

                    continue;
                }

                $inserts[] = [
                    ...$attributes,
                    'data' => json_encode($attributes['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'user_id' => $user->getKey(),
                ];

                $latest === null ? $state['created']++ : $state['updated']++;

                if (count($inserts) >= 250) {
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
     * Fetch the latest stored version for each OC in a chunk.
     *
     * @param  array<int, array<string, mixed>>  $operations
     * @return array<string, AnalyticsNokiaRecord>
     */
    private function latestRecordsForChunk(User $user, array $operations): array
    {
        $codes = array_values(array_unique(array_map(
            fn (array $attributes): string => (string) $attributes['oc'],
            $operations,
        )));

        return AnalyticsNokiaRecord::query()
            ->where('user_id', $user->getKey())
            ->whereIn('oc', $codes)
            ->orderBy('id')
            ->get()
            ->keyBy(fn (AnalyticsNokiaRecord $record): string => (string) $record->oc)
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

        AnalyticsNokiaRecord::query()->insert($records);
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
     * @param  array<string, ?string>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null) {
                return false;
            }
        }

        return true;
    }

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

    private function valueFor(string $column, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($column) {
            'uf' => mb_substr(mb_strtoupper($value), 0, 2) ?: null,
            'site_id' => $this->truncate($value, 100),
            'city' => $this->truncate($value, 150),
            'regional' => $this->truncate($value, 100),
            'project' => $this->truncate($value, 150),
            'gap_status' => $this->truncate($value, 150),
            'macro_status' => $this->truncate($value, 150),
            'status_geral' => $this->truncate($value, 150),
            default => $value,
        };
    }

    private function truncate(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr($value, 0, $length) ?: null;
    }
}
