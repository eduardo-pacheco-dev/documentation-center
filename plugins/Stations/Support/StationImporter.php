<?php

namespace Plugins\Stations\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Plugins\Stations\Http\Requests\StoreStationRequest;
use Plugins\Stations\Models\Station;

final class StationImporter
{
    public const MAX_STATIONS = 100;

    /**
     * Import the stations from a CSV file.
     *
     * @return int The number of imported stations.
     *
     * @throws StationImportException
     */
    public function import(UploadedFile $file): int
    {
        $rows = $this->rows($file);

        if (count($rows) > self::MAX_STATIONS) {
            throw new StationImportException([
                'A importação aceita no máximo '.self::MAX_STATIONS.' estações por arquivo.',
            ]);
        }

        $prepared = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $normalized = StoreStationRequest::normalizeValues($row);

            $validator = Validator::make(
                $normalized,
                StoreStationRequest::fieldRules(),
                StoreStationRequest::fieldMessages(),
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
            throw new StationImportException($errors);
        }

        DB::transaction(function () use ($prepared): void {
            foreach ($prepared as $data) {
                Station::query()->create($data);
            }
        });

        return count($prepared);
    }

    /**
     * Parse the uploaded CSV into associative rows.
     *
     * @return list<array<string, string>>
     *
     * @throws StationImportException
     */
    private function rows(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new StationImportException(['Não foi possível ler o arquivo enviado.']);
        }

        $content = $this->toUtf8($content);

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (trim($content) === '') {
            throw new StationImportException(['O arquivo está vazio.']);
        }

        $delimiter = $this->detectDelimiter($content);

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new StationImportException(['Não foi possível processar o arquivo enviado.']);
        }

        fwrite($stream, $content);
        rewind($stream);

        try {
            $header = fgetcsv($stream, 0, $delimiter, '"', '');

            if ($header === false) {
                throw new StationImportException(['Não foi possível ler o cabeçalho do arquivo.']);
            }

            $aliases = $this->headerAliases();

            $header = array_map(function (mixed $value) use ($aliases): string {
                $key = $this->normalizeHeader((string) $value);

                return $aliases[$key] ?? $key;
            }, $header);

            if (! in_array('site_id', $header, true)) {
                throw new StationImportException([
                    'O arquivo precisa conter a coluna site_id (ou "ID do site").',
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
            throw new StationImportException(['O arquivo não possui nenhuma estação para importar.']);
        }

        return $rows;
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
     * Map the accepted headers to the station fields.
     *
     * @return array<string, string>
     */
    private function headerAliases(): array
    {
        $aliases = [];

        foreach (config('stations.fields', []) as $group) {
            foreach ($group['fields'] as $field) {
                $aliases[$field['name']] = $field['name'];
                $aliases[$this->normalizeHeader($field['label'])] = $field['name'];
            }
        }

        return array_merge($aliases, [
            'site' => 'site_id',
            'siteid' => 'site_id',
            'id_site' => 'site_id',
            'station' => 'station_id',
            'lat' => 'latitude',
            'lng' => 'longitude',
            'lon' => 'longitude',
            'long' => 'longitude',
            'uf' => 'estado',
            'cidade' => 'municipio',
            'endereco' => 'logradouro',
        ]);
    }

    /**
     * Check if the row has no filled cells.
     *
     * @param  list<string|null>  $values
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
