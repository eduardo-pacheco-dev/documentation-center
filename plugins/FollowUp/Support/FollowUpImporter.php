<?php

namespace Plugins\FollowUp\Support;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Http\Requests\StoreFollowUpRequest;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpAssignee;

final class FollowUpImporter
{
    public const MAX_ROWS = 500;

    /**
     * Import the follow-ups from an uploaded CSV or Excel file.
     *
     * @return int The number of imported follow-ups.
     *
     * @throws FollowUpImportException
     */
    public function import(UploadedFile $file, int $userId): int
    {
        $rows = $this->rows($file);

        if (count($rows) > self::MAX_ROWS) {
            throw new FollowUpImportException([
                'A importação aceita no máximo '.self::MAX_ROWS.' follow-ups por arquivo.',
            ]);
        }

        $prepared = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $data = $this->prepareRow($row);
            } catch (InvalidArgumentException $exception) {
                $errors[] = 'Linha '.($index + 2).': '.$exception->getMessage();

                continue;
            }

            $validator = Validator::make(
                $data,
                StoreFollowUpRequest::fieldRules(),
                StoreFollowUpRequest::fieldMessages(),
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
            throw new FollowUpImportException($errors);
        }

        DB::transaction(function () use ($prepared, $userId): void {
            foreach ($prepared as $data) {
                $followUp = FollowUp::query()->create([
                    ...$data,
                    'created_by' => $userId,
                ]);

                $followUp->updates()->create([
                    'user_id' => $userId,
                    'body' => 'Follow-up importado.',
                    'status_to' => $data['status'],
                ]);
            }
        });

