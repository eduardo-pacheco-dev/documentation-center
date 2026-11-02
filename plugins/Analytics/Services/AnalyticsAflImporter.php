<?php

namespace Plugins\Analytics\Services;

use App\Models\User;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Plugins\Analytics\Models\AnalyticsAflRecord;
use Plugins\Analytics\Support\AnalyticsDatabase;
use Throwable;

/**
 * Imports the AFL spreadsheet using its positional layout (108 columns).
 *
 * Like the FAMs import, it is append-only: rows identical to the latest
 * version of the same Cód_AFL are skipped, while any change appends a new
 * version so the status history is preserved.
 */
class AnalyticsAflImporter
{
    /**
     * The spreadsheet columns, in the order of the official layout.
     *
     * @var list<string>
     */
    public const FIELDS = [
        'code',
        'general_status',
        'site_id_a',
        'end_id_a',
        'site_id_b',
        'end_id_b',
        'project',
        'description',
        'supervisor',
        'coordinator',
        'oc_tim',
        'chave_mw',
        'smp_nokia',
        'general_notes',
        'activation_registered_on',
        'boq_status',
        'request_date',
        'created_on',
        'boq_number',
        'boq_notes',
        'access_status',
        'access_request_a',
        'access_approval_a',
        'access_start_a',
        'access_end_a',
        'crq_a',
        'crq_start_a',
        'crq_end_a',
        'access_request_b',
        'access_approval_b',
        'access_start_b',
        'access_end_b',
        'crq_b',
        'crq_start_b',
        'crq_end_b',
        'access_notes',
        'mos_status',
        'mos_planned_on',
        'mos_actual_on',
        'mos_notes',
        'activity_status',
        'activity_responsible_a',
        'activity_planned_start_a',
        'activity_actual_start_a',
        'activity_planned_end_a',
        'activity_actual_end_a',
        'activity_responsible_b',
        'activity_planned_start_b',
        'activity_actual_start_b',
        'activity_planned_end_b',
        'activity_actual_end_b',
        'activity_notes',
        'activation_status',
        'activation_responsible_a',
        'activation_planned_start_a',
        'activation_actual_start_a',
        'activation_planned_end_a',
        'activation_actual_end_a',
        'activation_responsible_b',
        'activation_planned_start_b',
        'activation_actual_start_b',
        'activation_planned_end_b',
        'activation_actual_end_b',
        'activation_notes',
        'documentation_status',
        'documentation_responsible_a',
        'documentation_planned_start_a',
        'documentation_actual_start_a',
        'documentation_planned_end_a',
        'documentation_actual_end_a',
        'documentation_sent_a',
        'documentation_responsible_b',
        'documentation_planned_start_b',
        'documentation_actual_start_b',
        'documentation_planned_end_b',
        'documentation_actual_end_b',
        'documentation_sent_b',
        'documentation_directory',
        'documentation_notes',
        'acceptance_status',
        'acceptance_responsible_a',
        'acceptance_planned_start_a',
        'acceptance_actual_start_a',
        'acceptance_planned_end_a',
        'acceptance_actual_end_a',
        'acceptance_responsible_b',
        'acceptance_planned_start_b',
        'acceptance_actual_start_b',
        'acceptance_planned_end_b',
        'acceptance_actual_end_b',
        'acceptance_notes',
        'billing_status',
        'billed_value',
        'contracted_value',
        'invoice_value',
        'invoice_number',
        'invoice_issued_on',
        'billed_on',
        'service_po',
        'gr_ia',
        'gr_ia_on',
        'is_billed',
        'advance_billing',
        'billed_percentage',
        'billing_notes',
        'os_cancelled',
        'last_changed_at',
        'last_user',
    ];

