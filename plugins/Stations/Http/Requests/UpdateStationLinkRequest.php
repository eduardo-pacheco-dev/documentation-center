<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Plugins\Stations\Enums\StationLinkStatus;
use Plugins\Stations\Models\Station;

class UpdateStationLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $station = $this->route('station');

        return $station instanceof Station
            && $this->user()?->can('update', $station) === true;
    }

    /**
     * Ensure the link belongs to the given station and to the user.
     */
    protected function prepareForValidation(): void
    {
        $station = $this->route('station');
        $link = $this->route('link');

        abort_unless(
            $station instanceof Station
                && $link !== null
                && $link->user_id === $this->user()?->getKey()
                && in_array($station->getKey(), [$link->station_a_id, $link->station_b_id], true),
            404,
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:60'],
            'equipment_a' => ['nullable', 'string', 'max:255'],
            'equipment_b' => ['nullable', 'string', 'max:255'],
            'frequency' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'bandwidth' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'capacity' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'polarization' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', new Enum(StationLinkStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
