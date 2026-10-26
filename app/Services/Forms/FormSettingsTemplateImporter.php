<?php

namespace App\Services\Forms;

use App\Models\FormTemplate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FormSettingsTemplateImporter
{
    /**
     * Parse and validate the rows of an imported configuration template.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function import(array $rows): array
    {
        $types = [];
        $statuses = [];
        $view = null;
        $perPage = null;
        $templateName = null;
        $errors = [];

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;
            $key = $this->normalizeKey($row['configuracao'] ?? null);
            $value = $this->normalizeValue($row['valor'] ?? null);

            if (in_array($key, ['tipo', 'tipos', 'tipo de formulario', 'tipos de formulario', 'tipo do formulario'], true)) {
                $types[] = ['value' => $value, 'line' => $line];

                continue;
            }

            if (in_array($key, ['status', 'statuses', 'status de formulario', 'status de formularios', 'status do formulario'], true)) {
                $statuses[] = ['value' => $value, 'line' => $line];

                continue;
            }

            if (in_array($key, ['visualizacao', 'visualizacao padrao', 'visualizacao do formulario', 'view'], true)) {
                $view = $value;

                continue;
            }

            if (in_array($key, ['itens por pagina', 'itens por pagina do formulario', 'itens pagina', 'per page'], true)) {
                $perPage = $value;

                continue;
            }

            if (in_array($key, ['template', 'template padrao', 'modelo', 'modelo padrao'], true)) {
                $templateName = $value;
            }
        }

        $types = $this->validateList($types, 'tipo de formulário', 255, 'Tipo de formulário', $errors);
        $statuses = $this->validateList($statuses, 'status de formulário', 40, 'Status de formulário', $errors);

        $defaultView = $this->parseView($view, $errors);
        $parsedPerPage = $this->parsePerPage($perPage, $errors);
        $defaultTemplateId = $this->resolveTemplateId($templateName, $errors);

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return [
            'types' => $types,
            'statuses' => $statuses,
            'default_template_id' => $defaultTemplateId,
            'default_view' => $defaultView,
            'per_page' => $parsedPerPage,
        ];
    }

    /**
     * Validate a list of values and return only the values.
     *
     * @param  array<int, array{value: string, line: int}>  $rows
     * @param  array<int, string>  $errors
     * @return array<int, string>
     */
    private function validateList(array $rows, string $label, int $maxLength, string $heading, array &$errors): array
    {
        if ($rows === []) {
            $errors[] = "Informe ao menos um {$label} no template (linha \"{$heading}\").";

            return [];
        }

        $values = [];
        $seen = [];

        foreach ($rows as $row) {
            $value = $row['value'];

            if ($value === '') {
                $errors[] = "Linha {$row['line']}: informe o {$label}.";

                continue;
            }

            if (Str::length($value) > $maxLength) {
                $errors[] = "Linha {$row['line']}: o {$label} deve ter no máximo {$maxLength} caracteres.";

                continue;
            }

            $normalized = $this->normalizeKey($value);

            if (isset($seen[$normalized])) {
                $errors[] = "Linha {$row['line']}: o {$label} \"{$value}\" está repetido.";

                continue;
            }

            $seen[$normalized] = true;
            $values[] = $value;
        }

        return $values;
    }

    /**
     * Resolve the default view from its key or label.
     *
     * @param  array<int, string>  $errors
     */
    private function parseView(?string $value, array &$errors): string
    {
        if ($value === null || $value === '') {
            $errors[] = 'Informe a visualização padrão no template.';

            return FormModuleSettings::DEFAULT_VIEW;
        }

        return match ($this->normalizeKey($value)) {
            'table', 'tabela' => 'table',
            'cards', 'card' => 'cards',
            'compact', 'lista', 'list' => 'compact',
            default => $this->invalidView($value, $errors),
        };
    }

    /**
     * Record an invalid view and return the default value.
     *
     * @param  array<int, string>  $errors
     */
    private function invalidView(string $value, array &$errors): string
    {
        $errors[] = "A visualização \"{$value}\" é inválida. Use Tabela, Cards ou Lista.";

        return FormModuleSettings::DEFAULT_VIEW;
    }

    /**
     * Resolve the number of items per page.
     *
     * @param  array<int, string>  $errors
     */
    private function parsePerPage(?string $value, array &$errors): int
    {
        if ($value === null || $value === '') {
            $errors[] = 'Informe a quantidade de itens por página no template.';

            return FormModuleSettings::DEFAULT_PER_PAGE;
        }

        if (! preg_match('/^\s*(\d+)/', $value, $matches)) {
            $errors[] = 'A quantidade de itens por página deve ser 12, 24 ou 36.';

            return FormModuleSettings::DEFAULT_PER_PAGE;
        }

        $perPage = (int) $matches[1];

        if (! in_array($perPage, FormModuleSettings::PER_PAGE_OPTIONS, true)) {
            $errors[] = 'A quantidade de itens por página deve ser 12, 24 ou 36.';

            return FormModuleSettings::DEFAULT_PER_PAGE;
        }

        return $perPage;
    }

    /**
     * Resolve the default template by its name or identifier.
     *
     * @param  array<int, string>  $errors
     */
    private function resolveTemplateId(?string $value, array &$errors): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $template = FormTemplate::query()->find((int) $value);

            if ($template !== null) {
                return $template->getKey();
            }
        }

        $normalized = $this->normalizeKey($value);

        $template = FormTemplate::query()
            ->get(['id', 'name'])
            ->first(fn (FormTemplate $template): bool => $this->normalizeKey($template->name) === $normalized);

        if ($template === null) {
            $errors[] = "O template padrão \"{$value}\" não foi encontrado.";

            return null;
        }

        return $template->getKey();
    }

    /**
     * Normalize a heading or value for comparison.
     */
    private function normalizeKey(mixed $value): string
    {
        return Str::of((string) $value)->ascii()->lower()->squish()->toString();
    }

    /**
     * Normalize a spreadsheet cell into a trimmed string.
     */
    private function normalizeValue(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return is_string($value) ? trim($value) : '';
    }
}
