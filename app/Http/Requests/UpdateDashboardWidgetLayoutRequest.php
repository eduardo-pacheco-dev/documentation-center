<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDashboardWidgetLayoutRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'direction' => ['nullable', Rule::in(['up', 'down'])],
            'span' => ['nullable', 'integer', 'between:1,3'],
        ];
    }

    /**
     * Get the validation messages in Brazilian Portuguese.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'direction.in' => 'A direção informada é inválida.',
            'span.integer' => 'A largura do widget deve ser um número.',
            'span.between' => 'A largura do widget deve ficar entre 1 e 3 colunas.',
        ];
    }
}
