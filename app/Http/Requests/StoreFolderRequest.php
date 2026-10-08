<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders', 'name')
                    ->where(fn (Builder $query): Builder => $query
                        ->where('user_id', $this->user()->getKey())
                        ->where('parent_id', $this->input('parent_id'))
                        ->whereNull('deleted_at')),
            ],
            'parent_id' => [
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