        return count($prepared);
    }

    /**
     * Normalize an imported row.
     *
     * @param  array<string, string>  $row
     * @return array<string, mixed>
     */
    private function prepareRow(array $row): array
    {
        $status = $this->normalizeEnumValue(
            (string) ($row['status'] ?? ''),
            FollowUpStatus::options(),
            FollowUpStatus::Pending->value,
        );

        $priority = $this->normalizeEnumValue(
            (string) ($row['priority'] ?? ''),
            FollowUpPriority::options(),
            FollowUpPriority::Medium->value,
        );

        $data = [
            'title' => trim((string) ($row['title'] ?? '')),
            'code' => $this->nullableString($row['code'] ?? null),
            'description' => $this->nullableString($row['description'] ?? null),
            'client' => $this->nullableString($row['client'] ?? null),
            'project' => $this->nullableString($row['project'] ?? null),
            'status' => $status,
            'priority' => $priority,
            'due_date' => $this->parseDate($row['due_date'] ?? null),
            'completed_at' => $this->parseDate($row['completed_at'] ?? null),
            'assignee_id' => null,
        ];

        if ($status === FollowUpStatus::Completed->value && blank($data['completed_at'])) {
            $data['completed_at'] = today()->toDateString();
        }

        if ($status !== FollowUpStatus::Completed->value) {
            $data['completed_at'] = null;
        }

        $assigneeValue = trim((string) ($row['assignee'] ?? ''));

        if ($assigneeValue !== '') {
            $assignee = FollowUpAssignee::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($assigneeValue)])
                ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($assigneeValue)])
                ->first();

            if ($assignee === null) {
                throw new InvalidArgumentException("O responsável \"{$assigneeValue}\" não foi encontrado. Cadastre-o antes de importar.");
            }

            $data['assignee_id'] = $assignee->getKey();
        }

        return $data;
    }

    /**
     * Normalize statuses and priorities by value or label.
     *
     * @param  array<string, string>  $options
     */
    private function normalizeEnumValue(string $value, array $options, string $default): string
    {
        $normalized = $this->normalizeText($value);

        if ($normalized === '') {
            return $default;
        }

        foreach ($options as $optionValue => $label) {
            if ($normalized === $this->normalizeText($optionValue) || $normalized === $this->normalizeText($label)) {
                return $optionValue;
            }
        }

        throw new InvalidArgumentException("\"{$value}\" não é um valor válido.");
    }

    /**
     * Normalize text for comparisons.
     */
    private function normalizeText(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $value = trim((string) $value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);

                if ($date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                // Try the next accepted format.
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            throw new InvalidArgumentException("\"{$value}\" não é uma data válida.");
        }
    }

    /**
     * Parse the uploaded file into associative rows.
     *
     * @return list<array<string, string>>
     *
     * @throws FollowUpImportException
     */
    private function rows(UploadedFile $file): array
    {
        return in_array(strtolower($file->getClientOriginalExtension()), ['xlsx', 'xls'], true)
            ? $this->rowsFromSpreadsheet($file)
            : $this->rowsFromCsv($file);
    }

    /**
     * @return list<array<string, string>>
     */
    private function rowsFromCsv(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new FollowUpImportException(['Não foi possível ler o arquivo enviado.']);
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (trim($content) === '') {
            throw new FollowUpImportException(['O arquivo está vazio.']);
        }

        $delimiter = $this->detectDelimiter($content);
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new FollowUpImportException(['Não foi possível processar o arquivo enviado.']);
        }

        fwrite($stream, $content);
        rewind($stream);

        try {
            $header = fgetcsv($stream, 0, $delimiter, '"', '');

            if ($header === false) {
                throw new FollowUpImportException(['Não foi possível ler o cabeçalho do arquivo.']);
            }

            $header = $this->mapHeader($header);

            if (! in_array('title', $header, true)) {
                throw new FollowUpImportException(['O arquivo precisa conter a coluna Título (ou title).']);
            }

            $rows = [];

            while (($values = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
                if ($this->isEmptyRow($values)) {
                    continue;
                }

                $rows[] = $this->combineRow($header, $values);
            }
        } finally {
            fclose($stream);
        }

        if ($rows === []) {
            throw new FollowUpImportException(['O arquivo não possui nenhum follow-up para importar.']);
        }

        return $rows;
    }

    /**
     * @return list<array<string, string>>
     */
    private function rowsFromSpreadsheet(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable) {
            throw new FollowUpImportException(['Não foi possível ler a planilha enviada.']);
        }

        try {
            $sheet = $spreadsheet->getSheet(0);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();

            if ($highestRow < 2) {
                throw new FollowUpImportException(['A planilha não possui nenhum follow-up para importar.']);
            }

            $header = $this->mapHeader(
                $sheet->rangeToArray('A1:'.$highestColumn.'1', null, true, false, false)[0] ?? [],
            );

            if (! in_array('title', $header, true)) {
                throw new FollowUpImportException(['A planilha precisa conter a coluna Título (ou title).']);
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

                $rows[] = $this->combineRow($header, $values);
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        if ($rows === []) {
            throw new FollowUpImportException(['A planilha não possui nenhum follow-up para importar.']);
        }

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<string>
     */
    private function mapHeader(array $values): array
    {
        $aliases = [
            'codigo' => 'code',
            'code' => 'code',
            'titulo' => 'title',
            'title' => 'title',
            'descricao' => 'description',
            'description' => 'description',
            'cliente' => 'client',
            'client' => 'client',
            'projeto' => 'project',
            'project' => 'project',
            'status' => 'status',
            'prioridade' => 'priority',
            'priority' => 'priority',
            'prazo' => 'due_date',
            'due_date' => 'due_date',
            'data_de_prazo' => 'due_date',
            'concluido_em' => 'completed_at',
            'completed_at' => 'completed_at',
            'data_de_conclusao' => 'completed_at',
            'responsavel' => 'assignee',
            'assignee' => 'assignee',
        ];

        return array_map(function (mixed $value) use ($aliases): string {
            $key = $this->normalizeText((string) $value);

            return $aliases[$key] ?? $key;
        }, array_values($values));
    }

    /**
     * @param  list<string>  $header
     * @param  array<int, mixed>  $values
     * @return array<string, string>
     */
    private function combineRow(array $header, array $values): array
    {
        $row = [];

        foreach ($header as $index => $key) {
            if ($key === '') {
                continue;
            }

            $row[$key] = trim((string) ($values[$index] ?? ''));
        }

        return $row;
    }

    /**
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

        return $candidates[$delimiter] > 0 ? $delimiter : ',';
    }
}
