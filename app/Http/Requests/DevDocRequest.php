<?php

namespace App\Http\Requests;

use App\Enums\DevDocCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DevDocRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::enum(DevDocCategory::class)],
            'summary' => ['nullable', 'string', 'max:300'],
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
