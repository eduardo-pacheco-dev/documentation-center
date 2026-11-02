<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Plugins\Stations\Enums\StationLinkStatus;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationLink;

class StoreStationLinkRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $station = $this->route('station');
        $userId = $this->user()?->getKey();

        return [
            'station_b_id' => [
                'required',
                'integer',
                Rule::notIn([$station->getKey()]),
                Rule::exists('stations', 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId)->whereNull('deleted_at')),
            ],
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

    /**
     * Reject links that would duplicate an existing pair of stations.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                $station = $this->route('station');
                $otherId = $this->input('station_b_id');

                if (! $station instanceof Station || ! is_numeric($otherId)) {
                    return;
                }

                $exists = StationLink::query()
                    ->ownedBy($this->user())
                    ->where(function ($query) use ($station, $otherId) {
                        $query->where(fn ($inner) => $inner
                            ->where('station_a_id', $station->getKey())
                            ->where('station_b_id', $otherId))
                            ->orWhere(fn ($inner) => $inner
                                ->where('station_a_id', $otherId)
                                ->where('station_b_id', $station->getKey()));
                    })
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('station_b_id', 'Já existe um enlace entre estas stations.');
                }
            },
        ];
    }
}
