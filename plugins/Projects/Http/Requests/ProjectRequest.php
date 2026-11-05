<?php

namespace Plugins\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Plugins\Projects\Models\Project;

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
