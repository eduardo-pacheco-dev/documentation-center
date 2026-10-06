<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RegisterRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreUserRequest extends RegisterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'is_admin' => ['sometimes', 'boolean'],
        ];
    }
}
