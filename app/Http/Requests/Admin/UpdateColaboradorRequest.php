<?php

namespace App\Http\Requests\Admin;

use App\Enums\ColaboradorStatus;
use App\Models\Colaborador;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateColaboradorRequest extends FormRequest
{
    /**
     * The colaborador the request acts on.
     */
    public function colaborador(): Colaborador
    {
        return $this->route('colaborador');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->colaborador()) === true;
    }

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
            'role' => ['nullable', 'string', 'max:255'],
            'document' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('colaboradores', 'document')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->whereNull('deleted_at')
                    ->ignore($this->colaborador()->getKey()),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', new Enum(ColaboradorStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
