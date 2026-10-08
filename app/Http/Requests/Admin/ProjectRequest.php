<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

abstract class ProjectRequest extends FormRequest
{
    /**
     * The project the request acts on.
     */
    public function project(): Project
    {
        return $this->route('project');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can($this->ability(), $this->project()) === true;
    }

    /**
     * The policy ability required to fulfil the request.
     */
    protected function ability(): string
    {
        return 'plan';
    }
}
