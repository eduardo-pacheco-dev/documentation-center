<?php

namespace App\Http\Requests;

use App\Enums\AdminModule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModulesRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowed = array_map(
            fn (AdminModule $module): string => $module->value,
            AdminModule::toggleableFor($this->user()),
        );

        return [
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in($allowed)],
        ];
    }
}
