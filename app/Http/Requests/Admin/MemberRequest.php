<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class MemberRequest extends ProjectRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                Rule::exists('users', 'email'),
                Rule::notIn([$this->project()->user_id, $this->user()?->getKey()]),
            ],
            'role' => ['nullable', new Enum(ProjectRole::class)],
        ];
    }

    /**
     * The policy ability required to fulfil the request.
     */
    protected function ability(): string
    {
        return 'manageMembers';
    }
}
