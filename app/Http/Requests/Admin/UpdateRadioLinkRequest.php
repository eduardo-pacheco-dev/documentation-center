<?php

namespace App\Http\Requests\Admin;

use App\Enums\RadioLinkPolarization;
use App\Enums\RadioLinkStatus;
use App\Models\RadioLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateRadioLinkRequest extends FormRequest
{
    /**
     * The radio link the request acts on.
     */
    public function radioLink(): RadioLink
    {
        return $this->route('radioLink');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->radioLink()) === true;
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
            'code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('radio_links', 'code')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->whereNull('deleted_at')
                    ->ignore($this->radioLink()->getKey()),
            ],
            'erb_a_id' => [
                'required',
                'integer',
                'different:erb_b_id',
                Rule::exists('erbs', 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->whereNull('deleted_at'),
            ],
            'erb_b_id' => [
                'required',
                'integer',
                Rule::exists('erbs', 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->whereNull('deleted_at'),
            ],
            'equipment_a' => ['nullable', 'string', 'max:255'],
            'equipment_b' => ['nullable', 'string', 'max:255'],
            'frequency' => ['nullable', 'numeric', 'between:0,300'],
            'bandwidth' => ['nullable', 'numeric', 'between:0,1000'],
            'capacity' => ['nullable', 'numeric', 'between:0,100000'],
            'polarization' => ['nullable', new Enum(RadioLinkPolarization::class)],
            'status' => ['nullable', new Enum(RadioLinkStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
