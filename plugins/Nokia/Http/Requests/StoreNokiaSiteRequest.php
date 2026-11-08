<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Plugins\Nokia\Models\NokiaSite;

class StoreNokiaSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalize the checklist values before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(self::normalizeChecklistValues($this->all()));
    }

    /**
     * Normalize the checklist values from a request or import row.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function normalizeChecklistValues(array $values): array
    {
        foreach (array_keys(config('nokia.checklists', [])) as $field) {
            $values[$field] = self::parseBoolean($values[$field] ?? false);
        }

        return $values;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::fieldRules();
    }

    /**
     * The validation rules shared with the bulk import.
     *
     * @return array<string, mixed>
     */
    public static function fieldRules(
        ?NokiaSite $ignore = null,
        bool $requireOc = false,
        bool $requireUniqueOc = true,
    ): array {
        $ocRules = [
            $requireOc ? 'required' : 'nullable',
            'string',
            'max:100',
        ];

        if ($requireUniqueOc) {
            $ocRules[] = Rule::unique('nokia_sites', 'oc')->ignore($ignore?->getKey());
        }

        return [
            'site_id' => ['required', 'string', 'max:100'],
            'nokia_rom_id' => ['nullable', 'integer', 'exists:nokia_roms,id'],
            'end_id' => ['nullable', 'string', 'max:100'],
            'oc' => $ocRules,
            'os_fam_entrega' => ['nullable', 'string', 'max:100'],
            'os_fam_instalacao' => ['nullable', 'string', 'max:100'],
            'checklist_fam_entrega' => ['boolean'],
            'checklist_fam_instalacao' => ['boolean'],
            'checklist_fam_panoramica' => ['boolean'],
        ];
    }

    /**
     * Interpret the common spreadsheet values as a boolean.
     */
    public static function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return $value !== 0;
        }

        $normalized = Str::of((string) $value)->ascii()->lower()->trim()->toString();

        return in_array($normalized, ['1', 'sim', 's', 'x', 'yes', 'y', 'true', 'on', 'concluido', 'concluida', 'aprovado', 'aprovada', 'ok'], true);
    }
}
