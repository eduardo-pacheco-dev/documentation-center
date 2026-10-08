<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveDocumentsRequest extends FormRequest
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
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => [
                'integer',
                Rule::exists('documents', 'id')
                    ->where(fn (Builder $query): Builder => $query
                        ->where('user_id', $this->user()->getKey())
                        ->whereNull('deleted_at')),
            ],
            'folder' => [
                'nullable',
                'integer',
                Rule::exists('folders', 'id')
                    ->where(fn (Builder $query): Builder => $query
                        ->where('user_id', $this->user()->getKey())
                        ->whereNull('deleted_at')),
            ],
        ];
    }
}
