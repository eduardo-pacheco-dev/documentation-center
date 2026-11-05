<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'order' => ['nullable', 'array'],
            'order.*' => ['string', 'max:100'],
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
            'order.array' => 'A ordem dos widgets é inválida.',
            'order.*.string' => 'A ordem dos widgets é inválida.',
            'span.integer' => 'A largura do widget deve ser um número.',
            'span.between' => 'A largura do widget deve ficar entre 1 e 3 colunas.',
        ];
    }
}