    /**
     * @var list<string>
     */
    private const DATE_FIELDS = [
        'activation_registered_on',
        'request_date',
        'created_on',
        'access_request_a',
        'access_approval_a',
        'access_start_a',
        'access_end_a',
        'crq_start_a',
        'crq_end_a',
        'access_request_b',
        'access_approval_b',
        'access_start_b',
        'access_end_b',
        'crq_start_b',
        'crq_end_b',
        'mos_planned_on',
        'mos_actual_on',
        'activity_planned_start_a',
        'activity_actual_start_a',
        'activity_planned_end_a',
        'activity_actual_end_a',
        'activity_planned_start_b',
        'activity_actual_start_b',
        'activity_planned_end_b',
        'activity_actual_end_b',
        'activation_planned_start_a',
        'activation_actual_start_a',
        'activation_planned_end_a',
        'activation_actual_end_a',
        'activation_planned_start_b',
        'activation_actual_start_b',
        'activation_planned_end_b',
        'activation_actual_end_b',
        'documentation_planned_start_a',
        'documentation_actual_start_a',
        'documentation_planned_end_a',
        'documentation_actual_end_a',
        'documentation_sent_a',
        'documentation_planned_start_b',
        'documentation_actual_start_b',
        'documentation_planned_end_b',
        'documentation_actual_end_b',
        'documentation_sent_b',
        'acceptance_planned_start_a',
        'acceptance_actual_start_a',
        'acceptance_planned_end_a',
        'acceptance_actual_end_a',
        'acceptance_planned_start_b',
        'acceptance_actual_start_b',
        'acceptance_planned_end_b',
        'acceptance_actual_end_b',
        'invoice_issued_on',
        'billed_on',
        'gr_ia_on',
    ];

    /**
     * @var list<string>
     */
    private const DECIMAL_FIELDS = ['billed_percentage', 'billed_value', 'contracted_value', 'invoice_value'];

