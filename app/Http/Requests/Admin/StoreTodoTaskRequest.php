<?php

namespace App\Http\Requests\Admin;

use App\Enums\TodoRepeat;
use App\Models\TodoTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTodoTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TodoTask::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'todo_list_id' => [
                'required',
                'integer',
                Rule::exists('todo_lists', 'id')->where('user_id', $this->user()->getKey()),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
            'remind_at' => ['nullable', 'date'],
            'repeat_type' => ['nullable', new Enum(TodoRepeat::class)],
            'is_important' => ['nullable', 'boolean'],
            'my_day' => ['nullable', 'boolean'],
        ];
    }
}
