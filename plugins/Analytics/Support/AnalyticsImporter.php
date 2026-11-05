<?php

declare(strict_types=1);

namespace Plugins\Analytics\Support;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Plugins\Analytics\Imports\AnalyticsImport;
use Plugins\Analytics\Models\AnalyticsRecord;
use Throwable;

final class AnalyticsImporter
{
    /**
     * Import the records from an uploaded spreadsheet.
     *
     * @return array{created: int, updated: int}
     *
     * @throws AnalyticsImportException
     */
    public function import(UploadedFile $file): array
    {
        $import = new AnalyticsImport($this->delimiter($file));

        try {
            Excel::import($import, $file);
        } catch (Throwable) {
            throw new AnalyticsImportException(['Não foi possível ler a planilha enviada.']);
        }

        $rows = $import->rows;

        if ($rows === []) {
            throw new AnalyticsImportException(['A planilha não possui registros para importar.']);
        }

        $limit = (int) config('analytics.max_import_rows', 5000);

        if (count($rows) > $limit) {
            throw new AnalyticsImportException(["A importação aceita no máximo {$limit} registros por arquivo."]);
        }

        $prepared = [];
        $errors = [];
        $line = 2;

        foreach ($rows as $row) {
            try {
                $data = $this->prepareRow($row);
            } catch (AnalyticsImportException $exception) {
                $errors = [...$errors, ...$exception->errors];

                $line++;

                continue;
            }

            $validator = Validator::make($data, $this->rules(), $this->messages());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Linha {$line}: {$message}";
                }

                $line++;

                continue;
            }

            $prepared[] = $validator->validated();
            $line++;
        }

        if ($errors !== []) {
            throw new AnalyticsImportException($errors);
        }

        return DB::transaction(function () use ($prepared): array {
            $created = 0;
            $updated = 0;

            foreach ($prepared as $data) {
                $record = AnalyticsRecord::query()->updateOrCreate(
                    ['os' => $data['os']],
                    $data,
                );

                $record->wasRecentlyCreated ? $created++ : $updated++;
            }

            return [
                'created' => $created,
                'updated' => $updated,
            ];
        });
    }

    /**
     * Normalize an imported row.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function prepareRow(array $row): array
    {
        $data = [
            'oc' => $this->requiredString($row['oc'] ?? null),
            'os' => $this->requiredString($row['os'] ?? null),
            'data_criacao' => $this->parseDate($row['data_criacao'] ?? null),
            'fornecedor' => $this->requiredString($row['fornecedor'] ?? null),
            'fornecedor_fiscalizacao' => $this->nullableString($row['fornecedor_fiscalizacao'] ?? null),
            'tipo_projeto' => $this->nullableString($row['tipo_projeto'] ?? null),
            'etapa_processo' => $this->nullableString($row['etapa_processo'] ?? null),
            'tipo_elemento' => $this->nullableString($row['tipo_elemento'] ?? null),
            'endereco_atendimento' => $this->nullableString($row['endereco_atendimento'] ?? null),
            'cidade' => $this->nullableString($row['cidade'] ?? null),
            'estado' => $this->nullableString($row['estado'] ?? null),
            'node_name' => $this->requiredString($row['node_name'] ?? null),
            'localizacao' => $this->nullableString($row['localizacao'] ?? null),
            'operador' => $this->nullableString($row['operador'] ?? null),
            'status' => $this->requiredString($row['status'] ?? null),
        ];

        if ($data['estado'] !== null) {
            $data['estado'] = mb_strtoupper($data['estado']);
        }

        return $data;
    }

    /**
     * Validation rules applied to every imported row.
     *
     * @return array<string, list<string>>
     */
    private function rules(): array
    {
        return [
            'oc' => ['required', 'string', 'max:100'],
            'os' => ['required', 'string', 'max:100'],
            'data_criacao' => ['required', 'date'],
            'fornecedor' => ['required', 'string', 'max:120'],
            'fornecedor_fiscalizacao' => ['nullable', 'string', 'max:120'],
            'tipo_projeto' => ['nullable', 'string', 'max:120'],
            'etapa_processo' => ['nullable', 'string', 'max:120'],
            'tipo_elemento' => ['nullable', 'string', 'max:120'],
            'endereco_atendimento' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
            'node_name' => ['required', 'string', 'max:120'],
            'localizacao' => ['nullable', 'string', 'max:120'],
            'operador' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Validation messages in Brazilian Portuguese.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'data_criacao.date' => 'A data de criação informada é inválida.',
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
        ];
    }

    private function requiredString(mixed $value): string
    {
        return trim((string) $value);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * Parse Excel serial dates or the usual Brazilian date formats.
     */
    private function parseDate(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject((float) $value))->toDateString();
        }

        $value = trim((string) $value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);

                if ($date !== false && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (Throwable) {
                // Try the next accepted format.
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Detect the delimiter used by CSV uploads.
     */
    private function delimiter(UploadedFile $file): string
    {
        if (! in_array(mb_strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            return ',';
        }

        $content = (string) file_get_contents($file->getRealPath());
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
