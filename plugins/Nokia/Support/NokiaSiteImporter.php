<?php

namespace Plugins\Nokia\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaDatesRequest;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaTask;

final class NokiaSiteImporter
{
    public const MAX_SITES = 500;

    /**
     * Import the sites from a CSV or Excel file.
     *
     * @return int The number of imported sites.
     *
     * @throws NokiaSiteImportException
     */
    public function import(UploadedFile $file): int
    {
        $rows = $this->rows($file);

        if (count($rows) > self::MAX_SITES) {
            throw new NokiaSiteImportException([
                'A importação aceita no máximo '.self::MAX_SITES.' sites por arquivo.',
            ]);
        }

        $taskColumns = $this->taskColumns();

        $prepared = [];
        $errors = [];
        $seenOc = [];

        foreach ($rows as $index => $row) {
            $tasks = $this->extractTaskValues($row, $taskColumns);

            $normalized = StoreNokiaSiteRequest::normalizeChecklistValues($row);
            $normalized = $this->normalizeDates($normalized);

            $validator = Validator::make(
                $normalized,
                array_merge(
                    StoreNokiaSiteRequest::fieldRules(requireOc: true, requireUniqueOc: false),
                    $this->dateRules(),
                ),
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = 'Linha '.($index + 2).': '.$message;
                }

                continue;
            }

            $data = $validator->validated();
            $oc = isset($data['oc']) ? trim((string) $data['oc']) : '';
            $data['oc'] = $oc !== '' ? $oc : null;

            if ($data['oc'] !== null) {
                if (array_key_exists($data['oc'], $seenOc)) {
                    $errors[] = 'Linha '.($index + 2).': O OC '.$data['oc'].' já foi usado na linha '.$seenOc[$data['oc']].'.';

                    continue;
                }

                $seenOc[$data['oc']] = $index + 2;
            }

            $prepared[] = [
                'data' => $data,
                'tasks' => $tasks,
            ];
        }

        if ($errors !== []) {
            throw new NokiaSiteImportException($errors);
        }

        DB::transaction(function () use ($prepared): void {
            foreach ($prepared as $item) {
                $site = NokiaSite::query()->updateOrCreate(
                    ['oc' => $item['data']['oc']],
                    $item['data'],
                );

                foreach ($item['tasks'] as $taskId => $completed) {
                    $site->tasks()->syncWithoutDetaching([
                        $taskId => ['completed_at' => $completed ? now() : null],
                    ]);
                }
            }
        });

