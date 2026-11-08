<?php

namespace App\Http\Requests;

use App\Enums\AdminModule;
use App\Navigation\NavigationCatalog;
use App\Navigation\NavigationEntry;
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
        $entries = app(NavigationCatalog::class)->entries($this->user(), $this);

        $available = $entries
            ->map(fn (NavigationEntry $entry): string => $entry->key())
            ->all();

        $allowed = $entries
            ->filter(fn (NavigationEntry $entry): bool => $entry->isToggleable())
            ->map(fn (NavigationEntry $entry): string => $entry->key())
            ->all();

        return [
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in($allowed)],
            'order' => ['nullable', 'array'],
            'order.*' => ['string', 'distinct', Rule::in($available)],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', Rule::in([AdminModule::SECTION_SIDEBAR, AdminModule::SECTION_MODULES])],
        ];
    }
}
