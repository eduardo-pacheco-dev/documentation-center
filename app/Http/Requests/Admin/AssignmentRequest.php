<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class AssignmentRequest extends ProjectRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->project()->getKey();

        return [
            'task_id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->where('project_id', $projectId),
            ],
            'resource_id' => [
                'required',
                'integer',
                Rule::exists('resources', 'id')->where('project_id', $projectId),
            ],
            'units' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
