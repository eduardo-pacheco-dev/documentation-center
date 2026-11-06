<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Plugins\Nokia\Models\NokiaSite;

class UpdateNokiaDatesRequest extends FormRequest
{
    /**
     * The date fields available for a site.
     *
     * @var list<string>
     */
    public const FIELDS = NokiaSite::DATE_FIELDS;

    /**
     * The date formats accepted from the text inputs.
     *
     * @var list<string>
     */
    private const DATE_FORMATS = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'Y/m/d'];

    /**
     * Normalize the typed dates before validation.
     */
    protected function prepareForValidation(): void
    {
        $dates = [];

        foreach (self::FIELDS as $field) {
            $value = $this->input($field);

            if (is_string($value) && trim($value) !== '') {
                $dates[$field] = self::normalizeDate(trim($value));
            }
        }

        $this->merge($dates);
    }

    /**
     * Convert a typed date to the database format.
     */
    public static function normalizeDate(string $value): string
    {
        foreach (self::DATE_FORMATS as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);
            } catch (\Throwable) {
                continue;
            }

            if ($date->format($format) === $value) {
                return $date->toDateString();
            }
        }

        return $value;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (self::FIELDS as $field) {
            $rules[$field] = ['nullable', 'date'];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            '*.date' => 'Informe uma data válida.',
        ];
    }
}
