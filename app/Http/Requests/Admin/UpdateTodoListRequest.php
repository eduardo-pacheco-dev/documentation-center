<?php

namespace App\Http\Requests\Admin;

use App\Enums\TodoColor;
use App\Enums\TodoIcon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTodoListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('todoList')) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'color' => ['required', new Enum(TodoColor::class)],
            'icon' => ['required', new Enum(TodoIcon::class)],
        ];
    }
}
