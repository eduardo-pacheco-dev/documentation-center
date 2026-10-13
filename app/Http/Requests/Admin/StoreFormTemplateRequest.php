<?php

namespace App\Http\Requests\Admin;

use App\Models\FormTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', FormTemplate::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'form_id' => [
                'required',
                'integer',
                Rule::exists('forms', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()?->getKey())
                    ->whereNull('deleted_at')),
            ],
        ];
    }
}