    /**
     * @var list<string>
     */
    private const BOOLEAN_FIELDS = ['is_billed', 'os_cancelled'];

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  (Closure(array{processed: int, total: int, created: int, updated: int, unchanged: int, skipped: int}): void)|null  $onProgress
     * @return array{created: int, updated: int, unchanged: int, skipped: int}
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows, ?Closure $onProgress = null): array
    {
        $rows = $this->stripHeaderRow($rows);

        $operations = [];
        $operationIndexByCode = [];
        $errors = [];
        $skipped = 0;

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;

            $attributes = $this->attributesFromRow((array) $row);

            if (blank($attributes['code'])) {
                if ($this->rowIsEmpty((array) $row)) {
                    $skipped++;

                    continue;
                }

                $errors[] = "Linha {$line}: Cód_AFL é obrigatório.";

                continue;
            }

            $key = mb_strtoupper((string) $attributes['code']);

            if (isset($operationIndexByCode[$key])) {
                $operations[$operationIndexByCode[$key]] = $attributes;

                continue;
            }

            $operations[] = $attributes;
            $operationIndexByCode[$key] = array_key_last($operations);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return AnalyticsDatabase::transaction(function () use ($user, $operations, $skipped, $onProgress): array {
            $created = 0;
            $updated = 0;
            $unchanged = 0;
            $inserts = [];
            $total = count($operations) + $skipped;

            $report = function () use ($onProgress, &$created, &$updated, &$unchanged, $skipped, $total): void {
                $onProgress?->__invoke([
                    'processed' => $created + $updated + $unchanged + $skipped,
                    'total' => $total,
                    'created' => $created,
                    'updated' => $updated,
                    'unchanged' => $unchanged,
                    'skipped' => $skipped,
                ]);
            };

            foreach (array_chunk($operations, 250) as $chunk) {
                $latestByCode = $this->latestRecordsForChunk($user, $chunk);

                foreach ($chunk as $attributes) {
                    $key = mb_strtoupper((string) $attributes['code']);
                    $latest = $latestByCode[$key] ?? null;

                    if ($latest !== null && $this->isSameVersion($latest, $attributes)) {
                        $unchanged++;

                        continue;
                    }

                    $inserts[] = [
                        ...$attributes,
                        'user_id' => $user->getKey(),
                    ];

                    $latest === null ? $created++ : $updated++;

                    if (count($inserts) >= 250) {
                        $this->insertRecords($inserts);
                        $inserts = [];
                    }
                }

                $report();
            }

            if ($inserts !== []) {
                $this->insertRecords($inserts);
            }

            $report();

            return [
                'created' => $created,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Fetch the latest stored version for each AFL code in a chunk.
     *
     * @param  array<int, array<string, mixed>>  $operations
     * @return array<string, AnalyticsAflRecord>
     */
    private function latestRecordsForChunk(User $user, array $operations): array
    {
        $codes = array_values(array_unique(array_map(
            fn (array $attributes): string => (string) $attributes['code'],
            $operations,
        )));

        return AnalyticsAflRecord::query()
            ->where('user_id', $user->getKey())
            ->whereIn('code', $codes)
            ->orderBy('id')
            ->get()
            ->keyBy(fn (AnalyticsAflRecord $record): string => mb_strtoupper((string) $record->code))
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

        AnalyticsAflRecord::query()->insert($records);
    }

    /**
     * Remove the header row when the uploaded file includes it.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, array<int, mixed>>
     */
    private function stripHeaderRow(array $rows): array
    {
        $first = $rows[0] ?? null;

        if (is_array($first) && isset($first[0]) && is_string($first[0]) && str_contains(mb_strtolower($first[0]), 'afl')) {
            return array_slice($rows, 1);
        }

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<string, mixed>
     */
    private function attributesFromRow(array $row): array
    {
        $attributes = [];

        foreach (self::FIELDS as $index => $field) {
            $value = $this->normalize($row[$index] ?? null);

            if (in_array($field, self::DATE_FIELDS, true)) {
                $attributes[$field] = $this->parseDate($value);

                continue;
            }

            if (in_array($field, self::DECIMAL_FIELDS, true)) {
                $attributes[$field] = $this->parseDecimal($value);

                continue;
            }

            if (in_array($field, self::BOOLEAN_FIELDS, true)) {
                $attributes[$field] = $this->parseBoolean($value);

                continue;
            }

            if ($field === 'last_changed_at') {
                $attributes[$field] = $this->parseDateTime($value);

                continue;
            }

            $attributes[$field] = $value;
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function isSameVersion(AnalyticsAflRecord $latest, array $attributes): bool
    {
        foreach ($attributes as $field => $value) {
            $current = $latest->getAttribute($field);

            if (in_array($field, self::DATE_FIELDS, true)) {
                $current = $current?->format('Y-m-d');

                if ($current !== $value) {
                    return false;
                }

                continue;
            }

            if (in_array($field, self::DECIMAL_FIELDS, true)) {
                if (($current === null) !== ($value === null)) {
                    return false;
                }

                if ($current !== null && abs((float) $current - (float) $value) > 0.001) {
                    return false;
                }

                continue;
            }

            if (in_array($field, self::BOOLEAN_FIELDS, true)) {
                if (($current === null) !== ($value === null)) {
                    return false;
                }

                if ($current !== null && (bool) $current !== (bool) $value) {
                    return false;
                }

                continue;
            }

            if ($field === 'last_changed_at') {
                $current = $current?->format('Y-m-d H:i:s');

                if ($current !== $value) {
                    return false;
                }

                continue;
            }

            if ((string) ($current ?? '') !== (string) ($value ?? '')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
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

    private function parseDateTime(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d H:i:s');
            } catch (Throwable) {
                return null;
            }
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->format('Y-m-d H:i:s');
                }
            } catch (Throwable) {
                // Try the next format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function parseDate(?string $value): ?string
    {
        $dateTime = $this->parseDateTime($value);

        return $dateTime === null ? null : substr($dateTime, 0, 10);
    }

    private function parseDecimal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';

        if ($value === '' || $value === '-') {
            return null;
        }

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (string) round((float) $value, 2) : null;
    }

    private function parseBoolean(?string $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return match (mb_strtolower($value)) {
            'true', '1', 'sim', 'yes' => true,
            'false', '0', 'não', 'nao', 'no' => false,
            default => null,
        };
    }
}
