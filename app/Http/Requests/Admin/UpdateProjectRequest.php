<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProjectRequest extends ProjectRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
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

    /**
     * The policy ability required to fulfil the request.
     */
    protected function ability(): string
    {
        return 'update';
    }
}
