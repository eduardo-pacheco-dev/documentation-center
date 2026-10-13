<?php

namespace App\Http\Requests;

use App\Enums\ShortLinkType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShortLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ShortLinkType::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'redirect_url' => ['nullable', 'required_if:type,redirect', 'url', 'max:2048'],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'document_ids' => ['sometimes', 'array'],
            'document_ids.*' => [
                'integer',
                Rule::exists('documents', 'id')->where('user_id', $this->user()->getKey()),
            ],
        ];
    }
}
