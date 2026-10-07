<?php

namespace App\Http\Requests;

use App\Enums\ShortLinkType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShortLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('shortLink'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', Rule::enum(ShortLinkType::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
