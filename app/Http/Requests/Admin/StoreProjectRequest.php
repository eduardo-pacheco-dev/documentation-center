<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreProjectRequest extends FormRequest
{
    /**
     * Normalize blank numeric fields to their database defaults.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('budget') && blank($this->input('budget'))) {
            $this->merge(['budget' => 0]);
        }

        if ($this->has('priority') && blank($this->input('priority'))) {
            $this->merge(['priority' => 500]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('projects', 'code')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', new Enum(ProjectStatus::class)],
            'start_date' => ['required', 'date'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'erb_id' => [
                'nullable',
                'integer',
                Rule::exists('erbs', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
        ];
    }
}
