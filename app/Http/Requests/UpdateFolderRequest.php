<?php

namespace App\Http\Requests;

use App\Models\Folder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('folder'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Folder $folder */
        $folder = $this->route('folder');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders', 'name')
                    ->where(fn (Builder $query): Builder => $query
                        ->where('user_id', $this->user()->getKey())
                        ->where('parent_id', $folder->parent_id)
                        ->whereNull('deleted_at'))
                    ->ignore($folder->getKey()),
            ],
        ];
    }
}
