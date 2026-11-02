<?php

namespace Plugins\Stations\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Plugins\Stations\Models\Station;

class StoreStationDocumentRequest extends FormRequest
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
        return [
            'documents' => ['required', 'array', 'min:1', 'max:'.config('stations.documents.max_files', 10)],
            'documents.*' => [
                'required',
                'file',
                'max:'.config('stations.documents.max_size', 20480),
                'mimes:'.implode(',', config('stations.documents.types', [])),
            ],
        ];
    }
}