        return count($prepared);
    }

    /**
     * Map the normalized header of every configurable activity to its task.
     *
     * Both "Tarefa - Nome" and "Atividade - Nome" headers are accepted.
     *
     * @return array<string, int>
     */
    private function taskColumns(): array
    {
        $columns = [];

        foreach (NokiaTask::query()->orderBy('position')->orderBy('name')->get() as $task) {
            foreach (['Tarefa', 'Atividade'] as $prefix) {
                $columns[$this->normalizeHeader($prefix.' '.$task->name)] = $task->getKey();
            }
        }

        return $columns;
    }

    /**
     * Pull the configurable activity values out of the row.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $columns
     * @return array<int, bool>
     */
    private function extractTaskValues(array &$row, array $columns): array
    {
        $values = [];

        foreach ($row as $key => $value) {
            if (! isset($columns[$key])) {
                continue;
            }

            $values[$columns[$key]] = StoreNokiaSiteRequest::parseBoolean($value);
            unset($row[$key]);
        }

        return $values;
    }

    /**
     * The validation rules of the date columns accepted by the import.
     *
     * @return array<string, array<int, string>>
     */
    private function dateRules(): array
    {
        $rules = [];

        foreach (NokiaSite::DATE_FIELDS as $field) {
            $rules[$field] = ['nullable', 'date'];
        }

        return $rules;
    }

    /**
     * Normalize the date columns present in the row.
     *
     * Columns absent from the file are left untouched so updating a site by OC
     * does not clear dates that were already filled in.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeDates(array $row): array
    {
        foreach (NokiaSite::DATE_FIELDS as $field) {
            if (! array_key_exists($field, $row)) {
                continue;
            }

            $value = $row[$field];

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);
            $row[$field] = $value === '' ? null : UpdateNokiaDatesRequest::normalizeDate($value);
        }

        return $row;
    }

    /**
     * Parse the uploaded file into associative rows.
     *
     * @return list<array<string, string>>
     *
     * @throws NokiaSiteImportException
     */
    private function rows(UploadedFile $file): array
    {
        return in_array(strtolower($file->getClientOriginalExtension()), ['xlsx', 'xls'], true)
            ? $this->rowsFromSpreadsheet($file)
            : $this->rowsFromCsv($file);
    }

    /**
     * Parse the uploaded CSV into associative rows.
     *
     * @return list<array<string, string>>
     *
     * @throws NokiaSiteImportException
     */
    private function rowsFromCsv(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new NokiaSiteImportException(['Não foi possível ler o arquivo enviado.']);
        }

        $content = $this->toUtf8($content);

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (trim($content) === '') {
            throw new NokiaSiteImportException(['O arquivo está vazio.']);
        }

        $delimiter = $this->detectDelimiter($content);

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new NokiaSiteImportException(['Não foi possível processar o arquivo enviado.']);
        }

        fwrite($stream, $content);
        rewind($stream);

        try {
            $header = fgetcsv($stream, 0, $delimiter, '"', '');

            if ($header === false) {
                throw new NokiaSiteImportException(['Não foi possível ler o cabeçalho do arquivo.']);
            }

            $header = $this->mapHeader($header);

            if (! in_array('site_id', $header, true)) {
                throw new NokiaSiteImportException([
                    'O arquivo precisa conter a coluna site_id (ou "Site ID").',
                ]);
            }

            if (! in_array('oc', $header, true)) {
                throw new NokiaSiteImportException([
                    'O arquivo precisa conter a coluna oc (ou "OC").',
                ]);
            }

            $rows = [];

            while (($values = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
                if ($this->isEmptyRow($values)) {
                    continue;
                }

                $row = [];

                foreach ($header as $index => $key) {
                    if ($key === '') {
                        continue;
                    }

                    $row[$key] = trim((string) ($values[$index] ?? ''));
                }

                $rows[] = $row;
            }
        } finally {
            fclose($stream);
        }

        if ($rows === []) {
            throw new NokiaSiteImportException(['O arquivo não possui nenhum site para importar.']);
        }

        return $rows;
    }

    /**
     * Parse the first sheet of an Excel file into associative rows.
     *
     * @return list<array<string, string>>
     *
     * @throws NokiaSiteImportException
     */
    private function rowsFromSpreadsheet(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable) {
            throw new NokiaSiteImportException(['Não foi possível ler a planilha enviada.']);
        }

        try {
            $sheet = $spreadsheet->getSheet(0);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();

            if ($highestRow < 2) {
                throw new NokiaSiteImportException(['A planilha não possui nenhum site para importar.']);
            }

            $header = $this->mapHeader(
                $sheet->rangeToArray('A1:'.$highestColumn.'1', null, true, false, false)[0] ?? [],
            );

            if (! in_array('site_id', $header, true)) {
                throw new NokiaSiteImportException([
                    'A planilha precisa conter a coluna Site ID (ou site_id).',
                ]);
            }

            if (! in_array('oc', $header, true)) {
                throw new NokiaSiteImportException([
                    'A planilha precisa conter a coluna OC (ou oc).',
                ]);
            }

            $rows = [];

            for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
                $values = $sheet->rangeToArray(
                    'A'.$rowNumber.':'.$highestColumn.$rowNumber,
                    null,
                    true,
                    false,
                    false,
                )[0] ?? [];

                if ($this->isEmptyRow($values)) {
                    continue;
                }

                $row = [];

                foreach ($header as $index => $key) {
                    if ($key === '') {
                        continue;
                    }

                    $row[$key] = trim((string) ($values[$index] ?? ''));
                }

                $rows[] = $row;
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        if ($rows === []) {
            throw new NokiaSiteImportException(['A planilha não possui nenhum site para importar.']);
        }

        return $rows;
    }

    /**
     * Map the header values to the site fields.
     *
     * @param  array<int, mixed>  $values
     * @return list<string>
     */
    private function mapHeader(array $values): array
    {
        $aliases = $this->headerAliases();

        return array_map(function (mixed $value) use ($aliases): string {
            $key = $this->normalizeHeader((string) $value);

            return $aliases[$key] ?? $key;
        }, array_values($values));
    }

    /**
     * Convert a value to UTF-8 when the file comes from Excel.
     */
    private function toUtf8(string $content): string
    {
        if (mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        return mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
    }

    /**
     * Detect the delimiter used by the first line.
     */
    private function detectDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\r\n") ?: '';
        $candidates = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($candidates);

        $delimiter = array_key_first($candidates);
        $count = $candidates[$delimiter];

        return $count > 0 ? $delimiter : ',';
    }

    /**
     * Normalize a column name.
     */
    private function normalizeHeader(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    /**
     * Map the accepted headers to the site fields.
     *
     * @return array<string, string>
     */
    private function headerAliases(): array
    {
        $aliases = [
            'site' => 'site_id',
            'siteid' => 'site_id',
            'id_site' => 'site_id',
            'end' => 'end_id',
            'endid' => 'end_id',
            'id_end' => 'end_id',
            'os_entrega' => 'os_fam_entrega',
            'os_de_entrega' => 'os_fam_entrega',
            'ordem_servico_entrega' => 'os_fam_entrega',
            'os_instalacao' => 'os_fam_instalacao',
            'os_de_instalacao' => 'os_fam_instalacao',
            'ordem_servico_instalacao' => 'os_fam_instalacao',
            'fam_entrega' => 'checklist_fam_entrega',
            'fam_instalacao' => 'checklist_fam_instalacao',
            'fam_panoramica' => 'checklist_fam_panoramica',
            'mos_plan' => 'mos_plan_at',
            'mos_real' => 'mos_real_at',
            'instalacao_plan' => 'instalacao_plan_at',
            'instalacao_real' => 'instalacao_real_at',
            'integracao_plan' => 'integracao_plan_at',
            'integracao_real' => 'integracao_real_at',
            'rfa_plan' => 'rfa_plan_at',
            'rfa_real' => 'rfa_real_at',
            'fam_entrega_aprovado' => 'fam_entrega_approved_at',
            'fam_entrega_aprovada' => 'fam_entrega_approved_at',
            'fam_instalacao_aprovado' => 'fam_instalacao_approved_at',
            'fam_instalacao_aprovada' => 'fam_instalacao_approved_at',
            'fam_panoramica_aprovado' => 'fam_panoramica_approved_at',
            'fam_panoramica_aprovada' => 'fam_panoramica_approved_at',
        ];

        foreach (array_keys(config('nokia.checklists', [])) as $field) {
            $aliases[$field] = $field;
        }

        return $aliases;
    }

    /**
     * Check if the row has no filled cells.
     *
     * @param  array<int, mixed>  $values
     */
    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
