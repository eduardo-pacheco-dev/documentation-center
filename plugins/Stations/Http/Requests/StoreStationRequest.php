<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Plugins\Stations\Enums\StationOperator;
use Plugins\Stations\Enums\StationStatus;
use Plugins\Stations\Enums\StationTechnology;
use Plugins\Stations\Models\Station;

class StoreStationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Station::class) === true;
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
                'nullable',
                'string',
                'max:60',
                Rule::unique('stations', 'code')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->whereNull('deleted_at'),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'string', 'max:255', 'required_without:end_id'],
            'end_id' => ['nullable', 'string', 'max:60', 'required_without:site_id'],
            'operator' => ['nullable', new Enum(StationOperator::class)],
            'technology' => ['nullable', new Enum(StationTechnology::class)],
            'assigned_to' => ['nullable', 'email', 'max:255'],
            'nokia_smp_id' => ['nullable', 'string', 'max:60'],
            'service_order_id' => ['nullable', 'string', 'max:60'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', new Enum(StationStatus::class)],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip' => ['nullable', 'string', 'max:9'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
