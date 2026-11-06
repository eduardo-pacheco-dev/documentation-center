<?php

namespace Plugins\Nokia\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteRequest;
use Plugins\Nokia\Models\NokiaSite;

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

        $prepared = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $normalized = StoreNokiaSiteRequest::normalizeChecklistValues($row);

            $validator = Validator::make(
                $normalized,
                StoreNokiaSiteRequest::fieldRules(),
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = 'Linha '.($index + 2).': '.$message;
                }

                continue;
            }

            $prepared[] = $validator->validated();
        }

        if ($errors !== []) {
            throw new NokiaSiteImportException($errors);
        }

        DB::transaction(function () use ($prepared): void {
            foreach ($prepared as $data) {
                NokiaSite::query()->create($data);
            }
        });

        return count($prepared);
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
            'tarefas_enviadas' => 'checklist_tarefas_enviadas',
            'fam_entrega' => 'checklist_fam_entrega',
            'fam_instalacao' => 'checklist_fam_instalacao',
            'fam_panoramica' => 'checklist_fam_panoramica',
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
